<?php

namespace App\Services;

use App\Models\Content;
use App\Models\Menu;
use App\Models\SiteSetting;
use App\Services\DTOs\MenuDTO;
use App\Services\DTOs\QueryFilterDTO;
use Illuminate\Support\Facades\DB;

class MenuService
{
    protected SaveContentsService $saveContentService;

    protected GlobalSearchService $globalSearchService;

    public function __construct(
        protected QueryService $queryService
    ) {
        $this->saveContentService = new SaveContentsService;
        $this->globalSearchService = new GlobalSearchService;
    }

    public function maxMenusShowFront(): int
    {
        $setting = SiteSetting::first();

        return $setting?->value('menu_max_show_front') ?? 5;
    }

    public function index(QueryFilterDTO $filter)
    {
        $query = DB::table('menus')
            ->leftJoin('contents', function ($join) {
                $join->on('menus.id', '=', 'contents.parent_id')
                    ->where('contents.parent_type', '=', Menu::class)
                    ->where('is_section', '!=', true);
            })
            ->select(
                'menus.id',
                'menus.title',
                'menus.is_active',
                'menus.slug',
                'contents.type',
                'menus.has_sub',
                'menus.updated_at'
            )
            ->selectRaw("
                CASE 
                    WHEN menus.has_sub = false 
                    THEN '/' || menus.slug
                    ELSE NULL
                END as url
            ");
        if ($filter->has_sub !== null) {
            $query->where('menus.has_sub', $filter->has_sub);
        }
        if ($filter->has_child !== null) {
            $query->whereExists(function ($subQuery) use ($filter) {
                $subQuery->select(DB::raw(1))
                    ->from('sub_menus as sm')
                    ->whereRaw('sm.menu_id = menus.id AND sm.has_child = ?', [$filter->has_child]);
            });
        }

        if ($filter->without_pagination) {
            $data = $query->get();

            return [
                'data' => $data,
                'meta' => [
                    'total' => $data->count(),
                    'page' => 1,
                    'limit' => $data->count(),
                    'total_page' => 1,
                ],
            ];
        }

        return $this->queryService->applyFiltersAndPaginate($query, $filter, ['menus.title', 'contents.type']);
    }

    public function detail($id)
    {
        $menu = Menu::findOrFail($id);

        if (! $menu->has_sub) {
            $content = Content::where(['parent_type' => Menu::class, 'parent_id' => $menu->id])->first();
            $menu->applyContent($content);
            $menu->applyAdditionalContent($content);
        }

        return $menu;
    }

    public function create(MenuDTO $dto)
    {
        DB::beginTransaction();
        try {
            $menu = new Menu;
            $menu->title = $dto->title;
            $menu->slug = $dto->slug;
            $menu->is_active = $dto->is_active;
            $menu->order = $dto->order;
            $menu->created_by = $dto->created_by;
            $menu->updated_by = $dto->updated_by;
            $menu->has_sub = $dto->has_sub;
            $menu->is_show_front = $dto->is_show_front;
            $menu->is_show_side_menu = $dto->is_show_side_menu ?? false;

            if ($menu->is_show_front == true && $menu->countMenusWithShowFront() >= $this->maxMenusShowFront()) {
                DB::rollBack();
                throw new \App\Exceptions\Domain\ValidationException('Anda telah mencapai batas maksimum untuk menampilkan menu prioritas di halaman depan (Maksimal '.$this->maxMenusShowFront().')');
            }

            $menu->save();

            if ($menu->has_sub == false) {
                $content = new Content;
                $content->parent_type = Menu::class;
                $content->parent_id = $menu->id;
                $content->type = $dto->type;
                $content->layout_id = $dto->layout_id;
                $content->created_by = $dto->created_by;
                $content->updated_by = $dto->updated_by;
                $content->is_accessible = $menu->is_active ? true : ($dto->is_accessible ?? false);
                if (in_array($dto->type, ['publication', 'repository', 'gallery'])) {
                    $content->is_show_home = $dto->is_show_home;
                    $content->is_show_tag = $dto->is_show_tag;
                    if ($dto->type == 'gallery') {
                        $content->sub_type = $dto->sub_type ?? 'image';
                    }
                }
                $content->save();

                $this->saveContentService->store($content, $dto->toArray(), $dto->created_by);
                $this->saveContentService->storeAdditionalContent(Content::class, $content, $dto->toArray(), $dto->created_by);

                if ($content->is_accessible && ! $content->is_section) {
                    $this->globalSearchService->insertContent($content->id);
                }
            }

            DB::commit();

            return $menu;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function update($id, MenuDTO $dto)
    {
        DB::beginTransaction();
        try {
            $menu = Menu::findOrFail($id);

            $menu->title = $dto->title;
            $menu->slug = $dto->slug;
            $menu->is_active = $dto->is_active;
            $menu->order = $dto->order;
            $menu->updated_by = $dto->updated_by;
            $menu->is_show_front = $dto->is_show_front;
            $menu->is_show_side_menu = $dto->is_show_side_menu ?? $menu->is_show_side_menu;

            if ($menu->is_show_front == true && $menu->countMenusWithShowFront() >= $this->maxMenusShowFront()) {
                DB::rollBack();
                throw new \App\Exceptions\Domain\ValidationException('Anda telah mencapai batas maksimum untuk menampilkan menu prioritas di halaman depan (Maksimal '.$this->maxMenusShowFront().')');
            }

            $menu->save();

            if ($menu->has_sub == false) {
                $content = Content::findOrFail($menu->content?->id);
                $content->parent_type = Menu::class;
                $content->parent_id = $menu->id;
                $content->layout_id = $dto->layout_id;
                $content->updated_by = $dto->updated_by;
                $content->is_accessible = $menu->is_active ? true : ($dto->is_accessible ?? false);
                if (in_array($dto->type, ['publication', 'repository', 'gallery'])) {
                    $content->is_show_home = $dto->is_show_home;
                    $content->is_show_tag = $dto->is_show_tag;
                }
                $content->save();

                $this->saveContentService->store($content, $dto->toArray(), $dto->updated_by);
                $this->saveContentService->storeAdditionalContent(Content::class, $content, $dto->toArray(), $dto->updated_by);

                if ($content->is_accessible && ! $content->is_section) {
                    $this->globalSearchService->insertContent($content->id);
                } else {
                    $this->globalSearchService->delete(Menu::class, $content->id);
                }
            }

            DB::commit();

            return $menu;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $data = Menu::findOrFail($id);
            if ($data->is_dedicated) {
                DB::rollBack();
                throw new \App\Exceptions\Domain\ValidationException('Menu tidak boleh dihapus');
            }
            if ($data->subMenu()->exists()) {
                DB::rollBack();
                throw new \App\Exceptions\Domain\ValidationException('Menu masih memiliki sub menu');
            }
            $data->delete();

            DB::commit();

            return true;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
}
