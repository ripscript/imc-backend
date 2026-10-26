# Master-Service Constitution Compliance Refactor Plan

> Service: **master-service**
> Pilot reference: `user-service` (completed 2026-07-23)
> Constitution: `.specify/memory/constitution.md` v1.0.0
> Checklist: `specs/001-constitution-compliance-refactor/checklist.md`
> Estimated effort: **15-20 hours**

---

## Current State Summary

| Category | Count | Status |
|----------|-------|--------|
| Controllers | 16 (+ base) | 11 use service layer, 5 use raw DB::table() |
| Services | 12 | 10 clean, 2 return error responses (FaqService, PerwakilanService) |
| Models | 17 | 5 use $guarded, 6 use $fillable (3 wrong), 6 missing both |
| DTOs | 13 | QueryFilterDTO missing `is_active`/`without_pagination` |
| Resources | 10 | 9/10 are pass-through `parent::toArray()` |
| FormRequests | 9 on disk | 14 missing, 4 broken (`authorize(): false`) |
| Policies | 11 | Registered but never enforced in controllers |
| Factories | 12 | MsFaqFactory has wrong field name |
| Domain Exceptions | 0 | None exist |
| Unit Tests | 0 | `tests/Unit/Services/` empty |
| Feature Tests | 13 files | Exist but may be stale |

---

## Phase 1: Scaffold Missing Files

No dependencies. All steps can run in parallel.

### Step 1a — Create 14 missing FormRequest files

| File | Fields | Rules |
|------|--------|-------|
| `CreateAlasanKeberatanRequest` | name, is_active | name: required\|string\|max:255, is_active: required\|boolean |
| `UpdateAlasanKeberatanRequest` | name, is_active | same + UniqueSoftDelete for name |
| `CreateHolidayRequest` | name, date | name: required\|string\|max:255, date: required\|date |
| `UpdateHolidayRequest` | name, date | same |
| `CreateKategoriContentRequest` | title, is_active | title: required\|string\|max:255, is_active: required\|boolean |
| `UpdateKategoriContentRequest` | title, is_active | same |
| `CreatePerwakilanRequest` | name, type, negara_id | name: required\|string, type: required\|string, negara_id: required\|array |
| `UpdatePerwakilanRequest` | name, type, negara_id | same |
| `CreateSatuanKerjaRequest` | name, sub | name: required\|string, sub: nullable\|array |
| `UpdateSatuanKerjaRequest` | name, sub | same |
| `CreateSurveyQuestionRequest` | question, type, is_active, order | question: required\|string, type: required\|string, is_active: required\|boolean, order: nullable\|integer |
| `UpdateSurveyQuestionRequest` | question, type, is_active, order | same |
| `CreateTagRequest` | title, is_active | title: required\|string\|max:255, is_active: required\|boolean |
| `UpdateTagRequest` | title, is_active | same |

All must have `authorize(): true`. Follow pattern from `CreateFaqRequest`.

### Step 1b — Fix 4 broken FormRequest files

| File | Current | Fix |
|------|---------|-----|
| `CreateNegaraRequest` | `authorize(): false`, empty rules | `authorize(): true`, rules: name required\|string |
| `UpdateNegaraRequest` | `authorize(): false`, empty rules | `authorize(): true`, rules: name required\|string |
| `CreateSifatInformasiRequest` | `authorize(): false`, empty rules | `authorize(): true`, rules: name required\|string, icon_path nullable\|string, description nullable\|string, link nullable\|string |
| `UpdateSifatInformasiRequest` | `authorize(): false`, empty rules | `authorize(): true`, same rules |

### Step 1c — Create domain exceptions

Create `app/Exceptions/Domain/` directory with:
- `EntityNotFoundException.php` — code 404
- `InvalidRoleException.php` — code 422
- `ValidationException.php` — code 422, has `$errors` property
- `AuthenticationException.php` — code 401

Pattern from user-service.

### Step 1d — Fix QueryFilterDTO

Add missing properties to `app/Services/DTOs/QueryFilterDTO.php`:
```php
public ?bool $is_active = null,
public bool $without_pagination = false,
```

Update `fromRequest()` to accept these from request input.

---

## Phase 2: Fix Models + Factories

### Step 2a — Fix $fillable on models

