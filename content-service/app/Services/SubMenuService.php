<?php

namespace App\Services;

use App\Models\Content;
use App\Models\SubMenu;
use App\Services\DTOs\QueryFilterDTO;
use App\Services\DTOs\SubMenuDTO;
use Illuminate\Support\Facades\DB;

class SubMenuService
{
    protected SaveContentsService $saveContentService;

    protected GlobalSearchService $globalSearchService;

    public function __construct(
        protected QueryService $queryService
    ) {
        $this->saveContentService = new SaveContentsService;
        $this->globalSearchService = new GlobalSearchService;
    }

    public function index(QueryFilterDTO $filter)
    {
        $query = DB::table('sub_menus')
            ->join('menus', 'sub_menus.menu_id', '=', 'menus.id')
            ->leftJoin('contents', function ($join) {
                $join->on('sub_menus.id', '=', 'contents.parent_id')
                    ->where('contents.parent_type', '=', SubMenu::class);
            })
            ->select(
                'sub_menus.id',
                'menus.title as menu_title',
                'sub_menus.title',
                'sub_menus.is_active',
                'sub_menus.slug',
                'contents.type',
                'sub_menus.has_child',
                'sub_menus.updated_at'
            )
            ->selectRaw("
                CASE 
                    WHEN sub_menus.has_child = false 
                    THEN '/' || menus.slug || '/' || sub_menus.slug
                    ELSE NULL
                END as url
            ");
        if ($filter->has_child !== null) {
            $query->where('sub_menus.has_child', $filter->has_child);
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

        return $this->queryService->applyFiltersAndPaginate($query, $filter, ['sub_menus.title', 'contents.type']);
    }

    public function detail($id)
    {
        $subMenu = SubMenu::findOrFail($id);

        if (! $subMenu->has_child) {
            $content = Content::where(['parent_type' => SubMenu::class, 'parent_id' => $subMenu->id])->first();
            $subMenu->applyContent($content);
            $subMenu->applyAdditionalContent($content);
        }

        return $subMenu;
    }

    public function create(SubMenuDTO $dto)
    {
        DB::beginTransaction();
        try {
            $subMenu = new SubMenu;
            $subMenu->menu_id = $dto->menu_id;
            $subMenu->title = $dto->title;
            $subMenu->slug = $dto->slug;
            $subMenu->is_active = $dto->is_active;
            $subMenu->order = $dto->order;
            $subMenu->created_by = $dto->created_by;
            $subMenu->updated_by = $dto->updated_by;
            $subMenu->has_child = $dto->has_child;
            $subMenu->is_show_side_menu = $dto->is_show_side_menu ?? false;
            $subMenu->save();

            if ($subMenu->has_child == false) {
                $content = new Content;
                $content->parent_type = SubMenu::class;
                $content->parent_id = $subMenu->id;
                $content->type = $dto->type;
                $content->layout_id = $dto->layout_id;
                $content->created_by = $dto->created_by;
                $content->updated_by = $dto->updated_by;
                $content->is_accessible = $subMenu->is_active ? true : ($dto->is_accessible ?? false);
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

            return $subMenu;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function update($id, SubMenuDTO $dto)
    {
        DB::beginTransaction();
        try {
            $subMenu = SubMenu::findOrFail($id);

            $subMenu->menu_id = $dto->menu_id;
            $subMenu->title = $dto->title;
            $subMenu->slug = $dto->slug;
            $subMenu->is_active = $dto->is_active;
            $subMenu->order = $dto->order;
            $subMenu->updated_by = $dto->updated_by;
            $subMenu->is_show_side_menu = $dto->is_show_side_menu ?? $subMenu->is_show_side_menu;
            $subMenu->save();

            if ($subMenu->has_child == false) {
                $content = Content::findOrFail($subMenu->content?->id);
                $content->parent_type = SubMenu::class;
                $content->parent_id = $subMenu->id;
                $content->layout_id = $dto->layout_id;
                $content->updated_by = $dto->updated_by;
                $content->is_accessible = $subMenu->is_active ? true : ($dto->is_accessible ?? false);
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
                    $this->globalSearchService->delete(SubMenu::class, $content->id);
                }
            }

            DB::commit();

            return $subMenu;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $data = SubMenu::findOrFail($id);
            if ($data->childSubMenu()->exists()) {
                DB::rollBack();
                throw new \Exception('Sub menu masih memiliki child sub menu', 409);
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
