<?php

namespace App\Services;

use App\Models\ChildSubMenu;
use App\Models\Content;
use App\Services\DTOs\ChildSubMenuDTO;
use App\Services\DTOs\QueryFilterDTO;
use Illuminate\Support\Facades\DB;

class ChildSubMenuService
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
        $query = DB::table('child_sub_menus')
            ->join('sub_menus', 'child_sub_menus.sub_menu_id', '=', 'sub_menus.id')
            ->join('menus', 'sub_menus.menu_id', '=', 'menus.id')
            ->leftJoin('contents', function ($join) {
                $join->on('child_sub_menus.id', '=', 'contents.parent_id')
                    ->where('contents.parent_type', '=', ChildSubMenu::class);
            })
            ->select(
                'child_sub_menus.id',
                'menus.title as menu_title',
                'sub_menus.title as sub_menu_title',
                'child_sub_menus.title',
                'child_sub_menus.is_active',
                'child_sub_menus.slug',
                'contents.type',
                'child_sub_menus.updated_at'
            )
            ->selectRaw("
                '/' || menus.slug || '/' || sub_menus.slug || '/' || child_sub_menus.slug as url
            ");

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

        return $this->queryService->applyFiltersAndPaginate($query, $filter, ['child_sub_menus.title', 'contents.type']);
    }

    public function detail($id)
    {
        $childSubMenu = ChildSubMenu::findOrFail($id);
        $content = Content::where(['parent_type' => ChildSubMenu::class, 'parent_id' => $childSubMenu->id])->first();
        $childSubMenu->applyContent($content);
        $childSubMenu->applyAdditionalContent($content);

        return $childSubMenu;
    }

    public function create(ChildSubMenuDTO $dto)
    {
        DB::beginTransaction();
        try {
            $childSubMenu = new ChildSubMenu;
            $childSubMenu->sub_menu_id = $dto->sub_menu_id;
            $childSubMenu->title = $dto->title;
            $childSubMenu->slug = $dto->slug;
            $childSubMenu->is_active = $dto->is_active;
            $childSubMenu->order = $dto->order;
            $childSubMenu->created_by = $dto->created_by;
            $childSubMenu->updated_by = $dto->updated_by;
            $childSubMenu->save();

            $content = new Content;
            $content->parent_type = ChildSubMenu::class;
            $content->parent_id = $childSubMenu->id;
            $content->type = $dto->type;
            $content->layout_id = $dto->layout_id;
            $content->created_by = $dto->created_by;
            $content->updated_by = $dto->updated_by;
            $content->is_accessible = $childSubMenu->is_active ? true : ($dto->is_accessible ?? false);
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

            DB::commit();

            return $childSubMenu;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function update($id, ChildSubMenuDTO $dto)
    {
        DB::beginTransaction();
        try {
            $childSubMenu = ChildSubMenu::findOrFail($id);

            $childSubMenu->sub_menu_id = $dto->sub_menu_id;
            $childSubMenu->title = $dto->title;
            $childSubMenu->slug = $dto->slug;
            $childSubMenu->is_active = $dto->is_active;
            $childSubMenu->order = $dto->order;
            $childSubMenu->updated_by = $dto->updated_by;
            $childSubMenu->save();

            $content = Content::findOrFail($childSubMenu->content?->id);
            $content->parent_type = ChildSubMenu::class;
            $content->parent_id = $childSubMenu->id;
            $content->layout_id = $dto->layout_id;
            $content->updated_by = $dto->updated_by;
            $content->is_accessible = $childSubMenu->is_active ? true : ($dto->is_accessible ?? false);
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
                $this->globalSearchService->delete(ChildSubMenu::class, $content->id);
            }

            DB::commit();

            return $childSubMenu;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $data = ChildSubMenu::findOrFail($id);
            $content = Content::where(['parent_type' => ChildSubMenu::class, 'parent_id' => $data->id])->first();
            if ($content) {
                $content->delete();
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
