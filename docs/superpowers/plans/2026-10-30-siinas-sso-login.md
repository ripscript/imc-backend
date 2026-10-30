# SIINas SSO Authentication Implementation Plan

> **Goal:** Enable Single Sign-On (SSO) login for industrial users (`industri` role) by verifying credentials against Kemenperin's SIINas API, fetching enterprise company profiles, upserting user and profile data locally, and issuing standard application JWT tokens.

**Spec:** [specs/007-siinas-sso-login/spec.md](../../../specs/007-siinas-sso-login/spec.md)

---

## Tasks

- [x] **Task 1: Role Seeding and Migration**
  - Update `migration-service/database/seeders/RoleSeeder.php` to include `['code' => 'industri', 'name' => 'Industri']`.
  - Create migration for `company_profiles` in `migration-service/database/migrations/` and `user-service/database/migrations/`.
  
- [x] **Task 2: CompanyProfile Model and User Relationship**
  - Create `user-service/app/Models/CompanyProfile.php` with fillable attributes, JSON casts for `alamat_kantor` and `alamat_pabrik`, and `belongsTo(User::class)`.
  - Update `user-service/app/Models/User.php` with `companyProfile()` relation.

- [x] **Task 3: Configuration and SIINas Client Service**
  - Add `siinas` configuration to `user-service/config/services.php` (`base_url`, `timeout`).
  - Implement `user-service/app/Services/SiinasService.php` to communicate with SIINas endpoints:
    - `getLogin_v2.php` (GET with JSON body)
    - `getPerusahaanByID_v2.php` (POST with `id_siinas`)
  - Throw custom exceptions for auth failure (401) and service downtime/timeout (502).

- [x] **Task 4: Authentication Orchestrator and Request Validation**
  - Create `user-service/app/Http/Requests/SiinasLoginRequest.php` (`username`, `password` required).
  - Implement `user-service/app/Services/SiinasAuthService.php`:
    - Call `SiinasService` to authenticate and fetch company profile.
    - Check for conflicting non-industri accounts (throw 409 if conflict).
    - Upsert user (`name` = `nama_perusahaan`, `username`, `email` = `email_daftar_akun`, `role_id` for `industri`, random password).
    - Upsert `CompanyProfile`.
    - Generate JWT token via `auth('admin')->login($user)`.
    - Return token envelope and user with profile.

- [x] **Task 5: Controller and Route Registration**
  - Create `SiinasAuthController` (or add to `AdminAuthController`).
  - Register route `POST /auth/sso/siinas` in `user-service/routes/api.php`.

- [x] **Task 6: Feature Test Suite (TDD)**
  - Implement comprehensive feature tests in `user-service/tests/Feature/SiinasSsoLoginTest.php`:
    - Successful initial login & profile creation.
    - Re-login updating company profile and issuing fresh JWT.
    - Invalid SIINas credentials returning 401.
    - Upstream SIINas failure/timeout returning 502.
    - Conflict on existing internal user email returning 409.
