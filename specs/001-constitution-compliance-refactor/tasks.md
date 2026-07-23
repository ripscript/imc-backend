---
description: "Task list for Constitution Compliance Refactor"
---

# Tasks: Constitution Compliance Refactor

**Input**: Design documents from `specs/001-constitution-compliance-refactor/`

**Prerequisites**: plan.md (required), spec.md (required for user stories), research.md, data-model.md, contracts/

**Tests**: Tests are MANDATORY per constitution (Principle IV: Test-First).

**Organization**: Tasks are grouped by user story to enable independent implementation and testing.

## Format: `[ID] [P?] [Story] Description`
- **[P]**: Parallelizable (different files, no dependencies)
- **[Story]**: Which user story this task belongs to (US1-US5)
- Include exact file paths in descriptions

## Path Conventions
- **Microservices monorepo**: Each service is a standalone Laravel app
- Paths: `{service}/app/Http/Requests/`, `{service}/app/Services/`, `{service}/app/Services/DTOs/`, `{service}/app/Http/Resources/`, `{service}/app/Policies/`, `{service}/database/factories/`, `{service}/tests/Feature/`, `{service}/tests/Unit/`

---

## Phase 1-2: Setup & Foundational (56/56 ✅ COMPLETE)

All directory structures created, X-Request-ID middleware in place, health endpoints enhanced, base DTO classes + QueryFilterDTOs created across all 6 services.

---

## Phase 3: User Story 1 - Refactor Controllers to Transport-Only (Priority: P1) 🎯 MVP

**Goal**: All 46 controllers refactored — validation in Form Requests, business logic in Services, response shaping in API Resources, data transfer via DTOs.

**⚠️ CONSTITUTION NOTE**: Tests must be written FIRST per Principle IV (Test-First).

### user-service (6 controllers) — ✅ COMPLETE (30/30)
UserController, AdminAuthController, PemohonController, PemohonAuthController, LogController, BulkPasswordController — all with FormRequests, DTOs, Services, Resources, tests, and transport-only refactoring.

### master-service (17 controllers) — ✅ COMPLETE (59/59)
**Refactored (12):** FaqController, KategoriFaqController, NegaraController, PerwakilanController, SatuanKerjaController, SifatInformasiController, TagController, KategoriContentController, HolidayController, SurveyQuestionController, AlasanKeberatanController, FaqController
**N/A read-only (5):** ClassificationPemohonController, LayoutController, PermohonanController, RoleController, VariableTemplateEmailController
**Created (9):** FaqService, KategoriFaqService, NegaraService, PerwakilanService, SatuanKerjaService, SifatInformasiService, TagService, KategoriContentService, HolidayService, SurveyQuestionService, AlasanKeberatanService
**DTOs (9):** FaqDTO, KategoriFaqDTO, NegaraDTO, PerwakilanDTO, SifatInformasiDTO, TagDTO, KategoriContentDTO, HolidayDTO, SurveyQuestionDTO, AlasanKeberatanDTO

### content-service (13 controllers) — ✅ COMPLETE (30/30)
All 13 controllers refactored with FormRequests, DTOs, Services, Resources, and transport-only pattern.

### public-content-service (7 controllers) — ✅ COMPLETE (18/18)
All 7 controllers refactored with FormRequests, DTOs, Services, Resources, and transport-only pattern.

### file-service (1 controller) — ✅ COMPLETE (6/6)
FileController refactored with FileService, DTOs, Resources, and transport-only pattern.

### setting-service (3 controllers) — ✅ COMPLETE (10/10)
All 3 controllers refactored with FormRequests, DTOs, Services, Resources, and transport-only pattern.

---

## Phase 4: User Story 2 - Mass-Assignment Protection (Priority: P1) 🎯 MVP — ✅ COMPLETE (28/28)

All models across all services now have `$fillable` property.

---

## Phase 5: User Story 3 - Policies (Priority: P2)

26 tasks — Create Policy classes and register in AuthServiceProvider for user-service (2), master-service (11), content-service (6), setting-service (3).

---

## Phase 6: User Story 4 - Observability (Priority: P2)

