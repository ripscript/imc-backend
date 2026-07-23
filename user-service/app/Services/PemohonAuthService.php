<?php

namespace App\Services;

use App\Exceptions\Domain\AccountBlockedException;
use App\Exceptions\Domain\AuthenticationException;
use App\Exceptions\Domain\EntityNotFoundException;
use App\Exceptions\Domain\OtpException;
use App\Exceptions\Domain\RateLimitException;
use App\Helpers\LogActivity;
use App\Models\Pemohon;
use App\Services\DTOs\ForgotPasswordDTO;
use App\Services\DTOs\LoginDTO;
use App\Services\DTOs\RegisterDTO;
use App\Services\DTOs\ResendOtpDTO;
use App\Services\DTOs\ResetPasswordDTO;
use App\Services\DTOs\VerifyEmailDTO;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class PemohonAuthService
{
    public function __construct(
        protected EmailTemplateService $emailService
    ) {}

    public function register(RegisterDTO $dto)
    {
        DB::beginTransaction();
        try {
            $pemohon = new Pemohon;
            $pemohon->email = $dto->email;
            $pemohon->name = $dto->name;
            $pemohon->username = $dto->username;
            $pemohon->password = bcrypt($dto->password);
            $pemohon->otp = Pemohon::generateOtp();
            $pemohon->otp_created_at = now();
            $pemohon->identity_number = $dto->identity_number;
            $pemohon->classification_pemohon_id = $dto->classification_pemohon_id;
            $pemohon->is_privacy_policy_accepted = $dto->is_privacy_policy_accepted;
            $pemohon->save();

            DB::commit();

            LogActivity::createCustom('pemohon', 'Mendaftar sebagai pemohon', $pemohon, null);

            $this->emailService->sendTemplate('Pendaftaran Pemohon', $pemohon->email, [
                'otp' => $pemohon->otp,
                'nama' => $pemohon->name,
            ], 'PPID Kemlu – Kode OTP Verifikasi Akun');

            return $pemohon;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function login(LoginDTO $dto)
    {
        $credentialType = filter_var($dto->email_or_username, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        $user = Pemohon::where($credentialType, $dto->email_or_username)->first();
        if (! $user) {
            throw new AuthenticationException;
        }
        if ($user->status == Pemohon::STATUS['DIBLOKIR']) {
            throw new AccountBlockedException;
        }
        $credentials = [
            $credentialType => $dto->email_or_username,
            'password' => $dto->password,
        ];
        if (! $token = auth('pemohon')->attempt($credentials)) {
            throw new AuthenticationException;
        }

        $user->last_login_at = now();
        $user->save();

        LogActivity::createCustom('login pemohon', 'Masuk ke aplikasi', null, auth('pemohon')->user());

        return $this->createNewToken($token);
    }

    public function logout()
    {
        $user = Pemohon::find(auth('pemohon')->user()->id);
        LogActivity::createCustom('logout pemohon', 'Keluar dari aplikasi', null, $user);
        $user->increment('token_version');
        auth('pemohon')->logout();

        return true;
    }

    public function profile()
    {
        return Pemohon::find(auth('pemohon')->id());
    }

    public function verifyEmail(VerifyEmailDTO $dto)
    {
        DB::beginTransaction();
        try {
            $user = Pemohon::where('email', $dto->email)->first();

            if (! $user) {
                DB::rollBack();

                throw new EntityNotFoundException('Email');
            }

            if ($user->status != Pemohon::STATUS['BELUM_TERVERIFIKASI']) {
                DB::rollBack();

                throw new OtpException('Akun sudah terverifikasi');
            }

            if (! $user->otp || ! $user->otp_created_at) {
                DB::rollBack();

                throw new OtpException('OTP tidak ditemukan');
            }

            if (Carbon::parse($user->otp_created_at)->addHours(6)->isPast()) {
                DB::rollBack();

                throw new OtpException('OTP sudah kadaluarsa, silakan generate OTP baru');
            }

            if ($user->otp != $dto->otp) {
                DB::rollBack();

                throw new OtpException;
            }

            $user->otp = null;
            $user->otp_created_at = null;
            $user->email_verified_at = now();
            $user->status = Pemohon::STATUS['EMAIL_TERVERIFIKASI'];
            $user->save();

            $token = auth('pemohon')->login($user);

            DB::commit();

            LogActivity::createCustom('pemohon', 'Melakukan verifikasi email', $user, $user);

            return $this->createNewToken($token);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function sendResetLinkEmail(ForgotPasswordDTO $dto)
    {
        DB::beginTransaction();
        try {
            $rateLimitKey = Str::lower($dto->email).'|'.$dto->ip;

            if (RateLimiter::tooManyAttempts($rateLimitKey, 5)) {
                Log::warning('Forgot password rate limit exceeded', [
                    'email' => $dto->email,
                    'ip' => $dto->ip,
                ]);

                throw new RateLimitException;
            }
            RateLimiter::hit($rateLimitKey, 300);

            $user = Pemohon::where('email', $dto->email)->first();

            if (! $user) {
                DB::rollBack();

                return [
                    'success' => true,
                    'message' => 'Jika email terdaftar, instruksi reset password akan dikirim.',
                ];
            }

            $user->token = Pemohon::generateUniqueToken();
            $user->save();

            DB::commit();

            $link = rtrim(env('FRONTEND_URL'), '/').'/reset-password?token='.$user->token;
            $this->emailService->sendTemplate('Lupa Password', $user->email, [
                'nama' => $user->name,
                'link' => $link,
                'token' => $user->token,
            ], 'PPID Kemlu – Reset Password');

            return [
                'success' => true,
                'message' => 'Jika email terdaftar, instruksi reset password akan dikirim.',
            ];
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function resetPassword(ResetPasswordDTO $dto)
    {
        DB::beginTransaction();
        try {
            if (Auth::guard('pemohon')->check()) {
                $user = Auth::guard('pemohon')->user();
            } elseif ($dto->token) {
                $user = Pemohon::where('token', $dto->token)->first();
                if (! $user) {
                    DB::rollBack();

                    throw new EntityNotFoundException('Token');
                }
            } else {
                DB::rollBack();

                throw new AuthenticationException('Unauthenticated');
            }

            $user->password = bcrypt($dto->password);
            $user->token = null;
            $user->increment('token_version');
            $user->save();

            DB::commit();

            LogActivity::createCustom('pemohon', 'Melakukan reset password', $user, $user);

            return true;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function checkVerifyEmail(ResendOtpDTO $dto)
    {
        $pemohon = Pemohon::where('email', $dto->email)->first();
        if (! $pemohon) {
            throw new EntityNotFoundException('Email');
        }

        return [
            'is_email_verified' => $pemohon->is_email_verified,
            'status' => $pemohon->status,
        ];
    }

    public function resendOtp(ResendOtpDTO $dto)
    {
        DB::beginTransaction();
        try {
            $pemohon = Pemohon::where('email', $dto->email)->first();
            if (! $pemohon) {
                DB::rollBack();

                throw new EntityNotFoundException('Email');
            }

            if ($pemohon->status != Pemohon::STATUS['BELUM_TERVERIFIKASI']) {
                DB::rollBack();

                throw new OtpException('Akun sudah terverifikasi');
            }

            $pemohon->otp = Pemohon::generateOtp();
            $pemohon->otp_created_at = now();
            $pemohon->save();

            DB::commit();

            $this->emailService->sendTemplate('Pendaftaran Pemohon', $pemohon->email, [
                'otp' => $pemohon->otp,
                'nama' => $pemohon->name,
            ], 'PPID Kemlu – Kode OTP Verifikasi Akun');

            return true;
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
            'pemohon' => Pemohon::find(auth('pemohon')->id()),
        ];
    }
}
