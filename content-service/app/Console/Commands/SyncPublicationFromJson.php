<?php

namespace App\Console\Commands;

use App\Models\Content;
use App\Models\ContentAdditionalFile;
use App\Models\ContentAdditionalImage;
use App\Models\ContentAdditionalLink;
use App\Models\ContentAdditionalVideo;
use App\Models\ContentDetailPublication;
use App\Models\ContentTag;
use App\Models\MsLayout;
use App\Models\MsTag;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class SyncPublicationFromJson extends Command
{
    protected $signature = 'sync:publication-from-json
        {--path= : Path to the local JSON file (optional fallback)}
        {--page=1 : Page number to crawl from API}
        {--limit=8 : Number of publications to fetch from API, or limit of publications to sync}
        {--all-pages : Crawl all pages from API}
        {--content-id= : Existing content_id to attach publications to}
        {--menu-id= : Menu ID for creating new Content parent (parent_type=Menu)}
        {--dry-run : Preview what would be inserted without writing}
        {--output-sql= : Output SQL inserts to specified file instead of injecting to DB}';

    protected $description = 'Sync publication data from external API (or JSON file) into content-service tables or SQL file';

    public function handle(): int
    {
        $path = $this->option('path');
        $contentId = $this->option('content-id');
        $menuId = $this->option('menu-id');
        $dryRun = (bool) $this->option('dry-run');
        $limit = $this->option('limit') ? (int) $this->option('limit') : 8;
        $page = (int) ($this->option('page') ?? 1);
        $allPages = (bool) $this->option('all-pages');
        $outputSql = $this->option('output-sql');

        $publications = [];
        $tags = [];
        $data = [];

        // 1. Load data from local JSON or API
        if ($path) {
            if (! file_exists($path)) {
                $this->error("File not found: {$path}");

                return self::FAILURE;
            }

            $json = json_decode(file_get_contents($path), true);
            if (! $json || ! isset($json['data'])) {
                $this->error('Invalid JSON structure — missing data key');

                return self::FAILURE;
            }

            $data = $json['data'];
            $publications = $data['publication'] ?? [];
            $tags = $data['list_tag'] ?? [];

            if ($this->option('limit')) {
                $publications = array_slice($publications, 0, (int) $this->option('limit'));
            }
        } else {
            $crawlResult = $this->crawlApi($page, $limit, $allPages);
            if ($crawlResult === null) {
                return self::FAILURE;
            }

            $data = $crawlResult['data'];
            $publications = $crawlResult['publications'];
            $tags = $crawlResult['tags'];
        }

        $this->info('Found '.count($publications).' publications and '.count($tags).' tags');

        if ($dryRun) {
            $this->warn('--- DRY RUN — no database changes ---');
        }

        // 2. Resolve content_id and layout_id
        $resolvedContentId = $this->resolveContentId($contentId, $menuId, $data, $dryRun || ! empty($outputSql));
        if (! $resolvedContentId && empty($outputSql)) {
            $this->error('Failed to resolve content_id. Provide --content-id or --menu-id.');

            return self::FAILURE;
        }

        if ($resolvedContentId) {
            $this->info("Using content_id: {$resolvedContentId}");
        }

        $layoutId = null;
        try {
            $layout = MsLayout::findByType('publication_detail');
            $layoutId = $layout?->id;
        } catch (\Exception $e) {
            $this->warn('Could not find layout for publication_detail — layout_id will be null');
        }

        // 3. SQL output mode
        if ($outputSql) {
            return $this->handleSqlOutput(
                $publications,
                $tags,
                $resolvedContentId,
                $layoutId,
                $outputSql,
                $path ? false : true
            );
        }

        // 4. DB Sync mode
        $tagMap = $this->syncTags($tags, $dryRun);

        $created = 0;
        $skipped = 0;
        $errors = 0;

        $bar = $this->output->createProgressBar(count($publications));
        $bar->start();

        foreach ($publications as $pub) {
            try {
                $slug = $pub['slug'] ?? Str::slug($pub['title'] ?? '');

                // Check if publication with this slug already exists
                $existing = ContentDetailPublication::where('slug', $slug)->first();
                if ($existing) {
                    $skipped++;
                    $bar->advance();

                    continue;
                }

                // If crawled from API without detail, fetch detail endpoint
                if (! $path) {
                    $detailData = $this->fetchPublicationDetail($slug);
                    if ($detailData) {
                        $pub = $this->mergePublicationDetail($pub, $detailData);
                    }
                }

                if (! $dryRun) {
                    DB::beginTransaction();

                    $publication = ContentDetailPublication::create([
                        'content_id' => $resolvedContentId,
                        'title' => $pub['title'] ?? '',
                        'slug' => $slug,
                        'headline' => $pub['headline'] ?? null,
                        'description' => $pub['content_detail'] ?? null,
                        'is_publicated' => $pub['is_publicated'] ?? false,
                        'thumbnail_path' => $pub['thumbnail_path'] ?? null,
                        'publish_date' => $pub['publish_date'] ?? now(),
                        'layout_id' => $layoutId,
                        'is_slider' => $pub['is_slider'] ?? false,
                        'has_additional_image' => ! empty($pub['additional_image']),
                        'has_additional_video' => ! empty($pub['additional_video']),
                        'has_additional_file' => ! empty($pub['additional_file']),
                        'has_additional_link' => ! empty($pub['additional_link']),
                    ]);

                    if (! empty($pub['tag'])) {
                        $this->syncPublicationTags($publication, $pub['tag'], $tagMap);
                    }

                    if (! empty($pub['additional_image'])) {
                        $this->syncAdditionalImages($publication, $pub['additional_image']);
                    }

                    if (! empty($pub['additional_video'])) {
                        $this->syncAdditionalVideos($publication, $pub['additional_video']);
                    }

                    if (! empty($pub['additional_file'])) {
                        $this->syncAdditionalFiles($publication, $pub['additional_file']);
                    }

                    if (! empty($pub['additional_link'])) {
                        $this->syncAdditionalLinks($publication, $pub['additional_link']);
                    }

                    DB::commit();
                }

                $created++;
            } catch (\Exception $e) {
                if (! $dryRun) {
                    DB::rollBack();
                }
                $this->newLine();
                $this->error("Error syncing '{$pub['title']}': {$e->getMessage()}");
                $errors++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->table(
            ['Metric', 'Count'],
            [
                ['Created', $created],
                ['Skipped (existing)', $skipped],
                ['Errors', $errors],
                ['Total processed', count($publications)],
            ]
        );

        return $errors > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function crawlApi(int $page, ?int $limit, bool $allPages): ?array
    {
        $baseUrl = 'https://ilmate.kemenperin.go.id/back/public-content-service/api/contentMenu';
        $perPage = $limit ?? 8;
        $currentPage = $page;
        $allPublications = [];
        $allTags = [];
        $dataMeta = [];

        do {
            $this->info("Fetching list from API (page {$currentPage}, limit {$perPage})...");
            try {
                $response = Http::timeout(60)->retry(2, 1000)->get($baseUrl, [
                    'type' => 'sub-menu',
                    'slug' => ['publikasi', 'informasi-industri'],
                    'portal' => 'pusat',
                    'page' => $currentPage,
                    'limit' => $perPage,
                    'tag' => '',
                    'order_dir' => 'desc',
                    'order_by' => 'publish_date',
                    'lang' => 'id',
                ]);
            } catch (\Exception $e) {
                $this->error("Failed to connect to API: {$e->getMessage()}");

                return null;
            }

            if (! $response->successful()) {
                $this->error("API returned error status {$response->status()} on page {$currentPage}");

                return null;
            }

            $json = $response->json();
            $data = $json['data'] ?? [];
            $publications = $data['publication'] ?? [];
            $tags = $data['list_tag'] ?? [];

            foreach ($tags as $tag) {
                $allTags[] = $tag;
            }

            foreach ($publications as $pub) {
                $allPublications[] = $pub;
            }

            $dataMeta = $data;

            if (! $allPages) {
                break;
            }

            $total = (int) ($data['meta']['total'] ?? 0);
            $totalPages = $perPage > 0 ? (int) ceil($total / $perPage) : 1;

            $this->info("Fetched page {$currentPage} of {$totalPages} ({$total} total items)");

            $currentPage++;
            if ($currentPage > $totalPages) {
                break;
            }
        } while (true);

        return [
            'data' => $dataMeta,
            'publications' => $allPublications,
            'tags' => $allTags,
        ];
    }

    private function fetchPublicationDetail(string $slug): ?array
    {
        $detailUrl = 'https://ilmate.kemenperin.go.id/back/public-content-service/api/content/publication';
        try {
            $response = Http::timeout(60)->retry(2, 500)->get($detailUrl, [
                'type' => 'sub-menu',
                'slug' => $slug,
                'portal' => 'pusat',
            ]);

            if ($response->successful()) {
                return $response->json('data') ?? [];
            }
        } catch (\Exception $e) {
            $this->warn("Detail API request exception for slug '{$slug}': {$e->getMessage()}");
        }

        return null;
    }

    private function mergePublicationDetail(array $pub, array $detail): array
    {
        $pub['content_detail'] = $detail['content_detail'] ?? ($pub['content_detail'] ?? null);
        $pub['headline'] = $detail['headline'] ?? ($pub['headline'] ?? null);
        $pub['thumbnail_path'] = $detail['thumbnail_path'] ?? ($pub['thumbnail_path'] ?? null);
        $pub['publish_date'] = $detail['publish_date'] ?? ($pub['publish_date'] ?? null);
        $pub['is_slider'] = $detail['is_slider'] ?? ($pub['is_slider'] ?? false);
        $pub['tag'] = ! empty($detail['tag']) ? $detail['tag'] : ($pub['tag'] ?? []);
        $pub['additional_image'] = $detail['content_additional_photo'] ?? ($pub['additional_image'] ?? []);
        $pub['additional_video'] = $detail['content_additional_video'] ?? ($pub['additional_video'] ?? []);
        $pub['additional_file'] = $detail['content_additional_file'] ?? ($pub['additional_file'] ?? []);
        $pub['additional_link'] = $detail['content_additional_link'] ?? ($pub['additional_link'] ?? []);

        return $pub;
    }

    private function handleSqlOutput(
        array $publications,
        array $tags,
        ?string $resolvedContentId,
        ?int $layoutId,
        string $outputSql,
        bool $fetchDetailFromApi
    ): int {
        $this->info("Generating SQL file at {$outputSql}...");

        if ($fetchDetailFromApi) {
            $this->info('Fetching publication details from API...');
            $bar = $this->output->createProgressBar(count($publications));
            $bar->start();

            foreach ($publications as $i => $pub) {
                $slug = $pub['slug'] ?? Str::slug($pub['title'] ?? '');
                $detailData = $this->fetchPublicationDetail($slug);
                if ($detailData) {
                    $publications[$i] = $this->mergePublicationDetail($pub, $detailData);
                }
                $bar->advance();
            }
            $bar->finish();
            $this->newLine();
        }

        $this->generateSqlOutput($publications, $tags, $resolvedContentId, $layoutId, $outputSql);
        $this->info("SQL output written successfully to {$outputSql} (".count($publications).' publications)');

        return self::SUCCESS;
    }

    private function generateSqlOutput(
        array $publications,
        array $tags,
        ?string $resolvedContentId,
        ?int $layoutId,
        string $outputPath
    ): void {
        $dir = dirname($outputPath);
        if ($dir && ! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $handle = fopen($outputPath, 'w');
        if (! $handle) {
            throw new \RuntimeException("Could not open file for writing: {$outputPath}");
        }

        fwrite($handle, "-- ========================================================\n");
        fwrite($handle, "-- Publication SQL Dump\n");
        fwrite($handle, "-- Generated: ".date('Y-m-d H:i:s')."\n");
        fwrite($handle, "-- Source: ILMATE Public Content Service API\n");
        fwrite($handle, "-- ========================================================\n\n");
        fwrite($handle, "SET FOREIGN_KEY_CHECKS = 0;\n");
        fwrite($handle, "START TRANSACTION;\n\n");

        if ($resolvedContentId) {
            fwrite($handle, "SET @target_content_id = {$resolvedContentId};\n");
        } else {
            fwrite($handle, "SET @target_content_id = (SELECT id FROM contents WHERE type = 'publication' LIMIT 1);\n");
        }

        if ($layoutId) {
            fwrite($handle, "SET @target_layout_id = {$layoutId};\n");
        } else {
            fwrite($handle, "SET @target_layout_id = (SELECT id FROM ms_layout WHERE type = 'publication_detail' LIMIT 1);\n");
        }
        fwrite($handle, "\n");

        // Master Tags
        if (! empty($tags)) {
            fwrite($handle, "-- Master Tags\n");
            foreach ($tags as $tag) {
                $tagName = is_array($tag) ? ($tag['title'] ?? '') : $tag;
                if (empty($tagName)) {
                    continue;
                }
                $escapedTag = $this->escapeSql($tagName);
                fwrite($handle, "INSERT INTO ms_tags (`title`, `is_active`, `created_at`, `updated_at`)\n");
                fwrite($handle, "SELECT {$escapedTag}, 1, NOW(), NOW()\n");
                fwrite($handle, "WHERE NOT EXISTS (SELECT 1 FROM ms_tags WHERE `title` = {$escapedTag});\n\n");
            }
        }

        // Publications
        foreach ($publications as $pub) {
            $slug = $pub['slug'] ?? Str::slug($pub['title'] ?? '');
            $title = $pub['title'] ?? '';
            $headline = $pub['headline'] ?? null;
            $description = $pub['content_detail'] ?? null;
            $isPublicated = ! empty($pub['is_publicated']) ? 1 : 0;
            $thumbnail = $pub['thumbnail_path'] ?? null;
            $publishDate = $pub['publish_date'] ?? date('Y-m-d H:i:s');
            $isSlider = ! empty($pub['is_slider']) ? 1 : 0;

            $images = $pub['additional_image'] ?? [];
            $videos = $pub['additional_video'] ?? [];
            $files = $pub['additional_file'] ?? [];
            $links = $pub['additional_link'] ?? [];
            $pubTags = $pub['tag'] ?? [];

            $hasImage = ! empty($images) ? 1 : 0;
            $hasVideo = ! empty($videos) ? 1 : 0;
            $hasFile = ! empty($files) ? 1 : 0;
            $hasLink = ! empty($links) ? 1 : 0;

            fwrite($handle, "-- --------------------------------------------------------\n");
            fwrite($handle, "-- Publication: {$title}\n");
            fwrite($handle, "-- --------------------------------------------------------\n");
            fwrite($handle, "INSERT INTO content_detail_publications (\n");
            fwrite($handle, "    `content_id`, `title`, `slug`, `headline`, `description`,\n");
            fwrite($handle, "    `is_publicated`, `thumbnail_path`, `publish_date`, `layout_id`,\n");
            fwrite($handle, "    `is_slider`, `has_additional_image`, `has_additional_video`,\n");
            fwrite($handle, "    `has_additional_file`, `has_additional_link`, `has_additional_text`,\n");
            fwrite($handle, "    `created_at`, `updated_at`\n");
            fwrite($handle, ") SELECT\n");
            fwrite($handle, "    @target_content_id,\n");
            fwrite($handle, "    {$this->escapeSql($title)},\n");
            fwrite($handle, "    {$this->escapeSql($slug)},\n");
            fwrite($handle, "    {$this->escapeSql($headline)},\n");
            fwrite($handle, "    {$this->escapeSql($description)},\n");
            fwrite($handle, "    {$isPublicated},\n");
            fwrite($handle, "    {$this->escapeSql($thumbnail)},\n");
            fwrite($handle, "    {$this->escapeSql($publishDate)},\n");
            fwrite($handle, "    @target_layout_id,\n");
            fwrite($handle, "    {$isSlider},\n");
            fwrite($handle, "    {$hasImage},\n");
            fwrite($handle, "    {$hasVideo},\n");
            fwrite($handle, "    {$hasFile},\n");
            fwrite($handle, "    {$hasLink},\n");
            fwrite($handle, "    0,\n");
            fwrite($handle, "    NOW(),\n");
            fwrite($handle, "    NOW()\n");
            fwrite($handle, "WHERE NOT EXISTS (SELECT 1 FROM content_detail_publications WHERE `slug` = {$this->escapeSql($slug)});\n\n");

            fwrite($handle, "SET @pub_id = (SELECT id FROM content_detail_publications WHERE `slug` = {$this->escapeSql($slug)} LIMIT 1);\n\n");

            // Publication tags
            foreach ($pubTags as $pt) {
                $ptName = is_array($pt) ? ($pt['title'] ?? '') : $pt;
                if (empty($ptName)) {
                    continue;
                }
                $escapedPt = $this->escapeSql($ptName);
                fwrite($handle, "INSERT INTO ms_tags (`title`, `is_active`, `created_at`, `updated_at`)\n");
                fwrite($handle, "SELECT {$escapedPt}, 1, NOW(), NOW()\n");
                fwrite($handle, "WHERE NOT EXISTS (SELECT 1 FROM ms_tags WHERE `title` = {$escapedPt});\n");

                fwrite($handle, "INSERT INTO content_tags (`content_type`, `content_id`, `tag_id`, `created_at`, `updated_at`)\n");
                fwrite($handle, "SELECT 'App\\\\Models\\\\ContentDetailPublication', @pub_id, (SELECT id FROM ms_tags WHERE `title` = {$escapedPt} LIMIT 1), NOW(), NOW()\n");
                fwrite($handle, "WHERE @pub_id IS NOT NULL\n");
                fwrite($handle, "  AND NOT EXISTS (\n");
                fwrite($handle, "      SELECT 1 FROM content_tags\n");
                fwrite($handle, "      WHERE `content_type` = 'App\\\\Models\\\\ContentDetailPublication'\n");
                fwrite($handle, "        AND `content_id` = @pub_id\n");
                fwrite($handle, "        AND `tag_id` = (SELECT id FROM ms_tags WHERE `title` = {$escapedPt} LIMIT 1)\n");
                fwrite($handle, "  );\n\n");
            }

            // Additional images
            foreach ($images as $i => $img) {
                $imgPath = is_string($img) ? $img : ($img['image_path'] ?? $img['path'] ?? $img['file_path'] ?? '');
                $imgDesc = is_array($img) ? ($img['description'] ?? null) : null;
                $imgOrder = is_array($img) && isset($img['order']) ? (int) $img['order'] : ($i + 1);

                fwrite($handle, "INSERT INTO content_additional_images (`content_type`, `content_id`, `image_path`, `description`, `order`, `created_at`, `updated_at`)\n");
                fwrite($handle, "SELECT 'App\\\\Models\\\\ContentDetailPublication', @pub_id, {$this->escapeSql($imgPath)}, {$this->escapeSql($imgDesc)}, {$imgOrder}, NOW(), NOW()\n");
                fwrite($handle, "WHERE @pub_id IS NOT NULL;\n\n");
            }

            // Additional videos
            foreach ($videos as $i => $vid) {
                $vidTitle = is_array($vid) ? ($vid['title'] ?? null) : null;
                $vidType = is_array($vid) ? ($vid['type'] ?? 'external') : 'external';
                $vidUrl = is_string($vid) ? $vid : ($vid['url'] ?? $vid['video_path'] ?? '');
                $vidPath = is_array($vid) ? ($vid['video_path'] ?? null) : null;
                $vidOrder = is_array($vid) && isset($vid['order']) ? (int) $vid['order'] : ($i + 1);

                fwrite($handle, "INSERT INTO content_additional_videos (`content_type`, `content_id`, `title`, `type`, `url`, `video_path`, `order`, `created_at`, `updated_at`)\n");
                fwrite($handle, "SELECT 'App\\\\Models\\\\ContentDetailPublication', @pub_id, {$this->escapeSql($vidTitle)}, {$this->escapeSql($vidType)}, {$this->escapeSql($vidUrl)}, {$this->escapeSql($vidPath)}, {$vidOrder}, NOW(), NOW()\n");
                fwrite($handle, "WHERE @pub_id IS NOT NULL;\n\n");
            }

            // Additional files
            foreach ($files as $i => $file) {
                $fileTitle = is_array($file) ? ($file['title'] ?? null) : null;
                $filePath = is_string($file) ? $file : ($file['file_path'] ?? $file['path'] ?? '');
                $fileDesc = is_array($file) ? ($file['description'] ?? null) : null;
                $fileOrder = is_array($file) && isset($file['order']) ? (int) $file['order'] : ($i + 1);

                fwrite($handle, "INSERT INTO content_additional_files (`content_type`, `content_id`, `title`, `file_path`, `description`, `order`, `created_at`, `updated_at`)\n");
                fwrite($handle, "SELECT 'App\\\\Models\\\\ContentDetailPublication', @pub_id, {$this->escapeSql($fileTitle)}, {$this->escapeSql($filePath)}, {$this->escapeSql($fileDesc)}, {$fileOrder}, NOW(), NOW()\n");
                fwrite($handle, "WHERE @pub_id IS NOT NULL;\n\n");
            }

            // Additional links
            foreach ($links as $i => $link) {
                $linkTitle = is_array($link) ? ($link['title'] ?? null) : null;
                $linkUrl = is_string($link) ? $link : ($link['link'] ?? $link['url'] ?? '');
                $linkOrder = is_array($link) && isset($link['order']) ? (int) $link['order'] : ($i + 1);

                fwrite($handle, "INSERT INTO content_additional_links (`content_type`, `content_id`, `title`, `link`, `order`, `created_at`, `updated_at`)\n");
                fwrite($handle, "SELECT 'App\\\\Models\\\\ContentDetailPublication', @pub_id, {$this->escapeSql($linkTitle)}, {$this->escapeSql($linkUrl)}, {$linkOrder}, NOW(), NOW()\n");
                fwrite($handle, "WHERE @pub_id IS NOT NULL;\n\n");
            }
        }

        fwrite($handle, "COMMIT;\n");
        fwrite($handle, "SET FOREIGN_KEY_CHECKS = 1;\n");
        fclose($handle);
    }

    private function escapeSql(?string $value): string
    {
        if ($value === null) {
            return 'NULL';
        }

        $escaped = str_replace(
            ['\\', "\0", "\n", "\r", "'", '"', "\x1a"],
            ['\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'],
            $value
        );

        return "'{$escaped}'";
    }

    private function resolveContentId(?string $contentId, ?int $menuId, array $data, bool $dryRun): ?string
    {
        // If content_id provided directly, verify it exists
        if ($contentId) {
            try {
                $content = Content::find($contentId);
                if ($content) {
                    return (string) $contentId;
                }
            } catch (\Exception $e) {
                // Ignore DB query errors in case DB is unconfigured
            }

            $this->warn("Content ID {$contentId} not found in database — will use directly if in SQL mode");

            return (string) $contentId;
        }

        // Try to find existing publication content
        try {
            $existingContent = Content::where('type', 'publication')->first();
            if ($existingContent) {
                $this->info("Found existing publication content: {$existingContent->id}");

                return (string) $existingContent->id;
            }
        } catch (\Exception $e) {
            $this->warn("Could not query Content table: {$e->getMessage()}");
        }

        // Create new Content record if menu_id provided
        if ($menuId && ! $dryRun) {
            $contentData = $data['content'] ?? [];
            $content = Content::create([
                'parent_type' => 'App\Models\Menu',
                'parent_id' => $menuId,
                'type' => 'publication',
                'section_title' => $contentData['title'] ?? 'Publication',
                'is_section' => false,
                'is_show_home' => true,
                'is_accessible' => true,
                'is_show_tag' => true,
                'sub_type' => $contentData['sub_type'] ?? null,
                'order' => 0,
            ]);
            $this->info("Created new Content record: {$content->id}");

            return (string) $content->id;
        }

        if (! $menuId && ! $contentId) {
            $this->warn('No existing publication content found. Provide --content-id or --menu-id');
        }

        return null;
    }

    private function syncTags(array $tags, bool $dryRun): array
    {
        $map = [];
        foreach ($tags as $tag) {
            $name = is_array($tag) ? ($tag['title'] ?? '') : $tag;
            if (empty($name)) {
                continue;
            }

            if ($dryRun) {
                $map[$name] = 0;

                continue;
            }
            $msTag = MsTag::firstOrCreate(
                ['title' => $name],
                ['is_active' => true]
            );
            $map[$name] = $msTag->id;
        }

        return $map;
    }

    private function syncPublicationTags(ContentDetailPublication $pub, array $tags, array $tagMap): void
    {
        foreach ($tags as $tag) {
            $tagName = is_array($tag) ? ($tag['title'] ?? '') : $tag;
            if (empty($tagName)) {
                continue;
            }

            $tagId = $tagMap[$tagName] ?? null;
            if (! $tagId) {
                // Try to find or create on the fly
                $msTag = MsTag::firstOrCreate(['title' => $tagName], ['is_active' => true]);
                $tagId = $msTag->id;
            }
            ContentTag::firstOrCreate([
                'content_type' => ContentDetailPublication::class,
                'content_id' => $pub->id,
                'tag_id' => $tagId,
            ]);
        }
    }

    private function syncAdditionalImages(ContentDetailPublication $pub, array $images): void
    {
        foreach ($images as $i => $img) {
            ContentAdditionalImage::create([
                'content_type' => ContentDetailPublication::class,
                'content_id' => $pub->id,
                'image_path' => is_string($img) ? $img : ($img['image_path'] ?? $img['path'] ?? $img['file_path'] ?? ''),
                'description' => is_array($img) ? ($img['description'] ?? null) : null,
                'order' => is_array($img) && isset($img['order']) ? (int) $img['order'] : ($i + 1),
            ]);
        }
    }

    private function syncAdditionalVideos(ContentDetailPublication $pub, array $videos): void
    {
        foreach ($videos as $i => $vid) {
            ContentAdditionalVideo::create([
                'content_type' => ContentDetailPublication::class,
                'content_id' => $pub->id,
                'title' => is_array($vid) ? ($vid['title'] ?? null) : null,
                'type' => is_array($vid) ? ($vid['type'] ?? 'external') : 'external',
                'url' => is_string($vid) ? $vid : ($vid['url'] ?? $vid['video_path'] ?? ''),
                'video_path' => is_array($vid) ? ($vid['video_path'] ?? null) : null,
                'order' => is_array($vid) && isset($vid['order']) ? (int) $vid['order'] : ($i + 1),
            ]);
        }
    }

    private function syncAdditionalFiles(ContentDetailPublication $pub, array $files): void
    {
        foreach ($files as $i => $file) {
            ContentAdditionalFile::create([
                'content_type' => ContentDetailPublication::class,
                'content_id' => $pub->id,
                'title' => is_array($file) ? ($file['title'] ?? null) : null,
                'file_path' => is_string($file) ? $file : ($file['file_path'] ?? $file['path'] ?? ''),
                'description' => is_array($file) ? ($file['description'] ?? null) : null,
                'order' => is_array($file) && isset($file['order']) ? (int) $file['order'] : ($i + 1),
            ]);
        }
    }

    private function syncAdditionalLinks(ContentDetailPublication $pub, array $links): void
    {
        foreach ($links as $i => $link) {
            ContentAdditionalLink::create([
                'content_type' => ContentDetailPublication::class,
                'content_id' => $pub->id,
                'title' => is_array($link) ? ($link['title'] ?? null) : null,
                'link' => is_string($link) ? $link : ($link['link'] ?? $link['url'] ?? ''),
                'order' => is_array($link) && isset($link['order']) ? (int) $link['order'] : ($i + 1),
            ]);
        }
    }
}
