<?php

namespace App\Services;

use App\Models\MsSatuanKerja;
use App\Models\MsSubSatuanKerja;
use App\Services\DTOs\QueryFilterDTO;
use App\Services\DTOs\SatuanKerjaDTO;
use Illuminate\Support\Facades\DB;

class SatuanKerjaService
{
    public function __construct(
        protected QueryService $queryService
    ) {}

    public function index(QueryFilterDTO $filter)
    {
        $query = DB::table('ms_satuan_kerja')
            ->whereNull('deleted_at')
            ->select('id', 'name');

        return $this->queryService->applyFiltersAndPaginate($query, $filter, ['name']);
    }

    public function detail($id)
    {
        return MsSatuanKerja::with('subSatuanKerja')->findOrFail($id);
    }

    public function create(SatuanKerjaDTO $dto)
    {
        DB::beginTransaction();
        try {
            $satuanKerja = new MsSatuanKerja;
            $satuanKerja->name = $dto->name;
            $satuanKerja->created_by = $dto->created_by;
            $satuanKerja->updated_by = $dto->updated_by;
            $satuanKerja->save();

            if ($dto->sub && count($dto->sub) > 0) {
                foreach ($dto->sub as $subSatuanKerja) {
                    MsSubSatuanKerja::firstOrCreate([
                        'name' => $subSatuanKerja,
                        'satuan_kerja_id' => $satuanKerja->id,
                    ], [
                        'created_by' => $dto->created_by,
                        'updated_by' => $dto->updated_by,
                    ]);
                }
            }

            DB::commit();

            return $satuanKerja;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function update($id, SatuanKerjaDTO $dto)
    {
        DB::beginTransaction();
        try {
            $satuanKerja = MsSatuanKerja::findOrFail($id);
            $satuanKerja->name = $dto->name;
            $satuanKerja->updated_by = $dto->updated_by;
            $satuanKerja->save();

            DB::commit();

            return $satuanKerja;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function destroy($id)
    {
        MsSatuanKerja::findOrFail($id)->delete();

        return true;
    }
}
