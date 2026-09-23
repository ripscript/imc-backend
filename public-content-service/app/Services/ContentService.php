<?php

namespace App\Services;

use App\Exceptions\Domain\EntityNotFoundException;
use App\Models\ChildSubMenu;
use App\Models\Content;
use App\Models\ContentDetailGallery;
use App\Models\ContentDetailProject;
use App\Models\ContentDetailPublication;
use App\Models\ContentDetailRepository;
use App\Models\ContentDetailShowcase;
use App\Models\Menu;
use App\Models\SubMenu;
use App\Services\DTOs\QueryFilterDTO;
use Illuminate\Support\Facades\DB;

class ContentService
{
    const TYPE_CLASS = [
        'menu' => Menu::class,
        'sub_menu' => SubMenu::class,
        'child_sub_menu' => ChildSubMenu::class,
    ];

    public function index(string $type, array $slug, QueryFilterDTO $filter)
    {
        switch ($type) {
            case 'menu':
                $parent = Menu::where('slug', $slug[0])
                    ->active()
                    ->whereHas('content', function ($query) {
                        $query->accessible();
                    })
                    ->first();
                break;

            case 'sub_menu':
                $parent = SubMenu::where('slug', $slug[1])
                    ->active()
                    ->whereHas('menu', function ($query) use ($slug) {
                        $query->where('slug', $slug[0])->active();
                    })
                    ->whereHas('content', function ($query) {
                        $query->accessible();
                    })
                    ->first();
                break;

            case 'child_sub_menu':
                $parent = ChildSubMenu::where('slug', $slug[2])
                    ->active()
                    ->whereHas('subMenu', function ($query) use ($slug) {
                        $query->where('slug', $slug[1])
                            ->active()
                            ->whereHas('menu', function ($qq) use ($slug) {
                                $qq->where('slug', $slug[0])->active();
                            });
                    })
                    ->whereHas('content', function ($query) {
                        $query->accessible();
                    })
                    ->first();
                break;
        }

        if (! $parent) {
            throw new EntityNotFoundException("Content {$type}");
        }

        $content = Content::where([
            'parent_type' => self::TYPE_CLASS[$type],
            'parent_id' => $parent->id,
        ])->first();

        if (! $content) {
            throw new EntityNotFoundException("Content {$type}");
        }

        $content->title = $parent->title;
        $content->slug = $parent->slug;
        $content->order = $parent->order;
        $content->breadcrumb = $this->getBreadcrumb($type, $parent);

        $content->applyContent($content, $filter);
        $content->applyAdditionalContent($content);

        return $content;
    }

    public function detailPublication(string $type, string $contentSlug, string $slug)
    {
        $content = Content::whereHas('parent', function ($query) use ($contentSlug) {
            $query->where('slug', $contentSlug);
        })
            ->where('parent_type', self::TYPE_CLASS[$type])
            ->accessible()
            ->first();

        if (! $content) {
            throw new EntityNotFoundException('Parent content');
        }

        $breadcrumb = $this->getBreadcrumb($type, $content->parent);

        $publication = ContentDetailPublication::where([
            'content_id' => $content->id,
            'slug' => $slug,
            'is_publicated' => true,
        ])
            ->with('contentTag.tag', 'kategori', 'layout')
            ->first();

        if (! $publication) {
            throw new EntityNotFoundException('Publikasi');
        }
        $breadcrumb[] = $publication->title;
        $publication->applyAdditionalContent($publication);
        $publication->parent_menu = $content->parent_slug;
        $publication->breadcrumb = $breadcrumb;

        return $publication;
    }

    public function detailRepository(string $type, string $contentSlug, string $slug)
    {
        $content = Content::whereHas('parent', function ($query) use ($contentSlug) {
            $query->where('slug', $contentSlug);
        })
            ->where('parent_type', self::TYPE_CLASS[$type])
            ->accessible()
            ->first();

        if (! $content) {
            throw new EntityNotFoundException('Parent content');
        }

        $breadcrumb = $this->getBreadcrumb($type, $content->parent);

        $repository = ContentDetailRepository::where([
            'content_id' => $content->id,
            'slug' => $slug,
            'is_publicated' => true,
        ])
            ->with('contentTag', 'layout')
            ->first();

        if (! $repository) {
            throw new EntityNotFoundException('Repositori');
        }
        $breadcrumb[] = $repository->title;
        $repository->parent_menu = $content->parent_slug;
        $repository->breadcrumb = $breadcrumb;

        return $repository;
    }

