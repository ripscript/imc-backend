# Tasks: Custom Native RBAC with JWT Claims

**Spec**: [spec.md](spec.md) · **Plan**: [plan.md](plan.md) · **ADR**: [docs/adr/0002-native-rbac-with-jwt-claims.md](../../docs/adr/0002-native-rbac-with-jwt-claims.md)

**Status**: Implemented (2026-11-03). `user-service` test suite: **19 Feature tests green** (66 assertions). `project-service` test suite: **99 tests green** (1188 assertions). Pint clean.

## Phase 1 — Database Migrations & Seeders (`migration-service`)

- [x] Add `is_system` column to `ms_roles` table migration
- [x] Create `ms_permissions` table migration (`name`, `action`, `code`, `label`, `group`, `is_system`)
- [x] Create `ms_role_permissions` pivot table migration (`role_id`, `permission_id` with onDelete cascade)
- [x] Create `PermissionSeeder.php` with initial base permissions and role mappings
- [x] Update `RoleSeeder.php` to set `is_system = true` for core roles

## Phase 2 — Eloquent Models & JWT Claims (`user-service`)

- [x] Create `MsPermission` model (`$fillable`, `roles()` belongsToMany)
- [x] Update `MsRole` model (add `is_system` to `$fillable`, `permissions()` belongsToMany, `users()` hasMany)
- [x] Update `User::getJWTCustomClaims()` to embed `role` code and `permissions` array

## Phase 3 — Management APIs in `user-service`

- [x] Create `PermissionController`, Form Requests (`CreatePermissionRequest`, `UpdatePermissionRequest`), DTO, Service, and Resource
- [x] Implement Permission CRUD with automatic `code = name.action` generation, uniqueness, and immutability
- [x] Create `RoleController`, Form Requests (`CreateRoleRequest`, `UpdateRoleRequest`), DTO, Service, and Resource
- [x] Implement Role CRUD with permission assignment, `is_system` protection, and active-user deletion guard
- [x] Register routes in `user-service/routes/api.php` under `auth` and role protection

## Phase 4 — Middleware & Integration Tests

- [x] Implement `CheckPermission` middleware in `user-service` and register in `Kernel.php`
- [x] Configure `ENABLE_RBAC_PERMISSION` fallback logic (bypass if false/null, bypass super_admin, 403 on missing permission)
- [x] Feature tests for Permission Management (CRUD, validations, immutability, system protection)
- [x] Feature tests for Role Management (CRUD, permissions sync, active users guard)
- [x] Feature tests for `CheckPermission` middleware & JWT custom claims
- [x] Replicate `CheckPermission` middleware and models to downstream services (`project-service`)
