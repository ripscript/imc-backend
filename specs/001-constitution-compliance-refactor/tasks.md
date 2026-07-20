---
description: "Task list for Constitution Compliance Refactor"
---

# Tasks: Constitution Compliance Refactor

**Input**: Design documents from `specs/001-constitution-compliance-refactor/`

**Prerequisites**: plan.md (required), spec.md (required for user stories), research.md, data-model.md, contracts/

**Tests**: Tests are MANDATORY per constitution (Principle IV: Test-First). Every new model MUST have a factory; every new endpoint MUST have a feature test; unit tests MUST mock external dependencies. Coverage gate: 80% new code, no regression.

**Organization**: Tasks are grouped by user story to enable independent implementation and testing of each story.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: Which user story this task belongs to (e.g., US1, US2, US3)
- Include exact file paths in descriptions

## Path Conventions

- **Microservices monorepo**: Each service is a standalone Laravel app under its own directory
- Paths: `{service}/app/Http/Requests/`, `{service}/app/Services/`, `{service}/app/Services/DTOs/`, `{service}/app/Http/Resources/`, `{service}/app/Policies/`, `{service}/database/factories/`, `{service}/tests/Feature/`, `{service}/tests/Unit/`

---

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Create directory structures and base classes across all 6 services

- [x] T001 [P] Create `Http/Requests/` directory in user-service/app/Http/
- [ ] T002 [P] Create `Http/Requests/` directory in master-service/app/Http/
- [ ] T003 [P] Create `Http/Requests/` directory in content-service/app/Http/
- [ ] T004 [P] Create `Http/Requests/` directory in public-content-service/app/Http/
- [ ] T005 [P] Create `Http/Requests/` directory in file-service/app/Http/
- [ ] T006 [P] Create `Http/Requests/` directory in setting-service/app/Http/
- [ ] T007 [P] Create `Http/Resources/` directory in user-service/app/Http/
- [ ] T008 [P] Create `Http/Resources/` directory in master-service/app/Http/
- [ ] T009 [P] Create `Http/Resources/` directory in content-service/app/Http/
- [ ] T010 [P] Create `Http/Resources/` directory in public-content-service/app/Http/
- [ ] T011 [P] Create `Http/Resources/` directory in file-service/app/Http/
- [ ] T012 [P] Create `Http/Resources/` directory in setting-service/app/Http/
- [ ] T013 [P] Create `Services/` directory (if not exists) in user-service/app/
- [ ] T014 [P] Create `Services/` directory (if not exists) in master-service/app/
- [ ] T015 [P] Create `Services/` directory (if not exists) in content-service/app/
- [ ] T016 [P] Create `Services/` directory (if not exists) in public-content-service/app/
- [ ] T017 [P] Create `Services/` directory (if not exists) in file-service/app/
- [ ] T018 [P] Create `Services/` directory (if not exists) in setting-service/app/
- [ ] T019 [P] Create `Services/DTOs/` directory in user-service/app/
- [ ] T020 [P] Create `Services/DTOs/` directory in master-service/app/
- [ ] T021 [P] Create `Services/DTOs/` directory in content-service/app/
- [ ] T022 [P] Create `Services/DTOs/` directory in public-content-service/app/
- [ ] T023 [P] Create `Services/DTOs/` directory in file-service/app/
- [ ] T024 [P] Create `Services/DTOs/` directory in setting-service/app/
- [ ] T025 [P] Create `Policies/` directory in user-service/app/
- [ ] T026 [P] Create `Policies/` directory in master-service/app/
- [ ] T027 [P] Create `Policies/` directory in content-service/app/
- [ ] T028 [P] Create `Policies/` directory in setting-service/app/

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Core infrastructure that MUST be complete before ANY user story can be implemented

**⚠️ CRITICAL**: No user story work can begin until this phase is complete

### X-Request-ID Middleware

