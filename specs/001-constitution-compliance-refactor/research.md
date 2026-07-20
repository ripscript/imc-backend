# Research: Constitution Compliance Refactor

**Phase**: 0 — Outline & Research
**Date**: 2026-07-20
**Spec**: [spec.md](spec.md)

## Research Decisions

### 1. DTO Implementation Strategy

- **Decision**: Simple readonly PHP classes (no external package)
- **Rationale**: Aligns with Principle VIII (YAGNI/Simplicity). Avoids adding a new dependency. Easy to understand and test. Can be upgraded to `spatie/laravel-data` later if pain justifies it.
- **Alternatives considered**:
  - `spatie/laravel-data` package — full DTO support with validation, transformation, TypeScript generation. Rejected because it adds a dependency for a pattern that may not need all features.
  - Laravel Form Requests as DTOs — simplest but couples services to HTTP layer, violating Principle II.

### 2. Service Decomposition Strategy

- **Decision**: One service per controller (1:1 mapping)
- **Rationale**: Simplest approach that minimizes refactoring risk. Each controller delegates to its corresponding service. Follows the existing controller structure. Can be refactored further later if needed. Aligns with Principle VIII (YAGNI).
- **Alternatives considered**:
  - One service per domain aggregate — more reusable but requires understanding domain boundaries upfront, increasing risk of incorrect decomposition.
  - One service per operation (Action pattern) — most granular but highest class count. Premature optimization for this refactoring.

### 3. Form Request Extraction Pattern

- **Decision**: One Form Request class per controller method that requires validation
- **Rationale**: Each controller method has unique validation rules. Extracting to a dedicated class keeps validation logic testable and follows Laravel conventions. Form Request naming: `{Action}{Entity}Request` (e.g., `CreateUserRequest`, `UpdateFaqRequest`).
- **Pattern**: Each Form Request will:
  - Implement `authorize()` method (delegates to Policy or returns true for public endpoints)
  - Implement `rules()` method with exact validation rules from the original controller
  - Preserve custom validation logic (e.g., `UniqueSoftDelete` rule)

### 4. API Resource Pattern

- **Decision**: One API Resource class per model
- **Rationale**: API Resources own response shaping, ensuring consistent `{success, message, data}` envelope. Resources wrap model data and are returned from controllers via `ApiResponse::success()`.
- **Pattern**: Each Resource will:
  - Extend `Illuminate\Http\Resources\Json\JsonResource`
  - Implement `toArray()` with the exact fields currently returned by the controller
  - Be wrapped by `ApiResponse::success()` in the controller

### 5. Policy Registration Pattern

- **Decision**: One Policy class per model with CRUD endpoints, registered in `AuthServiceProvider`
- **Rationale**: Policies centralize authorization logic and make it testable. Existing `check.role` middleware remains in place for route-level access control. Policies add model-level gates.
- **Pattern**: Each Policy will:
  - Implement `viewAny()`, `view()`, `create()`, `update()`, `delete()` methods
  - Mirror the role checks currently performed inline in controllers
  - Be registered in `AuthServiceProvider::$policies` array

### 6. X-Request-ID Middleware Implementation

- **Decision**: One middleware per service, registered in HTTP kernel
- **Rationale**: Middleware is the standard Laravel pattern for request/response header manipulation. Each service needs its own middleware instance.
- **Pattern**: Middleware will:
  - Check for `X-Request-ID` header on incoming request
  - If absent, generate a UUID (v4)
  - Add the correlation ID to the request attributes
  - Add the correlation ID to the response headers
  - Include the correlation ID in structured log context via `Log::withContext()`

### 7. Health Endpoint Enhancement Pattern

- **Decision**: Enhanced inline route closure in `routes/api.php`
- **Rationale**: Health endpoints are currently simple inline closures. Enhancement adds dependency status checks (DB, cache, queue) while preserving the existing response structure.
- **Pattern**: Enhanced health endpoint will:
  - Check DB connection via `DB::connection()->getPdo()`
  - Check cache connection via `Cache::store()->get('health-check')`
  - Check queue connection via `Queue::size()` or connection test
  - Return `{status, message, time, dependencies: {db, cache, queue}}`

### 8. HasActivityLog Trait Migration Strategy

- **Decision**: Migrate manual `LogActivity::create()` calls in controllers to `HasActivityLog` trait model events where applicable
- **Rationale**: The `HasActivityLog` trait already exists in content-service, master-service, and setting-service. It uses model events (`created`, `updated`, `deleted`) to automatically log activity. Manual calls in controllers should be removed and the trait applied to models instead.
- **Pattern**: For each model that has manual `LogActivity::create()` calls in its controller:
  - Apply the `HasActivityLog` trait to the model
  - Define `$logName` property on the model
  - Remove the manual `LogActivity::create()` call from the controller
  - For cases where the trait is not suitable (e.g., auth controllers), move the call to the Service layer

### 9. Test Pyramid Strategy

- **Decision**: Unit (service logic) > Feature (HTTP endpoint) > Integration (inter-service contract)
- **Rationale**: Follows Principle IV (Test-First) and standard Laravel testing conventions.
- **Pattern**:
  - **Unit tests**: Mock external dependencies (DB, cache, queue). Test service methods in isolation. One test class per service class.
  - **Feature tests**: Use test database. Test HTTP endpoints via `get()`, `post()`, `put()`, `delete()`. Verify response envelope, status code, and pagination shape. One test class per controller.
  - **Integration tests**: Test inter-service contract preservation. Compare before/after responses for the same inputs.

### 10. Regression Test Approach for E2E Contract Preservation

- **Decision**: Snapshot-based regression testing
- **Rationale**: The critical constraint is that no e2e contract changes. Snapshot testing captures the current response for each endpoint and compares after refactoring.
- **Pattern**: For each endpoint:
  - Capture current response (status code, headers, body) as a snapshot
  - After refactoring, run the same request and compare
  - Any difference in status code, envelope shape, pagination format, or field names is a regression
  - Use PHPUnit's `assertJsonStructure()` and `assertJsonFragment()` for structural comparison

### 11. Mass-Assignment Protection Strategy

- **Decision**: Use `$fillable` property on every model
- **Rationale**: `$fillable` is the Laravel convention for whitelisting mass-assignable attributes. It's more secure than `$guarded` (blacklist) because it explicitly declares which fields are allowed.
- **Pattern**: For each model currently lacking `$fillable`:
  - Analyze the controller to identify which fields are manually assigned
  - Set `$fillable` to the exact set of those fields
  - This ensures the refactored code (using `create()` or `update()`) behaves identically to the current manual assignment

### 12. Controller Refactoring Pattern

- **Decision**: Extract business logic in a consistent order: validation → Form Request, response shaping → API Resource, business logic → Service, data transfer → DTO
- **Rationale**: Consistent pattern across all 39+ controllers reduces cognitive load and review effort.
- **Pattern**:
  1. Create Form Request class with validation rules from controller
  2. Create DTO class with fields from controller method parameters
  3. Create Service class method with business logic from controller
  4. Create API Resource class with response fields from controller
  5. Refactor controller to: receive Form Request → create DTO → call Service → return ApiResponse with Resource