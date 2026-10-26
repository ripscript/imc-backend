<?php

namespace App\Services;

use App\Exceptions\Domain\ValidationException;
use App\Models\MsNegara;
use App\Models\MsPerwakilan;
use App\Models\MsPerwakilanNegara;
use App\Services\DTOs\PerwakilanDTO;
use App\Services\DTOs\QueryFilterDTO;
use Illuminate\Support\Facades\DB;

class PerwakilanService
{
    public function __construct(
        protected QueryService $queryService
    ) {}

    public function index(QueryFilterDTO $filter)
    {
        $query = DB::table('ms_perwakilan')
            ->whereNull('deleted_at')
            ->select('id', 'name', 'type');

        if ($filter->search) {
            $search = $filter->search;
            $perwakilanIds = DB::table('ms_perwakilan_negara')
                ->leftJoin('ms_negara', 'ms_perwakilan_negara.negara_id', '=', 'ms_negara.id')
                ->where('ms_negara.name', 'ilike', "%$search%")
                ->pluck('ms_perwakilan_negara.perwakilan_id');

            $query->where(function ($q) use ($search, $perwakilanIds) {
                $q->where('name', 'ilike', "%$search%")
                    ->orWhere('type', 'ilike', "%$search%")
                    ->orWhereIn('id', $perwakilanIds);
            });
        }

        $getData = $this->queryService->applyFiltersAndPaginate($query, $filter, []);

        $perwakilanList = $getData['data'] ?? $getData;
        $ids = collect($perwakilanList)->pluck('id');

        $negaraList = DB::table('ms_perwakilan_negara')
            ->leftJoin('ms_negara', 'ms_perwakilan_negara.negara_id', '=', 'ms_negara.id')
            ->whereIn('ms_perwakilan_negara.perwakilan_id', $ids)
            ->whereNull('ms_negara.deleted_at')
            ->select(
                'ms_perwakilan_negara.perwakilan_id',
                'ms_negara.id as negara_id',
                'ms_negara.name as negara_name'
            )
            ->get()
            ->groupBy('perwakilan_id');

        $data = [];
        foreach ($perwakilanList as $item) {
            $negara = isset($negaraList[$item->id])
                ? $negaraList[$item->id]->map(function ($n) {
                    return [
                        'id' => $n->negara_id,
                        'name' => $n->negara_name,
                    ];
                })->values()->toArray()
                : [];

            $data[] = [
                'id' => $item->id,
                'name' => $item->name,
                'type' => $item->type,
                'negara' => $negara,
            ];
        }

        return ['data' => $data, 'meta' => $getData['meta'] ?? null];
    }

    public function detail($id)
    {
        $perwakilan = MsPerwakilan::findOrFail($id);
        $negaraIds = MsPerwakilanNegara::join('ms_negara', 'ms_negara.id', '=', 'ms_perwakilan_negara.negara_id')
            ->where('ms_perwakilan_negara.perwakilan_id', $id)
            ->whereNull('ms_negara.deleted_at')
            ->pluck('ms_perwakilan_negara.negara_id');
        $perwakilan->negara_id = $negaraIds;

        return $perwakilan;
    }

    public function create(PerwakilanDTO $dto)
    {
        DB::beginTransaction();
        try {
            $perwakilan = new MsPerwakilan;
            $perwakilan->name = $dto->name;
            $perwakilan->type = $dto->type;
            $perwakilan->created_by = $dto->created_by;
            $perwakilan->updated_by = $dto->updated_by;
            $perwakilan->save();

            if ($dto->negara_id && count($dto->negara_id) > 0) {
                $totalNegaraValid = MsNegara::whereIn('id', $dto->negara_id)->count();
                if ($totalNegaraValid !== count($dto->negara_id)) {
                    DB::rollBack();

                    throw new ValidationException('Terdapat negara yang tidak valid');
                }
                foreach ($dto->negara_id as $negara) {
                    MsPerwakilanNegara::create([
                        'perwakilan_id' => $perwakilan->id,
                        'negara_id' => $negara,
                        'created_by' => $dto->created_by,
                        'updated_by' => $dto->updated_by,
                    ]);
                }
            }

            DB::commit();

            return $perwakilan;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function update($id, PerwakilanDTO $dto)
    {
        DB::beginTransaction();
        try {
            $perwakilan = MsPerwakilan::findOrFail($id);
            $perwakilan->name = $dto->name;
            $perwakilan->type = $dto->type;
            $perwakilan->updated_by = $dto->updated_by;
            $perwakilan->save();

            if ($dto->negara_id && count($dto->negara_id) > 0) {
                $totalNegaraValid = MsNegara::whereIn('id', $dto->negara_id)->count();
                if ($totalNegaraValid !== count($dto->negara_id)) {
                    DB::rollBack();

                    throw new ValidationException('Terdapat negara yang tidak valid');
                }
                foreach ($dto->negara_id as $negara) {
                    MsPerwakilanNegara::firstOrCreate([
                        'perwakilan_id' => $perwakilan->id,
                        'negara_id' => $negara,
                    ], [
                        'created_by' => $dto->created_by,
                        'updated_by' => $dto->updated_by,
                    ]);
                }
                MsPerwakilanNegara::where('perwakilan_id', $id)->whereNotIn('negara_id', $dto->negara_id)->delete();
            } else {
                MsPerwakilanNegara::where('perwakilan_id', $id)->delete();
            }

            DB::commit();

            return $perwakilan;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function destroy($id)
    {
        MsPerwakilan::findOrFail($id)->delete();

        return true;
    }
}