- [ ] T029 [P] Create X-Request-ID middleware in user-service/app/Http/Middleware/AddRequestIdMiddleware.php
- [ ] T030 [P] Create X-Request-ID middleware in master-service/app/Http/Middleware/AddRequestIdMiddleware.php
- [ ] T031 [P] Create X-Request-ID middleware in content-service/app/Http/Middleware/AddRequestIdMiddleware.php
- [ ] T032 [P] Create X-Request-ID middleware in public-content-service/app/Http/Middleware/AddRequestIdMiddleware.php
- [ ] T033 [P] Create X-Request-ID middleware in file-service/app/Http/Middleware/AddRequestIdMiddleware.php
- [ ] T034 [P] Create X-Request-ID middleware in setting-service/app/Http/Middleware/AddRequestIdMiddleware.php
- [ ] T035 [P] Register AddRequestIdMiddleware in user-service/app/Http/Kernel.php
- [ ] T036 [P] Register AddRequestIdMiddleware in master-service/app/Http/Kernel.php
- [ ] T037 [P] Register AddRequestIdMiddleware in content-service/app/Http/Kernel.php
- [ ] T038 [P] Register AddRequestIdMiddleware in public-content-service/app/Http/Kernel.php
- [ ] T039 [P] Register AddRequestIdMiddleware in file-service/app/Http/Kernel.php
- [ ] T040 [P] Register AddRequestIdMiddleware in setting-service/app/Http/Kernel.php

### Enhanced Health Endpoints

- [ ] T041 [P] Enhance health endpoint in user-service/routes/api.php to report DB/cache/queue status
- [ ] T042 [P] Enhance health endpoint in master-service/routes/api.php to report DB/cache/queue status
- [ ] T043 [P] Enhance health endpoint in content-service/routes/api.php to report DB/cache/queue status
- [ ] T044 [P] Enhance health endpoint in public-content-service/routes/api.php to report DB/cache/queue status
- [ ] T045 [P] Enhance health endpoint in file-service/routes/api.php to report DB/cache/queue status
- [ ] T046 [P] Enhance health endpoint in setting-service/routes/api.php to report DB/cache/queue status

### Base DTO Class

- [ ] T047 [P] Create base DTO class in user-service/app/Services/DTOs/BaseDTO.php
- [ ] T048 [P] Create base DTO class in master-service/app/Services/DTOs/BaseDTO.php
- [ ] T049 [P] Create base DTO class in content-service/app/Services/DTOs/BaseDTO.php
- [ ] T050 [P] Create base DTO class in public-content-service/app/Services/DTOs/BaseDTO.php
- [ ] T051 [P] Create base DTO class in file-service/app/Services/DTOs/BaseDTO.php
- [ ] T052 [P] Create base DTO class in setting-service/app/Services/DTOs/BaseDTO.php

**Checkpoint**: Foundation ready - user story implementation can now begin in parallel

---

## Phase 3: User Story 1 - Refactor Controllers to Transport-Only (Priority: P1) 🎯 MVP

**Goal**: All 39 controllers across 6 services are refactored to be transport-only — validation in Form Requests, business logic in Services, response shaping in API Resources, data transfer via DTOs.

**Independent Test**: For each refactored endpoint, calling it with the same input as before refactoring returns an identical HTTP response (status code, envelope shape, pagination format, field names).

### user-service (9 controllers: AdminAuthController, UserController, PemohonController, PemohonAuthController, LogController, BulkPasswordController)

#### Tests for user-service US1 (MANDATORY per Constitution Principle IV) ⚠️

- [ ] T053 [P] [US1] Create feature test for GET /user/ in user-service/tests/Feature/UserListTest.php
- [ ] T054 [P] [US1] Create feature test for POST /user/ in user-service/tests/Feature/UserCreateTest.php
- [ ] T055 [P] [US1] Create feature test for GET /user/{id} in user-service/tests/Feature/UserDetailTest.php
- [ ] T056 [P] [US1] Create feature test for PUT /user/{id} in user-service/tests/Feature/UserUpdateTest.php
- [ ] T057 [P] [US1] Create feature test for DELETE /user/{id} in user-service/tests/Feature/UserDeleteTest.php
- [ ] T058 [P] [US1] Create feature test for POST auth/login in user-service/tests/Feature/AdminLoginTest.php
- [ ] T059 [P] [US1] Create feature test for POST auth/logout in user-service/tests/Feature/AdminLogoutTest.php
- [ ] T060 [P] [US1] Create feature test for GET auth/profile in user-service/tests/Feature/AdminProfileTest.php
- [ ] T061 [P] [US1] Create feature test for pemohon auth endpoints in user-service/tests/Feature/PemohonAuthTest.php
- [ ] T062 [P] [US1] Create feature test for pemohon management in user-service/tests/Feature/PemohonManagementTest.php
- [ ] T063 [P] [US1] Create feature test for log endpoints in user-service/tests/Feature/LogTest.php
- [ ] T064 [P] [US1] Create feature test for bulk password in user-service/tests/Feature/BulkPasswordTest.php

