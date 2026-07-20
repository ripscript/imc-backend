<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Models\MsNegara;
use App\Models\MsPerwakilan;
use App\Models\MsPerwakilanNegara;
use App\Rules\UniqueSoftDelete;
use App\Services\QueryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PerwakilanController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = DB::table('ms_perwakilan')
                ->whereNull('deleted_at')
                ->select('id', 'name', 'type');

            if ($request->filled('search')) {
                $search = $request->search;

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

            $getData = $this->queryService->applyFiltersAndPaginate($query, $request, []);

            $perwakilanList = $getData['data'];
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
                    'negara' => $negara
                ];
            }

            return ApiResponse::success([
                'data' => $data,
                'meta' => $getData['meta']
            ], 'Berhasil mengambil data perwakilan');
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function detail($id)
    {
        try {
            $perwakilan = MsPerwakilan::findOrFail($id);
            $negaraIds = MsPerwakilanNegara::join('ms_negara', 'ms_negara.id', '=', 'ms_perwakilan_negara.negara_id')
                ->where('ms_perwakilan_negara.perwakilan_id', $id)
                ->whereNull('ms_negara.deleted_at')
                ->pluck('ms_perwakilan_negara.negara_id');
            $perwakilan->negara_id = $negaraIds;

            return ApiResponse::success($perwakilan, 'Berhasil mengambil detail perwakilan');
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function create(Request $request)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'name' => ['required', 'string', 'max:100', new UniqueSoftDelete('ms_perwakilan')],
                'negara_id' => 'nullable|array',
                'type' => 'required|string|max:10'
            ]);

            $user = auth('admin')->user()->name;

            $perwakilan = new MsPerwakilan();
            $perwakilan->name = $request->name;
            $perwakilan->type = $request->type;
            $perwakilan->created_by = $user;
            $perwakilan->updated_by = $user;
            $perwakilan->save();

            if (count($request->negara_id) > 0) {
                $totalNegaraValid = MsNegara::whereIn('id', $request->negara_id)->count();
                if ($totalNegaraValid !== count($request->negara_id)) {
                    DB::rollBack();
                    return ApiResponse::error('Terdapat negara yang tidak valid', '', 422);
                }
                foreach ($request->negara_id as $negara) {
                    MsPerwakilanNegara::create([
                        'perwakilan_id' => $perwakilan->id,
                        'negara_id' => $negara,
                        'created_by' => $user,
                        'updated_by' => $user
                    ]);
                }
            }

            DB::commit();
            return ApiResponse::success($perwakilan, 'Berhasil menambahkan data perwakilan', 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $perwakilan = MsPerwakilan::findOrFail($id);
            
            $request->validate([
                'name' => ['required', 'string', 'max:100', new UniqueSoftDelete('ms_perwakilan', 'name', $id)],
                'negara_id' => 'nullable|array',
                'type' => 'required|string|max:10'
            ]);

            $user = auth('admin')->user()->name;

            $perwakilan->name = $request->name;
            $perwakilan->type = $request->type;
            $perwakilan->updated_by = $user;
            $perwakilan->save();

            if (count($request->negara_id) > 0) {
                $totalNegaraValid = MsNegara::whereIn('id', $request->negara_id)->count();
                if ($totalNegaraValid !== count($request->negara_id)) {
                    DB::rollBack();
                    return ApiResponse::error('Terdapat negara yang tidak valid', '', 422);
                }
                foreach ($request->negara_id as $negara) {
                    MsPerwakilanNegara::firstOrCreate([
                        'perwakilan_id' => $perwakilan->id,
                        'negara_id' => $negara,
                    ],
                    [
                        'created_by' => $user,
                        'updated_by' => $user,
                    ]);
                }
                MsPerwakilanNegara::where('perwakilan_id', $id)->whereNotIn('negara_id', $request->negara_id)->delete();
            } else {
                MsPerwakilanNegara::where('perwakilan_id', $id)->delete();
            }

            DB::commit();
            return ApiResponse::success($perwakilan, 'Berhasil memperbarui data perwakilan');
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            MsPerwakilan::findOrFail($id)->delete();

            DB::commit();
            return ApiResponse::success('', 'Berhasil menghapus data perwakilan');
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
}