| Model | Current | Correct $fillable |
|-------|---------|-------------------|
| `MsFaq` | `['name', 'is_active', 'created_by', 'updated_by']` | `['title', 'description', 'is_active', 'kategori_faq_id', 'created_by', 'updated_by']` |
| `MsKategoriContent` | `['name', 'is_active', 'created_by', 'updated_by']` | `['title', 'is_active', 'created_by', 'updated_by']` |
| `MsTag` | `['name', 'is_active', 'created_by', 'updated_by']` | `['title', 'is_active', 'created_by', 'updated_by']` |

### Step 2b — Fix MsFaqFactory

Change `name` to `title`, add `description` and `kategori_faq_id` fields.

### Step 2c — Add $fillable to models missing both $fillable and $guarded

| Model | Action |
|-------|--------|
| `MsSurveyQuestion` | Add `$fillable = ['question', 'type', 'is_active', 'order', 'created_by', 'updated_by']` |
| `MsSubSatuanKerja` | Already has `$guarded = ['id']` — OK |
| `MsPerwakilanNegara` | Already has `$guarded = ['id']` — OK |
| `MsVariableTemplateEmail` | Already has `$fillable` — OK |
| `MsRole` | Already has `$fillable` — OK |
| `User` | Already has `$fillable` — OK |
| `Pemohon` | Already has `$fillable` — OK |

---

## Phase 3: Refactor Services to Throw Exceptions

### Step 3a — FaqService

Replace `ApiResponse::error('Kategori FAQ tidak valid', '', 422)` with `throw new ValidationException('Kategori FAQ tidak valid')` in `create()` and `update()`.

Remove `use App\Helpers\ApiResponse;` import.

### Step 3b — PerwakilanService

Replace `return ['error' => 'Terdapat negara yang tidak valid', 'code' => 422]` with `throw new ValidationException('Terdapat negara yang tidak valid')` in `create()` and `update()`.

### Step 3c — HolidayService

Remove `LogActivity::createCustom()` call from `importBulk()`. The `MsHoliday` model already has `HasActivityLog` trait — the insert will be logged automatically by the trait's `created` event.

Remove `use App\Helpers\LogActivity;` import.

---

## Phase 4: Wire Handler + Refactor Controllers

### Step 4a — Map domain exceptions in Handler.php

Add catches for domain exceptions BEFORE existing catches in `render()` method:

```php
if ($e instanceof \App\Exceptions\Domain\ValidationException) {
    return ApiResponse::error($e->getMessage(), $e->getErrors(), 422);
}
if ($e instanceof \App\Exceptions\Domain\EntityNotFoundException) {
    return ApiResponse::error($e->getMessage(), null, 404);
}
if ($e instanceof \App\Exceptions\Domain\InvalidRoleException) {
    return ApiResponse::error($e->getMessage(), null, 422);
}
if ($e instanceof \App\Exceptions\Domain\AuthenticationException) {
    return ApiResponse::error($e->getMessage(), null, 401);
}
```

### Step 4b — Remove instanceof JsonResponse from FaqController

Delete the `if ($result instanceof \Illuminate\Http\JsonResponse) { return $result; }` blocks in `create()` and `update()` methods. After Phase 3a, service never returns JsonResponse.

### Step 4c — Remove redundant try/catch from all 16 controllers

Every controller method wraps logic in `try { ... } catch (\Throwable $th) { throw $th; }` — this is dead code. Remove the try/catch wrapper from every method in every controller.

### Step 4d — Wire $this->authorize() calls in controllers

For each controller with CRUD methods, add authorization:

| Method | Authorization |
|--------|--------------|
| `index()` | `$this->authorize('viewAny', Model::class)` |
| `create()` | `$this->authorize('create', Model::class)` |
| `detail()` | `$this->authorize('view', $result)` after service call |
| `update()` | `$this->authorize('update', $result)` after service call |
| `destroy()` | `$this->authorize('delete', $model)` before service call |

Controllers to update (11 with service layer):
- AlasanKeberatanController → MsAlasanKeberatan
- FaqController → MsFaq
- HolidayController → MsHoliday
- KategoriContentController → MsKategoriContent
- KategoriFaqController → MsKategoriFaq
- NegaraController → MsNegara
- PerwakilanController → MsPerwakilan
- SatuanKerjaController → MsSatuanKerja
- SifatInformasiController → MsSifatInformasi
- SurveyQuestionController → MsSurveyQuestion
- TagController → MsTag

---

## Phase 5: Fix Middleware

### Step 5a — JwtErrorHandlerMiddleware

Replace `response()->json()` on line 39 with `ApiResponse::error()`:

