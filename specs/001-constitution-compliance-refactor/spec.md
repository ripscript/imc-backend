# Feature Specification: Constitution Compliance Refactor

**Feature Branch**: `001-constitution-compliance-refactor`

**Created**: 2026-07-20

**Status**: Draft

**Input**: User description: "refactor the existing project against the constitution, always make sure it doesnt change e2e request response, always clarify if theres a changes."

## Clarifications

### Session 2026-07-20

- Q: Should pemohon (applicant) endpoints be refactored in this spec, or only admin/internal endpoints? → A: Include all endpoints (admin + pemohon) — full constitution compliance across the entire codebase.
- Q: What DTO implementation strategy should be used for cross-layer data transfer? → A: Simple readonly PHP classes (no external package) — plain PHP classes with typed readonly properties and a constructor.
- Q: How should Service classes be organized for business logic extraction? → A: One service per controller (1:1 mapping) — e.g., UserController → UserService, FaqController → FaqService. Simplest, lowest risk.

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Refactor Controllers to Transport-Only (Priority: P1)

As a developer maintaining the IMC backend, I want all controllers to be transport-only (receive request, delegate to service, return response) so that business logic is properly separated per the Layered Clean Architecture principle.

**Why this priority**: This is the most impactful violation — 39 controllers contain inline validation, business logic, and DB transactions. Fixing this unlocks testability, maintainability, and all downstream compliance.

**Independent Test**: Can be fully tested by verifying that for each refactored endpoint, the HTTP request/response contract (status code, response envelope shape, pagination shape) remains identical to the pre-refactor behavior. A regression test suite comparing before/after responses for the same inputs validates this independently.

**Acceptance Scenarios**:

1. **Given** a controller that previously contained inline `$request->validate()` calls, **When** the refactored code is deployed, **Then** validation is handled by a dedicated Form Request class and the controller contains no validation logic.
2. **Given** a controller that previously contained inline business logic (DB queries, role checks, manual property assignment), **When** the refactored code is deployed, **Then** that logic lives in a Service class and the controller only delegates to the service.
3. **Given** a controller that previously returned raw model instances, **When** the refactored code is deployed, **Then** response shaping is handled by an API Resource class.
4. **Given** any refactored endpoint, **When** called with the same input as before refactoring, **Then** the HTTP response (status code, `{success, message, data/errors}` envelope, pagination `{data, meta}` shape) is identical.

---

### User Story 2 - Add Mass-Assignment Protection to All Models (Priority: P1)

As a security-conscious developer, I want all Eloquent models to declare `$fillable` or `$guarded` so that mass-assignment vulnerabilities are eliminated.

**Why this priority**: 50 models currently lack mass-assignment protection, creating a direct security vulnerability (Principle VII). This is a high-impact, low-complexity fix.

**Independent Test**: Can be fully tested by verifying that every model class in every service has either `$fillable` or `$guarded` property defined. A static analysis check or unit test can validate this independently.

**Acceptance Scenarios**:

1. **Given** a model that previously lacked `$fillable` or `$guarded`, **When** the refactored code is deployed, **Then** the model declares `$fillable` with the exact set of columns that were previously assigned manually in controllers.
2. **Given** any model, **When** a mass-assignment attempt is made with attributes not in `$fillable`, **Then** the model throws a `MassAssignmentException`.

---

### User Story 3 - Add Policies for Authorization (Priority: P2)

As a security-conscious developer, I want authorization logic to use Laravel Policies instead of inline role checks in controllers, so that authorization is centralized, testable, and follows the constitution.

**Why this priority**: Currently authorization is only via `check.role` middleware. Adding Policies enables model-level authorization gates and aligns with Principle VII (Security & Consistency).

**Independent Test**: Can be fully tested by verifying that each model with CRUD endpoints has a corresponding Policy class registered in `AuthServiceProvider`, and that Policy methods return the same authorization results as the previous inline checks.

**Acceptance Scenarios**:

1. **Given** a model with CRUD endpoints, **When** the refactored code is deployed, **Then** a Policy class exists for that model with `viewAny`, `view`, `create`, `update`, `delete` methods.
2. **Given** a Policy method, **When** called with the same user and model as the previous inline role check, **Then** it returns the same boolean result.

---

### User Story 4 - Add Observability: Correlation ID & Enhanced Health (Priority: P2)

