# Spec 007: SIINas SSO Authentication and Company Profile Synchronization

## Problem Statement

Industrial partners and manufacturing enterprises need to access the IMC platform to interact with projects, submit technical requirements, and collaborate. Currently, authentication only supports internal administrative roles (`super_admin`, `admin_pusat`, `admin_satker`, `pimpinan`, `admin_pustik`) using locally stored credentials in `user-service`.

Requiring industrial users to undergo separate local registration creates administrative friction, risks mismatched enterprise data, and violates interoperability expectations with the Ministry of Industry (Kementerian Perindustrian - Kemenperin). Industrial companies already possess verified SIINas (Sistem Informasi Industri Nasional) credentials and legal enterprise profiles. Without direct SSO integration, our system cannot guarantee the authenticity of industrial partner identities or keep corporate addresses and legal statuses up-to-date.

## Solution

Introduce a dedicated Single Sign-On (SSO) authentication mechanism for the `industri` role by integrating directly with Kemenperin's SIINas API endpoints:

1. **Role Seeding**: Introduce the `industri` role (`code: industri`, `name: Industri`) in the role master seeders.
2. **SIINas Proxy Authentication**: Expose an endpoint `POST /auth/sso/siinas` that accepts SIINas credentials (`username` and `password`), authenticating against SIINas `getLogin_v2.php`.
3. **Company Profile Retrieval & Upsert**: Upon obtaining the SIINas token (`id_siinas`), query SIINas `getPerusahaanByID_v2.php` to fetch official enterprise details (`nama_perusahaan`, `tipe_akun`, `badan_hukum`, `badan_usaha`, `npwp`, `nib`, `alamat_kantor`, `alamat_pabrik`, etc.).
4. **Data Persistence**:
   - Upsert the account in `users` mapped to `industri` role with a secure non-usable local password hash.
   - Upsert the rich company data, SIINas token, and token expiration in a dedicated `company_profiles` table.
5. **Session Issuance**: Issue a standard application JWT token so the frontend and downstream microservices (`project-service`, `content-service`, etc.) can treat the user as an authenticated `industri` user seamlessly.

---

## User Stories

1. As an industrial user, I want to log in using my SIINas username and password, so that I do not have to register and remember a separate set of credentials for the IMC platform.
2. As an industrial user, I want my verified company name (`nama_perusahaan`) to be reflected as my account name, so that my organization is accurately represented in the system.
3. As an industrial user, I want my official email registered with SIINas (`email_daftar_akun`) to be used for my IMC account email, so that notifications reach my authorized company contact.
4. As an industrial user, I want my corporate profile details (NIB, NPWP, legal status, office address, factory address) to be automatically fetched and updated every time I log in, so that our company information is always synchronized without manual entry.
5. As an industrial user, I want to receive clear error feedback when my SIINas username or password is incorrect (HTTP 401), so that I know to check my credentials.
6. As an industrial user, I want to receive a standard application JWT bearer token upon successful SIINas login, so that my client application can interact with protected IMC APIs identically to administrative users.
7. As an industrial user, I want multiple office addresses (`alamat_kantor`) and factory addresses (`alamat_pabrik`) from SIINas to be preserved in my profile, so that complex organizational setups with multiple facilities are supported.
8. As a frontend client developer, I want a single endpoint `POST /auth/sso/siinas` accepting `{ "username": "...", "password": "..." }`, so that integrating SIINas login into the frontend UI is straightforward.
9. As a frontend client developer, I want the login response to return the JWT payload (`access_token`, `token_type`, `expires_in`) alongside the user object and populated company profile, so that the UI can immediately display the company dashboard.
10. As a system administrator, I want internal administrative accounts to be protected from hijacking, such that if a SIINas account attempts to log in with an email already bound to a non-industri role, the request is rejected with HTTP 409 Conflict.
11. As a system administrator, I want the `industri` role to be safely seeded in the database (`ms_roles`), so that role checks and RBAC can refer to it deterministically.
12. As a backend developer, I want SIINas API base URLs and request timeouts to be configurable via `.env`, so that staging, mock, and production environments can be configured independently.
13. As an operations engineer, I want upstream SIINas service downtimes or timeouts to be mapped cleanly to HTTP 502 Bad Gateway with diagnostic logs, so that our team can distinguish third-party outages from internal system bugs.
14. As a security officer, I want SIINas industrial accounts to have unguessable randomized passwords in `users.password`, ensuring that industrial accounts cannot be accessed via the standard local login endpoint `POST /auth/login`.
15. As a backend developer, I want the SIINas session token (`id_siinas`) and expiry (`token_expired_at`) stored in `company_profiles`, so that future background tasks or integrations can interact with SIINas on behalf of the company without prompting for re-login.