```php
// BEFORE:
return response()->json([
    'status' => false,
    'message' => 'Sesi telah berakhir. Silakan login kembali.',
], 401);

// AFTER:
return ApiResponse::error('Sesi telah berakhir. Silakan login kembali.', null, 401);
```

Also fix `ApiResponse::error()` calls that pass status code as second argument instead of third. Current calls like `ApiResponse::error('Token has expired', 401)` should be `ApiResponse::error('Token has expired', null, 401)`.

---

## Phase 6: Full Refactor for 5 Raw-DB Controllers

These 5 controllers bypass the architecture entirely. Full refactor: create models, services, DTOs, resources, FormRequests for each.

### Step 6a — ClassificationPemohonController

**Current**: `DB::table('ms_classification_pemohon')->get()`

**Create**:
- Model: `MsClassificationPemohon` — `$table = 'ms_classification_pemohon'`, `$fillable = ['name', 'description', 'is_active']`
- Service: `ClassificationPemohonService` — `index()` returns all, no pagination needed (small lookup table)
- Resource: `ClassificationPemohonResource` — explicit field mapping
- Policy: `MsClassificationPemohonPolicy` — same pattern as other policies
- Register in AuthServiceProvider

**Controller refactor**: Inject service, delegate to `index()`, wrap in ApiResponse.

### Step 6b — LayoutController

**Current**: `DB::table('ms_layout')->select('id', 'title')` with conditional `where` for `is_custom` and `type`

**Create**:
- Model: `MsLayout` — `$table = 'ms_layout'`, `$fillable = ['title', 'is_custom', 'type']`
- Service: `LayoutService` — `index(Request $request)` handles conditional filtering
- Resource: `MsLayoutResource` — id, title
- No Policy needed (read-only endpoint, no CRUD)
- No FormRequest needed (read-only, uses query params)

**Controller refactor**: Inject service, delegate to `index()`, wrap in ApiResponse.

**Note**: This is a read-only endpoint — no create/update/destroy. Keep it simple.

### Step 6c — PermohonanController

**Current**: 3 methods querying 3 different tables directly

**Create**:
- Models:
  - `MsPermohonanAksesData` — `$table = 'ms_permohonan_akses_data'`, `$fillable = ['name']`
  - `MsPermohonanFormatData` — `$table = 'ms_permohonan_format_data'`, `$fillable = ['name']`
  - `MsPermohonanSalinanInformasi` — `$table = 'ms_permohonan_salinan_informasi'`, `$fillable = ['name']`
- Service: `PermohonanService` — `aksesData()`, `formatData()`, `salinanInformasi()` methods
- Resource: `PermohonanResource` — shared resource for all 3 (same shape: id, name)
- No Policy needed (read-only endpoints)
- No FormRequest needed (read-only)

**Controller refactor**: Inject service, delegate to 3 methods, wrap in ApiResponse.

**Note**: These are read-only endpoints shared between admin and pemohon middleware groups.

### Step 6d — RoleController

**Current**: `DB::table('ms_roles')->get()`

**Create**:
- Model already exists: `MsRole` — already has `$fillable = ['name', 'code']`
- Service: `RoleService` — `index()` returns all roles
- Resource: `MsRoleResource` — id, name, code
- No Policy needed (read-only endpoint)
- No FormRequest needed (read-only)

**Controller refactor**: Inject service, delegate to `index()`, wrap in ApiResponse.

### Step 6e — VariableTemplateEmailController

**Current**: `DB::table('ms_variable_template_email')` with conditional `where('template_email', ...)`

**Create**:
- Model already exists: `MsVariableTemplateEmail` — already has `$fillable`
- Service: `VariableTemplateEmailService` — `index(Request $request)` handles conditional filtering
- Resource: `VariableTemplateEmailResource` — explicit field mapping
- No Policy needed (read-only endpoint)
- No FormRequest needed (read-only, uses query param)

**Controller refactor**: Inject service, delegate to `index()`, wrap in ApiResponse.

---

## Phase 7: Enhance API Resources

Replace `parent::toArray($request)` with explicit field mapping on all 9 pass-through resources.

