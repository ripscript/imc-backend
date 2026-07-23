<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\PemohonLoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\ResendOtpRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Http\Requests\VerifyEmailRequest;
use App\Http\Resources\PemohonResource;
use App\Services\DTOs\ForgotPasswordDTO;
use App\Services\DTOs\LoginDTO;
use App\Services\DTOs\RegisterDTO;
use App\Services\DTOs\ResendOtpDTO;
use App\Services\DTOs\ResetPasswordDTO;
use App\Services\DTOs\VerifyEmailDTO;
use App\Services\PemohonAuthService;

class PemohonAuthController extends Controller
{
    public function __construct(
        protected PemohonAuthService $pemohonAuthService
    ) {}

    public function register(RegisterRequest $request)
    {
        try {
            $dto = new RegisterDTO($request->validated());
            $result = $this->pemohonAuthService->register($dto);

            return ApiResponse::success($result->makeHidden(['id']), 'Berhasil registrasi, silakan cek email untuk verifikasi', 201);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function login(PemohonLoginRequest $request)
    {
        try {
            $dto = new LoginDTO($request->validated());
            $result = $this->pemohonAuthService->login($dto);

            return response()->json($result);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function logout()
    {
        try {
            $this->pemohonAuthService->logout();

            return ApiResponse::success('', 'Berhasil logout');
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function profile()
    {
        try {
            $user = $this->pemohonAuthService->profile();

            return ApiResponse::success(new PemohonResource($user), 'Berhasil mengambil data profil');
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function verifyEmail(VerifyEmailRequest $request)
    {
        try {
            $dto = new VerifyEmailDTO($request->validated());
            $result = $this->pemohonAuthService->verifyEmail($dto);

            return ApiResponse::success($result, 'Berhasil melakukan verifikasi email');
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function sendResetLinkEmail(ForgotPasswordRequest $request)
    {
        try {
            $dto = new ForgotPasswordDTO([
                ...$request->validated(),
                'ip' => $request->ip(),
            ]);
            $result = $this->pemohonAuthService->sendResetLinkEmail($dto);

            return response()->json($result);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function resetPassword(ResetPasswordRequest $request)
    {
        try {
            $dto = new ResetPasswordDTO([
                ...$request->validated(),
                'token' => $request->input('token'),
            ]);
            $result = $this->pemohonAuthService->resetPassword($dto);

            return $result;
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function checkVerifyEmail(ForgotPasswordRequest $request)
    {
        try {
            $dto = new ResendOtpDTO($request->validated());
            $result = $this->pemohonAuthService->checkVerifyEmail($dto);

            return ApiResponse::success($result, 'Berhasil mengambil status verifikasi email');
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function resendOtp(ResendOtpRequest $request)
    {
        try {
            $dto = new ResendOtpDTO($request->validated());
            $result = $this->pemohonAuthService->resendOtp($dto);

            return $result;
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
