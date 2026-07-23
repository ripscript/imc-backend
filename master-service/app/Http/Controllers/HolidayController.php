<?php

namespace App\Http\Controllers;

use App\Exports\HolidayTemplateExport;
use App\Helpers\ApiResponse;
use App\Http\Requests\CreateHolidayRequest;
use App\Http\Requests\HolidayImportRequest;
use App\Http\Requests\UpdateHolidayRequest;
use App\Http\Resources\HolidayResource;
use App\Imports\HolidayImport;
use App\Services\DTOs\HolidayDTO;
use App\Services\DTOs\QueryFilterDTO;
use App\Services\HolidayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class HolidayController extends Controller
{
    public function __construct(
        protected HolidayService $holidayService
    ) {}

    public function index(Request $request)
    {
        try {
            $data = $this->holidayService->index(QueryFilterDTO::fromRequest($request));

            return ApiResponse::success($data, 'Berhasil mengambil data hari libur');
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function detail($id)
    {
        try {
            $hariLibur = $this->holidayService->detail($id);

            return ApiResponse::success(new HolidayResource($hariLibur), 'Berhasil mengambil data detail hari libur');
        } catch (\Exception $th) {
            throw $th;
        }
    }

    public function create(CreateHolidayRequest $request)
    {
        try {
            $dto = new HolidayDTO($request->validated());
            $dto->created_by = auth('admin')->user()->name;
            $dto->updated_by = auth('admin')->user()->name;

            $result = $this->holidayService->create($dto);

            return ApiResponse::success(new HolidayResource($result), 'Berhasil menambahkan data hari libur', 201);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function update(UpdateHolidayRequest $request, $id)
    {
        try {
            $dto = new HolidayDTO($request->validated());
            $dto->updated_by = auth('admin')->user()->name;

            $result = $this->holidayService->update($id, $dto);

            return ApiResponse::success(new HolidayResource($result), 'Berhasil memperbarui data hari libur');
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function destroy($id)
    {
        try {
            $this->holidayService->destroy($id);

            return ApiResponse::success('', 'Berhasil menghapus data hari libur');
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function templateImport()
    {
        $fileName = 'hari_libur_template.xlsx';

        return Excel::download(new HolidayTemplateExport, $fileName);
    }

    public function import(HolidayImportRequest $request)
    {
        DB::beginTransaction();
        try {
            $import = new HolidayImport;
            Excel::import($import, $request->file('file'));

            if (! empty($import->invalidDates)) {
                $invalidRows = array_map(function ($item) {
                    return $item['row'];
                }, $import->invalidDates);
                $invalidRowsString = implode(', ', $invalidRows);

                DB::rollBack();

                return ApiResponse::error('Import dibatalkan. Terdapat tanggal tidak valid pada baris: '.$invalidRowsString, null, 422);
            }

            if (! empty($import->parsedRows)) {
                $this->holidayService->importBulk($import->parsedRows);
            }

            DB::commit();

            return ApiResponse::success('', 'Import hari libur berhasil dilakukan.');
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
}
