<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Services\ParentContentService;

class OrganizationalStructureController extends Controller
{
    protected ParentContentService $parentContentService;

    public function __construct()
    {
        parent::__construct();
        $this->parentContentService = new ParentContentService;
    }

    public function sideMenu()
    {
        try {
            $data = $this->parentContentService->getParentByType('organizational_structure');

            return ApiResponse::success($data, 'Berhasil mengambil side menu untuk tipe struktur organisasi');
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
