<?php

namespace App\Services;

use App\Models\MsSifatInformasi;
use App\Services\DTOs\QueryFilterDTO;
use App\Services\DTOs\SifatInformasiDTO;
use Illuminate\Support\Facades\DB;

class SifatInformasiService
{
    public function __construct(
        protected QueryService $queryService
    ) {}

    public function index(QueryFilterDTO $filter)
    {
        $query = DB::table('ms_sifat_informasi')
            ->whereNull('deleted_at')
            ->select('id', 'name', 'icon_path');

        return $this->queryService->applyFiltersAndPaginate($query, $filter, ['name']);
    }

    public function detail($id)
    {
        return MsSifatInformasi::findOrFail($id);
    }

    public function create(SifatInformasiDTO $dto)
    {
        $sifatInformasi = new MsSifatInformasi;
        $sifatInformasi->name = $dto->name;
        $sifatInformasi->icon_path = $dto->icon_path;
        $sifatInformasi->description = $dto->description;
        $sifatInformasi->link = $dto->link;
        $sifatInformasi->created_by = $dto->created_by;
        $sifatInformasi->updated_by = $dto->updated_by;
        $sifatInformasi->save();

        return $sifatInformasi;
    }

    public function update($id, SifatInformasiDTO $dto)
    {
        $sifatInformasi = MsSifatInformasi::findOrFail($id);
        $sifatInformasi->name = $dto->name;
        $sifatInformasi->icon_path = $dto->icon_path;
        $sifatInformasi->description = $dto->description;
        $sifatInformasi->link = $dto->link;
        $sifatInformasi->updated_by = $dto->updated_by;
        $sifatInformasi->save();

        return $sifatInformasi;
    }

    public function destroy($id)
    {
        MsSifatInformasi::findOrFail($id)->delete();

        return true;
    }
}
