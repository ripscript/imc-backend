<?php

namespace App\Traits;

use App\Services\QueryService;
use Illuminate\Support\Facades\DB;

trait HasContentMapping
{
    protected $queryService;

    public function __construct()
    {
        $this->queryService = new QueryService;
    }

    public function applyContent($content, $request = null)
    {
        if (! $content) {
            return;
        }

        $this->type = $content->type;
        $this->layout = $content->layout;

        if ($this->type === 'gallery') {
            $this->sub_type = $content->sub_type;
        }

        if (in_array($this->type, ['publication', 'repository', 'gallery', 'project'])) {
            switch ($this->type) {
                case 'publication':
                    $query = DB::table('content_detail_publications')
                        ->where('content_id', $content->id)
                        ->where('is_publicated', true)
                        ->select(
                            'title',
                            'slug',
                            'thumbnail_path',
                            'publish_date'
                        );

                    $listContent = $this->queryService->applyFiltersAndPaginate($query, $request, ['title']);
                    break;
                case 'repository':
                    $query = DB::table('content_detail_repositories')
                        ->where('content_id', $content->id)
                        ->where('is_publicated', true)
                        ->select(
                            'title',
                            'slug',
                            'thumbnail_path',
                            'publish_date'
                        );

                    $listContent = $this->queryService->applyFiltersAndPaginate($query, $request, ['title']);
                    break;
                case 'gallery':
                    $query = DB::table('content_detail_galleries as g')
                        ->where('g.content_id', $content->id)
                        ->where('g.is_publicated', true)
                        ->select(
                            'g.title',
                            'g.slug',
                            'g.publish_date'
                        )
                        ->selectSub(function ($q) {
                            $q->from('content_detail_gallery_lists as l')
                                ->select('l.image_path')
                                ->whereColumn('l.content_detail_gallery_id', 'g.id')
                                ->orderBy('l.order', 'asc')
                                ->limit(1);
                        }, 'first_image_path');

                    $listContent = $this->queryService->applyFiltersAndPaginate($query, $request, ['g.title']);
                    break;
                case 'project':
                    $query = DB::table('content_detail_projects')
                        ->where('content_id', $content->id)
                        ->where('is_publicated', true)
                        ->select(
                            'title',
                            'slug',
                            'thumbnail_path',
                            'publish_date',
                            'duration',
                            'location',
                            'start_date',
                            'budget',
                            'organization_name'
                        )
                        ->selectSub(function ($q) {
                            $q->from('ms_kategori_contents')
                                ->select('ms_kategori_contents.title')
                                ->whereColumn('ms_kategori_contents.id', 'content_detail_projects.kategori_content_id')
                                ->limit(1);
                        }, 'kategori_title');

                    $listContent = $this->queryService->applyFiltersAndPaginate($query, $request, ['title']);
                    break;
            }
            $this->content = $listContent['data'] ?? [];
            $this->meta = $listContent['meta'] ?? [];
        } else {
            $this->content = $content->getDetailBasedOnType();
        }
    }
}