    public function detailGallery(string $type, string $contentSlug, string $slug)
    {
        $content = Content::whereHas('parent', function ($query) use ($contentSlug) {
            $query->where('slug', $contentSlug);
        })
            ->where('parent_type', self::TYPE_CLASS[$type])
            ->accessible()
            ->first();

        if (! $content) {
            throw new EntityNotFoundException('Parent content');
        }

        $breadcrumb = $this->getBreadcrumb($type, $content->parent);

        $gallery = ContentDetailGallery::where([
            'content_id' => $content->id,
            'slug' => $slug,
            'is_publicated' => true,
        ])
            ->with('list', 'contentTag', 'layout')
            ->first();

        if (! $gallery) {
            throw new EntityNotFoundException('Galeri');
        }
        $breadcrumb[] = $gallery->title;
        $gallery->parent_menu = $content->parent_slug;
        $gallery->sub_type = $content->sub_type;
        $gallery->breadcrumb = $breadcrumb;

        return $gallery;
    }

    public function detailProject(string $type, string $contentSlug, string $slug)
    {
        $content = Content::whereHas('parent', function ($query) use ($contentSlug) {
            $query->where('slug', $contentSlug);
        })
            ->where('parent_type', self::TYPE_CLASS[$type])
            ->accessible()
            ->first();

        if (! $content) {
            throw new EntityNotFoundException('Parent content');
        }

        $breadcrumb = $this->getBreadcrumb($type, $content->parent);

        $project = ContentDetailProject::where([
            'content_id' => $content->id,
            'slug' => $slug,
            'is_publicated' => true,
        ])
            ->with('layout')
            ->first();

        if (! $project) {
            throw new EntityNotFoundException('Proyek');
        }
        $project->client_name = $project->client_id ? DB::table('pm_clients')->where('id', $project->client_id)->value('name') : null;
        $project->partner_name = $project->partner_id ? DB::table('pm_partners')->where('id', $project->partner_id)->value('name') : null;
        $project->category_name = $project->category_id ? DB::table('pm_project_categories')->where('id', $project->category_id)->value('name') : null;
        $breadcrumb[] = $project->title;
        $project->applyAdditionalContent($project);
        $project->parent_menu = $content->parent_slug;
        $project->breadcrumb = $breadcrumb;

        return $project;
    }

    public function detailShowcase(string $type, string $contentSlug, string $slug)
    {
        $content = Content::whereHas('parent', function ($query) use ($contentSlug) {
            $query->where('slug', $contentSlug);
        })
            ->where('parent_type', self::TYPE_CLASS[$type])
            ->accessible()
            ->first();

        if (! $content) {
            throw new EntityNotFoundException('Parent content');
        }

        $breadcrumb = $this->getBreadcrumb($type, $content->parent);

        $showcase = ContentDetailShowcase::where([
            'content_id' => $content->id,
            'slug' => $slug,
            'is_publicated' => true,
        ])
            ->with(['layout', 'kategoriContent'])
            ->first();

        if (! $showcase) {
            throw new EntityNotFoundException('Showcase');
        }
        $breadcrumb[] = $showcase->title;
        $showcase->applyAdditionalContent($showcase);
        $showcase->parent_menu = $content->parent_slug;
        $showcase->breadcrumb = $breadcrumb;

        return $showcase;
    }

    public function globalSearch(string $keyword, int $page, int $limit)
    {
        $service = new GlobalSearchService;

        return $service->search($keyword, $page, $limit);
    }

    public function getBreadcrumb($type, $content)
    {
        $breadcrumb = [];
        switch ($type) {
            case 'menu':
                $breadcrumb[] = $content->title;
                break;
            case 'sub_menu':
                $breadcrumb[] = $content->menu?->title;
                $breadcrumb[] = $content->title;
                break;
            case 'child_sub_menu':
                $breadcrumb[] = $content->subMenu?->menu?->title;
                $breadcrumb[] = $content->subMenu?->title;
                $breadcrumb[] = $content->title;
                break;
        }

        return $breadcrumb;
    }
}
