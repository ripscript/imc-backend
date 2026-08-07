<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GlobalSearchIndexSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Menu, Sub Menu, Child Sub Menu
        DB::statement("
            INSERT INTO global_search_index (
                source_type,
                source_id,
                title,
                url,
                search_vector,
                created_at,
                updated_at
            )
            SELECT
                source_type,
                source_id,
                title,
                url,
                search_vector,
                now(),
                now()
            FROM (
                SELECT
                    parent_type AS source_type,
                    parent_id   AS source_id,
                    max(parent_title) AS title,
                    max(parent_slug)  AS url,
                    to_tsvector('simple',
                        string_agg(search_vector::text, ' ')
                    ) AS search_vector
                FROM (
                    SELECT
                        c.id,
                        c.\"type\",
                        c.parent_type,
                        c.parent_id,

                        CASE
                            WHEN c.parent_type = 'App\\Models\\Menu' THEN
                                m.slug

                            WHEN c.parent_type = 'App\\Models\\SubMenu' THEN
                                rtrim(m.slug, '/') || '/' || ltrim(sm.slug, '/')

                            WHEN c.parent_type = 'App\\Models\\ChildSubMenu' THEN
                                rtrim(m.slug, '/') || '/' ||
                                trim(both '/' from sm.slug) || '/' ||
                                ltrim(csm.slug, '/')
                        END AS parent_slug,

                        CASE
                            WHEN c.parent_type = 'App\\Models\\Menu' THEN m.title
                            WHEN c.parent_type = 'App\\Models\\SubMenu' THEN sm.title
                            WHEN c.parent_type = 'App\\Models\\ChildSubMenu' THEN csm.title
                        END as parent_title,

                        (
                            CASE
                                WHEN c.type = 'card_link' THEN
                                    to_tsvector('simple', coalesce((
                                        SELECT string_agg(coalesce(cd.title), ' ')
                                        FROM content_detail_card_links cd
                                        WHERE cd.content_id = c.id
                                    ), '')) ||
                                    to_tsvector('simple',
                                        coalesce(m.title,'') || ' ' ||
                                        coalesce(sm.title,'') || ' ' ||
                                        coalesce(csm.title,'')
                                    )

                                WHEN c.type = 'text' THEN
                                    to_tsvector('simple', coalesce((
                                        SELECT string_agg(coalesce(dt.content, ''), ' ')
                                        FROM content_detail_texts dt
                                        WHERE dt.content_id = c.id
                                    ), '')) ||
                                    to_tsvector('simple',
                                        coalesce(m.title,'') || ' ' ||
                                        coalesce(sm.title,'') || ' ' ||
                                        coalesce(csm.title,'')
                                    )

                                WHEN c.type = 'accordion' THEN
                                    to_tsvector('simple', coalesce((
                                        SELECT string_agg(
                                            coalesce(al.title,'') || ' ' ||
                                            coalesce(al.description,'') || ' ' ||
                                            coalesce(ag.title,''),
                                        ' ')
                                        FROM content_detail_accordion_groups ag
                                        JOIN content_detail_accordion_lists al 
                                            ON al.content_detail_accordion_group_id = ag.id
                                        WHERE ag.content_id = c.id
                                    ), '')) ||
                                    to_tsvector('simple',
                                        coalesce(m.title,'') || ' ' ||
                                        coalesce(sm.title,'') || ' ' ||
                                        coalesce(csm.title,'')
                                    )

                                WHEN c.type = 'image_carousel' THEN
                                    to_tsvector('simple', coalesce((
                                        SELECT string_agg(coalesce(ic.title,'') || ' ' ||
                                            coalesce(ic.description,''), ' ')
                                        FROM content_detail_image_carousels ic
                                        WHERE ic.content_id = c.id
                                    ), '')) ||
                                    to_tsvector('simple',
                                        coalesce(m.title,'') || ' ' ||
                                        coalesce(sm.title,'') || ' ' ||
                                        coalesce(csm.title,'')
                                    )

                                WHEN c.type = 'image_link' THEN
                                    to_tsvector('simple', coalesce((
                                        SELECT string_agg(coalesce(il.title,''), ' ')
                                        FROM content_detail_image_links il
                                        WHERE il.content_id = c.id
                                    ), '')) ||
                                    to_tsvector('simple',
                                        coalesce(m.title,'') || ' ' ||
                                        coalesce(sm.title,'') || ' ' ||
                                        coalesce(csm.title,'')
                                    )

                                WHEN c.type = 'video_carousel' THEN
                                    to_tsvector('simple', coalesce((
                                        SELECT string_agg(coalesce(vc.title,''), ' ')
                                        FROM content_detail_video_carousels vc
                                        WHERE vc.content_id = c.id
                                    ), '')) ||
                                    to_tsvector('simple',
                                        coalesce(m.title,'') || ' ' ||
                                        coalesce(sm.title,'') || ' ' ||
                                        coalesce(csm.title,'')
                                    )

                                WHEN c.type = 'organizational_structure' THEN
                                    to_tsvector('simple', coalesce((
                                        SELECT string_agg(
                                            coalesce(osm.name,'') || ' ' ||
                                            coalesce(osm.position,'') || ' ' ||
                                            coalesce(osm.description,'') || ' ' ||
                                            coalesce(osg.name,''),
                                        ' ')
                                        FROM content_detail_organizational_structure_groups osg
                                        JOIN content_detail_organizational_structure_members osm 
                                            ON osm.content_detail_organizational_structure_group_id = osg.id
                                        WHERE osg.content_id = c.id
                                    ), '')) ||
                                    to_tsvector('simple',
                                        coalesce(m.title,'') || ' ' ||
                                        coalesce(sm.title,'') || ' ' ||
                                        coalesce(csm.title,'')
                                    )
                                
                                WHEN c.type IN ('publication', 'repository', 'gallery', 'agenda') THEN
                                    to_tsvector('simple',
                                        coalesce(m.title,'') || ' ' ||
                                        coalesce(sm.title,'') || ' ' ||
                                        coalesce(csm.title,'')
                                    )
                            END
                        )
                        ||
                        (
                            to_tsvector(
                                'simple',
                                coalesce((
                                    SELECT string_agg(
                                        coalesce(f.title,'') || ' ' || coalesce(f.description,''),
                                    ' ')
                                    FROM content_additional_files f
                                    WHERE f.content_id = c.id
                                    AND f.content_type = 'App\\Models\\Content'
                                ), '')
                            )
                            ||
                            to_tsvector(
                                'simple',
                                coalesce((
                                    SELECT string_agg(
                                        coalesce(i.description,''),
                                    ' ')
                                    FROM content_additional_images i
                                    WHERE i.content_id = c.id
                                    AND i.content_type = 'App\\Models\\Content'
                                ), '')
                            )
                            ||
                            to_tsvector(
                                'simple',
                                coalesce((
                                    SELECT string_agg(
                                        coalesce(l.title,''),
                                    ' ')
                                    FROM content_additional_links l
                                    WHERE l.content_id = c.id
                                    AND l.content_type = 'App\\Models\\Content'
                                ), '')
                            )
                            ||
                            to_tsvector(
                                'simple',
                                coalesce((
                                    SELECT string_agg(
                                        coalesce(t.description,''),
                                    ' ')
                                    FROM content_additional_texts t
                                    WHERE t.content_id = c.id
                                    AND t.content_type = 'App\\Models\\Content'
                                ), '')
                            )
                            ||
                            to_tsvector(
                                'simple',
                                coalesce((
                                    SELECT string_agg(
                                        coalesce(v.title,''),
                                    ' ')
                                    FROM content_additional_videos v
                                    WHERE v.content_id = c.id
                                    AND v.content_type = 'App\\Models\\Content'
                                ), '')
                            )
                        )
                        AS search_vector

                    FROM contents c

                    LEFT JOIN menus m 
                        ON (
                            (c.parent_type = 'App\\Models\\Menu' AND c.parent_id = m.id)
                            OR
                            (c.parent_type = 'App\\Models\\SubMenu' AND m.id = (
                                SELECT menu_id FROM sub_menus WHERE id = c.parent_id
                            ))
                            OR
                            (c.parent_type = 'App\\Models\\ChildSubMenu' AND m.id = (
                                SELECT sm.menu_id 
                                FROM sub_menus sm
                                JOIN child_sub_menus csm2 ON csm2.sub_menu_id = sm.id
                                WHERE csm2.id = c.parent_id
                            ))
                        )

                    LEFT JOIN sub_menus sm 
                        ON (
                            (c.parent_type = 'App\\Models\\SubMenu' AND c.parent_id = sm.id)
                            OR
                            (c.parent_type = 'App\\Models\\ChildSubMenu' AND sm.id = (
                                SELECT sub_menu_id FROM child_sub_menus WHERE id = c.parent_id
                            ))
                        )

                    LEFT JOIN child_sub_menus csm 
                        ON (
                            c.parent_type = 'App\\Models\\ChildSubMenu'
                            AND c.parent_id = csm.id
                        )

                    WHERE c.is_accessible = TRUE AND 
                    c.is_section = FALSE AND 
                    (m.title IS NOT NULL OR sm.title IS NOT NULL OR csm.title IS NOT NULL)
                ) q
                GROUP BY parent_type, parent_id
            ) final

            ON CONFLICT (source_type, source_id)
            DO UPDATE SET
                title = EXCLUDED.title,
                url = EXCLUDED.url,
                search_vector = EXCLUDED.search_vector,
                updated_at = now();
        ");

        // Publication
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
                'App\\Models\\ContentDetailPublication' AS source_type,
                p.id AS source_id,
                p.title,
                CASE
                    WHEN c.parent_type = 'App\\Models\\Menu' THEN
                        m.slug

                    WHEN c.parent_type = 'App\\Models\\SubMenu' THEN
                        rtrim(m.slug, '/') || '/' || ltrim(sm.slug, '/')

                    WHEN c.parent_type = 'App\\Models\\ChildSubMenu' THEN
                        rtrim(m.slug, '/') || '/' ||
                        trim(both '/' from sm.slug) || '/' ||
                        ltrim(csm.slug, '/')
                END AS url,
                p.slug AS content_slug,

                to_tsvector('simple',
                    coalesce(p.title,'') || ' ' ||
                    coalesce(p.headline,'') || ' ' ||
                    coalesce(p.content,'')
                )
                ||
                to_tsvector('simple',
                    coalesce(m.title,'') || ' ' ||
                    coalesce(sm.title,'') || ' ' ||
                    coalesce(csm.title,'')
                )
                ||
                (
                    to_tsvector(
                        'simple',
                        coalesce((
                            SELECT string_agg(
                                coalesce(f.title,'') || ' ' || coalesce(f.description,''),
                            ' ')
                            FROM content_additional_files f
                            WHERE f.content_id = p.id
                            AND f.content_type = 'App\\Models\\ContentDetailPublication'
                        ), '')
                    )
                    ||
                    to_tsvector(
                        'simple',
                        coalesce((
                            SELECT string_agg(
                                coalesce(i.description,''),
                            ' ')
                            FROM content_additional_images i
                            WHERE i.content_id = p.id
                            AND i.content_type = 'App\\Models\\ContentDetailPublication'
                        ), '')
                    )
                    ||
                    to_tsvector(
                        'simple',
                        coalesce((
                            SELECT string_agg(
                                coalesce(l.title,''),
                            ' ')
                            FROM content_additional_links l
                            WHERE l.content_id = p.id
                            AND l.content_type = 'App\\Models\\ContentDetailPublication'
                        ), '')
                    )
                    ||
                    to_tsvector(
                        'simple',
                        coalesce((
                            SELECT string_agg(
                                coalesce(t.description,''),
                            ' ')
                            FROM content_additional_texts t
                            WHERE t.content_id = p.id
                            AND t.content_type = 'App\\Models\\ContentDetailPublication'
                        ), '')
                    )
                    ||
                    to_tsvector(
                        'simple',
                        coalesce((
                            SELECT string_agg(
                                coalesce(v.title,''),
                            ' ')
                            FROM content_additional_videos v
                            WHERE v.content_id = p.id
                            AND v.content_type = 'App\\Models\\ContentDetailPublication'
                        ), '')
                    )
                )
                AS search_vector,

                now(),
                now()

            FROM content_detail_publications p
            JOIN contents c ON p.content_id = c.id

            LEFT JOIN menus m 
                ON (
                    (c.parent_type = 'App\\Models\\Menu' AND c.parent_id = m.id)
                    OR
                    (c.parent_type = 'App\\Models\\SubMenu' AND m.id = (
                        SELECT menu_id FROM sub_menus WHERE id = c.parent_id
                    ))
                    OR
                    (c.parent_type = 'App\\Models\\ChildSubMenu' AND m.id = (
                        SELECT sm.menu_id 
                        FROM sub_menus sm
                        JOIN child_sub_menus csm2 ON csm2.sub_menu_id = sm.id
                        WHERE csm2.id = c.parent_id
                    ))
                )

            LEFT JOIN sub_menus sm 
                ON (
                    (c.parent_type = 'App\\Models\\SubMenu' AND c.parent_id = sm.id)
                    OR
                    (c.parent_type = 'App\\Models\\ChildSubMenu' AND sm.id = (
                        SELECT sub_menu_id FROM child_sub_menus WHERE id = c.parent_id
                    ))
                )

            LEFT JOIN child_sub_menus csm 
                ON (
                    c.parent_type = 'App\\Models\\ChildSubMenu'
                    AND c.parent_id = csm.id
                )

            WHERE c.is_accessible = TRUE AND p.is_publicated = TRUE AND
            (m.title IS NOT NULL OR sm.title IS NOT NULL OR csm.title IS NOT NULL)

            ON CONFLICT (source_type, source_id)
            DO UPDATE SET
                title = EXCLUDED.title,
                url = EXCLUDED.url,
                content_slug = EXCLUDED.content_slug,
                search_vector = EXCLUDED.search_vector,
                updated_at = now();
        ");

        // Repository
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
                'App\\Models\\ContentDetailRepository' AS source_type,
                p.id AS source_id,
                p.title,
                CASE
                    WHEN c.parent_type = 'App\\Models\\Menu' THEN
                        m.slug

                    WHEN c.parent_type = 'App\\Models\\SubMenu' THEN
                        rtrim(m.slug, '/') || '/' || ltrim(sm.slug, '/')

                    WHEN c.parent_type = 'App\\Models\\ChildSubMenu' THEN
                        rtrim(m.slug, '/') || '/' ||
                        trim(both '/' from sm.slug) || '/' ||
                        ltrim(csm.slug, '/')
                END AS url,
                p.slug AS content_slug,

                to_tsvector('simple',
                    coalesce(p.title,'') || ' ' ||
                    coalesce(p.description,'')
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

            FROM content_detail_repositories p
            JOIN contents c ON p.content_id = c.id

            LEFT JOIN menus m 
                ON (
                    (c.parent_type = 'App\\Models\\Menu' AND c.parent_id = m.id)
                    OR
                    (c.parent_type = 'App\\Models\\SubMenu' AND m.id = (
                        SELECT menu_id FROM sub_menus WHERE id = c.parent_id
                    ))
                    OR
                    (c.parent_type = 'App\\Models\\ChildSubMenu' AND m.id = (
                        SELECT sm.menu_id 
                        FROM sub_menus sm
                        JOIN child_sub_menus csm2 ON csm2.sub_menu_id = sm.id
                        WHERE csm2.id = c.parent_id
                    ))
                )

            LEFT JOIN sub_menus sm 
                ON (
                    (c.parent_type = 'App\\Models\\SubMenu' AND c.parent_id = sm.id)
                    OR
                    (c.parent_type = 'App\\Models\\ChildSubMenu' AND sm.id = (
                        SELECT sub_menu_id FROM child_sub_menus WHERE id = c.parent_id
                    ))
                )

            LEFT JOIN child_sub_menus csm 
                ON (
                    c.parent_type = 'App\\Models\\ChildSubMenu'
                    AND c.parent_id = csm.id
                )

            WHERE c.is_accessible = TRUE AND p.is_publicated = TRUE AND
            (m.title IS NOT NULL OR sm.title IS NOT NULL OR csm.title IS NOT NULL)

            ON CONFLICT (source_type, source_id)
            DO UPDATE SET
                title = EXCLUDED.title,
                url = EXCLUDED.url,
                content_slug = EXCLUDED.content_slug,
                search_vector = EXCLUDED.search_vector,
                updated_at = now();
        ");

        // Gallery
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
                'App\\Models\\ContentDetailGallery' AS source_type,
                g.id AS source_id,
                g.title,
                CASE
                    WHEN c.parent_type = 'App\\Models\\Menu' THEN
                        m.slug

                    WHEN c.parent_type = 'App\\Models\\SubMenu' THEN
                        rtrim(m.slug, '/') || '/' || ltrim(sm.slug, '/')

                    WHEN c.parent_type = 'App\\Models\\ChildSubMenu' THEN
                        rtrim(m.slug, '/') || '/' ||
                        trim(both '/' from sm.slug) || '/' ||
                        ltrim(csm.slug, '/')
                END AS url,
                g.slug AS content_slug,

                (
                    to_tsvector('simple',
                        coalesce(g.title,'') || ' ' ||
                        coalesce(g.description,'')
                    )
                    ||
                    to_tsvector('simple',
                        coalesce((
                            SELECT string_agg(
                                coalesce(gl.description,''),
                            ' ')
                            FROM content_detail_gallery_lists gl
                            WHERE gl.content_detail_gallery_id = g.id
                        ), '')
                    )
                    ||
                    to_tsvector('simple',
                        coalesce(m.title,'') || ' ' ||
                        coalesce(sm.title,'') || ' ' ||
                        coalesce(csm.title,'')
                    )
                ) AS search_vector,

                now(),
                now()

            FROM content_detail_galleries g
            JOIN contents c ON g.content_id = c.id

            LEFT JOIN menus m 
                ON (
                    (c.parent_type = 'App\\Models\\Menu' AND c.parent_id = m.id)
                    OR
                    (c.parent_type = 'App\\Models\\SubMenu' AND m.id = (
                        SELECT menu_id FROM sub_menus WHERE id = c.parent_id
                    ))
                    OR
                    (c.parent_type = 'App\\Models\\ChildSubMenu' AND m.id = (
                        SELECT sm.menu_id 
                        FROM sub_menus sm
                        JOIN child_sub_menus csm2 ON csm2.sub_menu_id = sm.id
                        WHERE csm2.id = c.parent_id
                    ))
                )

            LEFT JOIN sub_menus sm 
                ON (
                    (c.parent_type = 'App\\Models\\SubMenu' AND c.parent_id = sm.id)
                    OR
                    (c.parent_type = 'App\\Models\\ChildSubMenu' AND sm.id = (
                        SELECT sub_menu_id FROM child_sub_menus WHERE id = c.parent_id
                    ))
                )

            LEFT JOIN child_sub_menus csm 
                ON (
                    c.parent_type = 'App\\Models\\ChildSubMenu'
                    AND c.parent_id = csm.id
                )

            WHERE c.is_accessible = TRUE AND g.is_publicated = TRUE AND
            (m.title IS NOT NULL OR sm.title IS NOT NULL OR csm.title IS NOT NULL)

            ON CONFLICT (source_type, source_id)
            DO UPDATE SET
                title = EXCLUDED.title,
                url = EXCLUDED.url,
                content_slug = EXCLUDED.content_slug,
                search_vector = EXCLUDED.search_vector,
                updated_at = now();
        ");
    }
}
