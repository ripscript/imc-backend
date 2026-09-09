<?php

namespace App\Services\ProjectManagement;

use App\Models\PmClient;
use App\Services\DTOs\ClientDTO;
use App\Services\DTOs\QueryFilterDTO;
use App\Services\QueryService;
use Illuminate\Support\Facades\DB;

class ClientService
{
    public function __construct(
        protected QueryService $queryService
    ) {}

    public function index(QueryFilterDTO $filter)
    {
        $query = PmClient::query();

        return $this->queryService->applyFiltersAndPaginate($query, $filter, ['name', 'identifier']);
    }

    public function detail($id)
    {
        return PmClient::findOrFail($id);
    }

    public function create(ClientDTO $dto)
    {
        return DB::transaction(function () use ($dto) {
            $client = new PmClient;
            $client->name = $dto->name;
            $client->identifier = $dto->identifier;
            $client->status = $dto->status ?? 'active';
            $client->created_by = $dto->created_by;
            $client->updated_by = $dto->updated_by;
            $client->save();

            if (empty($client->identifier)) {
                $client->identifier = ProjectCodeGenerator::client($client->id);
                $client->save();
            }

            return $client;
        });
    }

    public function update($id, ClientDTO $dto)
    {
        $client = PmClient::findOrFail($id);

        if ($dto->name !== null) {
            $client->name = $dto->name;
        }
        if ($dto->status !== null) {
            $client->status = $dto->status;
        }
        $client->updated_by = $dto->updated_by;
        $client->save();

        return $client;
    }

    public function destroy($id)
    {
        PmClient::findOrFail($id)->delete();

        return true;
    }
}
