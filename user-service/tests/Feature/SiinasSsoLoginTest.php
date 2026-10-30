<?php

namespace Tests\Feature;

use App\Models\CompanyProfile;
use App\Models\MsRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SiinasSsoLoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->ensureSchema();
    }

    private function ensureSchema(): void
    {
        DB::statement('CREATE TABLE IF NOT EXISTS ms_roles (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT, code TEXT, is_active INTEGER DEFAULT 1, created_by TEXT, updated_by TEXT, created_at DATETIME, updated_at DATETIME)');
        DB::statement('CREATE TABLE IF NOT EXISTS activity_log (id INTEGER PRIMARY KEY AUTOINCREMENT, log_name TEXT, description TEXT, subject_type TEXT, subject_id INTEGER, causer_type TEXT, causer_id INTEGER, properties TEXT, event TEXT, batch_uuid TEXT, created_at DATETIME, updated_at DATETIME)');

        // Ensure users table in SQLite test has required columns
        if (! Schema::hasColumn('users', 'username')) {
            Schema::table('users', function ($table) {
                $table->string('username')->nullable();
                $table->unsignedBigInteger('role_id')->nullable();
                $table->integer('status')->default(1);
                $table->softDeletes();
            });
        }
    }

    public function test_validation_fails_when_fields_missing(): void
    {
        $response = $this->postJson('/api/auth/sso/siinas', []);

        $response->assertStatus(422)
            ->assertJsonPath('success', false);
    }

    public function test_login_successful_and_creates_user_and_company_profile(): void
    {
        $baseUrl = rtrim(config('services.siinas.base_url', 'https://api.kemenperin.go.id/SIINas'), '/');

        Http::fake([
            "{$baseUrl}/getLogin_v2.php*" => Http::response([
                'status' => 1,
                'keterangan' => 'Sukses',
                'data' => [
                    [
                        'id_siinas' => 'token_siinas_test_123',
                        'token_expired_at' => '2026-10-13 11:13:38',
                    ],
                ],
            ], 200),
            "{$baseUrl}/getPerusahaanByID_v2.php*" => Http::response([
                'status' => 1,
                'keterangan' => 'Sukses',
                'data' => [
                    [
                        'id_siinas' => 'token_siinas_test_123',
                        'token_expired_at' => '2026-10-21 12:08:18',
                        'nama_perusahaan' => 'PT Manufaktur Maju Jaya',
                        'tipe_akun' => 'Industri',
                        'badan_hukum' => 'PMDN',
                        'badan_usaha' => 'PT Perorangan',
                        'npwp_daftar_akun' => '1000 0000 0421 3255',
                        'nib_daftar_akun' => '2107250103277',
                        'email_daftar_akun' => 'corporate@majujaya.com',
                        'skala_usaha' => 'Kecil',
                        'website' => 'https://majujaya.com',
                        'alamat_kantor' => [
                            [
                                'alamat' => 'Jln. Sutajaya No. 10',
                                'provinsi' => 'Jawa Barat',
                                'kode_pos' => '45191',
                            ],
                        ],
                        'alamat_pabrik' => [
                            [
                                'alamat' => 'Kawasan Industri Cirebon Blok A',
                                'provinsi' => 'Jawa Barat',
                                'kode_pos' => '45191',
                            ],
                        ],
                        'tgl_daftar' => '2025-10-02 14:07:20',
                    ],
                ],
            ], 200),
        ]);

        $response = $this->postJson('/api/auth/sso/siinas', [
            'username' => 'majujaya_user',
            'password' => 'secret_siinas_pass',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'access_token',
                'token_type',
                'expires_in',
                'user' => [
                    'id',
                    'name',
                    'username',
                    'email',
                    'role_id',
                    'role',
                    'company_profile',
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'username' => 'majujaya_user',
            'email' => 'corporate@majujaya.com',
            'name' => 'PT Manufaktur Maju Jaya',
        ]);

        $this->assertDatabaseHas('company_profiles', [
            'id_siinas' => 'token_siinas_test_123',
            'nama_perusahaan' => 'PT Manufaktur Maju Jaya',
            'nib' => '2107250103277',
            'npwp' => '1000 0000 0421 3255',
        ]);

        $profile = CompanyProfile::first();
        $this->assertIsArray($profile->alamat_kantor);
        $this->assertSame('Jln. Sutajaya No. 10', $profile->alamat_kantor[0]['alamat']);
    }

    public function test_subsequent_login_updates_company_profile(): void
    {
        $baseUrl = rtrim(config('services.siinas.base_url', 'https://api.kemenperin.go.id/SIINas'), '/');

        Http::fake([
            "{$baseUrl}/getLogin_v2.php*" => Http::sequence()
                ->push(['status' => 1, 'data' => [['id_siinas' => 'token_initial', 'token_expired_at' => '2026-10-13 11:13:38']]])
                ->push(['status' => 1, 'data' => [['id_siinas' => 'token_updated', 'token_expired_at' => '2026-11-01 00:00:00']]]),
            "{$baseUrl}/getPerusahaanByID_v2.php*" => Http::sequence()
                ->push([
                    'status' => 1,
                    'data' => [
                        [
                            'id_siinas' => 'token_initial',
                            'nama_perusahaan' => 'PT Nama Lama',
                            'email_daftar_akun' => 'update@test.com',
                            'nib_daftar_akun' => '1111111111',
                        ],
                    ],
                ])
                ->push([
                    'status' => 1,
                    'data' => [
                        [
                            'id_siinas' => 'token_updated',
                            'nama_perusahaan' => 'PT Nama Baru Berubah',
                            'email_daftar_akun' => 'update@test.com',
                            'nib_daftar_akun' => '9999999999',
                        ],
                    ],
                ]),
        ]);

        $this->postJson('/api/auth/sso/siinas', [
            'username' => 'test_updater',
            'password' => 'pass123',
        ])->assertStatus(200);

        $this->assertSame(1, User::where('username', 'test_updater')->count());
        $this->assertSame('PT Nama Lama', User::where('username', 'test_updater')->first()->name);

        $this->postJson('/api/auth/sso/siinas', [
            'username' => 'test_updater',
            'password' => 'pass123',
        ])->assertStatus(200);

        // Assert single user record updated
        $this->assertSame(1, User::where('username', 'test_updater')->count());
        $user = User::where('username', 'test_updater')->first();
        $this->assertSame('PT Nama Baru Berubah', $user->name);
        $this->assertSame('PT Nama Baru Berubah', $user->companyProfile->nama_perusahaan);
        $this->assertSame('9999999999', $user->companyProfile->nib);
        $this->assertSame('token_updated', $user->companyProfile->id_siinas);
    }

    public function test_invalid_siinas_credentials_returns_401(): void
    {
        $baseUrl = rtrim(config('services.siinas.base_url', 'https://api.kemenperin.go.id/SIINas'), '/');

        Http::fake([
            "{$baseUrl}/getLogin_v2.php*" => Http::response([
                'status' => 0,
                'keterangan' => 'Username atau Password salah',
                'data' => [],
            ], 200),
        ]);

        $response = $this->postJson('/api/auth/sso/siinas', [
            'username' => 'invalid_user',
            'password' => 'wrong_pass',
        ]);

        $response->assertStatus(401)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Username atau Password salah');
    }

    public function test_siinas_service_outage_returns_502(): void
    {
        $baseUrl = rtrim(config('services.siinas.base_url', 'https://api.kemenperin.go.id/SIINas'), '/');

        Http::fake([
            "{$baseUrl}/getLogin_v2.php*" => Http::response(null, 500),
        ]);

        $response = $this->postJson('/api/auth/sso/siinas', [
            'username' => 'any_user',
            'password' => 'any_pass',
        ]);

        $response->assertStatus(502)
            ->assertJsonPath('success', false);
    }

    public function test_conflict_when_email_already_registered_to_non_industri_user(): void
    {
        $baseUrl = rtrim(config('services.siinas.base_url', 'https://api.kemenperin.go.id/SIINas'), '/');

        $adminRole = MsRole::create(['code' => 'admin_pusat', 'name' => 'Admin Pusat']);
        User::create([
            'name' => 'Internal Admin',
            'email' => 'admin@internal.go.id',
            'username' => 'admin_internal',
            'password' => bcrypt('password'),
            'role_id' => $adminRole->id,
            'status' => 1,
        ]);

        Http::fake([
            "{$baseUrl}/getLogin_v2.php*" => Http::response([
                'status' => 1,
                'data' => [['id_siinas' => 'token_conflict', 'token_expired_at' => '2026-10-13 11:13:38']],
            ], 200),
            "{$baseUrl}/getPerusahaanByID_v2.php*" => Http::response([
                'status' => 1,
                'data' => [
                    [
                        'id_siinas' => 'token_conflict',
                        'nama_perusahaan' => 'PT Rogue Industri',
                        'email_daftar_akun' => 'admin@internal.go.id',
                    ],
                ],
            ], 200),
        ]);

        $response = $this->postJson('/api/auth/sso/siinas', [
            'username' => 'rogue_user',
            'password' => 'secret_pass',
        ]);

        $response->assertStatus(409)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Email sudah terdaftar untuk akun internal non-industri');
    }
}