#### Implementation for user-service US1

- [ ] T065 [P] [US1] Create FormRequest classes for UserController in user-service/app/Http/Requests/ (CreateUserRequest, UpdateUserRequest)
- [ ] T066 [P] [US1] Create FormRequest classes for AdminAuthController in user-service/app/Http/Requests/ (LoginRequest)
- [ ] T067 [P] [US1] Create FormRequest classes for PemohonController in user-service/app/Http/Requests/ (UpdatePemohonByAdminRequest, UpdatePemohonByPemohonRequest)
- [ ] T068 [P] [US1] Create FormRequest classes for PemohonAuthController in user-service/app/Http/Requests/ (RegisterRequest, ForgotPasswordRequest, ResetPasswordRequest, VerifyEmailRequest)
- [ ] T069 [P] [US1] Create DTO classes for user-service in user-service/app/Services/DTOs/ (UserDTO, PemohonDTO, LoginDTO, etc.)
- [ ] T070 [P] [US1] Create UserService in user-service/app/Services/UserService.php
- [ ] T071 [P] [US1] Create PemohonService in user-service/app/Services/PemohonService.php
- [ ] T072 [P] [US1] Create AdminAuthService in user-service/app/Services/AdminAuthService.php
- [ ] T073 [P] [US1] Create PemohonAuthService in user-service/app/Services/PemohonAuthService.php
- [ ] T074 [P] [US1] Create LogService in user-service/app/Services/LogService.php
- [ ] T075 [P] [US1] Create BulkPasswordService in user-service/app/Services/BulkPasswordService.php
- [ ] T076 [P] [US1] Create API Resource classes for user-service in user-service/app/Http/Resources/ (UserResource, PemohonResource)
- [ ] T077 [US1] Refactor UserController in user-service/app/Http/Controllers/UserController.php to transport-only
- [ ] T078 [US1] Refactor AdminAuthController in user-service/app/Http/Controllers/AdminAuthController.php to transport-only
- [ ] T079 [US1] Refactor PemohonController in user-service/app/Http/Controllers/PemohonController.php to transport-only
- [ ] T080 [US1] Refactor PemohonAuthController in user-service/app/Http/Controllers/PemohonAuthController.php to transport-only
- [ ] T081 [US1] Refactor LogController in user-service/app/Http/Controllers/LogController.php to transport-only
- [ ] T082 [US1] Refactor BulkPasswordController in user-service/app/Http/Controllers/BulkPasswordController.php to transport-only

### master-service (13 controllers: NegaraController, PerwakilanController, SifatInformasiController, KategoriFaqController, FaqController, SatuanKerjaController, KategoriContentController, TagController, AlasanKeberatanController, HolidayController, SurveyQuestionController, RoleController, VariableTemplateEmailController, LayoutController, PermohonanController, ClassificationPemohonController)

#### Tests for master-service US1 (MANDATORY) ⚠️

