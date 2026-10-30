<?php

namespace App\Services;

use App\Exceptions\Domain\AuthenticationException;
use App\Exceptions\Domain\SiinasServiceException;
use Illuminate\Support\Facades\Http;
use Throwable;

class SiinasService
{
    protected string $baseUrl;

    protected int $timeout;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.siinas.base_url', 'https://api.kemenperin.go.id/SIINas'), '/');
        $this->timeout = (int) config('services.siinas.timeout', 15);
    }

    /**
     * Authenticate credentials against SIINas getLogin_v2.php.
     *
     * @return array{id_siinas: string, token_expired_at: string}
     *
     * @throws AuthenticationException
     * @throws SiinasServiceException
     */
    public function login(string $username, string $password): array
    {
        $url = "{$this->baseUrl}/getLogin_v2.php";

        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ])
                ->send('GET', $url, [
                    'json' => [
                        'username' => $username,
                        'password' => $password,
                    ],
                ]);
        } catch (Throwable $e) {
            throw new SiinasServiceException('Layanan SIINas tidak dapat dihubungi: '.$e->getMessage(), 502, $e);
        }

        if (! $response->successful()) {
            throw new SiinasServiceException('Layanan SIINas mengembalikan respon gagal (HTTP '.$response->status().')', 502);
        }

        $result = $response->json();

        if (! isset($result['status']) || (int) $result['status'] !== 1) {
            $keterangan = $result['keterangan'] ?? 'Kredensial SIINas tidak valid';
            throw new AuthenticationException($keterangan, 401);
        }

        $tokenData = $result['data'][0] ?? null;
        if (! $tokenData || empty($tokenData['id_siinas'])) {
            throw new AuthenticationException('Token SIINas tidak ditemukan dalam respon', 401);
        }

        return [
            'id_siinas' => $tokenData['id_siinas'],
            'token_expired_at' => $tokenData['token_expired_at'] ?? null,
        ];
    }

    /**
     * Get company profile details using SIINas token.
     *
     * @throws SiinasServiceException
     */
    public function getCompanyProfile(string $idSiinas): array
    {
        $url = "{$this->baseUrl}/getPerusahaanByID_v2.php";

        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ])
                ->post($url, [
                    'id_siinas' => $idSiinas,
                ]);
        } catch (Throwable $e) {
            throw new SiinasServiceException('Layanan SIINas tidak dapat dihubungi: '.$e->getMessage(), 502, $e);
        }

        if (! $response->successful()) {
            throw new SiinasServiceException('Layanan SIINas mengembalikan respon gagal saat memuat profil (HTTP '.$response->status().')', 502);
        }

        $result = $response->json();

        if (! isset($result['status']) || (int) $result['status'] !== 1) {
            $keterangan = $result['keterangan'] ?? 'Gagal memuat profil perusahaan SIINas';
            throw new SiinasServiceException($keterangan, 502);
        }

        $profile = $result['data'][0] ?? null;
        if (! $profile) {
            throw new SiinasServiceException('Data profil perusahaan SIINas kosong', 502);
        }

        return $profile;
    }
}