As an operations engineer, I want `X-Request-ID` correlation IDs propagated across all services and health endpoints reporting dependency status, so that I can trace requests across the microservice mesh and monitor system health.

**Why this priority**: Currently no correlation ID propagation exists and health endpoints only return a static response. This is critical for debugging production issues (Principle VI).

**Independent Test**: Can be fully tested by sending a request with an `X-Request-ID` header and verifying the same ID appears in the response and in structured logs. Health endpoint can be tested by calling `/health` and verifying DB/cache/queue status fields.

**Acceptance Scenarios**:

1. **Given** a request with `X-Request-ID` header, **When** processed by any service, **Then** the response includes the same `X-Request-ID` header and the ID is included in all structured log entries for that request.
2. **Given** a request without `X-Request-ID` header, **When** processed by any service, **Then** a UUID is generated and used as the correlation ID for that request.
3. **Given** the `/health` endpoint on any service, **When** called, **Then** the response includes status fields for DB connection, cache connection, and queue connection.

---

### User Story 5 - Add Test Foundation (Priority: P3)

As a quality assurance engineer, I want factories for all models and feature tests for all endpoints, so that the codebase has a foundation for TDD and the 80% coverage gate can be enforced going forward.

**Why this priority**: Currently only stub tests and default UserFactory exist. Without tests, no refactoring can be verified as safe. This is foundational but lower priority than the structural refactors.

**Independent Test**: Can be fully tested by running `phpunit` and verifying all new tests pass with 80%+ coverage on refactored code.

**Acceptance Scenarios**:

1. **Given** any Eloquent model, **When** the test suite runs, **Then** a factory exists for that model that can generate valid model instances.
2. **Given** any public endpoint, **When** the test suite runs, **Then** a feature test exists that verifies the endpoint returns the correct response envelope and status code.
3. **Given** any service class, **When** the test suite runs, **Then** a unit test exists that mocks external dependencies and verifies business logic.
4. **Given** the full test suite, **When** run, **Then** code coverage on refactored code is at least 80%.

---

### Edge Cases

- What happens when a Form Request validation rule differs from the previous inline validation? — All Form Requests must be verified against the original controller validation to ensure identical behavior.
- How does the system handle a service that calls another service internally? — Inter-service calls must preserve the correlation ID and use the same response envelope.
- What happens to the `LogActivity::create()` manual calls in controllers? — These must be migrated to `HasActivityLog` trait model events where applicable, or moved to the Service layer if the trait is not suitable.
- How are existing `check.role` middleware interactions preserved when adding Policies? — Policies are additive; `check.role` middleware remains in place. Policies provide an additional authorization layer for model-level gates.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: System MUST extract all inline `$request->validate()` calls from controllers into dedicated Form Request classes, one per controller method that requires validation.
- **FR-002**: System MUST move all business logic (DB queries, transactions, role checks, entity lookups) from controllers into Service classes.
- **FR-003**: System MUST move all response shaping from controllers into API Resource classes.
- **FR-004**: System MUST use DTOs/value objects for data transfer between controllers and services — `Illuminate\Http\Request` MUST NOT be passed to services.
- **FR-005**: System MUST declare `$fillable` on every Eloquent model that currently lacks it, with the exact set of columns that were previously assigned manually.
- **FR-006**: System MUST create a Policy class for every model that has CRUD endpoints, registered in the service's `AuthServiceProvider`.
- **FR-007**: System MUST propagate `X-Request-ID` correlation ID across all services via middleware, generating a UUID if none is provided.
- **FR-008**: System MUST enhance `/health` endpoints to report dependency status (DB connection, cache connection, queue connection).
- **FR-009**: System MUST migrate manual `LogActivity::create()` calls in controllers to `HasActivityLog` trait model events where applicable.
- **FR-010**: System MUST create Eloquent factories for every model that currently lacks one.
- **FR-011**: System MUST create feature tests for every public endpoint, verifying response envelope, status code, and pagination shape.
- **FR-012**: System MUST create unit tests for every Service class method, mocking external dependencies.
- **FR-013**: System MUST NOT change any existing e2e HTTP response contract (envelope shape, status codes, pagination format, route paths).
- **FR-014**: System MUST document duplicated cross-cutting code (ApiResponse, QueryService, LogActivity, CheckRole, HasActivityLog) as justified per Principle VIII (YAGNI) in the plan.md Complexity Tracking table — no shared package extraction in this spec.