- [ ] T083 [P] [US1] Create feature test for CRUD endpoints in master-service/tests/Feature/NegaraTest.php
- [ ] T084 [P] [US1] Create feature test for CRUD endpoints in master-service/tests/Feature/PerwakilanTest.php
- [ ] T085 [P] [US1] Create feature test for CRUD endpoints in master-service/tests/Feature/SifatInformasiTest.php
- [ ] T086 [P] [US1] Create feature test for CRUD endpoints in master-service/tests/Feature/KategoriFaqTest.php
- [ ] T087 [P] [US1] Create feature test for CRUD endpoints in master-service/tests/Feature/FaqTest.php
- [ ] T088 [P] [US1] Create feature test for CRUD endpoints in master-service/tests/Feature/SatuanKerjaTest.php
- [ ] T089 [P] [US1] Create feature test for CRUD endpoints in master-service/tests/Feature/KategoriContentTest.php
- [ ] T090 [P] [US1] Create feature test for CRUD endpoints in master-service/tests/Feature/TagTest.php
- [ ] T091 [P] [US1] Create feature test for CRUD endpoints in master-service/tests/Feature/AlasanKeberatanTest.php
- [ ] T092 [P] [US1] Create feature test for CRUD endpoints in master-service/tests/Feature/HolidayTest.php
- [ ] T093 [P] [US1] Create feature test for CRUD endpoints in master-service/tests/Feature/SurveyQuestionTest.php
- [ ] T094 [P] [US1] Create feature test for read-only endpoints in master-service/tests/Feature/ReadOnlyEndpointsTest.php

#### Implementation for master-service US1

- [ ] T095 [P] [US1] Create FormRequest classes for master-service controllers in master-service/app/Http/Requests/
- [ ] T096 [P] [US1] Create DTO classes for master-service in master-service/app/Services/DTOs/
- [ ] T097 [P] [US1] Create service classes for master-service in master-service/app/Services/ (NegaraService, PerwakilanService, SifatInformasiService, KategoriFaqService, FaqService, SatuanKerjaService, KategoriContentService, TagService, AlasanKeberatanService, HolidayService, SurveyQuestionService)
- [ ] T098 [P] [US1] Create API Resource classes for master-service in master-service/app/Http/Resources/
- [ ] T099 [US1] Refactor all master-service controllers in master-service/app/Http/Controllers/ to transport-only

### content-service (12 controllers: MenuController, SubMenuController, ChildSubMenuController, PublicationController, GalleryController, RepositoryController, AgendaController, HomeController, LatestInformationController, QuickLinkController, ContentController, DashboardController)

#### Tests for content-service US1 (MANDATORY) ⚠️

- [ ] T100 [P] [US1] Create feature tests for content-service CRUD endpoints in content-service/tests/Feature/
- [ ] T101 [P] [US1] Create feature test for content list and dashboard in content-service/tests/Feature/ContentDashboardTest.php

#### Implementation for content-service US1

- [ ] T102 [P] [US1] Create FormRequest classes for content-service controllers in content-service/app/Http/Requests/
- [ ] T103 [P] [US1] Create DTO classes for content-service in content-service/app/Services/DTOs/
- [ ] T104 [P] [US1] Create service classes for content-service in content-service/app/Services/ (MenuService, SubMenuService, ChildSubMenuService, PublicationService, GalleryService, RepositoryService, AgendaService, HomeService, LatestInformationService, QuickLinkService, ContentService, DashboardService)
- [ ] T105 [P] [US1] Create API Resource classes for content-service in content-service/app/Http/Resources/
- [ ] T106 [US1] Refactor all content-service controllers in content-service/app/Http/Controllers/ to transport-only

### public-content-service (5 controllers: MenuController, SettingController, QuickLinkController, VisitorController, HomeController, ContentController, ChatbotController)

#### Tests for public-content-service US1 (MANDATORY) ⚠️

- [ ] T107 [P] [US1] Create feature tests for public-content-service endpoints in public-content-service/tests/Feature/

#### Implementation for public-content-service US1

- [ ] T108 [P] [US1] Create FormRequest classes for public-content-service controllers in public-content-service/app/Http/Requests/
- [ ] T109 [P] [US1] Create DTO classes for public-content-service in public-content-service/app/Services/DTOs/
- [ ] T110 [P] [US1] Create service classes for public-content-service in public-content-service/app/Services/
- [ ] T111 [P] [US1] Create API Resource classes for public-content-service in public-content-service/app/Http/Resources/
- [ ] T112 [US1] Refactor all public-content-service controllers in public-content-service/app/Http/Controllers/ to transport-only

### file-service (1 controller: FileController)

#### Tests for file-service US1 (MANDATORY) ⚠️

- [ ] T113 [P] [US1] Create feature tests for file-service endpoints in file-service/tests/Feature/FileTest.php

