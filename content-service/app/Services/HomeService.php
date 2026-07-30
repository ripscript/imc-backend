<?php

namespace App\Services;

use App\Models\Content;
use App\Models\Menu;
use App\Models\MsLayout;
use App\Services\DTOs\ContentDTO;
use App\Services\DTOs\QueryFilterDTO;
use Illuminate\Support\Facades\DB;

class HomeService
{
    protected SaveContentsService $saveContentService;

    const SLUG_BERANDA = 'beranda';

    public function __construct(
        protected QueryService $queryService
    ) {
        $this->saveContentService = new SaveContentsService;
    }

    public function index(QueryFilterDTO $filter)
    {
        $query = DB::table('contents')
            ->join('menus', function ($join) {
                $join->on('menus.id', '=', 'contents.parent_id')
                    ->where('contents.parent_type', '=', Menu::class);
            })
            ->where('menus.slug', '=', self::SLUG_BERANDA)
            ->select(
                'contents.id',
                'contents.order',
                'contents.type',
                'contents.section_title',
                'contents.is_section'
            );

        return $this->queryService->applyFiltersAndPaginate($query, $filter, ['contents.section_title', 'contents.type'], 'contents.updated_at');
    }

    public function detail($id)
    {
        $content = Content::findOrFail($id);
        $content->content = $content->getDetailBasedOnType();

        return $content;
    }

    public function create(ContentDTO $dto)
    {
        DB::beginTransaction();
        try {
            $beranda = Menu::where('slug', self::SLUG_BERANDA)->first();
            if (! $beranda) {
                DB::rollBack();
                throw new \App\Exceptions\Domain\EntityNotFoundException('Menu beranda tidak ditemukan');
            }

            $layoutId = MsLayout::findByType('landing_page')->id;

            $content = new Content;
            $content->parent_type = Menu::class;
            $content->parent_id = $beranda->id;
            $content->section_title = $dto->section_title;
            $content->is_section = $dto->is_section;
            $content->order = $dto->order;
            $content->type = $dto->type;
            $content->layout_id = $layoutId;
            $content->created_by = $dto->created_by;
            $content->updated_by = $dto->updated_by;
            $content->save();

            $this->saveContentService->store($content, $dto->toArray(), $dto->created_by);

            DB::commit();

            return $content;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function update($id, ContentDTO $dto)
    {
        DB::beginTransaction();
        try {
            $content = Content::findOrFail($id);

            $content->is_section = $dto->is_section;
            $content->section_title = $dto->section_title;
            $content->order = $dto->order;
            $content->updated_by = $dto->updated_by;
            $content->save();

            $this->saveContentService->store($content, $dto->toArray(), $dto->updated_by);

            DB::commit();

            return $content;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $data = Content::findOrFail($id);
            $data->delete();

            DB::commit();

            return true;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
}
