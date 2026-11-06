# Implementation Plan: Custom Native RBAC with JWT Claims

**Spec**: [spec.md](spec.md) · **ADR**: [docs/adr/0002-native-rbac-with-jwt-claims.md](../../docs/adr/0002-native-rbac-with-jwt-claims.md)

## Overview

Implement custom native RBAC with four structured phases:

| Phase | Theme | Primary Scope |
|---|---|---|
| 1 | Database Migrations & Seeders | `migration-service` |
| 2 | Eloquent Models & JWT Claims | `user-service/app/Models` |
| 3 | Management APIs (Permissions & Roles) | `user-service` (Controller, DTO, Request, Service, Resource, Routes) |
| 4 | Middleware `CheckPermission` & Integration Tests | `user-service`, `project-service`, and test suites |

---

## Phase 1 — Database Migrations & Seeders (`migration-service`)

1. Migration `2026_11_03_000001_add_is_system_to_ms_roles_table.php`:
   - Add `is_system` boolean default `false` to `ms_roles`.
2. Migration `2026_11_03_000002_create_ms_permissions_table.php`:
   - `id`, `name`, `action`, `code` (unique), `label`, `group` (nullable), `is_system` (default false), `timestamps`.
3. Migration `2026_11_03_000003_create_ms_role_permissions_table.php`:
   - `id`, `role_id` (foreign key `ms_roles`, cascade on delete), `permission_id` (foreign key `ms_permissions`, cascade on delete), unique composite `['role_id', 'permission_id']`, `timestamps`.
4. `PermissionSeeder.php`:
   - Seed baseline permissions (`user.create`, `user.read`, `user.update`, `user.delete`, `role.manage`, `permission.manage`, `project.create`, `project.read`, `project.update`, `project.delete`, etc.).
   - Assign permissions to default roles (`super_admin` has full, `admin_pusat`, etc.).
   - Update `RoleSeeder.php` to set `is_system = true` for core roles.

---

## Phase 2 — Models & JWT Custom Claims (`user-service`)

1. Model `MsPermission.php`:
   - `$table = 'ms_permissions'`, `$fillable = ['name', 'action', 'code', 'label', 'group', 'is_system']`.
   - `roles()`: `belongsToMany(MsRole::class, 'ms_role_permissions', 'permission_id', 'role_id')`.
2. Model `MsRole.php` update:
   - Add `is_system` to `$fillable`.
   - `permissions()`: `belongsToMany(MsPermission::class, 'ms_role_permissions', 'role_id', 'permission_id')`.
   - `users()`: `hasMany(User::class, 'role_id')`.
3. Model `User.php` update:
   - In `getJWTCustomClaims()`:
     ```php
     return [
         'role' => $this->role?->code,
         'permissions' => $this->role?->permissions()->pluck('code')->toArray() ?? [],
     ];
     ```

---

## Phase 3 — Management APIs in `user-service`

1. **Permission CRUD**:
   - `PermissionController.php`: `index`, `show`, `store`, `update`, `destroy`.
   - `CreatePermissionRequest.php`, `UpdatePermissionRequest.php`.
   - `PermissionDTO.php`, `PermissionService.php`.
   - `PermissionResource.php`.
2. **Role CRUD & Permission Sync**:
   - `RoleController.php`: `index`, `show`, `store`, `update`, `destroy`.
   - `CreateRoleRequest.php`, `UpdateRoleRequest.php`.
   - `RoleDTO.php`, `RoleService.php`.
   - `RoleResource.php` (including permissions list).
3. **Validation & Protection Rules**:
   - Forbid deleting `is_system = true` records.
   - Forbid deleting role when `users()->exists()`.
   - Auto-generate `code = "{$name}.{$action}"` on create; make `code` immutable on update.

---

## Phase 4 — Middleware & Integration Tests

1. **`CheckPermission` Middleware**:
   - Implement `App\Http\Middleware\CheckPermission`:
     - Checks `env('ENABLE_RBAC_PERMISSION', false)`. Passes if false/null.
     - Passes if `$user->role->code === 'super_admin'`.
     - Validates permission from JWT claims (`auth('admin')->payload()->get('permissions')`) or user model.
     - Aborts with 403 `ApiResponse::error('Forbidden - Insufficient permissions', '', 403)`.
   - Register alias `'check.permission'` in `app/Http/Kernel.php`.
2. **Feature Tests**:
   - `tests/Feature/PermissionManagementTest.php`
   - `tests/Feature/RoleManagementTest.php`
   - `tests/Feature/CheckPermissionMiddlewareTest.php`
   - `tests/Feature/JwtClaimsTest.php`
