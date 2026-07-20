<?php

namespace App\Http\Controllers;

use App\Exports\HolidayTemplateExport;
use App\Helpers\ApiResponse;
use App\Helpers\LogActivity;
use App\Imports\HolidayImport;
use App\Models\MsHoliday;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class HolidayController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = DB::table('ms_holidays')
                ->select('id','name', 'date');

            $data = $this->queryService->applyFiltersAndPaginate($query, $request, ['name']);

            return ApiResponse::success($data, 'Berhasil mengambil data hari libur');
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function detail($id)
    {
        try {
            $hariLibur = MsHoliday::findOrFail($id);
            return ApiResponse::success($hariLibur, 'Berhasil mengambil data detail hari libur');
        } catch (\Exception $th) {
            throw $th; 
        }
    }

    // TODO: Update due date response permohonan dan keberatan yang masih berjalan
    public function create(Request $request)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'date' => 'required|date_format:Y-m-d',
            ]);

            $user = auth('admin')->user()->name;

            $hariLibur = new MsHoliday();
            $hariLibur->name = $request->name;
            $hariLibur->date = $request->date;
            $hariLibur->created_by = $user;
            $hariLibur->updated_by = $user;
            $hariLibur->save();

            DB::commit();
            return ApiResponse::success($hariLibur, 'Berhasil menambahkan data hari libur', 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $hariLibur = MsHoliday::findOrFail($id);
            
            $request->validate([
                'name' => 'required|string|max:255',
                'date' => 'required|date_format:Y-m-d',
            ]);

            $user = auth('admin')->user()->name;

            $hariLibur->name = $request->name;
            $hariLibur->date = $request->date;
            $hariLibur->updated_by = $user;
            $hariLibur->save();

            DB::commit();
            return ApiResponse::success($hariLibur, 'Berhasil memperbarui data hari libur');
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            MsHoliday::findOrFail($id)->delete();

            DB::commit();
            return ApiResponse::success('', 'Berhasil menghapus data hari libur');
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function templateImport()
    {
        $fileName = 'hari_libur_template.xlsx';
        return Excel::download(new HolidayTemplateExport, $fileName);
    }

    public function import(Request $request)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'file' => 'required|mimes:xlsx,xls'
            ]);

            $import = new HolidayImport();
            Excel::import($import, $request->file('file'));

            if (!empty($import->invalidDates)) {
                $invalidRows = array_map(function ($item) {
                    return $item['row'];
                }, $import->invalidDates);
                $invalidRowsString = implode(', ', $invalidRows);

                DB::rollBack();

                return ApiResponse::error('Import dibatalkan. Terdapat tanggal tidak valid pada baris: ' . $invalidRowsString, null, 422);
            }

            if (!empty($import->parsedRows)) {
                MsHoliday::insert($import->parsedRows);
            }

            DB::commit();

            LogActivity::createCustom('hari libur', 'Melakukan penginputan hari libur secara massal melalui unggahan excel', null, auth('admin')->user());

            return ApiResponse::success('', 'Import hari libur berhasil dilakukan.');
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
}
