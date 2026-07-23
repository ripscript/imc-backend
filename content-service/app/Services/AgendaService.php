<?php

namespace App\Services;

use App\Models\ContentDetailAgenda;
use App\Services\DTOs\AgendaDTO;
use App\Services\DTOs\QueryFilterDTO;
use Illuminate\Support\Facades\DB;

class AgendaService
{
    protected ParentContentService $parentContentService;

    public function __construct(
        protected QueryService $queryService
    ) {
        $this->parentContentService = new ParentContentService;
    }

    public function sideMenu()
    {
        return $this->parentContentService->getParentByType('agenda');
    }

    public function index(QueryFilterDTO $filter, int $contentId)
    {
        $query = DB::table('content_detail_agendas')
            ->where('content_id', $contentId);

        return $this->queryService->applyFiltersAndPaginate($query, $filter, ['event_name']);
    }

    public function detail($id)
    {
        return ContentDetailAgenda::findOrFail($id);
    }

    public function create(AgendaDTO $dto)
    {
        DB::beginTransaction();
        try {
            $data = new ContentDetailAgenda;
            $data->content_id = $dto->content_id;
            $data->event_name = $dto->event_name;
            $data->is_active = $dto->is_active;
            $data->is_range = $dto->is_range;
            $data->datetime_start = $dto->datetime_start;
            $data->datetime_end = $dto->is_range ? $dto->datetime_end : null;
            $data->created_by = $dto->created_by;
            $data->updated_by = $dto->updated_by;
            $data->save();

            DB::commit();

            return $data;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function update($id, AgendaDTO $dto)
    {
        DB::beginTransaction();
        try {
            $data = ContentDetailAgenda::findOrFail($id);

            $data->event_name = $dto->event_name;
            $data->is_active = $dto->is_active;
            $data->is_range = $dto->is_range;
            $data->datetime_start = $dto->datetime_start;
            $data->datetime_end = $dto->is_range ? $dto->datetime_end : null;
            $data->updated_by = $dto->updated_by;
            $data->save();

            DB::commit();

            return $data;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $data = ContentDetailAgenda::findOrFail($id);
            $data->delete();

            DB::commit();

            return true;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
}
