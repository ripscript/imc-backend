<?php

namespace App\Services;

use App\Models\LatestInformation;
use App\Services\DTOs\LatestInformationDTO;
use App\Services\DTOs\QueryFilterDTO;
use Illuminate\Support\Facades\DB;

class LatestInformationService
{
    public function __construct(
        protected QueryService $queryService
    ) {}

    public function index(QueryFilterDTO $filter)
    {
        $query = DB::table('latest_informations')
            ->select('id', 'title', 'url', 'is_active', 'publish_date');

        return $this->queryService->applyFiltersAndPaginate($query, $filter, ['title']);
    }

    public function detail($id)
    {
        return LatestInformation::findOrFail($id);
    }

    public function create(LatestInformationDTO $dto)
    {
        $informasi = new LatestInformation;
        $informasi->title = $dto->title;
        $informasi->url = $dto->url;
        $informasi->publish_date = $dto->publish_date;
        $informasi->is_active = $dto->is_active;
        $informasi->created_by = $dto->created_by;
        $informasi->updated_by = $dto->updated_by;
        $informasi->save();

        return $informasi;
    }

    public function update($id, LatestInformationDTO $dto)
    {
        $informasi = LatestInformation::findOrFail($id);
        $informasi->title = $dto->title;
        $informasi->url = $dto->url;
        $informasi->publish_date = $dto->publish_date;
        $informasi->is_active = $dto->is_active;
        $informasi->updated_by = $dto->updated_by;
        $informasi->save();

        return $informasi;
    }

    public function destroy($id)
    {
        LatestInformation::findOrFail($id)->delete();

        return true;
    }
}