### Non-Functional Requirements (Constitution-Driven)

- **NFR-001 (Performance)**: All list endpoints MUST continue to support pagination. N+1 queries MUST be avoided via eager loading. The existing `ILIKE '%...%'` search pattern is documented as a known performance issue deferred to a separate performance-focused spec.
- **NFR-002 (Testability)**: Every refactored model MUST include a factory. Every refactored endpoint MUST include a feature test. Unit tests MUST mock external dependencies. Coverage gate: 80% on all refactored code, no regression on existing.
- **NFR-003 (Layered Architecture)**: Controllers MUST be transport-only. `Illuminate\Http\Request` MUST NOT be passed to services — use DTOs. Business logic MUST NOT live in controllers. Form Requests own validation. Services own business logic. API Resources own response shaping.
- **NFR-004 (Observability)**: Activity log MUST be captured via model events (`HasActivityLog` trait), not manual calls. Structured logging with context (user, action, entity, correlation ID) is required. Correlation ID propagation across services MUST be included.
- **NFR-005 (Security)**: JWT guards on all non-public routes. Authorization via Policies + `check.role` middleware, not inline role checks. Mass-assignment protection via `$fillable` on every model. Consistent `ApiResponse` envelope across all services. Consistent HTTP status mapping preserved: 200 success, 201 created, 422 validation, 401 unauth, 403 forbidden, 404 not found, 500 server error.

### Key Entities *(include if feature involves data)*

- **FormRequest classes**: One per controller method requiring validation, encapsulating validation rules and authorization logic.
- **Service classes**: One per domain aggregate (e.g., UserService, FaqService, ContentService), encapsulating business logic and DB transactions.
- **API Resource classes**: One per model for response shaping, ensuring consistent `{success, message, data}` envelope.
- **DTO classes**: One per service method for cross-layer data transfer, replacing direct `Request` object passing.
- **Policy classes**: One per model with CRUD endpoints, encapsulating authorization gates.
- **Middleware (X-Request-ID)**: One middleware per service for correlation ID propagation.
- **Model factories**: One per Eloquent model for test data generation.
- **Feature tests**: One per public endpoint for contract verification.
- **Unit tests**: One per service method for business logic verification.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: Zero controllers contain inline `$request->validate()` calls, inline business logic, or raw model returns — verified by static analysis across all 7 services.
- **SC-002**: 100% of Eloquent models (50 currently non-compliant) declare `$fillable` or `$guarded` — verified by static analysis.
- **SC-003**: Every model with CRUD endpoints has a Policy class registered in `AuthServiceProvider` — verified by static analysis.
- **SC-004**: All 6 services propagate `X-Request-ID` and all 6 `/health` endpoints report dependency status — verified by integration test.
- **SC-005**: All 191+ endpoints return identical HTTP responses (status code, envelope shape, pagination format) before and after refactoring — verified by regression test suite.
- **SC-006**: Code coverage on all refactored code reaches at least 80% — verified by PHPUnit coverage report.
- **SC-007**: All manual `LogActivity::create()` calls in controllers are eliminated — verified by static analysis.

## Assumptions

- **Scope boundary**: Shared cross-cutting code duplication (ApiResponse, QueryService, LogActivity, CheckRole, HasActivityLog) is documented as justified per Principle VIII (YAGNI) and NOT extracted into a shared package in this spec. This is a deliberate decision to keep scope manageable.
- **Scope boundary**: The `ILIKE '%...%'` → full-text search migration is deferred to a separate performance-focused spec. The existing search behavior is preserved as-is.
- **Scope boundary**: The `migration-service` has no routes and is excluded from endpoint refactoring. It may be addressed in a separate spec.
- **Scope boundary**: The `permohonan-service` referenced in nginx config does not exist in this repository and is excluded.
- **Existing authentication system**: JWT guards, `check.role` middleware, and the existing auth flow are preserved. Policies are additive, not a replacement.
- **Existing response envelope**: The `{success, message, data/errors}` envelope and `{data, meta}` pagination shape are preserved exactly.
- **Existing route paths**: All route paths (via Nginx prefixes) remain unchanged.
- **Test coverage target**: 80% coverage applies to all refactored code (new/modified files), not the entire codebase. Existing untested code is not penalized.
- **Dependency on existing system**: Requires the existing Laravel 10 framework, PHPUnit 10, and Spatie laravel-activitylog package.