#### Implementation for file-service US1

- [ ] T114 [P] [US1] Create FormRequest classes for file-service in file-service/app/Http/Requests/
- [ ] T115 [P] [US1] Create DTO classes for file-service in file-service/app/Services/DTOs/
- [ ] T116 [P] [US1] Create FileService in file-service/app/Services/FileService.php
- [ ] T117 [P] [US1] Create API Resource classes for file-service in file-service/app/Http/Resources/
- [ ] T118 [US1] Refactor FileController in file-service/app/Http/Controllers/FileController.php to transport-only

### setting-service (3 controllers: TemplateEmailController, SiteSettingController, ChatSettingController)

#### Tests for setting-service US1 (MANDATORY) ⚠️

- [ ] T119 [P] [US1] Create feature tests for setting-service endpoints in setting-service/tests/Feature/

#### Implementation for setting-service US1

- [ ] T120 [P] [US1] Create FormRequest classes for setting-service in setting-service/app/Http/Requests/
- [ ] T121 [P] [US1] Create DTO classes for setting-service in setting-service/app/Services/DTOs/
- [ ] T122 [P] [US1] Create service classes for setting-service in setting-service/app/Services/ (TemplateEmailService, SiteSettingService, ChatSettingService)
- [ ] T123 [P] [US1] Create API Resource classes for setting-service in setting-service/app/Http/Resources/
- [ ] T124 [US1] Refactor all setting-service controllers in setting-service/app/Http/Controllers/ to transport-only

**Checkpoint**: At this point, User Story 1 should be fully functional. All 39 controllers are transport-only. All ~191 endpoints return identical responses.

---

## Phase 4: User Story 2 - Add Mass-Assignment Protection to All Models (Priority: P1) 🎯 MVP

**Goal**: All 50+ models currently missing `$fillable` or `$guarded` now declare `$fillable` with the exact set of columns previously assigned manually in controllers.

**Independent Test**: Static analysis confirms every model class has `$fillable` or `$guarded` property. Mass-assignment attempts with non-fillable fields are silently ignored.

### user-service Models

- [ ] T125 [P] [US2] Add $fillable to Pemohon model in user-service/app/Models/Pemohon.php
- [ ] T126 [P] [US2] Add $fillable to MsRole model in user-service/app/Models/MsRole.php
- [ ] T127 [P] [US2] Add $fillable to MsPerwakilan model in user-service/app/Models/MsPerwakilan.php
- [ ] T128 [P] [US2] Add $fillable to MsClassificationPemohon model in user-service/app/Models/MsClassificationPemohon.php
- [ ] T129 [P] [US2] Add $fillable to TemplateEmail model in user-service/app/Models/TemplateEmail.php

### master-service Models

- [ ] T130 [P] [US2] Add $fillable to MsFaq model in master-service/app/Models/MsFaq.php
- [ ] T131 [P] [US2] Add $fillable to MsKategoriFaq model in master-service/app/Models/MsKategoriFaq.php
- [ ] T132 [P] [US2] Add $fillable to MsSifatInformasi model in master-service/app/Models/MsSifatInformasi.php
- [ ] T133 [P] [US2] Add $fillable to MsTag model in master-service/app/Models/MsTag.php
- [ ] T134 [P] [US2] Add $fillable to MsKategoriContent model in master-service/app/Models/MsKategoriContent.php
- [ ] T135 [P] [US2] Add $fillable to MsVariableTemplateEmail model in master-service/app/Models/MsVariableTemplateEmail.php
- [ ] T136 [P] [US2] Add $fillable to MsRole model in master-service/app/Models/MsRole.php
- [ ] T137 [P] [US2] Add $fillable to Pemohon model in master-service/app/Models/Pemohon.php

### content-service Models

- [ ] T138 [P] [US2] Add $fillable to MsTag model in content-service/app/Models/MsTag.php
- [ ] T139 [P] [US2] Add $fillable to MsLayout model in content-service/app/Models/MsLayout.php
- [ ] T140 [P] [US2] Add $fillable to MsRole model in content-service/app/Models/MsRole.php
- [ ] T141 [P] [US2] Add $fillable to SiteSetting model in content-service/app/Models/SiteSetting.php

