<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\CreateAgendaRequest;
use App\Http\Requests\UpdateAgendaRequest;
use App\Services\AgendaService;
use App\Services\DTOs\AgendaDTO;
use App\Services\DTOs\QueryFilterDTO;
use App\Models\ContentDetailAgenda;
use Illuminate\Http\Request;

class AgendaController extends Controller
{
    public function __construct(
        protected AgendaService $agendaService
    ) {}

    public function sideMenu()
    {
        $this->authorize('viewAny', ContentDetailAgenda::class);
        return ApiResponse::success($this->agendaService->sideMenu(), 'Berhasil mengambil side menu untuk tipe agenda');
    }

    public function index(Request $request, $contentId)
    {
        $this->authorize('viewAny', ContentDetailAgenda::class);
        return ApiResponse::success($this->agendaService->index(QueryFilterDTO::fromRequest($request), $contentId), 'Berhasil mengambil data agenda');
    }

    public function create(CreateAgendaRequest $req)
    {
        $dto = new AgendaDTO($req->validated());
        $dto->created_by = $dto->updated_by = auth()->user()->name;
        $this->authorize('create', ContentDetailAgenda::class);

        return ApiResponse::success($this->agendaService->create($dto), 'Berhasil menambahkan data agenda', 201);
    }

    public function detail($id)
    {
        $result = $this->agendaService->detail($id);
        $this->authorize('view', $result);
        return ApiResponse::success($result, 'Berhasil mengambil data detail agenda');
    }

    public function update(UpdateAgendaRequest $req, $id)
    {
        $dto = new AgendaDTO($req->validated());
        $dto->updated_by = auth()->user()->name;

        $result = $this->agendaService->update($id, $dto);
        $this->authorize('update', $result);
        return ApiResponse::success($result, 'Berhasil memperbarui data agenda');
    }

    public function delete($id)
    {
        $model = ContentDetailAgenda::findOrFail($id);
        $this->authorize('delete', $model);
        $this->agendaService->destroy($id);

        return ApiResponse::success('', 'Berhasil menghapus agenda');
    }
}
