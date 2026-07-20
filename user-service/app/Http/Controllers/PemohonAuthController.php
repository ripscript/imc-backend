<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Helpers\LogActivity;
use App\Models\Pemohon;
use App\Rules\UniqueSoftDelete;
use App\Services\EmailTemplateService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class PemohonAuthController extends Controller
{
    protected $emailService;

    public function __construct()
    {
        $this->emailService = new EmailTemplateService();
    }

    public function register(Request $request)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'email' => ['required', 'string', 'email', 'max:255', new UniqueSoftDelete('pemohon', 'email')],
                'username' => ['required', 'string', 'max:100', new UniqueSoftDelete('pemohon', 'username')],
                'name' => 'required|string|max:255',
                'password' => [
                    'required',
                    'string',
                    'max:100',
                    'confirmed',
                    Password::min(8)
                        ->mixedCase() // 1 huruf besar + 1 huruf kecil
                        ->letters() // memastikan ada huruf
                        ->symbols(), // 1 karakter spesial
                ],
                'identity_number' => ['max:16', new UniqueSoftDelete('pemohon', 'identity_number')],
                'classification_pemohon_id' => 'exists:ms_classification_pemohon,id'
            ]);

            $data = new Pemohon();
            $data->email = $request->email;
            $data->name = $request->name;
            $data->username = $request->username;
            $data->password = bcrypt($request->password);
            $data->otp = Pemohon::generateOtp();
            $data->otp_created_at = now();
            $data->identity_number = $request->identity_number;
            $data->classification_pemohon_id = $request->classification_pemohon_id;
            $data->is_privacy_policy_accepted = $request->is_privacy_policy_accepted;
            $data->save();

            DB::commit();

            LogActivity::createCustom('pemohon', 'Mendaftar sebagai pemohon', $data, null);

            $this->emailService->sendTemplate('Pendaftaran Pemohon', $data->email, [
                'otp' => $data->otp,
                'nama' => $data->name
            ], 'PPID Kemlu – Kode OTP Verifikasi Akun');

            return ApiResponse::success($data->makeHidden(['id']), 'Berhasil registrasi, silakan cek email untuk verifikasi', 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function login(Request $request)
    {
        try {
            $request->validate([
                'email_or_username' => 'required',
                'password' => 'required|string|min:8|max:100',
            ]);

            $credentialType = filter_var($request->email_or_username, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
            $user = Pemohon::where($credentialType, $request->email_or_username)->first();
            if (!$user) {
                // return ApiResponse::error('Pemohon tidak ditemukan', 404);
                return ApiResponse::error("Email/username atau password salah", 401);
            }
            // if (!$user->is_email_verified) {
            //     return ApiResponse::error('Email belum diverifikasi, silakan verifikasi terlebih dahulu', 403);
            // }
            if ($user->status == Pemohon::STATUS['DIBLOKIR']) {
                return ApiResponse::error('Akun Anda telah diblokir, silakan hubungi admin', 403);
            }
            $credentials = [
                $credentialType => $request->email_or_username, 
                'password' => $request->password
            ];
            if (!$token = auth('pemohon')->attempt($credentials)) {
                return ApiResponse::error("Email/username atau password salah", 401);
            }

            $user->last_login_at = now();
            $user->save();

            LogActivity::createCustom('login pemohon', 'Masuk ke aplikasi', null, auth('pemohon')->user());

            $data = $this->createNewToken($token);
            return response()->json($data);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function logout() 
    {
        $user = Pemohon::find(auth('pemohon')->user()->id);
        LogActivity::createCustom('logout pemohon', 'Keluar dari aplikasi', null, $user);
        $user->increment('token_version');
        auth('pemohon')->logout();
        return ApiResponse::success('', 'Berhasil logout');
    }

    public function profile()
    {
        try {
            $user = Pemohon::find(auth('pemohon')->id());
            return ApiResponse::success($user, 'Berhasil mengambil data profil');
        } catch (\Throwable $th) {
            throw $th;
        }
        
    }

    public function verifyEmail(Request $request)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'email' => 'required|email',
                'otp' => 'required|string',
            ]);

            $user = Pemohon::where('email', $request->email)->first();

            if (!$user) {
                DB::rollBack();
                return ApiResponse::error('Email tidak ditemukan', null, 404);
            }

            if ($user->status != Pemohon::STATUS['BELUM_TERVERIFIKASI']) {
                DB::rollBack();
                return ApiResponse::error('Akun sudah terverifikasi', null, 400);
            }

            if (!$user->otp || !$user->otp_created_at) {
                DB::rollBack();
                return ApiResponse::error('OTP tidak ditemukan', null, 400);
            }

            if (Carbon::parse($user->otp_created_at)->addHours(6)->isPast()) { // OTP hanya berlaku 6 jam 
                DB::rollBack();
                return ApiResponse::error('OTP sudah kadaluarsa, silakan generate OTP baru', null, 400);
            }

            if ($user->otp != $request->otp) {
                DB::rollBack();
                return ApiResponse::error('OTP tidak valid', null, 400);
            }

            $user->otp = null;
            $user->otp_created_at = null;
            $user->email_verified_at = now();
            $user->status = Pemohon::STATUS['EMAIL_TERVERIFIKASI'];
            $user->save();

            $token = auth('pemohon')->login($user);
            
            DB::commit();

            LogActivity::createCustom('pemohon', 'Melakukan verifikasi email', $user, $user);

            $data = $this->createNewToken($token);

            return ApiResponse::success($data, 'Berhasil melakukan verifikasi email');
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
        
    }

    public function sendResetLinkEmail(Request $request)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'email' => 'required|email',
            ]);

            // Rate limit key: IP + email
            $rateLimitKey = Str::lower($request->email) . '|' . $request->ip();

            if (RateLimiter::tooManyAttempts($rateLimitKey, 5)) {
                Log::warning('Forgot password rate limit exceeded', [
                    'email' => $request->email,
                    'ip' => $request->ip(),
                ]);

                return ApiResponse::error('Terlalu banyak permintaan. Silakan coba kembali nanti.', null, 429);
            }
            RateLimiter::hit($rateLimitKey, 300); // 5 percobaan per 5 menit

            $user = Pemohon::where('email', $request->email)->first();

            if (!$user) {
                DB::rollBack();
                // return ApiResponse::error('Email tidak ditemukan', null, 404);
                return ApiResponse::success(null, "Jika email terdaftar, instruksi reset password akan dikirim.");
            }

            $user->token = Pemohon::generateUniqueToken();
            $user->save();

            DB::commit();

            $link = rtrim(env('FRONTEND_URL'), '/') . '/reset-password?token=' . $user->token;
            $this->emailService->sendTemplate('Lupa Password', $user->email, [
                'nama' => $user->name,
                'link' => $link,
                'token' => $user->token
            ], 'PPID Kemlu – Reset Password');

            // return ApiResponse::success('', 'Silakan cek email untuk melakukan reset password');
            return ApiResponse::success(null, "Jika email terdaftar, instruksi reset password akan dikirim.");
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function resetPassword(Request $request)
    {
        DB::beginTransaction();
        try {
            if (Auth::guard('pemohon')->check()) {
                $user = Auth::guard('pemohon')->user();
            } else if ($request->filled('token')) {
                $user = Pemohon::where('token', $request->token)->first();
                if (!$user) {
                    DB::rollBack();
                    return ApiResponse::error('Token tidak valid', 400);
                }
            } else {
                DB::rollBack();
                return ApiResponse::error('Unauthenticated', '', 401);
            }

            $request->validate([
                'password' => [
                    'required',
                    'string',
                    'max:100',
                    'confirmed',
                    Password::min(8)
                        ->mixedCase() // 1 huruf besar + 1 huruf kecil
                        ->letters() // memastikan ada huruf
                        ->symbols(), // 1 karakter spesial
                ],
            ]);

            $user->password = bcrypt($request->password);
            $user->token = null;
            $user->increment('token_version');
            $user->save();

            DB::commit();

            LogActivity::createCustom('pemohon', 'Melakukan reset password', $user, $user);

            return ApiResponse::success('', 'Berhasil menyimpan perubahan password');
        } catch (\Throwable $th) {
            DB::commit();
            throw $th;
        }
    }

    public function checkVerifyEmail(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email'
            ]);

            $pemohon = Pemohon::where('email', $request->email)->first();
            if (!$pemohon) {
                return ApiResponse::error('Email tidak ditemukan', '', 404);
            }

            return ApiResponse::success([
                'is_email_verified' => $pemohon->is_email_verified,
                'status' => $pemohon->status
            ], 'Berhasil mengambil status verifikasi email');
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function resendOtp(Request $request)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'email' => 'required|email'
            ]);

            $pemohon = Pemohon::where('email', $request->email)->first();
            if (!$pemohon) {
                DB::rollBack();
                return ApiResponse::error('Email tidak ditemukan', '', 404);
            }

            if ($pemohon->status != Pemohon::STATUS['BELUM_TERVERIFIKASI']) {
                DB::rollBack();
                return ApiResponse::error('Akun sudah terverifikasi', 400);
            }

            $pemohon->otp = Pemohon::generateOtp();
            $pemohon->otp_created_at = now();
            $pemohon->save();

            DB::commit();

            $this->emailService->sendTemplate('Pendaftaran Pemohon', $pemohon->email, [
                'otp' => $pemohon->otp,
                'nama' => $pemohon->name
            ], 'PPID Kemlu – Kode OTP Verifikasi Akun');
            return ApiResponse::success('', 'Berhasil mengirimkan email verifikasi OTP');
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    protected function createNewToken($token)
    {
        return [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('pemohon')->factory()->getTTL() * 60,
            'pemohon' => Pemohon::find(auth('pemohon')->id())
        ];
    }
}
