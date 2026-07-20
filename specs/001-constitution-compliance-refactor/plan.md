# Implementation Plan: Constitution Compliance Refactor

**Branch**: `001-constitution-compliance-refactor` | **Date**: 2026-07-20 | **Spec**: [spec.md](spec.md)

**Input**: Feature specification from `specs/001-constitution-compliance-refactor/spec.md`

## Summary

Refactor the IMC Backend microservices monorepo (6 active services: user-service, master-service, content-service, public-content-service, file-service, setting-service) to comply with the project constitution (v1.0.0). The refactoring enforces Layered Clean Architecture (controllers transport-only, Form Requests for validation, Services for business logic, API Resources for response shaping, DTOs for cross-layer data transfer), adds mass-assignment protection to 50+ models, introduces Policies for authorization, adds X-Request-ID correlation ID propagation and enhanced health endpoints, migrates manual LogActivity calls to model events, and establishes a test foundation with factories, feature tests, and unit tests targeting 80% coverage on refactored code. All ~191 existing endpoints must preserve their e2e HTTP response contracts (envelope shape, status codes, pagination format, route paths).

## Technical Context

**Language/Version**: PHP 8.1+

**Primary Dependencies**: Laravel 10, php-open-source-saver/jwt-auth, Spatie laravel-activitylog, Guzzle, Laravel Sanctum

**Storage**: PostgreSQL (shared instance, per-service schemas)

**Testing**: PHPUnit 10 + Mockery + Faker

**Target Platform**: Docker containers (Dockerfile.dev + Dockerfile.staging) behind Nginx reverse proxy

**Project Type**: Microservices monorepo (6 active services + 1 migration-service with no routes)

**Performance Goals**: All list endpoints paginated; N+1 queries avoided via eager loading; ILIKE search deferred to separate spec

**Constraints**: Zero e2e contract changes; 80% coverage on refactored code; no shared package extraction (YAGNI)

**Scale/Scope**: ~191 endpoints across 6 services, ~60+ Eloquent models, 7 Laravel applications

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

Reference: `.specify/memory/constitution.md` (v1.0.0)

### Gates (all MUST pass)

1. **Microservice Autonomy** — PASS. This feature does not introduce cross-service DB reads. Shared cross-cutting code duplication (ApiResponse, QueryService, LogActivity, CheckRole, HasActivityLog) is documented as justified per Principle VIII (YAGNI) in Complexity Tracking below — no shared package extraction in this spec.

2. **Layered Architecture** — PASS. The spec mandates: controllers are transport-only (FR-001, FR-002, FR-003), `Illuminate\Http\Request` is NOT passed to services (FR-004 uses DTOs), Form Requests own validation, Services own business logic, API Resources own response shaping. NFR-003 enforces this.

3. **Contract-First** — PARTIAL. Inter-service contracts are preserved via regression testing (SC-005) but OpenAPI specs are not in scope for this refactoring. Contract tests are planned as feature tests (FR-011). This is a known gap documented in Complexity Tracking.

4. **Test-First** — PASS. The spec mandates factories for every model (FR-010), feature tests for every endpoint (FR-011), unit tests for every service method (FR-012), and 80% coverage gate on refactored code (NFR-002, SC-006). Tests are written as part of the refactoring, not deferred.

5. **Performance** — PARTIAL. All list endpoints continue to paginate (NFR-001). N+1 queries are avoided via eager loading. The existing `ILIKE '%...%'` search pattern is documented as a known performance issue deferred to a separate performance-focused spec (Complexity Tracking).

6. **Observability** — PASS. X-Request-ID correlation ID propagation is mandated (FR-007). Health endpoints enhanced to report dependency status (FR-008). Manual LogActivity calls migrated to HasActivityLog model events (FR-009). Structured logging with context required (NFR-004).

7. **Security** — PASS. JWT guards preserved on all non-public routes. Policies added for model-level authorization (FR-006) alongside existing `check.role` middleware. Mass-assignment protection via `$fillable` on every model (FR-005). Consistent ApiResponse envelope and HTTP status mapping preserved (NFR-005).

8. **Simplicity/YAGNI** — PASS. Simplest approach chosen: 1:1 service-to-controller mapping (per clarification), simple readonly PHP DTOs (no external package), no shared package extraction. All deviations justified in Complexity Tracking.

### Gate Evaluation Result: **PASS with notes**

Two PARTIAL gates (Contract-First, Performance) have documented justifications in Complexity Tracking. All other gates PASS. Proceeding to Phase 0.

## Project Structure

### Documentation (this feature)

```text
specs/001-constitution-compliance-refactor/
├── plan.md              # This file (/speckit-plan command output)
├── research.md          # Phase 0 output (/speckit-plan command)
├── data-model.md        # Phase 1 output (/speckit-plan command)
├── quickstart.md        # Phase 1 output (/speckit-plan command)
├── contracts/           # Phase 1 output (/speckit-plan command)
│   ├── user-service.md
│   ├── master-service.md
│   ├── content-service.md
│   ├── public-content-service.md
│   ├── file-service.md
│   └── setting-service.md
└── tasks.md             # Phase 2 output (/speckit-tasks command - NOT created by /speckit-plan)
```

### Source Code (repository root)

```text
# Microservices monorepo — each service is a standalone Laravel application
# Refactoring adds new directories within each service:

{service}/
├── app/
│   ├── Http/
│   │   ├── Controllers/       # Existing controllers — refactored to transport-only
│   │   ├── Requests/          # NEW: Form Request classes (one per controller method)
│   │   └── Resources/         # NEW: API Resource classes (one per model)
│   ├── Services/              # NEW: Service classes (one per controller, 1:1 mapping)
│   │   └── DTOs/              # NEW: Simple readonly DTO classes
│   ├── Models/                # Existing models — add $fillable
│   ├── Policies/              # NEW: Policy classes (one per model with CRUD)
│   └── Http/Middleware/       # Existing + NEW: X-Request-ID middleware
├── database/
│   └── factories/             # NEW: Model factories (one per model)
└── tests/
    ├── Feature/               # NEW: Feature tests (one per endpoint)
    └── Unit/                  # NEW: Unit tests (one per service method)
```

**Structure Decision**: Each service retains its existing Laravel structure. New directories (Requests, Resources, Services/DTOs, Policies) are added within each service's `app/` namespace. Tests follow the existing `tests/Feature/` and `tests/Unit/` convention. No cross-service shared package is created (YAGNI-justified).

## Complexity Tracking

> Fill ONLY if Constitution Check has violations that must be justified

| Violation | Why Needed | Simpler Alternative Rejected Because |
|-----------|------------|-------------------------------------|
| Cross-cutting code duplication (ApiResponse, QueryService, LogActivity, CheckRole, HasActivityLog duplicated across services) | YAGNI — extracting a shared composer package adds build complexity, versioning overhead, and cross-service coupling that is not justified for code that is stable and rarely changes | Creating a shared package would require: new composer.json, CI pipeline changes, version tagging, and per-service dependency updates. The duplication is stable and well-understood. |
| ILIKE `'%...%'` full-text search pattern preserved | Scope boundary — migrating to tsvector/GIN full-text search requires DB migrations, index management, and query changes that are out of scope for this architecture-focused refactor | Deferring to a separate performance-focused spec allows focused effort on search optimization without blocking the architecture compliance work |
| No OpenAPI specs for inter-service contracts | Scope boundary — generating OpenAPI specs for ~191 endpoints is a documentation effort orthogonal to the code architecture refactoring | Contract preservation is verified via regression test suite (SC-005) instead. OpenAPI generation can be addressed in a follow-up spec. |