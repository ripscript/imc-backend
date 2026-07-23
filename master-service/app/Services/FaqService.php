<?php

namespace App\Services;

use App\Helpers\ApiResponse;
use App\Models\MsFaq;
use App\Models\MsKategoriFaq;
use App\Services\DTOs\FaqDTO;
use App\Services\DTOs\QueryFilterDTO;
use Illuminate\Support\Facades\DB;

class FaqService
{
    public function __construct(
        protected QueryService $queryService
    ) {}

    public function index(QueryFilterDTO $filter)
    {
        $query = DB::table('ms_faq')
            ->join('ms_kategori_faq', 'ms_faq.kategori_faq_id', '=', 'ms_kategori_faq.id')
            ->whereNull('ms_faq.deleted_at')
            ->whereNull('ms_kategori_faq.deleted_at')
            ->select('ms_faq.id', 'title', 'description', 'ms_kategori_faq.name as kategori_faq', 'ms_faq.is_active', 'ms_faq.updated_at');

        return $this->queryService->applyFiltersAndPaginate($query, $filter, ['title', 'description', 'ms_kategori_faq.name'], 'ms_faq.updated_at');
    }

    public function detail($id)
    {
        return MsFaq::findOrFail($id);
    }

    public function create(FaqDTO $dto)
    {
        DB::beginTransaction();
        try {
            $exists = MsKategoriFaq::find($dto->kategori_faq_id);
            if (! $exists) {
                DB::rollBack();

                return ApiResponse::error('Kategori FAQ tidak valid', '', 422);
            }

            $faq = new MsFaq;
            $faq->title = $dto->title;
            $faq->description = $dto->description;
            $faq->is_active = $dto->is_active;
            $faq->kategori_faq_id = $dto->kategori_faq_id;
            $faq->created_by = $dto->created_by;
            $faq->updated_by = $dto->updated_by;
            $faq->save();

            DB::commit();

            return $faq;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function update($id, FaqDTO $dto)
    {
        DB::beginTransaction();
        try {
            $faq = MsFaq::findOrFail($id);

            $exists = MsKategoriFaq::find($dto->kategori_faq_id);
            if (! $exists) {
                DB::rollBack();

                return ApiResponse::error('Kategori FAQ tidak valid', '', 422);
            }

            $faq->title = $dto->title;
            $faq->description = $dto->description;
            $faq->is_active = $dto->is_active;
            $faq->kategori_faq_id = $dto->kategori_faq_id;
            $faq->updated_by = $dto->updated_by;
            $faq->save();

            DB::commit();

            return $faq;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            MsFaq::findOrFail($id)->delete();
            DB::commit();

            return true;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
}