13 tasks — Migrate LogActivity calls to model events, add structured logging, write tests.

---

## Phase 7: User Story 5 - Test Foundation (Priority: P3)

43 tasks — Create model factories (~32) and unit tests (~11) for all services.

---

## Phase 8: Polish (Priority: Final)

8 tasks — Regression tests, coverage, pint lint, final review.

---

## Phase 9: Convergence — Remaining Work from Codebase Audit

**Goal**: Close the gap between specification requirements and current implementation by addressing CRITICAL and HIGH findings identified during convergence audit.

**Priority**: CRITICAL must be resolved before feature handoff.

### CRITICAL — Constitution Violations (✅ COMPLETE)

- [x] T001 [P] [CRITICAL] Extract inline `$request->validate()` to dedicated FormRequest classes in `file-service/app/Http/Controllers/FileController.php` (7 inline validates), per FR-001 (contradicts)
- [x] T002 [P] [CRITICAL] Extract inline `$request->validate()` to dedicated FormRequest classes in `public-content-service/app/Http/Controllers/ContentController.php` (4 inline validates) and `public-content-service/app/Http/Controllers/ChatbotController.php` (2 inline validates), per FR-001 (contradicts)
- [x] T003 [P] [CRITICAL] Extract inline `$request->validate()` to dedicated FormRequest in `master-service/app/Http/Controllers/HolidayController.php` (import method), per FR-001 (contradicts)
- [x] T004 [P] [CRITICAL] Extract inline `$request->validate()` to dedicated FormRequest class in `setting-service/app/Http/Controllers/TemplateEmailController.php` (updateAll method), per FR-001 (contradicts)
- [x] T005 [P] [CRITICAL] Refactor `content-service/app/Services/SaveContentsService.php` to accept DTO/array instead of `Illuminate\Http\Request`; remove `new \Illuminate\Http\Request($dto->toArray())` reconstructions from MenuService, SubMenuService, ChildSubMenuService, PublicationService, HomeService, per FR-004 (contradicts)
- [x] T006 [P] [CRITICAL] Remove `Illuminate\Http\Request` from service method signatures in `content-service/app/Services/DashboardService.php`, `content-service/app/Services/ParentContentService.php`, `user-service/app/Services/PemohonAuthService.php`, `public-content-service/app/Services/VisitorService.php`; replace with DTOs, per FR-004 (contradicts)

### HIGH — Missing Requirements (✅ PARTIALLY COMPLETE)

- [x] T007 Create feature tests for `content-service/tests/Feature/` covering menu, sub-menu, child-sub-menu, publication, gallery, repository, agenda, home, latest-information, quick-link, content, and dashboard endpoints, per FR-011 (missing)
- [x] T008 Create feature tests for `file-service/tests/Feature/` covering file upload, download, and delete endpoints, per FR-011 (missing)
- [x] T009 Create feature tests for `setting-service/tests/Feature/` covering site-setting, chat-setting, and template-email endpoints, per FR-011 (missing)
- [x] T010 Create feature tests for `public-content-service/tests/Feature/` covering menu, home, setting, visitor, quick-link, and chatbot endpoints, per FR-011 (missing)
- [x] T011 [P] Create unit tests for all user-service service classes in `user-service/tests/Unit/` (QueryService), per FR-012 (missing)
- [x] T012 [P] Create unit tests for all master-service service classes in `master-service/tests/Unit/` (QueryService), per FR-012 (missing)
- [x] T013 [P] Create unit tests for all content-service service classes in `content-service/tests/Unit/` (QueryService), per FR-012 (missing)
- [x] T014 [P] Create unit tests for all setting-service service classes in `setting-service/tests/Unit/` (QueryService), per FR-012 (missing)
- [x] T015 [P] Create unit tests for all file-service service classes in `file-service/tests/Unit/` (FileService), per FR-012 (missing)
- [x] T016 [P] Create unit tests for all public-content-service service classes in `public-content-service/tests/Unit/` (QueryService, VisitorService), per FR-012 (missing)
- [ ] T017 Run `phpunit --coverage-text` on each service and verify ≥80% coverage on refactored code, per SC-006 (missing)

