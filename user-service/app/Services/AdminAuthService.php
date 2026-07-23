<?php

namespace App\Services;

use App\Exceptions\Domain\AuthenticationException;
use App\Helpers\LogActivity;
use App\Models\User;
use App\Services\DTOs\LoginDTO;

class AdminAuthService
{
    public function login(LoginDTO $dto): array
    {
        $credentialType = filter_var($dto->email_or_username, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        $user = User::where($credentialType, $dto->email_or_username)->first();
        if (! $user) {
            throw new AuthenticationException;
        }
        $credentials = [
            $credentialType => $dto->email_or_username,
            'password' => $dto->password,
        ];
        if (! $token = auth('admin')->attempt($credentials)) {
            throw new AuthenticationException;
        }

        LogActivity::createCustom('login admin', 'Masuk ke aplikasi', null, auth('admin')->user());

        return [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('admin')->factory()->getTTL() * 60,
            'user' => User::find(auth('admin')->id()),
        ];
    }

    public function logout()
    {
        LogActivity::createCustom('logout admin', 'Keluar dari aplikasi', null, auth('admin')->user());
        auth('admin')->logout();

        return true;
    }

    public function profile()
    {
        return User::find(auth('admin')->id());
    }
}
