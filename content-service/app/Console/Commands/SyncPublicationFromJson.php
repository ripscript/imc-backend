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
use Illuminate\Support\Str;

class SyncPublicationFromJson extends Command
{
    protected $signature = 'sync:publication-from-json
        {--path= : Path to the JSON file}
        {--content-id= : Existing content_id to attach publications to}
        {--menu-id= : Menu ID for creating new Content parent (parent_type=Menu)}
        {--dry-run : Preview what would be inserted without writing}
        {--limit= : Limit number of publications to sync}';

    protected $description = 'Sync publication data from external JSON response into content-service tables';

    public function handle(): int
    {
        $path = $this->option('path') ?? base_path('contentMenu.json');
        $contentId = $this->option('content-id');
        $menuId = $this->option('menu-id');
        $dryRun = $this->option('dry-run');
        $limit = $this->option('limit') ? (int) $this->option('limit') : null;

        if (!file_exists($path)) {
            $this->error("File not found: {$path}");
            return self::FAILURE;
        }

        $json = json_decode(file_get_contents($path), true);
        if (!$json || !isset($json['data'])) {
            $this->error('Invalid JSON structure — missing data key');
            return self::FAILURE;
        }

        $data = $json['data'];
        $publications = $data['publication'] ?? [];
        $tags = $data['list_tag'] ?? [];

        if ($limit) {
            $publications = array_slice($publications, 0, $limit);
        }

        $this->info("Found " . count($publications) . " publications and " . count($tags) . " tags");

        if ($dryRun) {
            $this->warn('--- DRY RUN — no database changes ---');
        }

        // Resolve or create content_id
        $resolvedContentId = $this->resolveContentId($contentId, $menuId, $data, $dryRun);
        if (!$resolvedContentId) {
            return self::FAILURE;
        }

        $this->info("Using content_id: {$resolvedContentId}");

        // Resolve layout_id for publication_detail
        $layoutId = null;
        try {
            $layout = MsLayout::findByType('publication_detail');
            $layoutId = $layout?->id;
        } catch (\Exception $e) {
            $this->warn('Could not find layout for publication_detail — layout_id will be null');
        }

        // Sync tags first
        $tagMap = $this->syncTags($tags, $dryRun);

        // Sync publications
        $created = 0;
        $skipped = 0;
        $errors = 0;

        $bar = $this->output->createProgressBar(count($publications));
        $bar->start();

        foreach ($publications as $pub) {
            try {
                $slug = $pub['slug'] ?? Str::slug($pub['title']);

                // Check if publication with this slug already exists
                $existing = ContentDetailPublication::where('slug', $slug)->first();
                if ($existing) {
                    $skipped++;
                    $bar->advance();
                    continue;
                }

                if (!$dryRun) {
                    DB::beginTransaction();

                    // 1. Create ContentDetailPublication
                    $publication = ContentDetailPublication::create([
                        'content_id'       => $resolvedContentId,
                        'title'            => $pub['title'] ?? '',
                        'slug'             => $slug,
                        'headline'         => $pub['headline'] ?? null,
                        'description'      => $pub['content_detail'] ?? null,
                        'is_publicated'    => $pub['is_publicated'] ?? false,
                        'thumbnail_path'   => $pub['thumbnail_path'] ?? null,
                        'publish_date'     => $pub['publish_date'] ?? now(),
                        'layout_id'        => $layoutId,
                        'is_slider'        => false,
                        'has_additional_image' => !empty($pub['additional_image']),
                        'has_additional_video' => !empty($pub['additional_video']),
                        'has_additional_file'  => !empty($pub['additional_file']),
                        'has_additional_link'  => !empty($pub['additional_link']),
                    ]);

                    // 2. Sync tags for this publication
                    if (!empty($pub['tag'])) {
                        $this->syncPublicationTags($publication, $pub['tag'], $tagMap);
                    }

                    // 3. Sync additional images
                    if (!empty($pub['additional_image'])) {
                        $this->syncAdditionalImages($publication, $pub['additional_image']);
                    }

                    // 4. Sync additional videos
                    if (!empty($pub['additional_video'])) {
                        $this->syncAdditionalVideos($publication, $pub['additional_video']);
                    }

                    // 5. Sync additional files
                    if (!empty($pub['additional_file'])) {
                        $this->syncAdditionalFiles($publication, $pub['additional_file']);
                    }

                    // 6. Sync additional links
                    if (!empty($pub['additional_link'])) {
                        $this->syncAdditionalLinks($publication, $pub['additional_link']);
                    }

                    DB::commit();
                }

                $created++;
            } catch (\Exception $e) {
                if (!$dryRun) {
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

    private function resolveContentId(?string $contentId, ?int $menuId, array $data, bool $dryRun): ?string
    {
        // If content_id provided directly, verify it exists
        if ($contentId) {
            $content = Content::find($contentId);
            if ($content) {
                return $contentId;
            }
            $this->warn("Content ID {$contentId} not found — will try other methods");
        }

        // Try to find existing publication content
        $existingContent = Content::where('type', 'publication')->first();
        if ($existingContent) {
            $this->info("Found existing publication content: {$existingContent->id}");
            return $existingContent->id;
        }

        // Create new Content record if menu_id provided
        if ($menuId && !$dryRun) {
            $contentData = $data['content'] ?? [];
            $content = Content::create([
                'parent_type'      => 'App\Models\Menu',
                'parent_id'        => $menuId,
                'type'             => 'publication',
                'section_title'    => $contentData['title'] ?? 'Publication',
                'is_section'       => false,
                'is_show_home'     => true,
                'is_accessible'    => true,
                'is_show_tag'      => true,
                'sub_type'         => $contentData['sub_type'] ?? null,
                'order'            => 0,
            ]);
            $this->info("Created new Content record: {$content->id}");
            return $content->id;
        }

        if (!$menuId) {
            $this->error('No existing publication content found. Provide --content-id or --menu-id');
        }

        return null;
    }

    private function syncTags(array $tags, bool $dryRun): array
    {
        $map = [];
        foreach ($tags as $tag) {
            $name = $tag['title'];
            if ($dryRun) {
                $map[$name] = 0;
                continue;
            }
            $msTag = MsTag::firstOrCreate(
                ['name' => $name],
                ['is_active' => true]
            );
            $map[$name] = $msTag->id;
        }
        return $map;
    }

    private function syncPublicationTags(ContentDetailPublication $pub, array $tagNames, array $tagMap): void
    {
        foreach ($tagNames as $tagName) {
            $tagId = $tagMap[$tagName] ?? null;
            if (!$tagId) {
                // Try to find or create on the fly
                $msTag = MsTag::firstOrCreate(['name' => $tagName], ['is_active' => true]);
                $tagId = $msTag->id;
            }
            ContentTag::firstOrCreate([
                'content_type' => ContentDetailPublication::class,
                'content_id'   => $pub->id,
                'tag_id'       => $tagId,
            ]);
        }
    }

    private function syncAdditionalImages(ContentDetailPublication $pub, array $images): void
    {
        foreach ($images as $i => $img) {
            ContentAdditionalImage::create([
                'content_type'  => ContentDetailPublication::class,
                'content_id'    => $pub->id,
                'image_path'    => is_string($img) ? $img : ($img['image_path'] ?? $img['path'] ?? ''),
                'description'   => is_array($img) ? ($img['description'] ?? null) : null,
                'order'         => $i + 1,
            ]);
        }
    }

    private function syncAdditionalVideos(ContentDetailPublication $pub, array $videos): void
    {
        foreach ($videos as $i => $vid) {
            ContentAdditionalVideo::create([
                'content_type'  => ContentDetailPublication::class,
                'content_id'    => $pub->id,
                'title'         => is_array($vid) ? ($vid['title'] ?? null) : null,
                'type'          => is_array($vid) ? ($vid['type'] ?? 'external') : 'external',
                'url'           => is_string($vid) ? $vid : ($vid['url'] ?? $vid['video_path'] ?? ''),
                'video_path'    => is_array($vid) ? ($vid['video_path'] ?? null) : null,
                'order'         => $i + 1,
            ]);
        }
    }

    private function syncAdditionalFiles(ContentDetailPublication $pub, array $files): void
    {
        foreach ($files as $i => $file) {
            ContentAdditionalFile::create([
                'content_type'  => ContentDetailPublication::class,
                'content_id'    => $pub->id,
                'title'         => is_array($file) ? ($file['title'] ?? null) : null,
                'file_path'     => is_string($file) ? $file : ($file['file_path'] ?? $file['path'] ?? ''),
                'description'   => is_array($file) ? ($file['description'] ?? null) : null,
                'order'         => $i + 1,
            ]);
        }
    }

    private function syncAdditionalLinks(ContentDetailPublication $pub, array $links): void
    {
        foreach ($links as $i => $link) {
            ContentAdditionalLink::create([
                'content_type'  => ContentDetailPublication::class,
                'content_id'    => $pub->id,
                'title'         => is_array($link) ? ($link['title'] ?? null) : null,
                'link'          => is_string($link) ? $link : ($link['link'] ?? $link['url'] ?? ''),
                'order'         => $i + 1,
            ]);
        }
    }
}
