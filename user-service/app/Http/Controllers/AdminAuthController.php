<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\LoginRequest;
use App\Http\Resources\UserResource;
use App\Services\AdminAuthService;
use App\Services\DTOs\LoginDTO;

class AdminAuthController extends Controller
{
    public function __construct(
        protected AdminAuthService $adminAuthService
    ) {}

    public function login(LoginRequest $request)
    {
        try {
            $dto = new LoginDTO($request->validated());
            $result = $this->adminAuthService->login($dto);

            return response()->json($result);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function logout()
    {
        try {
            $this->adminAuthService->logout();

            return ApiResponse::success('', 'Berhasil logout');
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function profile()
    {
        try {
            $user = $this->adminAuthService->profile();

            return ApiResponse::success(new UserResource($user), 'Berhasil mengambil data profil');
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
