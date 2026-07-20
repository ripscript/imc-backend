<!--
Sync Impact Report
==================
Version change: 0.0.0 (unfilled template) → 1.0.0 (initial ratification)
Modified principles: N/A (all 8 principles newly added)
Added sections: Core Principles (I–VIII), Technology Stack, Development Workflow, Governance
Removed sections: N/A
Templates requiring updates:
  - .specify/templates/plan-template.md → ✅ updated (Constitution Check gates aligned with 8 principles)
  - .specify/templates/spec-template.md → ✅ updated (Non-Functional Requirements section added)
  - .specify/templates/tasks-template.md → ✅ updated (Tests marked MANDATORY per Principle IV)
Follow-up TODOs: None — all placeholders resolved.
-->

# IMC Backend Constitution

## Core Principles

### I. Microservice Autonomy & Bounded Contexts
Each service owns its schema and business domain; inter-service communication is via versioned HTTP contracts only (no shared DB reads across boundaries). Every service MUST be independently deployable, testable, and runnable in isolation. Shared cross-cutting code (ApiResponse, QueryService, middleware) MUST be extracted into a shared composer package or duplicated-with-justification documented in plan.md Complexity Tracking.

### II. Layered Clean Architecture (NON-NEGOTIABLE)
Strict layering enforced: Controllers are transport-only (receive request, delegate to service, return ApiResponse); Form Requests own validation and authorization; Services own business logic and DB transactions; Models own Eloquent concerns and relations; API Resources own response shaping. Business logic MUST NOT live in controllers. The `Illuminate\Http\Request` object MUST NOT be passed into services — use DTOs/value objects for cross-layer data transfer. No inline role checks in controllers — use Policies + middleware.

### III. Contract-First & Interface-Driven
Services depend on interfaces bound in ServiceProvider, not concrete classes. DTOs transfer data across layers. Inter-service endpoints are documented with OpenAPI specs and protected by contract tests. Breaking contract changes require version bump + consumer migration plan.

### IV. Test-First (NON-NEGOTIABLE)
TDD red-green-refactor enforced: tests written and approved before implementation. Test pyramid: unit (service logic, mocked dependencies) > feature (HTTP endpoint via test database) > integration (inter-service contract). Every Eloquent model MUST have a factory; every public endpoint MUST have a feature test; unit tests mock external dependencies. Coverage gate: 80% for new features, no regression on existing. Tests run in CI before merge.

### V. Performance & Scalability
No N+1 queries: eager-load with `with()`/`load()` or select only needed columns. All list endpoints MUST paginate. Avoid `ILIKE '%...%'` full scans — use indexed columns or full-text search (tsvector/GIN) for search. Cache read-heavy reference data (`Cache::remember` with tags). Queue heavy work (activity logs, file processing, notifications, emails) via Laravel Queue. Index all foreign keys and hot filter columns. Use `chunkById` for large dataset processing. Avoid loading entire collections into memory.

### VI. Observability & Auditability
Structured logging with context (user, action, entity, correlation ID). Activity log captured via model events (HasActivityLog trait) not manual calls. `X-Request-ID` correlation ID propagated across services. `/health` endpoint reports dependency status (DB, cache, queue). Errors logged with stack trace in non-prod, sanitized in prod. `ApiResponse::error` masks 500 details in production — maintain this behavior.

### VII. Security & Consistency
JWT guards on all non-public routes. Authorization via Policies + `check.role` middleware (not inline if/else role checks). Mass-assignment protection via `$fillable` or `$guarded` on every model. Consistent `ApiResponse` envelope (`{ success, message, data/errors }`) across all services. Consistent HTTP status mapping: 200 success, 201 created, 422 validation, 401 unauth, 403 forbidden, 404 not found, 500 server error. Validate all input via Form Requests. Sanitize file uploads.

### VIII. Simplicity & YAGNI
Start with the simplest passing solution. Introduce patterns (Repository, Strategy, DTO) only when pain justifies them. Prefer Laravel conventions over custom abstractions. Any deviation from principles MUST be justified in the plan.md Complexity Tracking table. No premature optimization — measure first, then optimize hot paths.

## Technology Stack

- **Language**: PHP 8.1+
- **Framework**: Laravel 10
- **Database**: PostgreSQL (shared instance, per-service schemas)
- **Auth**: php-open-source-saver/jwt-auth + Laravel Sanctum
- **Logging/Audit**: Spatie laravel-activitylog
- **HTTP Client**: Guzzle
- **Testing**: PHPUnit 10 + Mockery + Faker
- **Linting**: Laravel Pint (PSR-12)
- **Containerization**: Docker (Dockerfile.dev + Dockerfile.staging)
- **Reverse Proxy**: Nginx
- **Spec-Driven Development**: spec-kit v0.13.0 + Cline

## Development Workflow

### Quality Gates
1. **SDD Cycle**: `/speckit-specify` → `/speckit-plan` → `/speckit-tasks` → `/speckit-implement` with review gates between phases.
2. **Constitution Check**: MUST pass before Phase 0 research. Re-check after Phase 1 design. Verified against `.specify/memory/constitution.md`.
3. **Pre-merge**: Pint lint clean + full test suite green + coverage threshold met (80% new code, no regression).
4. **PR Review** verifies: layer boundaries respected, no N+1 introduced, no `Request` in services, factories/tests added for new models/endpoints.
5. **Feature Branch Naming**: `[###-feature-name]`; every spec lives in `specs/[###-feature]/`.

## Governance

- This constitution supersedes all ad-hoc practices; conflicts resolve in favor of the constitution.
- Amendments require: documented rationale, semver bump (MAJOR for principle removal/redefinition, MINOR for new principle/section, PATCH for clarification), migration plan for affected code, and PR approval.
- All PRs/reviews MUST verify constitution compliance; unjustified complexity is rejected.
- Use `.specify/memory/constitution.md` as the runtime guidance file referenced by plan.md Constitution Check.

**Version**: 1.0.0 | **Ratified**: 2026-07-20 | **Last Amended**: 2026-07-20