### public-content-service Models

- [ ] T142 [P] [US2] Add $fillable to all public-content-service models in public-content-service/app/Models/ (Content, Menu, SubMenu, ChildSubMenu, LatestInformation, QuickLink, SiteSetting, ChatSetting, Pemohon, all ContentDetail*, all ContentAdditional*)

### setting-service Models

- [ ] T143 [P] [US2] Add $fillable to MsRole model in setting-service/app/Models/MsRole.php

**Checkpoint**: At this point, User Stories 1 AND 2 should both work. All models have mass-assignment protection.

---

## Phase 5: User Story 3 - Add Policies for Authorization (Priority: P2)

**Goal**: Every model with CRUD endpoints has a Policy class registered in AuthServiceProvider, providing model-level authorization gates alongside existing `check.role` middleware.

**Independent Test**: Each model with CRUD endpoints has a corresponding Policy class registered in AuthServiceProvider. Policy methods return the same authorization results as the previous inline role checks.

### user-service Policies

- [ ] T144 [P] [US3] Create UserPolicy in user-service/app/Policies/UserPolicy.php
- [ ] T145 [P] [US3] Create PemohonPolicy in user-service/app/Policies/PemohonPolicy.php
- [ ] T146 [US3] Register policies in user-service/app/Providers/AuthServiceProvider.php

### master-service Policies

- [ ] T147 [P] [US3] Create FaqPolicy in master-service/app/Policies/FaqPolicy.php
- [ ] T148 [P] [US3] Create KategoriFaqPolicy in master-service/app/Policies/KategoriFaqPolicy.php
- [ ] T149 [P] [US3] Create SifatInformasiPolicy in master-service/app/Policies/SifatInformasiPolicy.php
- [ ] T150 [P] [US3] Create TagPolicy in master-service/app/Policies/TagPolicy.php
- [ ] T151 [P] [US3] Create KategoriContentPolicy in master-service/app/Policies/KategoriContentPolicy.php
- [ ] T152 [P] [US3] Create NegaraPolicy in master-service/app/Policies/NegaraPolicy.php
- [ ] T153 [P] [US3] Create PerwakilanPolicy in master-service/app/Policies/PerwakilanPolicy.php
- [ ] T154 [P] [US3] Create SatuanKerjaPolicy in master-service/app/Policies/SatuanKerjaPolicy.php
- [ ] T155 [P] [US3] Create HolidayPolicy in master-service/app/Policies/HolidayPolicy.php
- [ ] T156 [P] [US3] Create SurveyQuestionPolicy in master-service/app/Policies/SurveyQuestionPolicy.php
- [ ] T157 [P] [US3] Create AlasanKeberatanPolicy in master-service/app/Policies/AlasanKeberatanPolicy.php
- [ ] T158 [US3] Register policies in master-service/app/Providers/AuthServiceProvider.php

### content-service Policies

- [ ] T159 [P] [US3] Create ContentPolicy in content-service/app/Policies/ContentPolicy.php
- [ ] T160 [P] [US3] Create MenuPolicy in content-service/app/Policies/MenuPolicy.php
- [ ] T161 [P] [US3] Create SubMenuPolicy in content-service/app/Policies/SubMenuPolicy.php
- [ ] T162 [P] [US3] Create ChildSubMenuPolicy in content-service/app/Policies/ChildSubMenuPolicy.php
- [ ] T163 [P] [US3] Create LatestInformationPolicy in content-service/app/Policies/LatestInformationPolicy.php
- [ ] T164 [P] [US3] Create QuickLinkPolicy in content-service/app/Policies/QuickLinkPolicy.php
- [ ] T165 [US3] Register policies in content-service/app/Providers/AuthServiceProvider.php

### setting-service Policies

- [ ] T166 [P] [US3] Create TemplateEmailPolicy in setting-service/app/Policies/TemplateEmailPolicy.php
- [ ] T167 [P] [US3] Create SiteSettingPolicy in setting-service/app/Policies/SiteSettingPolicy.php
- [ ] T168 [P] [US3] Create ChatSettingPolicy in setting-service/app/Policies/ChatSettingPolicy.php
- [ ] T169 [US3] Register policies in setting-service/app/Providers/AuthServiceProvider.php