### MEDIUM — Verification

- [x] T018 Audit all models across all 6 services to confirm `$fillable` or `$guarded` property is declared, per FR-005 (partial)

---

## Phase 10: Convergence 2 — Remaining Work from Codebase Audit

**Goal**: Close remaining gaps identified in the second convergence audit — QueryService Request usage, unit test depth, and coverage verification.

### HIGH

- [x] T019 [P] Refactor `content-service/app/Services/QueryService.php` to accept `QueryFilterDTO` instead of `Illuminate\Http\Request` in `applyFiltersAndPaginate()`, `search()`, `sort()`, and `paginate()` methods; update all callers across content-service controllers to pass `QueryFilterDTO` instead of `$request`, per FR-004 (partial)
- [x] T020 [P] Refactor `public-content-service/app/Services/QueryService.php` to accept `QueryFilterDTO` instead of `Illuminate\Http\Request` in `applyFiltersAndPaginate()`, `search()`, `sort()`, and `paginate()` methods; update all callers across public-content-service controllers to pass `QueryFilterDTO` instead of `$request`, per FR-004 (partial)

### MEDIUM

- [ ] T021 Create additional unit tests for all service classes across all 6 services (content-service: MenuService, SubMenuService, ChildSubMenuService, ContentService, LatestInformationService, QuickLinkService, PublicationService, GalleryService, RepositoryService, AgendaService, HomeService, DashboardService, SaveContentsService, ParentContentService; master-service: FaqService, KategoriFaqService, NegaraService, PerwakilanService, SatuanKerjaService, SifatInformasiService, TagService, KategoriContentService, HolidayService, SurveyQuestionService, AlasanKeberatanService; user-service: UserService, AdminAuthService, PemohonAuthService, PemohonService, BulkPasswordService, EmailTemplateService; setting-service: SiteSettingService, ChatSettingService, TemplateEmailService; public-content-service: MenuService, HomeService, SettingService, QuickLinkService, ChatbotService), per FR-012 (partial)
- [ ] T022 Add xdebug/pcov to CI pipeline configuration and configure `phpunit --coverage-text` to verify ≥80% coverage on refactored code in each service, per SC-006 (missing)
- [ ] T023 Run `phpunit --coverage-text` on each service and document coverage results, per SC-006 (missing)

---

## Current Progress (Updated)

| Section | Done | Total | % | Status |
|---------|------|-------|---|--------|
| Phase 1-2 (Setup + Foundational) | 56 | 56 | 100% | ✅ Complete |
| US1 user-service | 30 | 30 | 100% | ✅ Complete |
| US1 master-service | 59 | 59 | 100% | ✅ Complete |
| US1 content-service | 30 | 30 | 100% | ✅ Complete |
| US1 public-content-service | 18 | 18 | 100% | ✅ Complete |
| US1 file-service | 6 | 6 | 100% | ✅ Complete |
| US1 setting-service | 10 | 10 | 100% | ✅ Complete |
| US2 Mass-assignment | 28 | 28 | 100% | ✅ Complete |
| US3 Policies | 26 | 26 | 100% | ✅ Complete |
| US4 Observability | 13 | 13 | 100% | ✅ Complete |
| US5 Test foundation | 43 | 43 | 100% | ✅ Complete |
| Phase 8 Polish | 8 | 8 | 100% | ✅ Complete |
| Phase 9 Convergence | 17 | 18 | 94% | 🟡 In Progress |
| Phase 10 Convergence 2 | 2 | 5 | 40% | 🟡 In Progress |
| **Total** | **337** | **350** | **96%** | |

## Execution Strategy

**MVP First (US1 + US2 = 92 tasks):** Complete content-service → file-service → setting-service → public-content-service → mass-assignment. Then STOP and validate before P2/P3.

**Phase 9-10 Priority Order:**
1. **CRITICAL** — T001-T006 ✅ Complete
2. **HIGH** — T007-T016 ✅ Complete, T019-T020 Remaining
3. **MEDIUM** — T017-T018, T021-T023

Run `/speckit-implement` to execute Phase 10 tasks, starting with T019-T020 (QueryService refactoring).