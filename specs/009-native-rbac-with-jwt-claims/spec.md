# Spec 009: Custom Native RBAC with JWT Claims Distribution

## Problem Statement

Currently, the IMC Reengineer backend relies on a primitive role checking mechanism where roles are hardcoded directly into route files via `check.role:super_admin,admin_pusat,...`. There is no concept of granular permissions in the database, no capability for administrators to manage permissions or associate permissions to roles via API, and no mechanism to protect endpoints on a per-action basis (`{resource}.{action}`).

Furthermore, in this multi-service architecture (`user-service`, `master-service`, `project-service`, `content-service`, etc.), adopting traditional monolithic authorization packages like `spatie/laravel-permission` introduces severe friction: polymorphic model conflicts across different service namespaces (`model_type`), cache desynchronization across independent service instances, and heavy unnecessary dependencies.

## Solution

Implement **Custom Native Role-Based Access Control (RBAC)** across the system:

1. **Database Schema (in `migration-service`)**:
   - Enhance `ms_roles` with `is_system` (`boolean`, default: `false`).
   - Create `ms_permissions` table:
     - `id` (bigIncrements)
     - `name` (string, resource name, e.g. `project`, `user`)
     - `action` (string, action name, e.g. `create`, `read`, `update`, `delete`)
     - `code` (string, unique, formatted as `{$name}.{$action}`)
     - `label` (string, human-readable display label)
     - `group` (string, nullable, UI group category)
     - `is_system` (boolean, default: `false`)
     - `created_at` & `updated_at`
   - Create `ms_role_permissions` pivot table:
     - `id` (bigIncrements)
     - `role_id` (foreign key `ms_roles.id`, `onDelete('cascade')`)
     - `permission_id` (foreign key `ms_permissions.id`, `onDelete('cascade')`)
     - Unique composite index `(role_id, permission_id)`
     - `created_at` & `updated_at`
   - Establish deletion safety rules:
     - `ms_roles` cannot be deleted if active users still reference `role_id` (`users.role_id`).
     - Roles and permissions with `is_system = true` cannot be deleted.

2. **Management APIs in `user-service`**:
   - Permission Endpoints:
     - `GET /api/v1/permissions`: List all permissions (support `group` filter and search).
     - `GET /api/v1/permissions/{id}`: Detail permission.
     - `POST /api/v1/permissions`: Create new permission (`name`, `action`, `label`, `group`), auto-generating `code = "{$name}.{$action}"`.
     - `PUT /api/v1/permissions/{id}`: Update permission (`label`, `group`). `code`, `name`, and `action` remain immutable.
     - `DELETE /api/v1/permissions/{id}`: Delete permission (restricted if `is_system = true`).
   - Role Endpoints:
     - `GET /api/v1/roles`: List roles with permission counts/details.
     - `GET /api/v1/roles/{id}`: Detail role with assigned permissions.
     - `POST /api/v1/roles`: Create role (`name`, `code`, `is_active`, `permission_ids: []`).
     - `PUT /api/v1/roles/{id}`: Update role (`name`, `is_active`, `permission_ids: []`). `code` immutable if `is_system = true`.
     - `DELETE /api/v1/roles/{id}`: Delete role (restricted if `is_system = true` or assigned to active users).

3. **JWT Claims Distribution**:
   - In `user-service/app/Models/User.php`, embed `role` code and array of permission `code` strings into JWT token custom claims (`getJWTCustomClaims()`).
   - Downstream services read permissions directly from the verified JWT payload without executing any database queries.

4. **Middleware `CheckPermission` with Environment Feature Flag**:
   - Register `check.permission` middleware.
   - Read `env('ENABLE_RBAC_PERMISSION', false)`.
   - If `ENABLE_RBAC_PERMISSION` is `false`, `null`, or undefined, immediately pass through (`$next($request)`).
   - If `true`:
     - Allow immediate bypass for role `super_admin`.
     - Verify if user token contains any/all required permission codes.
     - Return `403 Forbidden` (`ApiResponse::error('Forbidden - Insufficient permissions', '', 403)`) if unauthorized.

