# Constitution Compliance Implementation Checklist

> Pilot: **user-service** (COMPLETED 2026-07-23)
> Baseline: `.specify/memory/constitution.md` v1.0.0
> Spec: `specs/001-constitution-compliance-refactor/spec.md`

---

## Step-by-Step Implementation Guide

Each step maps to one or more constitution principles and functional requirements. Execute phases in order; within a phase, steps can run in parallel.

---

### Phase 1: Scaffold Missing Files

No dependencies. All steps in this phase can run in parallel.

#### Step 1a — Create API Resource files

| Principle | FR | Checklist |
|-----------|-----|-----------|
| II. Layered Clean Architecture | FR-003 | ✅ / ⬜ |

- For every controller that returns a model, ensure an `app/Http/Resources/{Model}Resource.php` exists
- Extend `JsonResource`; define explicit `toArray()` with all visible fields + `whenLoaded()` for relations
- Remove `parent::toArray($request)` pass-throughs — explicit fields prevent accidental PII leaks
- Hide: `password`, `remember_token`, `token`, `otp`

**Verification:**
```bash
grep -rn "new.*Resource" app/Http/Controllers/ | sort -u
# Every `new XResource(...)` must have a corresponding app/Http/Resources/XResource.php
```

---

#### Step 1b — Create Domain Exception classes

| Principle | FR | Checklist |
|-----------|-----|-----------|
| II. Layered Clean Architecture | FR-014 | ✅ / ⬜ |

- Create `app/Exceptions/Domain/` directory
- Minimum set of exceptions (create as needed per service):
  - `EntityNotFoundException` — 404, entity not found
  - `InvalidRoleException` — 422, role/permission invalid
  - `AccountBlockedException` — 403, account blocked
  - `AuthenticationException` — 401, bad credentials
  - `OtpException` — 400, OTP invalid/expired
  - `RateLimitException` — 429, too many attempts
  - `ValidationException` — 422, domain validation failure
- Each exception extends `\Exception`, takes optional message + code, implements `getErrors()` if needed

**Verification:**
```bash
ls app/Exceptions/Domain/
# At minimum: EntityNotFoundException must exist
```

---

#### Step 1c — Create DTO classes

| Principle | FR | Checklist |
|-----------|-----|-----------|
| II. Layered Clean Architecture | FR-004 | ✅ / ⬜ |

- For every service method that accepts >1 parameter, create a DTO in `app/Services/DTOs/`
- Extend `BaseDTO` (abstract class, takes array, maps to properties via `property_exists`)
- Property names MUST match validated request field names
- DTOs replace `$request->input()` in controller → service calls

**Pattern:**
```php
class LoginDTO extends BaseDTO
{
    public string $email_or_username;
    public string $password;
}
```

**Verification:**
```bash
grep -rn "request->input" app/Http/Controllers/
# Should return ZERO hits (except reset-password token from query param)
```

---

### Phase 2: Refactor Services to Throw Exceptions

Depends on: Phase 1b (domain exceptions)

#### Step 2 — Replace `ApiResponse::error()` with `throw`

| Principle | FR | Checklist |
|-----------|-----|-----------|
| II. Layered Clean Architecture | FR-002 | ✅ / ⬜ |

- Remove `use App\Helpers\ApiResponse;` from all service files
- Replace every `return ApiResponse::error(...)` with `throw new DomainException(...)`
- Replace every `return ApiResponse::success(...)` in services — success data should be returned raw (array, model, bool)
- Services MUST NOT return JSON responses; controllers own response formatting
- After refactoring, every service method either: returns data, or throws a domain exception

**Before (violation):**
```php
if (! $role) {
    DB::rollBack();
    return ApiResponse::error('Role tidak valid', '', 422);
}
```

**After (compliant):**
```php
if (! $role) {
    throw new InvalidRoleException('Role tidak valid');
}
```

**Verification:**
```bash
grep -rn "ApiResponse::" app/Services/
# Should return ZERO hits (or only return[] success arrays for obfuscation cases like sendResetLinkEmail)
```

---

### Phase 3: Wire Exception Handler + Refactor Controllers

Depends on: Phase 1c (DTOs), Phase 2 (services)

#### Step 3a — Map domain exceptions in Handler.php

| Principle | FR | Checklist |
|-----------|-----|-----------|
| II. Layered Clean Architecture, VII. Security & Consistency | FR-013 | ✅ / ⬜ |

