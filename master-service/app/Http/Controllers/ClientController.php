<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\CreateClientRequest;
use App\Http\Requests\UpdateClientRequest;
use App\Http\Resources\ClientResource;
use App\Models\PmClient;
use App\Services\DTOs\ClientDTO;
use App\Services\DTOs\QueryFilterDTO;
use App\Services\ProjectManagement\ClientService;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function __construct(
        protected ClientService $clientService
    ) {}

    public function index(Request $request)
    {
        $this->authorize('viewAny', PmClient::class);

        $data = $this->clientService->index(QueryFilterDTO::fromRequest($request));

        return ApiResponse::success($data, 'Berhasil mengambil data client');
    }

    public function detail($id)
    {
        $client = $this->clientService->detail($id);
        $this->authorize('view', $client);

        return ApiResponse::success(new ClientResource($client), 'Berhasil mengambil data detail client');
    }

    public function create(CreateClientRequest $request)
    {
        $this->authorize('create', PmClient::class);

        $dto = new ClientDTO($request->validated());
        $dto->created_by = auth('admin')->user()->name;
        $dto->updated_by = auth('admin')->user()->name;

        $result = $this->clientService->create($dto);

        return ApiResponse::success(new ClientResource($result), 'Client dibuat', 201);
    }

    public function update(UpdateClientRequest $request, $id)
    {
        $dto = new ClientDTO($request->validated());
        $dto->updated_by = auth('admin')->user()->name;

        $result = $this->clientService->update($id, $dto);
        $this->authorize('update', $result);

        return ApiResponse::success(new ClientResource($result), 'Client diperbarui');
    }

    public function destroy($id)
    {
        $this->authorize('delete', PmClient::class);

        $this->clientService->destroy($id);

        return ApiResponse::success('', 'Client dihapus');
    }
}