**Checkpoint**: At this point, User Stories 1, 2, AND 3 should all work. Authorization is centralized in Policies.

---

## Phase 6: User Story 4 - Add Observability: Correlation ID & Enhanced Health (Priority: P2)

**Goal**: X-Request-ID correlation IDs propagated across all services, health endpoints report dependency status, manual LogActivity calls migrated to HasActivityLog model events.

**Independent Test**: Sending a request with X-Request-ID header returns the same ID in response. Health endpoint returns dependency status. No manual LogActivity::create() calls remain in controllers.

### LogActivity Migration

- [ ] T170 [P] [US4] Apply HasActivityLog trait to models in user-service that have manual LogActivity calls (User, Pemohon)
- [ ] T171 [P] [US4] Apply HasActivityLog trait to models in master-service that have manual LogActivity calls (MsFaq, etc.)
- [ ] T172 [P] [US4] Apply HasActivityLog trait to models in content-service that have manual LogActivity calls
- [ ] T173 [P] [US4] Apply HasActivityLog trait to models in setting-service that have manual LogActivity calls
- [ ] T174 [US4] Remove manual LogActivity::create() calls from all refactored controllers across all services
- [ ] T175 [US4] Add structured logging context (user, action, entity, correlation ID) to all service methods

### Tests for US4

- [ ] T176 [P] [US4] Create feature test for X-Request-ID propagation in user-service/tests/Feature/RequestIdTest.php
- [ ] T177 [P] [US4] Create feature test for enhanced health endpoint in user-service/tests/Feature/HealthTest.php

**Checkpoint**: Observability is now in place across all services.

---

## Phase 7: User Story 5 - Add Test Foundation (Priority: P3)

**Goal**: Factories for all models, feature tests for all endpoints, unit tests for all service methods. 80% coverage on refactored code.

**Independent Test**: Running `phpunit` shows all tests pass with >= 80% coverage on refactored code.

### Model Factories

- [ ] T178 [P] [US5] Create factories for user-service models in user-service/database/factories/
- [ ] T179 [P] [US5] Create factories for master-service models in master-service/database/factories/
- [ ] T180 [P] [US5] Create factories for content-service models in content-service/database/factories/
- [ ] T181 [P] [US5] Create factories for public-content-service models in public-content-service/database/factories/
- [ ] T182 [P] [US5] Create factories for setting-service models in setting-service/database/factories/

### Unit Tests for Services

- [ ] T183 [P] [US5] Create unit tests for user-service services in user-service/tests/Unit/Services/
- [ ] T184 [P] [US5] Create unit tests for master-service services in master-service/tests/Unit/Services/
- [ ] T185 [P] [US5] Create unit tests for content-service services in content-service/tests/Unit/Services/
- [ ] T186 [P] [US5] Create unit tests for public-content-service services in public-content-service/tests/Unit/Services/
- [ ] T187 [P] [US5] Create unit tests for file-service services in file-service/tests/Unit/Services/
- [ ] T188 [P] [US5] Create unit tests for setting-service services in setting-service/tests/Unit/Services/

### Policy Tests

- [ ] T189 [P] [US5] Create unit tests for all Policy classes in {service}/tests/Unit/Policies/

**Checkpoint**: All user stories should now be independently functional with full test coverage.

---

## Phase 8: Polish & Cross-Cutting Concerns

**Purpose**: Improvements that affect multiple user stories

- [ ] T190 [P] Run regression test suite comparing before/after responses for all ~191 endpoints
- [ ] T191 [P] Run PHPUnit coverage report and verify >= 80% on refactored code
- [ ] T192 [P] Run Laravel Pint lint check across all services
- [ ] T193 [P] Update plan.md Complexity Tracking with any new deviations discovered during implementation
- [ ] T194 [P] Run quickstart.md validation scenarios
- [ ] T195 [P] Final review: verify no `Illuminate\Http\Request` passed to any service class

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: No dependencies - can start immediately
- **Foundational (Phase 2)**: Depends on Setup completion - BLOCKS all user stories
- **User Stories (Phase 3-7)**: All depend on Foundational phase completion
  - US1 (Phase 3) and US2 (Phase 4) can proceed in parallel (different files)
  - US3 (Phase 5) depends on US1 (controllers must be refactored first to use Policies)
  - US4 (Phase 6) depends on US1 (controllers must be refactored first to remove LogActivity calls)
  - US5 (Phase 7) depends on US1, US2, US3, US4 (tests verify all refactored code)