- In `app/Exceptions/Handler.php` `render()` method, add catches for domain exceptions BEFORE the generic Laravel handler
- Each domain exception maps to a consistent `ApiResponse::error()` response
- Domain exception checks MUST come before `ValidationException` and `ModelNotFoundException` checks
- Status codes must match exception codes

**Pattern:**
```php
public function render($request, Throwable $e)
{
    if ($e instanceof DomainValidationException) {
        return ApiResponse::error($e->getMessage(), $e->getErrors(), $e->getCode());
    }
    if ($e instanceof EntityNotFoundException) {
        return ApiResponse::error($e->getMessage(), null, $e->getCode());
    }
    // ... other domain exceptions ...

    if ($e instanceof ValidationException) {
        // existing Laravel ValidationException handling
    }
    // ... rest of existing handler ...
}
```

---

#### Step 3b — Remove `instanceof JsonResponse` checks from controllers

| Principle | FR | Checklist |
|-----------|-----|-----------|
| II. Layered Clean Architecture | FR-002 | ✅ / ⬜ |

- Delete every `if ($result instanceof \Illuminate\Http\JsonResponse) { return $result; }` block
- After Phase 2, services never return `JsonResponse` — these guards are dead code

**Verification:**
```bash
grep -rn "instanceof.*JsonResponse" app/Http/Controllers/
# Should return ZERO hits
```

---

#### Step 3c — Inject DTOs in controller methods

| Principle | FR | Checklist |
|-----------|-----|-----------|
| II. Layered Clean Architecture | FR-004 | ✅ / ⬜ |

- Replace all `$request->input('field1')`, `$request->input('field2')` → `new DtoClass($request->validated())`
- Update service method signatures to accept DTOs instead of primitives
- Exception: `resetPassword` token from query string (not in FormRequest) — `$request->input('token')` is acceptable

**Before (violation):**
```php
public function login(LoginRequest $request)
{
    return $this->authService->login(
        $request->input('email_or_username'),
        $request->input('password')
    );
}
```

**After (compliant):**
```php
public function login(LoginRequest $request)
{
    $dto = new LoginDTO($request->validated());
    return $this->authService->login($dto);
}
```

**Verification:**
```bash
grep -rn "request->input" app/Http/Controllers/
# Should return 0–2 hits (token/ip query params only — not validated fields)
```

---

### Phase 4: Wire Policies for Authorization

Independent — no dependency on Phases 1–3

#### Step 4 — Add `$this->authorize()` in controllers

| Principle | FR | Checklist |
|-----------|-----|-----------|
| VII. Security & Consistency | FR-006 | ✅ / ⬜ |

- Ensure Policy classes exist for every model with CRUD endpoints
- Register policies in `AuthServiceProvider`
- Add `$this->authorize()` calls:
  - `index()` → `$this->authorize('viewAny', Model::class)`
  - `create()` → `$this->authorize('create', Model::class)`
  - `detail()` → after service call: `$this->authorize('view', $result)`
  - `update()` → after service call: `$this->authorize('update', $result)`
  - `delete()` → `$this->authorize('delete', $model)` before service call
- Policies are ADDITIVE to `check.role` middleware, not a replacement

**Verification:**
```bash
grep -rn "authorizeResource\|authorize(" app/Http/Controllers/
# Every CRUD controller should have authorize calls
```

---

### Phase 5: Remove Duplicate Activity Logging

Independent — no dependency

#### Step 5 — Audit `LogActivity::createCustom()` calls

| Principle | FR | Checklist |
|-----------|-----|-----------|
| VI. Observability & Auditability | FR-009 | ✅ / ⬜ |

- If model has `HasActivityLog` trait: delete `LogActivity::createCustom()` calls that duplicate trait's `created`/`updated`/`deleted` events
- Keep `LogActivity::createCustom()` only for non-model actions (login, logout, password reset)
- Controllers MUST NOT call `LogActivity` directly; if a custom log is needed, move it to the service

**Verification:**
```bash
grep -rn "LogActivity::createCustom" app/Models/
grep -rn "HasActivityLog" app/Models/
# If model has HasActivityLog, ensure it doesn't also call createCustom for createModel/updateModel/delete events
```

---

### Phase 6: Standardize Middleware

Independent — no dependency

#### Step 6 — Ensure all middleware uses `ApiResponse` envelope

