<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\ContentDetailRequest;
use App\Http\Requests\ContentIndexRequest;
use App\Models\ChildSubMenu;
use App\Models\Content;
use App\Models\ContentDetailGallery;
use App\Models\ContentDetailPublication;
use App\Models\ContentDetailRepository;
use App\Models\Menu;
use App\Models\SubMenu;
use App\Services\GlobalSearchService;
use Illuminate\Http\Request;

class ContentController extends Controller
{
    const TYPE_CLASS = [
        'menu' => Menu::class,
        'sub_menu' => SubMenu::class,
        'child_sub_menu' => ChildSubMenu::class,
    ];

    public function index(ContentIndexRequest $request)
    {
        try {

            $type = $request->type;
            $slug = $request->slug;

            switch ($type) {
                case 'menu':
                    $content = Menu::where('slug', $slug[0])
                        ->active()
                        ->whereHas('content', function ($query) {
                            $query->accessible();
                        })
                        ->first();
                    break;

                case 'sub_menu':
                    $content = SubMenu::where('slug', $slug[1])
                        ->active()
                        ->whereHas('menu', function ($query) use ($slug) {
                            $query->where('slug', $slug[0])
                                ->active();
                        })
                        ->whereHas('content', function ($query) {
                            $query->accessible();
                        })
                        ->first();
                    break;

                case 'child_sub_menu':
                    $content = ChildSubMenu::where('slug', $slug[2])
                        ->active()
                        ->whereHas('subMenu', function ($query) use ($slug) {
                            $query->where('slug', $slug[1])
                                ->active()
                                ->whereHas('menu', function ($qq) use ($slug) {
                                    $qq->where('slug', $slug[0])
                                        ->active();
                                });
                        })
                        ->whereHas('content', function ($query) {
                            $query->accessible();
                        })
                        ->first();
                    break;
            }

            if (! $content) {
                return ApiResponse::error("Content {$type} tidak ditemukan", '', 404);
            }

            $detailContent = Content::where([
                'parent_type' => self::TYPE_CLASS[$type],
                'parent_id' => $content->id,
            ])->first();
            $content->applyContent($detailContent, $request);
            $content->applyAdditionalContent($detailContent);
            $content->breadcrumb = $this->getBreadcrumb($request->type, $content);

            return ApiResponse::success($content, "Berhasil mengambil detail content {$type}");
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function detailPublication(ContentDetailRequest $request)
    {
        try {

            $content = Content::whereHas('parent', function ($query) use ($request) {
                $query->where('slug', $request->content_slug);
            })
                ->where('parent_type', self::TYPE_CLASS[$request->type])
                ->accessible()
                ->first();

            if (! $content) {
                return ApiResponse::error('Parent content tidak ditemukan', '', 404);
            }

            $breadcrumb = $this->getBreadcrumb($request->type, $content->parent);

            $publication = ContentDetailPublication::where([
                'content_id' => $content->id,
                'slug' => $request->slug,
                'is_publicated' => true,
            ])
                ->with('contentTag', 'layout')
                ->first();

            if (! $publication) {
                return ApiResponse::error('Publikasi tidak ditemukan', '', 404);
            }
            $breadcrumb[] = $publication->title;
            $publication->applyAdditionalContent($publication);
            $publication->parent_menu = $content->parent_slug;
            $publication->breadcrumb = $breadcrumb;

            return ApiResponse::success($publication, 'Berhasil mengambil detail publikasi');
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function detailRepository(ContentDetailRequest $request)
    {
        try {

            $content = Content::whereHas('parent', function ($query) use ($request) {
                $query->where('slug', $request->content_slug);
            })
                ->where('parent_type', self::TYPE_CLASS[$request->type])
                ->accessible()
                ->first();

            if (! $content) {
                return ApiResponse::error('Parent content tidak ditemukan', '', 404);
            }

            $breadcrumb = $this->getBreadcrumb($request->type, $content->parent);

            $repository = ContentDetailRepository::where([
                'content_id' => $content->id,
                'slug' => $request->slug,
                'is_publicated' => true,
            ])
                ->with('contentTag', 'layout')
                ->first();

            if (! $repository) {
                return ApiResponse::error('Repositori tidak ditemukan', '', 404);
            }
            $breadcrumb[] = $repository->title;
            $repository->parent_menu = $content->parent_slug;
            $repository->breadcrumb = $breadcrumb;

            return ApiResponse::success($repository, 'Berhasil mengambil detail repositori');
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function detailGallery(ContentDetailRequest $request)
    {
        try {

            $content = Content::whereHas('parent', function ($query) use ($request) {
                $query->where('slug', $request->content_slug);
            })
                ->where('parent_type', self::TYPE_CLASS[$request->type])
                ->accessible()
                ->first();

            if (! $content) {
                return ApiResponse::error('Parent content tidak ditemukan', '', 404);
            }

            $breadcrumb = $this->getBreadcrumb($request->type, $content->parent);

            $gallery = ContentDetailGallery::where([
                'content_id' => $content->id,
                'slug' => $request->slug,
                'is_publicated' => true,
            ])
                ->with('list', 'contentTag', 'layout')
                ->first();

            if (! $gallery) {
                return ApiResponse::error('Galeri tidak ditemukan', '', 404);
            }
            $breadcrumb[] = $gallery->title;
            $gallery->parent_menu = $content->parent_slug;
            $gallery->sub_type = $content->sub_type;
            $gallery->breadcrumb = $breadcrumb;

            return ApiResponse::success($gallery, 'Berhasil mengambil detail galeri');
        } catch (\Throwable $th) {
            throw $th;
        }
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

    public function globalSearch(Request $request)
    {
        try {
            $keyword = $request->input('q');
            $page = (int) $request->input('page', 1);
            $limit = (int) $request->input('limit', 10);

            if (! $keyword) {
                return ApiResponse::error('Keyword tidak boleh kosong', '', 422);
            }

            $service = new GlobalSearchService;
            $data = $service->search($keyword, $page, $limit);

            return ApiResponse::success($data, 'Berhasil mendapatkan data pencarian');
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