- **Polish (Phase 8)**: Depends on all user stories being complete

### User Story Dependencies

- **User Story 1 (P1)**: Can start after Foundational (Phase 2) - No dependencies on other stories
- **User Story 2 (P1)**: Can start after Foundational (Phase 2) - No dependencies on other stories (independent of US1)
- **User Story 3 (P2)**: Depends on US1 (controllers must be refactored to use Policies in FormRequest authorize())
- **User Story 4 (P2)**: Depends on US1 (LogActivity calls must be removed from refactored controllers)
- **User Story 5 (P3)**: Depends on US1, US2, US3, US4 (tests verify all refactored code)

### Within Each User Story

- Tests MUST be written and FAIL before implementation
- Models before services
- Services before endpoints
- Core implementation before integration
- Story complete before moving to next priority

### Parallel Opportunities

- All Setup tasks marked [P] can run in parallel (across all 6 services)
- All Foundational tasks marked [P] can run in parallel
- US1 and US2 can run in parallel (different files, no cross-dependencies)
- Within US1: All 6 services can be refactored in parallel
- Within each service: Form Requests, DTOs, Services, Resources can be created in parallel
- All US2 tasks can run in parallel (each model is independent)
- All US3 tasks can run in parallel (each Policy is independent)
- All US5 tasks can run in parallel (each factory/test is independent)

---

## Parallel Example: User Story 1

```bash
# Launch all 6 services in parallel:
Task: "Refactor user-service controllers to transport-only"
Task: "Refactor master-service controllers to transport-only"
Task: "Refactor content-service controllers to transport-only"
Task: "Refactor public-content-service controllers to transport-only"
Task: "Refactor file-service controllers to transport-only"
Task: "Refactor setting-service controllers to transport-only"

# Within each service, create Form Requests, DTOs, Services, Resources in parallel:
Task: "Create FormRequest classes for user-service"
Task: "Create DTO classes for user-service"
Task: "Create Service classes for user-service"
Task: "Create API Resource classes for user-service"
```

---

## Implementation Strategy

### MVP First (User Stories 1 + 2 Only)

1. Complete Phase 1: Setup
2. Complete Phase 2: Foundational (CRITICAL - blocks all stories)
3. Complete Phase 3: User Story 1 (Refactor controllers)
4. Complete Phase 4: User Story 2 (Mass-assignment protection)
5. **STOP and VALIDATE**: Run regression tests on all ~191 endpoints
6. Deploy/demo if ready

### Incremental Delivery

1. Complete Setup + Foundational → Foundation ready
2. Add User Story 1 + 2 → Test independently → Deploy/Demo (MVP!)
3. Add User Story 3 → Test independently → Deploy/Demo
4. Add User Story 4 → Test independently → Deploy/Demo
5. Add User Story 5 → Test independently → Deploy/Demo
6. Each story adds value without breaking previous stories

### Parallel Team Strategy

With multiple developers:

1. Team completes Setup + Foundational together
2. Once Foundational is done:
   - Developer A: user-service (US1 + US2)
   - Developer B: master-service (US1 + US2)
   - Developer C: content-service + public-content-service (US1 + US2)
   - Developer D: file-service + setting-service (US1 + US2)
3. Then redistribute for US3, US4, US5

---

## Notes

- [P] tasks = different files, no dependencies
- [Story] label maps task to specific user story for traceability
- Each user story should be independently completable and testable
- Verify tests fail before implementing
- Commit after each task or logical group
- Stop at any checkpoint to validate story independently
- Avoid: vague tasks, same file conflicts, cross-story dependencies that break independence
- **CRITICAL**: Never change the e2e response contract. Run regression tests after each user story.