| Principle | FR | Checklist |
|-----------|-----|-----------|
| VII. Security & Consistency | FR-013 | ✅ / ⬜ |

- Replace any `response()->json([...])` in middleware with `ApiResponse::error(...)` or `ApiResponse::success(...)`
- Common offenders: JWT error handler middleware, CORS middleware, auth middleware
- Response shapes `{status: false}` must become `{success: false}` for consistency

**Verification:**
```bash
grep -rn "response()->json" app/Http/Middleware/
# Should return ZERO hits
```

---

### Phase 7: Enhance API Resources

Depends on: Phase 1a (Resource files must exist)

#### Step 7 — Add explicit field mapping to all Resources

| Principle | FR | Checklist |
|-----------|-----|-----------|
| II. Layered Clean Architecture | FR-003 | ✅ / ⬜ |

- Replace `parent::toArray($request)` with explicit field arrays
- Use `$this->whenLoaded('relation', fn()` for eager-loaded relations
- Only expose fields the client needs (no PII by accident)
- Hide sensitive fields: password, remember_token, token, otp, token_version

**Verification:**
```bash
grep -rn "parent::toArray" app/Http/Resources/
# Should return ZERO hits
```

---

### Phase 8: Fill `$fillable` on All Models

Independent — no dependency

#### Step 8 — Declare mass-assignment protection

| Principle | FR | Checklist |
|-----------|-----|-----------|
| VII. Security & Consistency | FR-005 | ✅ / ⬜ |

- Every Eloquent model MUST declare `$fillable` with the exact set of columns that code assigns
- OR declare `$guarded = ['id', 'created_at', 'updated_at', 'deleted_at']`
- Models that previously relied on `Model::unguard()` or had no protection need explicit lists
- Use `$fillable` (explicit allowlist) over `$guarded` (blocklist) — more secure

**Verification:**
```bash
grep -rn "class.*extends Model" app/Models/ -l | while read f; do
    if ! grep -q 'fillable\|guarded' "$f"; then echo "MISSING: $f"; fi
done
# Should return nothing
```

---

### Phase 9: Create Factories for All Models

Independent — no dependency

#### Step 9 — Add Eloquent factories

| Principle | FR | Checklist |
|-----------|-----|-----------|
| IV. Test-First | FR-010 | ✅ / ⬜ |

- Every model MUST have a factory in `database/factories/`
- Factory must generate valid, realistic model instances
- Use Faker for random data generation
- Foreign key fields should use `ModelName::factory()` or hardcoded valid IDs

**Verification:**
```bash
for model in $(grep -rn "class.*extends Model" app/Models/ | sed 's/.*class \([a-zA-Z]*\).*/\1/'); do
    if [ ! -f "database/factories/${model}Factory.php" ]; then echo "MISSING: ${model}Factory"; fi
done
# Should return nothing
```

---

### Phase 10: Unit Tests for Services

Independent — but easier after Phase 2 (services are simpler to test)

#### Step 10 — Add unit tests for service methods

| Principle | FR | Checklist |
|-----------|-----|-----------|
| IV. Test-First | FR-012 | ✅ / ⬜ |

- One test file per service in `tests/Unit/`
- Mock ALL external dependencies (QueryService, EmailService, DB facade, external APIs)
- Test: happy path, edge cases, each exception path
- Goal: 80% coverage on service classes

**Verification:**
```bash
php artisan test --testsuite=Unit
# Goal: all pass, no skipped, coverage ≥ 80% on services
```

---

### Phase 11: Feature Tests for Endpoints

Independent — but easier with factories (Phase 9)

#### Step 11 — Add feature tests for all public endpoints

| Principle | FR | Checklist |
|-----------|-----|-----------|
| IV. Test-First | FR-011 | ✅ / ⬜ |

- One test file per controller/endpoint group in `tests/Feature/`
- Test: response status, response envelope shape (`success`, `message`, `data`/`errors`)
- Test: pagination shape when applicable (`data`, `meta` with `page`, `limit`, `total`, `total_page`)
- Use factories + `RefreshDatabase` trait
- Authenticate with JWT where required

**Verification:**
```bash
php artisan test --testsuite=Feature
# Goal: all pass, no skipped, one test per public route
```

---

## Per-Service Tracking