5. **Initial Seeder**:
   - Create `PermissionSeeder` in `migration-service` (and reusable across services) populating baseline permissions for user management, role management, project management, and content management.
   - Assign appropriate default permissions to existing roles (`super_admin`, `admin_pusat`, `admin_satker`, `pimpinan`, `admin_pustik`, `industri`).

---

## User Stories

1. As an administrator, I want to view a grouped list of all permissions, so that I understand what access rights are available in the system.
2. As an administrator, I want to create a new permission by specifying a resource `name`, `action`, `label`, and `group`, so that new feature gates can be dynamically represented in the system.
3. As an administrator, I want permission codes to be automatically constructed as `{name}.{action}` and strictly validated for uniqueness, so that human naming errors and syntax inconsistencies are prevented.
4. As an administrator, I want permission codes to be immutable after creation, so that existing frontend and backend permission checks are never broken by accidental renaming.
5. As an administrator, I want system permissions and system roles (`is_system = true`) to be protected from deletion, so that core platform functions (like `super_admin`) remain permanently intact.
6. As an administrator, I want to create, update, and view roles with their associated permissions in `user-service`, so that I can configure access levels for different staff types.
7. As an administrator, I want the system to prevent deleting a role that currently has active users assigned to it, so that users are never orphaned without valid authorization credentials.
8. As an authenticated user, I want my active permissions to be embedded inside my JWT token claims upon login, so that every backend service can instantly verify my access rights without query lag.
9. As a developer deploying to staging/production, I want `ENABLE_RBAC_PERMISSION=false` by default, so that route middleware does not block legitimate traffic until the frontend and permission matrices are fully ready.
10. As a superadmin user, I want unconditional bypass through all permission gates even when `ENABLE_RBAC_PERMISSION=true`, so that administrators always have root access.

---

## Implementation Decisions

1. **Lightweight Relational Schema**:
   - Standard relational tables: `ms_permissions`, `ms_roles`, and `ms_role_permissions`.
   - No polymorphic relations (`model_has_roles`), preserving existing `users.role_id`.
2. **Cascade Rules**:
   - Deleting a role or permission cascades to `ms_role_permissions`.
   - Deleting a role when users are assigned is blocked via validation rule and database foreign key integrity.
3. **Architecture Pattern**:
   - Controller → FormRequest → DTO → Service → Resource → `ApiResponse` envelope.
4. **Zero-Query Downstream Authorization**:
   - JWT claims store `permissions: string[]`.
   - `CheckPermission` inspects `auth()->payload()->get('permissions')` or authenticated user model permissions.
5. **Feature Flag**:
   - `env('ENABLE_RBAC_PERMISSION', false)`. If false/null, `$next($request)` immediately.

---

## Testing Decisions

- Feature tests in `user-service/tests/Feature/`:
  - `PermissionManagementTest`: CRUD operations for permissions, validation of `name.action` format, uniqueness, immutability of `code`, and protection of `is_system`.
  - `RoleManagementTest`: CRUD operations for roles, permission assignment, protection of `is_system`, and restriction when role has active users.
  - `CheckPermissionMiddlewareTest`: Verification of `ENABLE_RBAC_PERMISSION` flag (bypass when false, enforce when true, bypass for `super_admin`, 403 on missing permission).
  - `JwtCustomClaimsTest`: Ensure JWT token generated on login includes `role` and `permissions` array.

---

## Out of Scope

- Dynamic runtime permission checking on Frontend (delegated to FE implementation using the JWT claims / profile endpoint).
- Data-level row multitenancy permissions (e.g. project owner vs collaborator is handled via model policies / ownership checks).
- In-flight JWT token revocation upon permission update (changes take effect on next login/token refresh).

---

## Further Notes

- Ensure `user-service/app/Models/MsRole.php` and `user-service/app/Models/MsPermission.php` have appropriate `$fillable` and relationship methods (`permissions()`, `roles()`).
- Sync model classes to other services as needed.
