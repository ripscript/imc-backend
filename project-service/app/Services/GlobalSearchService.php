<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class GlobalSearchService
{
    public function insertProject($projectId)
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        DB::statement("
            INSERT INTO global_search_index (
                source_type,
                source_id,
                title,
                url,
                content_slug,
                search_vector,
                created_at,
                updated_at
            )
            SELECT
                'App\\\\Models\\\\ContentDetailProject' AS source_type,
                p.id AS source_id,
                p.title,
                CASE
                    WHEN c.parent_type = 'App\\\\Models\\\\Menu' THEN
                        m.slug

                    WHEN c.parent_type = 'App\\\\Models\\\\SubMenu' THEN
                        rtrim(m.slug, '/') || '/' || ltrim(sm.slug, '/')

                    WHEN c.parent_type = 'App\\\\Models\\\\ChildSubMenu' THEN
                        rtrim(m.slug, '/') || '/' ||
                        trim(both '/' from sm.slug) || '/' ||
                        ltrim(csm.slug, '/')
                END AS url,
                p.slug AS content_slug,

                to_tsvector('simple',
                    coalesce(p.title,'') || ' ' ||
                    coalesce(p.description,'') || ' ' ||
                    coalesce(p.duration,'') || ' ' ||
                    coalesce(p.location,'') || ' ' ||
                    coalesce(p.organization_name,'')
                )
                ||
                to_tsvector('simple',
                    coalesce(m.title,'') || ' ' ||
                    coalesce(sm.title,'') || ' ' ||
                    coalesce(csm.title,'')
                )
                AS search_vector,

                now(),
                now()

            FROM content_detail_projects p
            JOIN contents c ON p.content_id = c.id

            LEFT JOIN menus m
                ON (
                    (c.parent_type = 'App\\\\Models\\\\Menu' AND c.parent_id = m.id)
                    OR
                    (c.parent_type = 'App\\\\Models\\\\SubMenu' AND m.id = (
                        SELECT menu_id FROM sub_menus WHERE id = c.parent_id
                    ))
                    OR
                    (c.parent_type = 'App\\\\Models\\\\ChildSubMenu' AND m.id = (
                        SELECT sm.menu_id
                        FROM sub_menus sm
                        JOIN child_sub_menus csm2 ON csm2.sub_menu_id = sm.id
                        WHERE csm2.id = c.parent_id
                    ))
                )

            LEFT JOIN sub_menus sm
                ON (
                    (c.parent_type = 'App\\\\Models\\\\SubMenu' AND c.parent_id = sm.id)
                    OR
                    (c.parent_type = 'App\\\\Models\\\\ChildSubMenu' AND sm.id = (
                        SELECT sub_menu_id FROM child_sub_menus WHERE id = c.parent_id
                    ))
                )

            LEFT JOIN child_sub_menus csm
                ON (
                    c.parent_type = 'App\\\\Models\\\\ChildSubMenu'
                    AND c.parent_id = csm.id
                )

            WHERE c.is_accessible = TRUE AND p.is_publicated = TRUE AND
            (m.title IS NOT NULL OR sm.title IS NOT NULL OR csm.title IS NOT NULL) AND
            p.id = {$projectId}

            ON CONFLICT (source_type, source_id)
            DO UPDATE SET
                title = EXCLUDED.title,
                url = EXCLUDED.url,
                content_slug = EXCLUDED.content_slug,
                search_vector = EXCLUDED.search_vector,
                updated_at = now();
        ");
    }

    public function delete($sourceType, $sourceId)
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        DB::statement("
            DELETE from global_search_index WHERE source_type = '{$sourceType}' AND source_id = {$sourceId};
        ");
    }
}
