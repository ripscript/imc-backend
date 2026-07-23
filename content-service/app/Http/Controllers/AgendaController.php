<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\CreateAgendaRequest;
use App\Http\Requests\UpdateAgendaRequest;
use App\Services\AgendaService;
use App\Services\DTOs\AgendaDTO;
use App\Services\DTOs\QueryFilterDTO;
use Illuminate\Http\Request;

class AgendaController extends Controller
{
    public function __construct(
        protected AgendaService $agendaService
    ) {}

    public function sideMenu()
    {
        try {
            return ApiResponse::success($this->agendaService->sideMenu(), 'Berhasil mengambil side menu untuk tipe agenda');
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function index(Request $request, $contentId)
    {
        try {
            return ApiResponse::success($this->agendaService->index(QueryFilterDTO::fromRequest($request), $contentId), 'Berhasil mengambil data agenda');
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function create(CreateAgendaRequest $req)
    {
        try {
            $dto = new AgendaDTO($req->validated());
            $dto->created_by = $dto->updated_by = auth()->user()->name;

            return ApiResponse::success($this->agendaService->create($dto), 'Berhasil menambahkan data agenda', 201);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function detail($id)
    {
        try {
            return ApiResponse::success($this->agendaService->detail($id), 'Berhasil mengambil data detail agenda');
        } catch (\Exception $th) {
            throw $th;
        }
    }

    public function update(UpdateAgendaRequest $req, $id)
    {
        try {
            $dto = new AgendaDTO($req->validated());
            $dto->updated_by = auth()->user()->name;

            return ApiResponse::success($this->agendaService->update($id, $dto), 'Berhasil memperbarui data agenda');
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function delete($id)
    {
        try {
            $this->agendaService->destroy($id);

            return ApiResponse::success('', 'Berhasil menghapus agenda');
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
