# Custom Native RBAC with JWT Claims Distribution

To support granular access control across our multi-service Laravel architecture without multi-node cache desynchronization or polymorphic model namespace conflicts, we use a custom lightweight schema (`ms_permissions`, `ms_role_permissions`) instead of `spatie/laravel-permission`. Permissions are defined by resource `name` and `action`, composed into an immutable unique `code` (`{name}.{action}`), and can be managed by administrators. User permissions are evaluated at login in `user-service`, embedded directly into the JWT token claims, and validated by downstream services with zero database lookups. A feature flag `ENABLE_RBAC_PERMISSION` controls enforcement.

## Considered Options

- **`spatie/laravel-permission`**: Rejected because its default polymorphic schema (`model_type`) creates tight coupling to specific class namespaces across services, and its built-in in-memory/Redis permission cache requires complex cross-service invalidation whenever permissions are modified in `user-service`.
- **Synchronous HTTP / DB lookups on every request**: Rejected because querying the database or `user-service` on every downstream request creates latency bottlenecks and inter-service availability dependencies.
- **Custom Native RBAC with JWT Claims**: Selected because it keeps existing `ms_roles` and `users.role_id` intact, simplifies permission storage to two straightforward relational tables, allows instant zero-query validation in any downstream service via verified JWT claims, and permits safe phased rollout via the `ENABLE_RBAC_PERMISSION` environment flag.
