<?php

namespace App\Services;

use App\Helpers\LogActivity;
use App\Models\MsHoliday;
use App\Services\DTOs\HolidayDTO;
use App\Services\DTOs\QueryFilterDTO;
use Illuminate\Support\Facades\DB;

class HolidayService
{
    public function __construct(
        protected QueryService $queryService
    ) {}

    public function index(QueryFilterDTO $filter)
    {
        $query = DB::table('ms_holidays')
            ->select('id', 'name', 'date');

        return $this->queryService->applyFiltersAndPaginate($query, $filter, ['name']);
    }

    public function detail($id)
    {
        return MsHoliday::findOrFail($id);
    }

    public function create(HolidayDTO $dto)
    {
        $hariLibur = new MsHoliday;
        $hariLibur->name = $dto->name;
        $hariLibur->date = $dto->date;
        $hariLibur->created_by = $dto->created_by;
        $hariLibur->updated_by = $dto->updated_by;
        $hariLibur->save();

        return $hariLibur;
    }

    public function update($id, HolidayDTO $dto)
    {
        $hariLibur = MsHoliday::findOrFail($id);
        $hariLibur->name = $dto->name;
        $hariLibur->date = $dto->date;
        $hariLibur->updated_by = $dto->updated_by;
        $hariLibur->save();

        return $hariLibur;
    }

    public function destroy($id)
    {
        MsHoliday::findOrFail($id)->delete();

        return true;
    }

    public function importBulk(array $parsedRows): void
    {
        MsHoliday::insert($parsedRows);
        LogActivity::createCustom('hari libur', 'Melakukan penginputan hari libur secara massal melalui unggahan excel', null, auth('admin')->user());
    }
}
