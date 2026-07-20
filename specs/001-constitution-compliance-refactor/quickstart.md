# Quickstart: Constitution Compliance Refactor

**Phase**: 1 — Design & Contracts
**Date**: 2026-07-20
**Spec**: [spec.md](spec.md)

## Prerequisites

- Docker and docker-compose
- PHP 8.1+ (for local tooling)
- Composer
- PostgreSQL (via Docker)
- Access to the IMC Backend repository

## Setup

```bash
# Start all services
docker-compose up -d

# Run migrations for each service
for service in user-service master-service content-service public-content-service file-service setting-service; do
  docker-compose exec -T $service php artisan migrate
done
```

## Validation Scenarios

### Scenario 1: Regression Test — User List Endpoint

**Purpose**: Verify that the `GET /user-service/api/user/` endpoint returns identical response after refactoring.

**Setup**:
```bash
# Capture baseline response (before refactoring)
curl -s -H "Authorization: Bearer $ADMIN_TOKEN" \
  "http://localhost:1869/user-service/api/user/?page=1&limit=10" \
  > /tmp/baseline-user-list.json
```

**Validation**:
```bash
# After refactoring, capture new response
curl -s -H "Authorization: Bearer $ADMIN_TOKEN" \
  "http://localhost:1869/user-service/api/user/?page=1&limit=10" \
  > /tmp/refactored-user-list.json

# Compare (should be identical)
diff /tmp/baseline-user-list.json /tmp/refactored-user-list.json
# Expected: no output (files are identical)
```

**Expected Outcome**: No differences in status code, envelope shape, pagination format, or field names.

### Scenario 2: Regression Test — FAQ Create Endpoint

**Purpose**: Verify that `POST /master-service/api/faq/` returns identical response after refactoring.

**Setup**:
```bash
# Capture baseline response (before refactoring)
curl -s -X POST -H "Authorization: Bearer $ADMIN_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"title":"Test FAQ","description":"Test description","is_active":true,"kategori_faq_id":1}' \
  "http://localhost:1869/master-service/api/faq/" \
  > /tmp/baseline-faq-create.json
```

**Validation**:
```bash
# After refactoring, capture new response
curl -s -X POST -H "Authorization: Bearer $ADMIN_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"title":"Test FAQ","description":"Test description","is_active":true,"kategori_faq_id":1}' \
  "http://localhost:1869/master-service/api/faq/" \
  > /tmp/refactored-faq-create.json

# Compare
diff /tmp/baseline-faq-create.json /tmp/refactored-faq-create.json
# Expected: no output (files are identical)
```

**Expected Outcome**: Status 201, response envelope `{success: true, message: "...", data: {...}}`.

### Scenario 3: Health Endpoint Enhancement

**Purpose**: Verify that health endpoints now report dependency status.

**Validation**:
```bash
curl -s "http://localhost:1869/user-service/api/health" | jq .
# Expected: {status: "ok", message: "...", time: "...", dependencies: {db: "ok", cache: "ok", queue: "ok"}}
```

**Expected Outcome**: Response includes `dependencies` object with `db`, `cache`, and `queue` status fields.

### Scenario 4: X-Request-ID Propagation

**Purpose**: Verify that correlation ID is propagated through the system.

**Validation**:
```bash
# Send request with X-Request-ID header
curl -s -H "X-Request-ID: test-correlation-123" \
  "http://localhost:1869/user-service/api/health" \
  -D - | grep -i x-request-id
# Expected: x-request-id: test-correlation-123

# Send request without X-Request-ID header
curl -s "http://localhost:1869/user-service/api/health" \
  -D - | grep -i x-request-id
# Expected: x-request-id: <generated-uuid>
```

**Expected Outcome**: Response includes `X-Request-ID` header matching the request header (or a generated UUID if none provided).

### Scenario 5: Mass-Assignment Protection

**Purpose**: Verify that models with missing `$fillable` now reject mass-assignment of unauthorized fields.

**Validation**:
```bash
# Attempt to mass-assign a non-fillable field
curl -s -X POST -H "Authorization: Bearer $ADMIN_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"title":"Test","description":"Test","is_active":true,"kategori_faq_id":1,"non_existent_field":"hack"}' \
  "http://localhost:1869/master-service/api/faq/"
# Expected: 422 validation error (non_existent_field is not in $fillable)
```

**Expected Outcome**: The `non_existent_field` is silently ignored (not stored in DB).

### Scenario 6: Policy Authorization

**Purpose**: Verify that Policies are registered and working.

**Validation**:
```bash
# Check that Policy is registered (via artisan)
docker-compose exec -T user-service php artisan policy:list
# Expected: UserPolicy listed
```

**Expected Outcome**: Policy classes are registered in `AuthServiceProvider`.

### Scenario 7: Test Suite Execution

**Purpose**: Verify that all tests pass and coverage meets 80% threshold.

**Validation**:
```bash
# Run tests for each service
for service in user-service master-service content-service public-content-service file-service setting-service; do
  echo "=== $service ==="
  docker-compose exec -T $service php artisan test --coverage
done
```

**Expected Outcome**: All tests pass. Coverage report shows >= 80% on refactored code.

## Per-Service Validation

| Service | Key Endpoints to Test | Contract File |
|---------|----------------------|---------------|
| user-service | auth/login, user/, user/{id}, pemohon/auth/* | [contracts/user-service.md](contracts/user-service.md) |
| master-service | faq/, negara/, satuan-kerja/, tag/ | [contracts/master-service.md](contracts/master-service.md) |
| content-service | menu/, sub-menu/, publication/, gallery/ | [contracts/content-service.md](contracts/content-service.md) |
| public-content-service | menu, content/, chatbot/ | [contracts/public-content-service.md](contracts/public-content-service.md) |
| file-service | upload, show, download | [contracts/file-service.md](contracts/file-service.md) |
| setting-service | template-email/, site-setting/, chat-setting/ | [contracts/setting-service.md](contracts/setting-service.md) |

## References

- [Data Model](data-model.md) — Entity inventory and $fillable status
- [Contracts](contracts/) — API contract documentation per service
- [Spec](spec.md) — Feature specification
- [Research](research.md) — Design decisions and patterns