---

## Implementation Decisions

### Modules Built / Modified

1. **`migration-service`**:
   - `database/seeders/RoleSeeder.php`: Add entry `['code' => 'industri', 'name' => 'Industri']`.
   - Migration for `company_profiles` table.
2. **`user-service`**:
   - `database/migrations/xxxx_xx_xx_create_company_profiles_table.php`: Define `company_profiles` table.
   - `app/Models/CompanyProfile.php`: Eloquent model with JSON casts for `alamat_kantor` and `alamat_pabrik`, belongsTo `User`.
   - `app/Models/User.php`: Add `hasOne(CompanyProfile::class)` relationship.
   - `app/Services/SiinasService.php`: Client encapsulating external HTTP calls to SIINas (`getLogin_v2.php` and `getPerusahaanByID_v2.php`) using Laravel's `Http` facade with timeout and error wrapping.
   - `app/Services/SiinasAuthService.php`: Orchestrates SIINas authentication, user upsert, profile upsert, and JWT token issuance via `auth('admin')->login($user)`.
   - `app/Http/Requests/SiinasLoginRequest.php`: Form request validating `username` and `password`.
   - `app/Http/Controllers/AdminAuthController.php` (or dedicated `SiinasAuthController.php`): Action `loginSiinas(SiinasLoginRequest $request)`.
   - `routes/api.php`: Register `POST /auth/sso/siinas`.
   - `config/services.php`: Add `siinas` configuration (`base_url`, `timeout`).

### Database Schema: `company_profiles`

| Column | Type | Description |
|---|---|---|
| `id` | `bigint unsigned PK` | Primary key |
| `user_id` | `bigint unsigned FK` | Foreign key to `users.id` (unique, cascade on delete) |
| `id_siinas` | `string` | SIINas token identifier (indexed) |
| `token_expired_at` | `timestamp nullable` | Expiration timestamp from SIINas token |
| `nama_perusahaan` | `string` | Company legal name |
| `tipe_akun` | `string nullable` | Account type (e.g. "Industri") |
| `badan_hukum` | `string nullable` | Legal entity type (e.g. "PMDN", "PMA") |
| `badan_usaha` | `string nullable` | Business structure (e.g. "PT Perorangan", "PT", "CV") |
| `npwp` | `string nullable` | Tax ID number (`npwp_daftar_akun`) |
| `nib` | `string nullable` | Business Identification Number (`nib_daftar_akun`) |
| `email_perusahaan` | `string nullable` | Contact email (`email_daftar_akun`) |
| `skala_usaha` | `string nullable` | Enterprise scale (e.g. "Kecil", "Menengah", "Besar") |
| `website` | `string nullable` | Company website |
| `tgl_daftar_siinas` | `timestamp nullable` | Registration date in SIINas (`tgl_daftar`) |
| `alamat_kantor` | `json nullable` | List of office addresses |
| `alamat_pabrik` | `json nullable` | List of factory addresses |
| `created_at`, `updated_at` | `timestamp` | Standard timestamps |

### Testing Seams

- **Primary Seam**: Feature HTTP test at `POST /api/auth/sso/siinas` using `Tests\TestCase` and Laravel's `Http::fake()` to stub SIINas responses.
- Test scenarios:
  1. Successful login and initial user + company profile creation.
  2. Subsequent login updating existing profile data and refreshing JWT token.
  3. Invalid SIINas credentials returning HTTP 401.
  4. SIINas upstream timeout / connection failure returning HTTP 502.
  5. Account collision when email already belongs to a non-industri user returning HTTP 409.
