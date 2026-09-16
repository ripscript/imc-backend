<?php

namespace App\Console\Commands;

use App\Models\Content;
use App\Models\ContentAdditionalFile;
use App\Models\ContentDetailAgenda;
use App\Models\ContentDetailGallery;
use App\Models\ContentDetailGalleryList;
use App\Models\ContentDetailPublication;
use App\Models\ContentTag;
use App\Models\MsLayout;
use App\Models\MsTag;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SyncContentsFromMysql extends Command
{
    protected $signature = 'sync:contents-from-mysql
        {--publication-content-id= : Existing content_id (type=publication) to attach news/artikel/jurnal}
        {--gallery-content-id= : Existing content_id (type=gallery) to attach infographic}
        {--agenda-content-id= : Existing content_id (type=agenda) to attach event}
        {--dry-run : Preview what would be inserted without writing}
        {--limit= : Limit number of rows to sync}
        {--export-sql= : Write a self-contained Postgres SQL seeder to this path instead of syncing}';

    protected $description = 'Sync legacy MySQL imc-prod.contents rows into content-service tables';

    protected string $actor = 'legacy-import';

    public function handle(): int
    {
        $source = DB::connection('legacy_mysql');
        $dryRun = $this->option('dry-run');
        $limit = $this->option('limit') ? (int) $this->option('limit') : null;
        $exportSql = $this->option('export-sql');

        $rows = $source->table('contents as c')
            ->leftJoin('categories as cat', 'cat.id', '=', 'c.category_id')
            ->leftJoin('category_types as ct', 'ct.id', '=', 'cat.category_type_id')
            ->whereNull('c.deleted_at')
            ->orderBy('c.id')
            ->select('c.*', 'cat.category_name', 'ct.category_type as category_type_name')
            ->get()
            ->map(fn ($r) => (array) $r)
            ->toArray();

        if ($limit) {
            $rows = array_slice($rows, 0, $limit);
        }

        $this->info('Fetched '.count($rows).' non-deleted legacy rows');

        if ($exportSql) {
            $entityConfig = $this->entityConfig();
            foreach (['publication', 'gallery', 'agenda'] as $entity) {
                if (! $entityConfig[$entity]['content_id']) {
                    $this->error("Missing --{$entity}-content-id required for export");
                    return self::FAILURE;
                }
            }

            $sourceTags = $this->fetchSourceTags($source, $rows);
            $sourceFiles = $this->fetchSourceFiles($source, $rows);

            $sql = $this->buildExportSql($rows, $sourceTags, $sourceFiles, $entityConfig);
            $dir = dirname($exportSql);
            if (! is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            file_put_contents($exportSql, $sql);
            $this->info("SQL seeder written to: {$exportSql}");
            return self::SUCCESS;
        }

        if ($dryRun) {
            $this->warn('--- DRY RUN — no database changes ---');
        }

        // Map every row to its target entity + content id
        $entityConfig = $this->entityConfig();

        $sourceTags = $this->fetchSourceTags($source, $rows);
        $sourceFiles = $this->fetchSourceFiles($source, $rows);

        $tagCache = [];
        $kategoriCache = [];
        $seenSlugs = [];
        $bar = $this->output->createProgressBar(count($rows));
        $bar->start();

        $created = $skippedSlug = $skippedType = $skippedNoContent = $errors = 0;

        foreach ($rows as $row) {
            try {
                $entity = $this->entityForType($row['type']);

                if (! $entity) {
                    $skippedType++;
                    $bar->advance();
                    continue;
                }

                if (! $entityConfig[$entity]['content_id']) {
                    $skippedNoContent++;
                    $bar->advance();
                    continue;
                }

                $contentId = (int) $entityConfig[$entity]['content_id'];
                $modelClass = $entityConfig[$entity]['model'];
                $title = trim((string) ($row['title'] ?? ''));

                if ($entity !== 'agenda') {
                    $slug = $this->makeSlug($row['slug'], $title);

                    $table = $entity === 'publication'
                        ? 'content_detail_publications'
                        : 'content_detail_galleries';

                    $dup = isset($seenSlugs[$table][$slug]) || DB::table($table)->where('slug', $slug)->exists();
                    if ($dup) {
                        $skippedSlug++;
                        $bar->advance();
                        continue;
                    }
                    $seenSlugs[$table][$slug] = true;
                } else {
                    $slug = null;
                }

                $isPublicated = strtoupper((string) $row['status_content']) === 'PUBLISH';
                $files = $sourceFiles[$row['id']] ?? [];

                if (! $dryRun) {
                    DB::beginTransaction();
                }

                $createdId = match ($entity) {
                    'publication' => $this->createPublication(
                        $row, $contentId, $slug, $isPublicated, $files,
                        $tagCache, $kategoriCache, $sourceTags, $dryRun
                    ),
                    'gallery' => $this->createGallery(
                        $row, $contentId, $slug, $isPublicated, $tagCache,
                        $sourceTags, $dryRun
                    ),
                    'agenda' => $this->createAgenda($row, $contentId, $isPublicated, $dryRun),
                    default => null,
                };

                if (! $dryRun) {
                    DB::commit();
                }

                if ($createdId) {
                    $created++;
                }
            } catch (\Throwable $e) {
                if (! $dryRun) {
                    DB::rollBack();
                }
                $this->newLine();
                $this->error("Error syncing #{$row['id']} ({$row['type']}): {$e->getMessage()}");
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
                ['Skipped (duplicate slug)', $skippedSlug],
                ['Skipped (unmapped type)', $skippedType],
                ['Skipped (no content-id)', $skippedNoContent],
                ['Errors', $errors],
                ['Total processed', count($rows)],
            ]
        );

        return $errors > 0 ? self::FAILURE : self::SUCCESS;
    }

    protected function entityConfig(): array
    {
        return [
            'publication' => [
                'content_id' => $this->option('publication-content-id') ? (int) $this->option('publication-content-id') : null,
                'model' => ContentDetailPublication::class,
            ],
            'gallery' => [
                'content_id' => $this->option('gallery-content-id') ? (int) $this->option('gallery-content-id') : null,
                'model' => ContentDetailGallery::class,
            ],
            'agenda' => [
                'content_id' => $this->option('agenda-content-id') ? (int) $this->option('agenda-content-id') : null,
                'model' => ContentDetailAgenda::class,
            ],
        ];
    }

    protected function entityForType(string $type): ?string
    {
        return match (strtolower($type)) {
            'news', 'artikel', 'jurnal' => 'publication',
            'infographic' => 'gallery',
            'event' => 'agenda',
            default => null,
        };
    }

    protected function parseTagIds($value): array
    {
        if ($value === null || trim((string) $value) === '') {
            return [];
        }

        $raw = trim((string) $value);
        $arr = str_starts_with($raw, '[') ? json_decode($raw, true) : explode(',', $raw);
        if (! is_array($arr)) {
            return [];
        }

        $ids = [];
        foreach ($arr as $item) {
            if (is_numeric($item)) {
                $ids[] = (int) $item;
            }
        }

        return array_values(array_unique($ids));
    }

    protected function makeSlug($rawSlug, string $title): string
    {
        $slug = trim((string) ($rawSlug ?? ''));
        $slug = $slug === '' ? Str::slug($title) : Str::slug($slug);

        return $slug === '' ? 'content-'.Str::lower(Str::random(8)) : $slug;
    }

    protected function dateOrNull($value): ?string
    {
        if ($value === null || (string) $value === '') {
            return null;
        }

        return Carbon::parse($value)->toDateString();
    }

    protected function datetimeOrNull($value): ?string
    {
        if ($value === null || (string) $value === '') {
            return null;
        }

        return Carbon::parse($value)->format('Y-m-d H:i:s');
    }

    protected function createPublication(array $row, int $contentId, string $slug, bool $isPublicated, array $files, array &$tagCache, array &$kategoriCache, array $sourceTags, bool $dryRun): ?int
    {
        $layoutId = null;
        try {
            $layoutId = MsLayout::findByType('publication_detail')->id;
        } catch (\Throwable $e) {
            // layout optional
        }

        $kategoriId = $this->resolveKategoriId($row['category_name'] ?? null, $kategoriCache, $dryRun);
        $tagIds = $this->resolveTagIds($this->parseTagIds($row['tag_id']), $sourceTags, $tagCache, $dryRun);
        $body = (string) ($row['content'] ?? '');

        $payload = [
            'content_id' => $contentId,
            'title' => trim((string) $row['title']),
            'slug' => $slug,
            'headline' => $body !== '' ? $body : ' ',
            'description' => $body,
            'is_publicated' => $isPublicated,
            'thumbnail_path' => ($row['thumbnail'] ?? null) ?: null,
            'publish_date' => $this->dateOrNull($row['publish_date']),
            'layout_id' => $layoutId,
            'is_slider' => false,
            'has_additional_image' => false,
            'has_additional_video' => false,
            'has_additional_file' => count($files) > 0,
            'has_additional_link' => false,
            'has_additional_text' => false,
            'kategori_content_id' => $kategoriId,
            'created_by' => $this->actor,
            'updated_by' => $this->actor,
        ];

        if ($dryRun) {
            return 1;
        }

        $publication = ContentDetailPublication::create($payload);

        foreach ($tagIds as $tagId) {
            ContentTag::firstOrCreate([
                'content_type' => ContentDetailPublication::class,
                'content_id' => $publication->id,
                'tag_id' => $tagId,
            ], [
                'created_by' => $this->actor,
                'updated_by' => $this->actor,
            ]);
        }

        foreach ($files as $i => $file) {
            ContentAdditionalFile::create([
                'content_type' => ContentDetailPublication::class,
                'content_id' => $publication->id,
                'title' => $file->name ?: 'file',
                'file_path' => $file->url,
                'description' => null,
                'order' => $i + 1,
                'created_by' => $this->actor,
                'updated_by' => $this->actor,
            ]);
        }

        return $publication->id;
    }

    protected function createGallery(array $row, int $contentId, string $slug, bool $isPublicated, array &$tagCache, array $sourceTags, bool $dryRun): ?int
    {
        $layoutId = null;
        try {
            $content = Content::find($contentId);
            $subType = $content?->sub_type ?: 'image';
            $layoutId = MsLayout::findByType('gallery_'.$subType.'_detail')->id;
        } catch (\Throwable $e) {
            // layout optional
        }

        $tagIds = $this->resolveTagIds($this->parseTagIds($row['tag_id']), $sourceTags, $tagCache, $dryRun);

        $payload = [
            'content_id' => $contentId,
            'title' => trim((string) $row['title']),
            'description' => $row['content'] ?? '',
            'slug' => $slug,
            'is_publicated' => $isPublicated,
            'publish_date' => $this->dateOrNull($row['publish_date']),
            'layout_id' => $layoutId,
            'created_by' => $this->actor,
            'updated_by' => $this->actor,
        ];

        if ($dryRun) {
            return 1;
        }

        $gallery = ContentDetailGallery::create($payload);

        if (! empty($row['thumbnail'])) {
            ContentDetailGalleryList::create([
                'content_detail_gallery_id' => $gallery->id,
                'title' => null,
                'description' => null,
                'image_path' => $row['thumbnail'],
                'order' => 1,
                'created_by' => $this->actor,
                'updated_by' => $this->actor,
            ]);
        }

        foreach ($tagIds as $tagId) {
            ContentTag::firstOrCreate([
                'content_type' => ContentDetailGallery::class,
                'content_id' => $gallery->id,
                'tag_id' => $tagId,
            ], [
                'created_by' => $this->actor,
                'updated_by' => $this->actor,
            ]);
        }

        return $gallery->id;
    }

    protected function createAgenda(array $row, int $contentId, bool $isPublicated, bool $dryRun): ?int
    {
        $start = $this->datetimeOrNull($row['event_date'] ?? $row['publish_date']);
        $end = $this->datetimeOrNull($row['event_end_date'] ?? null);

        if ($start === null) {
            $start = now()->format('Y-m-d H:i:s');
        }

        $eventName = trim((string) $row['title']);

        $exists = ContentDetailAgenda::where('content_id', $contentId)
            ->where('event_name', $eventName)
            ->exists();

        if ($exists) {
            return 0;
        }

        $payload = [
            'content_id' => $contentId,
            'event_name' => $eventName,
            'is_active' => $isPublicated,
            'is_range' => $end !== null,
            'datetime_start' => $start,
            'datetime_end' => $end,
            'created_by' => $this->actor,
            'updated_by' => $this->actor,
        ];

        if ($dryRun) {
            return 1;
        }

        return ContentDetailAgenda::create($payload)->id;
    }

    protected function resolveTagIds(array $tagIds, array $sourceTags, array &$tagCache, bool $dryRun): array
    {
        $resolved = [];
        foreach ($tagIds as $id) {
            $title = $sourceTags[$id] ?? null;
            if ($title === null || trim($title) === '') {
                continue;
            }

            if (! isset($tagCache[$title])) {
                if ($dryRun) {
                    $tagCache[$title] = 0;
                } else {
                    $tag = MsTag::firstOrCreate(
                        ['title' => $title],
                        ['is_active' => true, 'created_by' => $this->actor, 'updated_by' => $this->actor]
                    );
                    $tagCache[$title] = $tag->id;
                }
            }

            if ($tagCache[$title]) {
                $resolved[] = $tagCache[$title];
            }
        }

        return $resolved;
    }

    protected function resolveKategoriId($categoryName, array &$kategoriCache, bool $dryRun): ?int
    {
        if ($categoryName === null || trim((string) $categoryName) === '') {
            return null;
        }

        $name = trim((string) $categoryName);
        if (! isset($kategoriCache[$name])) {
            if ($dryRun) {
                $kategoriCache[$name] = 0;
            } else {
                $existing = DB::table('ms_kategori_contents')->where('title', $name)->first();
                if ($existing) {
                    $kategoriCache[$name] = $existing->id;
                } else {
                    $kategoriCache[$name] = DB::table('ms_kategori_contents')->insertGetId([
                        'title' => $name,
                        'is_active' => true,
                        'created_by' => $this->actor,
                        'updated_by' => $this->actor,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        return $kategoriCache[$name] ?: null;
    }

    protected function fetchSourceTags($source, array $rows): array
    {
        $tagIds = [];
        foreach ($rows as $row) {
            foreach ($this->parseTagIds($row['tag_id'] ?? null) as $id) {
                $tagIds[$id] = $id;
            }
        }

        if (! $tagIds) {
            return [];
        }

        return $source->table('tags')
            ->whereIn('id', array_values($tagIds))
            ->pluck('title', 'id')
            ->toArray();
    }

    protected function fetchSourceFiles($source, array $rows): array
    {
        $contentIds = array_column($rows, 'id');
        $files = [];
        if ($contentIds) {
            foreach ($source->table('content_files')->whereIn('content_id', $contentIds)->get() as $file) {
                $files[$file->content_id][] = $file;
            }
        }

        return $files;
    }

    protected function buildExportSql(array $rows, array $sourceTags, array $sourceFiles, array $entityConfig): string
    {
        $publicationClass = 'App\\Models\\ContentDetailPublication';
        $galleryClass = 'App\\Models\\ContentDetailGallery';

        $pubContentId = $entityConfig['publication']['content_id'];
        $galContentId = $entityConfig['gallery']['content_id'];
        $agdContentId = $entityConfig['agenda']['content_id'];

        $rowValues = [];
        $tagValues = [];
        $contentTagValues = [];
        $fileValues = [];
        $seenTags = [];

        foreach ($rows as $row) {
            $entity = $this->entityForType($row['type'] ?? '');
            if (! $entity) {
                continue;
            }

            $title = trim((string) ($row['title'] ?? ''));
            $slug = $entity === 'agenda' ? null : $this->makeSlug($row['slug'] ?? null, $title);
            $body = (string) ($row['content'] ?? '');
            $publishDate = $this->dateOrNull($row['publish_date'] ?? null);
            $eventStart = $this->datetimeOrNull($row['event_date'] ?? $row['publish_date'] ?? null);
            $eventEnd = $this->datetimeOrNull($row['event_end_date'] ?? null);

            $rowValues[] = '('
                .(int) $row['id'].', '
                .$this->sqlStr($row['type']).', '
                .$this->sqlStr($title).', '
                .$this->sqlStr($body).', '
                .$this->sqlStr($slug).', '
                .$this->sqlStr($row['thumbnail'] ?? null).', '
                .$this->sqlStr($row['status_content'] ?? null).', '
                .$this->sqlStr($publishDate).', '
                .$this->sqlStr($eventStart).', '
                .$this->sqlStr($eventEnd).', '
                .$this->sqlStr(trim((string) ($row['category_name'] ?? '')) ?: null)
                .')';

            foreach ($this->parseTagIds($row['tag_id'] ?? null) as $tagId) {
                $tagTitle = $sourceTags[$tagId] ?? null;
                if ($tagTitle === null || trim($tagTitle) === '') {
                    continue;
                }
                $tagTitle = trim($tagTitle);
                if (! isset($seenTags[$tagTitle])) {
                    $seenTags[$tagTitle] = true;
                    $tagValues[] = '('.(int) $tagId.', '.$this->sqlStr($tagTitle).')';
                }
                if ($entity === 'publication' || $entity === 'gallery') {
                    $contentTagValues[] = '('. (int) $row['id'].', '.$this->sqlStr($tagTitle).')';
                }
            }

            foreach ($sourceFiles[$row['id']] ?? [] as $file) {
                $fileValues[] = '('. (int) $row['id'].', '.$this->sqlStr($file->name ?: 'file').', '.$this->sqlStr($file->url).')';
            }
        }

        $l = [];

        $l[] = '-- Legacy IMC contents migration seeder';
        $l[] = '-- Generated by sync:contents-from-mysql --export-sql';
        $l[] = '-- Target content IDs: publication='.$pubContentId.', gallery='.$galContentId.', agenda='.$agdContentId;
        $l[] = '';
        $l[] = 'BEGIN;';
        $l[] = '';

        $l[] = 'CREATE TEMP TABLE _legacy_rows (id int, type text, title text, content text, slug text, thumbnail text, status_content text, publish_date text, event_date text, event_end_date text, category_name text);';
        $l[] = 'CREATE TEMP TABLE _legacy_tags (id int, title text);';
        $l[] = 'CREATE TEMP TABLE _legacy_content_tags (legacy_content_id int, tag_title text);';
        $l[] = 'CREATE TEMP TABLE _legacy_content_files (legacy_content_id int, file_name text, file_url text);';
        $l[] = 'CREATE TEMP TABLE _legacy_content_map (legacy_id int, entity text, detail_id int);';
        $l[] = '';

        if ($rowValues) {
            $l[] = 'INSERT INTO _legacy_rows VALUES';
            $l[] = '  '.implode(",\n  ", $rowValues).';';
            $l[] = '';
        }
        if ($tagValues) {
            $l[] = 'INSERT INTO _legacy_tags VALUES';
            $l[] = '  '.implode(",\n  ", $tagValues).';';
            $l[] = '';
        }
        if ($contentTagValues) {
            $l[] = 'INSERT INTO _legacy_content_tags VALUES';
            $l[] = '  '.implode(",\n  ", $contentTagValues).';';
            $l[] = '';
        }
        if ($fileValues) {
            $l[] = 'INSERT INTO _legacy_content_files VALUES';
            $l[] = '  '.implode(",\n  ", $fileValues).';';
            $l[] = '';
        }

        $l[] = '-- resolve categories (publication rows only)';
        $l[] = 'INSERT INTO ms_kategori_contents (title, is_active, created_by, updated_by, created_at, updated_at)';
        $l[] = 'SELECT DISTINCT category_name, true, \'legacy-import\', \'legacy-import\', now(), now()';
        $l[] = 'FROM _legacy_rows';
        $l[] = 'WHERE category_name IS NOT NULL';
        $l[] = 'AND lower(type) IN (\'news\', \'artikel\', \'jurnal\')';
        $l[] = 'AND NOT EXISTS (SELECT 1 FROM ms_kategori_contents k WHERE k.title = _legacy_rows.category_name);';
        $l[] = '';

        $l[] = '-- resolve tags';
        $l[] = 'INSERT INTO ms_tags (title, is_active, created_by, updated_by, created_at, updated_at)';
        $l[] = 'SELECT DISTINCT title, true, \'legacy-import\', \'legacy-import\', now(), now()';
        $l[] = 'FROM _legacy_tags';
        $l[] = 'WHERE NOT EXISTS (SELECT 1 FROM ms_tags t WHERE t.title = _legacy_tags.title);';
        $l[] = '';

        $l[] = 'DO $$';
        $l[] = 'DECLARE';
        $l[] = '  r _legacy_rows%ROWTYPE;';
        $l[] = '  v_detail_id int;';
        $l[] = '  v_kategori_id int;';
        $l[] = 'BEGIN';
        $l[] = '  FOR r IN SELECT * FROM _legacy_rows ORDER BY id LOOP';
        $l[] = '    v_detail_id := NULL;';
        $l[] = '    CASE lower(r.type)';
        $l[] = '      WHEN \'news\', \'artikel\', \'jurnal\' THEN';
        $l[] = '        IF EXISTS (SELECT 1 FROM content_detail_publications WHERE slug = r.slug) THEN CONTINUE; END IF;';
        $l[] = '        SELECT id INTO v_kategori_id FROM ms_kategori_contents WHERE title = r.category_name;';
        $l[] = '        INSERT INTO content_detail_publications (';
        $l[] = '          content_id, title, slug, headline, description, is_publicated, thumbnail_path,';
        $l[] = '          publish_date, layout_id, is_slider, has_additional_image, has_additional_video,';
        $l[] = '          has_additional_file, has_additional_link, has_additional_text, kategori_content_id,';
        $l[] = '          created_by, updated_by, created_at, updated_at';
        $l[] = '        ) VALUES (';
        $l[] = '          '.$pubContentId.', r.title, r.slug, r.content, r.content,';
        $l[] = '          upper(r.status_content) = \'PUBLISH\', r.thumbnail, r.publish_date::date, NULL,';
        $l[] = '          false, false, false, false, false, false, v_kategori_id,';
        $l[] = '          \'legacy-import\', \'legacy-import\', now(), now()';
        $l[] = '        ) RETURNING id INTO v_detail_id;';
        $l[] = '';
        $l[] = '      WHEN \'infographic\' THEN';
        $l[] = '        IF EXISTS (SELECT 1 FROM content_detail_galleries WHERE slug = r.slug) THEN CONTINUE; END IF;';
        $l[] = '        INSERT INTO content_detail_galleries (';
        $l[] = '          content_id, title, description, slug, is_publicated, publish_date, layout_id,';
        $l[] = '          created_by, updated_by, created_at, updated_at';
        $l[] = '        ) VALUES (';
        $l[] = '          '.$galContentId.', r.title, r.content, r.slug,';
        $l[] = '          upper(r.status_content) = \'PUBLISH\', r.publish_date::date, NULL,';
        $l[] = '          \'legacy-import\', \'legacy-import\', now(), now()';
        $l[] = '        ) RETURNING id INTO v_detail_id;';
        $l[] = '        IF r.thumbnail IS NOT NULL THEN';
        $l[] = '          INSERT INTO content_detail_gallery_lists (content_detail_gallery_id, title, description, image_path, "order", created_by, updated_by, created_at, updated_at)';
        $l[] = '          VALUES (v_detail_id, NULL, NULL, r.thumbnail, 1, \'legacy-import\', \'legacy-import\', now(), now());';
        $l[] = '        END IF;';
        $l[] = '';
        $l[] = '      WHEN \'event\' THEN';
        $l[] = '        IF EXISTS (SELECT 1 FROM content_detail_agendas WHERE content_id = '.$agdContentId.' AND event_name = r.title) THEN CONTINUE; END IF;';
        $l[] = '        INSERT INTO content_detail_agendas (';
        $l[] = '          content_id, event_name, is_active, is_range, datetime_start, datetime_end,';
        $l[] = '          created_by, updated_by, created_at, updated_at';
        $l[] = '        ) VALUES (';
        $l[] = '          '.$agdContentId.', r.title, upper(r.status_content) = \'PUBLISH\', r.event_end_date IS NOT NULL,';
        $l[] = '          COALESCE(r.event_date::timestamp, r.publish_date::timestamp, now()), r.event_end_date::timestamp,';
        $l[] = '          \'legacy-import\', \'legacy-import\', now(), now()';
        $l[] = '        ) RETURNING id INTO v_detail_id;';
        $l[] = '';
        $l[] = '      ELSE';
        $l[] = '        CONTINUE;';
        $l[] = '    END CASE;';
        $l[] = '    IF v_detail_id IS NOT NULL THEN';
        $l[] = '      INSERT INTO _legacy_content_map VALUES (r.id, lower(r.type), v_detail_id);';
        $l[] = '    END IF;';
        $l[] = '  END LOOP;';
        $l[] = 'END $$;';
        $l[] = '';

        $l[] = '-- content tags';
        $l[] = 'INSERT INTO content_tags (content_type, content_id, tag_id, created_by, updated_by, created_at, updated_at)';
        $l[] = 'SELECT';
        $l[] = '  CASE m.entity WHEN \'publication\' THEN \''.$publicationClass.'\' ELSE \''.$galleryClass.'\' END,';
        $l[] = '  m.detail_id, t.id, \'legacy-import\', \'legacy-import\', now(), now()';
        $l[] = 'FROM _legacy_content_tags ct';
        $l[] = 'JOIN _legacy_content_map m ON m.legacy_id = ct.legacy_content_id';
        $l[] = 'JOIN _legacy_tags lt ON lt.title = ct.tag_title';
        $l[] = 'JOIN ms_tags t ON t.title = lt.title;';
        $l[] = '';

        $l[] = '-- additional files';
        $l[] = 'INSERT INTO content_additional_files (content_type, content_id, title, file_path, description, "order", created_by, updated_by, created_at, updated_at)';
        $l[] = 'SELECT \''.$publicationClass.'\', m.detail_id, f.file_name, f.file_url, NULL,';
        $l[] = '  row_number() OVER (PARTITION BY f.legacy_content_id ORDER BY f.file_name),';
        $l[] = '  \'legacy-import\', \'legacy-import\', now(), now()';
        $l[] = 'FROM _legacy_content_files f';
        $l[] = 'JOIN _legacy_content_map m ON m.legacy_id = f.legacy_content_id;';
        $l[] = '';

        $l[] = '-- reset sequences';
        foreach (['content_detail_publications', 'content_detail_galleries', 'content_detail_agendas', 'content_detail_gallery_lists', 'content_additional_files', 'ms_tags', 'content_tags', 'ms_kategori_contents'] as $table) {
            $l[] = 'SELECT setval(\''.$table.'_id_seq\', GREATEST((SELECT COALESCE(MAX(id), 1) FROM '.$table.'), 1));';
        }
        $l[] = '';

        $l[] = 'DROP TABLE IF EXISTS _legacy_rows, _legacy_tags, _legacy_content_tags, _legacy_content_files, _legacy_content_map;';
        $l[] = 'COMMIT;';
        $l[] = '';

        return implode("\n", $l)."\n";
    }

    protected function sqlStr($value): string
    {
        if ($value === null) {
            return 'NULL';
        }

        return "'".str_replace(["\\", "'"], ["\\\\", "''"], (string) $value)."'";
    }
}
