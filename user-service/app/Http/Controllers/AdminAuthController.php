<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Helpers\LogActivity;
use App\Models\User;
use Illuminate\Http\Request;

class AdminAuthController extends Controller
{
    public function login(Request $request)
    {
        try {
            $request->validate([
                'email_or_username' => 'required',
                'password' => 'required|string|min:8|max:100|regex:/[0-9]/',
            ]);

            $credentialType = filter_var($request->email_or_username, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
            $user = User::where($credentialType, $request->email_or_username)->first();
            if (!$user) {
                // return ApiResponse::error('Pengguna tidak ditemukan', 404);
                return ApiResponse::error("Email/username atau password salah", 401);
            }
            $credentials = [
                $credentialType => $request->email_or_username, 
                'password' => $request->password
            ];
            if (!$token = auth('admin')->attempt($credentials)) {
                return ApiResponse::error("Email/username atau password salah", 401);
            }

            LogActivity::createCustom('login admin', 'Masuk ke aplikasi', null, auth('admin')->user());
            
            return $this->createNewToken($token);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function logout() 
    {
        LogActivity::createCustom('logout admin', 'Keluar dari aplikasi', null, auth('admin')->user());
        auth('admin')->logout();
        return ApiResponse::success('', 'Berhasil logout');
    }

    public function profile()
    {
        try {
            $user = User::find(auth('admin')->id());
            return ApiResponse::success($user, 'Berhasil mengambil data profil');
        } catch (\Throwable $th) {
            throw $th;
        }
        
    }

    protected function createNewToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('admin')->factory()->getTTL() * 60,
            'user' => User::find(auth('admin')->id())
        ]);
    }
}