| Resource | Fields to expose |
|----------|-----------------|
| `AlasanKeberatanResource` | id, name, is_active, created_by, updated_by, created_at, updated_at |
| `FaqResource` | id, title, description, kategori_faq_id, is_active, created_by, updated_by, created_at, updated_at |
| `HolidayResource` | id, name, date, created_by, updated_by, created_at, updated_at |
| `KategoriContentResource` | id, title, is_active, created_by, updated_by, created_at, updated_at |
| `NegaraResource` | id, name, created_by, updated_by, created_at, updated_at |
| `SatuanKerjaResource` | id, name, created_by, updated_by, created_at, updated_at, subSatuanKerja (whenLoaded) |
| `SifatInformasiResource` | id, name, icon_path, description, link, created_by, updated_by, created_at, updated_at |
| `SurveyQuestionResource` | id, question, type, is_active, order, created_by, updated_by, created_at, updated_at |
| `TagResource` | id, title, is_active, created_by, updated_by, created_at, updated_at |

`KategoriFaqResource` already has explicit mapping — no change needed.

---

## Phase 8: Create Factories for Missing Models

Models missing factories:
- `MsClassificationPemohonFactory` — name, description, is_active
- `MsLayoutFactory` — title, is_custom, type
- `MsPermohonanAksesDataFactory` — name
- `MsPermohonanFormatDataFactory` — name
- `MsPermohonanSalinanInformasiFactory` — name
- `MsSubSatuanKerjaFactory` — name, satuan_kerja_id
- `MsPerwakilanNegaraFactory` — perwakilan_id, negara_id
- `MsVariableTemplateEmailFactory` — name, is_active (may already exist, verify)
- `PemohonFactory` — name, email, password, is_active

---

## Phase 9: Feature Tests

Verify existing 13 feature test files still pass after refactor. Update any that break due to:
- Removed `instanceof JsonResponse` checks
- Changed error response shapes
- New authorization requirements

Add feature tests for endpoints that currently lack coverage:
- ClassificationPemohon (GET /classification-pemohon)
- Layout (GET /layout)
- Permohonan (GET /permohonan/akses-data, format-data, salinan-informasi)
- Role (GET /role)
- VariableTemplateEmail (GET /variable-template-email)

---

## Execution Order

```
Phase 1 (scaffold)  ──┐
Phase 2 (models)    ──┼── can run in parallel
Phase 5 (middleware) ──┘
         │
         ▼
Phase 3 (services throw exceptions)
         │
         ▼
Phase 4 (handler + controllers)
         │
         ▼
Phase 6 (raw-DB controllers)
         │
         ▼
Phase 7 (resources)
         │
         ▼
Phase 8 (factories)
         │
         ▼
Phase 9 (feature tests)
```

---

## Per-Phase Tracking

| Phase | Description | Files Changed | Status |
|-------|-------------|---------------|--------|
| 1a | Create 14 FormRequests | 14 new files | ✅ |
| 1b | Fix 4 broken FormRequests | 4 files | ✅ |
| 1c | Create domain exceptions | 4 new files + dir | ✅ |
| 1d | Fix QueryFilterDTO | 1 file | ✅ |
| 2a | Fix model $fillable | 3 files | ✅ |
| 2b | Fix MsFaqFactory | 1 file | ✅ |
| 2c | Add $fillable to models | 1 file (MsSurveyQuestion) | ✅ |
| 3a | FaqService throw exceptions | 1 file | ✅ |
| 3b | PerwakilanService throw exceptions | 1 file | ✅ |
| 3c | HolidayService remove LogActivity | 1 file | ✅ |
| 4a | Handler domain exceptions | 1 file | ✅ |
| 4b | FaqController remove JsonResponse | 1 file | ✅ |
| 4c | Remove try/catch from controllers | 16 files | ✅ |
| 4d | Wire authorize() in controllers | 11 files | ✅ |
| 5a | JwtErrorHandlerMiddleware fix | 1 file | ✅ |
| 6a | ClassificationPemohon full refactor | 4 new + 1 edit | ✅ |
| 6b | Layout full refactor | 3 new + 1 edit | ✅ |
| 6c | Permohonan full refactor | 4 new + 1 edit | ✅ |
| 6d | Role full refactor | 2 new + 1 edit | ✅ |
| 6e | VariableTemplateEmail full refactor | 3 new + 1 edit | ✅ |
| 7 | Enhance 9 Resources | 9 files | ✅ |
| 8 | Create missing factories | ~9 new files | ✅ |
| 9 | Feature tests | ~5 new + 13 verify | ✅ |

---

**Last updated**: 2026-07-24 | **Version**: 1.0.1 | **Review fix**: Removed PerwakilanController dead error-array guards (lines 48-50, 62-64)
