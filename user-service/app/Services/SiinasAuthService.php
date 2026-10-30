<?php

namespace App\Services;

use App\Exceptions\Domain\AccountConflictException;
use App\Helpers\LogActivity;
use App\Models\CompanyProfile;
use App\Models\MsRole;
use App\Models\User;
use Illuminate\Support\Str;

class SiinasAuthService
{
    public function __construct(
        protected SiinasService $siinasService
    ) {}

    /**
     * Authenticate via SIINas, upsert user and company profile, and issue internal JWT.
     *
     * @throws AccountConflictException
     * @throws \App\Exceptions\Domain\AuthenticationException
     * @throws \App\Exceptions\Domain\SiinasServiceException
     */
    public function login(string $username, string $password): array
    {
        // 1. Authenticate with SIINas
        $tokenInfo = $this->siinasService->login($username, $password);

        // 2. Fetch full company profile from SIINas
        $profileData = $this->siinasService->getCompanyProfile($tokenInfo['id_siinas']);

        // 3. Resolve 'industri' role
        $role = MsRole::firstOrCreate(
            ['code' => 'industri'],
            ['name' => 'Industri']
        );

        $email = $profileData['email_daftar_akun'] ?? null;
        $companyName = $profileData['nama_perusahaan'] ?? $username;

        // 4. Check conflict with existing non-industri accounts
        if ($email) {
            $existingUserByEmail = User::where('email', $email)->first();
            if ($existingUserByEmail && (int) $existingUserByEmail->role_id !== (int) $role->id) {
                throw new AccountConflictException('Email sudah terdaftar untuk akun internal non-industri');
            }
        }

        $existingUserByUsername = User::where('username', $username)->first();
        if ($existingUserByUsername && (int) $existingUserByUsername->role_id !== (int) $role->id) {
            throw new AccountConflictException('Username sudah terdaftar untuk akun internal non-industri');
        }

        // 5. Upsert User
        $user = $existingUserByUsername
            ?? ($email ? User::where('email', $email)->first() : null)
            ?? new User();

        $user->name = $companyName;
        $user->username = $username;
        if ($email) {
            $user->email = $email;
        }
        $user->role_id = $role->id;
        if (! $user->exists || empty($user->password)) {
            $user->password = bcrypt(Str::random(32));
        }
        $user->status = 1;
        $user->save();

        // 6. Upsert CompanyProfile
        CompanyProfile::updateOrCreate(
            ['user_id' => $user->id],
            [
                'id_siinas' => $tokenInfo['id_siinas'],
                'token_expired_at' => $tokenInfo['token_expired_at'] ?? $profileData['token_expired_at'] ?? null,
                'nama_perusahaan' => $companyName,
                'tipe_akun' => $profileData['tipe_akun'] ?? null,
                'badan_hukum' => $profileData['badan_hukum'] ?? null,
                'badan_usaha' => $profileData['badan_usaha'] ?? null,
                'npwp' => $profileData['npwp_daftar_akun'] ?? null,
                'nib' => $profileData['nib_daftar_akun'] ?? null,
                'email_perusahaan' => $email,
                'skala_usaha' => $profileData['skala_usaha'] ?? null,
                'website' => $profileData['website'] ?? null,
                'tgl_daftar_siinas' => $profileData['tgl_daftar'] ?? null,
                'alamat_kantor' => $profileData['alamat_kantor'] ?? null,
                'alamat_pabrik' => $profileData['alamat_pabrik'] ?? null,
            ]
        );

        // 7. Issue application JWT token
        $token = auth('admin')->login($user);

        LogActivity::createCustom('login industri sso', 'Masuk ke aplikasi via SIINas SSO', null, $user);

        return [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('admin')->factory()->getTTL() * 60,
            'user' => $user->load(['role', 'companyProfile']),
        ];
    }
}