| # | Service | P1: Resources | P1b: Exceptions | P1c: DTOs | P2: Svc-Exceptions | P3: Handler | P3b: No-JsonResp | P3c: DTO-in-Ctrl | P4: Policies | P5: No-Dup-Log | P6: MW-ApiResp | P7: Resource-Map | P8: Fillable | P9: Factories | P10: UnitTests | P11: FeatTests |
|---|---------|:---:|:---:|:---:|:---:|:---:|:---:|:---:|:---:|:---:|:---:|:---:|:---:|:---:|:---:|:---:|
| 1 | **user-service** | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ⬜ | ⬜ |
| 2 | **content-service** | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ⬜ | ⬜ |
| 3 | public-content-service | ⬜ | ⬜ | ⬜ | ⬜ | ⬜ | ⬜ | ⬜ | ⬜ | ⬜ | ⬜ | ⬜ | ⬜ | ⬜ | ⬜ | ⬜ |
| 4 | file-service | ⬜ | ⬜ | ⬜ | ⬜ | ⬜ | ⬜ | ⬜ | ⬜ | ⬜ | ⬜ | ⬜ | ⬜ | ⬜ | ⬜ | ⬜ |
| 5 | setting-service | ⬜ | ⬜ | ⬜ | ⬜ | ⬜ | ⬜ | ⬜ | ⬜ | ⬜ | ⬜ | ⬜ | ⬜ | ⬜ | ⬜ | ⬜ |
| 6 | master-service | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ⬜ | ⬜ | ⬜ |
| 7 | migration-service | ⬜ | ⬜ | ⬜ | ⬜ | ⬜ | ⬜ | ⬜ | ⬜ | ⬜ | ⬜ | ⬜ | ⬜ | ⬜ | ⬜ | ⬜ |

**Legend**: ✅ = Done, ⬜ = Pending, N/A = Not applicable

---

## Quick Verification Script

Run this after completing all phases on a service to verify compliance:

```bash
SERVICE_DIR="user-service"  # change per service

echo "=== P2: ApiResponse::error in services ==="
grep -rn "ApiResponse::" "$SERVICE_DIR/app/Services/" || echo "PASS: zero hits"

echo "=== P3b: instanceof JsonResponse in controllers ==="
grep -rn "instanceof.*JsonResponse" "$SERVICE_DIR/app/Http/Controllers/" || echo "PASS: zero hits"

echo "=== P3c: request->input in controllers ==="
grep -rn "\$request->input" "$SERVICE_DIR/app/Http/Controllers/" && echo "WARN: above hits should only be query params (token, ip)" || echo "PASS: zero hits"

echo "=== P6: response()->json in middleware ==="
grep -rn "response()->json" "$SERVICE_DIR/app/Http/Middleware/" || echo "PASS: zero hits"

echo "=== P7: parent::toArray in resources ==="
grep -rn "parent::toArray" "$SERVICE_DIR/app/Http/Resources/" || echo "PASS: zero hits"

echo "=== P8: models without fillable/guarded ==="
grep -rn "class.*extends Model" "$SERVICE_DIR/app/Models/" -l | while read f; do
    if ! grep -q 'fillable\|guarded' "$f"; then echo "FAIL: $f";
    fi
done

echo "=== P9: factories check ==="
for model in $(grep -rn "class.*extends Model" "$SERVICE_DIR/app/Models/" | grep -oP 'class \K[a-zA-Z]+(?=\s+extends)'); do
    if [ ! -f "$SERVICE_DIR/database/factories/${model}Factory.php" ]; then echo "WARN: ${model}Factory missing"; fi
done
```

---

## Principle → Phase Mapping Reference

| Constitution Principle | Phase(s) | FR(s) |
|---|---|---|
| I. Microservice Autonomy | Documented (shared code justified per YAGNI) | FR-014 |
| II. Layered Clean Architecture | P1a–P3c, P7 | FR-001, FR-002, FR-003, FR-004 |
| III. Contract-First | Deferred (OpenAPI specs + contract tests) | — |
| IV. Test-First | P9, P10, P11 | FR-010, FR-011, FR-012 |
| V. Performance & Scalability | Deferred (N+1 audit, search optimization) | — |
| VI. Observability & Auditability | P5 | FR-009 |
| VII. Security & Consistency | P4, P6, P8 | FR-005, FR-006, FR-013 |
| VIII. YAGNI | Documented (deferred items tracked) | FR-014 |

---

**Last updated**: 2026-07-30 | **Version**: 1.1.0 | **Pilot**: user-service | **content-service**
