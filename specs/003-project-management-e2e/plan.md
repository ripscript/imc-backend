# Implementation Plan: Project Management E2E Creation

**Phase**: 1 — Design & Contracts
**Date**: 2026-09-23
**Spec**: [spec.md](spec.md) · Contracts: [contracts/project-service.md](contracts/project-service.md)

## Overview

One phase per design screen, preceded by a schema phase and followed by cross-cutting hardening. All DDL in **migration-service**; domain logic in **project-service** following the existing pattern (Controller → FormRequest → DTO → Service → Resource → `ApiResponse`, JWT `jwt.error:admin` + `check.role:super_admin,admin_pusat,admin_pustik`). S6 (success modal) is skipped — FE only.

| Phase | Screen | Theme | Depends on |
|---|---|---|---|
| 0 | — | Schema + models + activity-log wiring + seeders | — |
| 1 | S1 | Daftar Proyek list | 0 |
| 2 | S2 | Wizard step 1 — Usulan Proyek Kolaborasi | 0 |
| 3 | S3 | Wizard step 2 — Tentukan Stage | 1–2 |
| 4 | S4 | Wizard step 3 — Tentukan Timeline | 3 |
| 5 | S5 | Wizard step 4 — Konfigurasi Setiap Stage | 3 |
| 6 | S8 | Wizard step 5 — Review & Activate | 4–5 |
| 7 | — | Cross-cutting: activities, members, custom fields, tests | 1–6 |
| 8 | S3/S5 | Workflow blueprint templates (deep copy, apply-template, approver gate) | 0–7 |
| 9 | S3/S5 | Barebone S3 sync, blueprint snapshot at first S5 touch (superseded by Phase 10) | 8 |
| 10 | S3/S5 | FE-held template, explicit nested full-tree saves, apply-template removed | 8 |

Phases 1 and 2 are independent and can proceed in parallel. Phases 4 and 5 both depend only on Phase 3 and can proceed in parallel.

## Phase 0 — Schema Foundation

**migration-service** (new migrations `2026_09_xx_*`, DDL per [data-model.md](data-model.md)):
- `pm_project_stages`, `pm_tasks`, `pm_deliverables`, `pm_documents`, `pm_approvals`, `pm_milestones`, `pm_change_requests`, `pm_project_members`
- Seeders: default workflow ("ETO Manufacturing v1" style, template stages) + `pm_document_types` + sample client/partner/category — PM master data currently seeds empty

**project-service**:
- Models: `ProjectStage`, `PmTask`, `PmDeliverable`, `PmDocument`, `PmApproval`, `PmMilestone`, `PmChangeRequest`, `PmProjectMember` (+ factories) with relations per data-model
- Add `spatie/laravel-activitylog` to composer; port `HasActivityLog` trait + `LogActivity` helper from master-service; `config/activitylog.php` already present

**Also**: update root `ERD.md` with the deltas documented in [gap-analysis.md](gap-analysis.md) §3.

**Acceptance**: migrations run clean on shared Postgres; models with factories resolve relations; a test mutation writes an `activity_log` row.

## Phase 1 — S1: Daftar Proyek List

- `ProjectService::index` — derive `current_stage` (first non-completed by sequence) and `progress` (avg stage progress) per row; filters `status`, `client_id`, `category_id`, `my` (PIC/member scope via `pm_project_members`); `sort_by` extended (see contract)
- `ProjectListResource` — replace `current_stage => null` placeholder
- `ProjectResource` — populate `stages` and `current_stage` placeholders (restored in later phases)

**Files**: `ProjectService.php`, `QueryFilterDTO.php`, `ProjectController.php`, `ProjectListResource.php`, `CreateProjectRequest`/`UpdateProjectRequest` (new filter params)
**Acceptance**: US1 scenarios 1–4.

## Phase 2 — S2: Wizard Step 1 — Usulan Proyek Kolaborasi

- Align `Create/UpdateProjectRequest` + `ProjectDTO` to S2 fields: `title`, `client_id`, `category_id`, `pic_user_id`, `target_end_date`, `budget` (Nilai Proyek), `description` (rich text), `attachments[]`
- Attachment validation: extensions `pdf,doc,docx,jpg,png`, max 5 MB, max 5 files (extend existing max-3 rule); optional `file_size_kb` per attachment for size validation at save time
- PIC email derived in `ProjectResource` from `users.email`
- New `GET /projects/{project_id}/setup-state` (`ProjectSetupStateService`): derived completeness per [data-model.md](data-model.md); works from partial data (refined as phases land)
- Keep `status = draft` semantics; every step save is a plain `PUT` (free navigation, decision 5)

**Files**: Requests, `ProjectDTO.php`, `ProjectService.php`, `ProjectResource.php`, new `ProjectSetupStateService.php` + `SetupStateResource.php`, routes
**Acceptance**: US2 + US7 scenarios.

## Phase 3 — S3: Wizard Step 2 — Tentukan Stage

- New `ProjectStageService` + `ProjectStageController`:
  - `GET /projects/{project_id}/stages`
  - `PUT /projects/{project_id}/stages` — bulk replace-all; workflow instantiation (copy `name`, `workflow_stage_id`, `is_required`, `allow_skip`, `allow_repeat` from `pm_workflow_stages`) or manual entries; reindex `sequence`; guard: reject removal of a stage that another stage depends on
- Workflow preview uses existing master-service `GET workflows/{id}/stages` (no master changes)
- `ProjectStageResource` as specified in contracts

**Files**: `ProjectStageService.php`, `ProjectStageController.php`, `ProjectStageDTO.php`, `SyncStagesRequest.php`, `ProjectStageResource.php`, `ProjectStage.php` model polish, routes
**Acceptance**: US3 scenarios.

## Phase 4 — S4: Wizard Step 3 — Tentukan Timeline

- `PUT /projects/{project_id}/timeline` (`ProjectStageService::saveTimeline` or `ProjectTimelineService`): project dates + per-stage dates + `depends_on_project_stage_id`
- Validation: date ranges, stage-within-project, self-ref, cycle detection (DFS over dependency graph), dependency start ordering
- Derived `duration_days` (inclusive) in response

**Files**: `ProjectTimelineService.php`, `SaveTimelineRequest.php`, controller + resource additions, routes
**Acceptance**: US4 scenarios.

## Phase 5 — S5: Wizard Step 4 — Konfigurasi Setiap Stage

- Stage detail/update: `GET|PUT /projects/{project_id}/stages/{stage_id}` (sets `is_configured`; `requires_approval=false` clears approval rows)
- `PmTaskService`: CRUD + `PATCH /projects/{project_id}/tasks/{task_id}/toggle` returning stage counters
- `PmDeliverableService`: CRUD
- `PmDocumentService`: CRUD + versioning rule (new row `version = n+1`, previous `superseded`); validation against `pm_document_types.allowed_extension`/`max_size_mb` (default 5 MB / `pdf,doc,docx,jpg,png`)
- `PmApprovalService`: `GET|PUT .../approvers` bulk upsert of pre-created `pm_approvals` rows (decision 3)
- Update `ProjectSetupStateService` step-4 rule (`is_configured` on all stages)

**Files**: `ProjectStageController` (extend), `services` `Pm{Task,Deliverable,Document,Approval}Service.php`, `Pm{Task,Deliverable,Document}Controller.php` (or one `StageContentController`), FormRequests + DTOs + Resources per entity, routes
**Acceptance**: US5 scenarios.

## Phase 6 — S8: Wizard Step 5 — Review & Activate

- `GET /projects/{project_id}/review` (`ProjectReviewService`): summary aggregates + `timeline_preview` per data-model Derived Values
- `POST /projects/{project_id}/activate` (`ProjectActivationService`): completeness gate (422 + `missing[]`), transaction — status → `active`, first stage → `in_progress`, stamp `requested_at`/`requested_by` on first-stage approvals, upsert `pm_project_members` (role `pic`), activity logs; 409 on already-active
- "Simpan Draft" = existing `PUT` (no endpoint)

**Files**: `ProjectReviewService.php`, `ProjectActivationService.php`, `ProjectController` (or new `ProjectActivationController`), `ReviewResource.php`, `ActivateProjectRequest.php`, routes
**Acceptance**: US6 scenarios.

## Phase 7 — Cross-Cutting & Hardening

- `GET /projects/{project_id}/activities` (spatie query by `properties.project_id`); verify `HasActivityLog` on all new models
- Members: `GET|POST|DELETE /projects/{project_id}/members`; auto-sync of project/stage PICs (data-model sync rule)
- Custom fields: merge master-service `custom-field-values` into `ProjectResource.custom_fields`
- Populate `ProjectResource.milestones` / `open_change_requests` remain `[]` until their epics (schema ready)
- Tests in `project-service/tests/Feature/` (none exist today; mirror `content-service` style + master-service's raw-SQL SQLite table creation trick), Postman collection under `postman/`, regenerate Scramble `public/specs/api.json`
- `ProjectService::destroy` guard review: only `draft` deletable (existing rule) cascades new child rows via FK + model `booted()` cleanup like existing additional-content cleanup

**Acceptance**: US8 scenarios; feature tests green; OpenAPI spec regenerates.

## Phase 8 — Workflow Blueprint Templates

Makes `workflow` + `workflow_stage` a reusable blueprint (never people, never dates). Semantics: copy-on-instantiate snapshot; children copy on create only; approvals never templated; activation requires approvers where flagged; manual stages unaffected (see spec.md Blueprint Semantics).

**8A — Schema (migration-service)**: `pm_workflow_stages` += `description`, `requires_approval`; new `pm_workflow_stage_tasks` / `pm_workflow_stage_deliverables` (DDL per data-model.md); ERD.md deltas.

**8B — master-service blueprint CRUD**: nested `stages[].description|requires_approval|tasks[]|deliverables[]` in `Create/UpdateWorkflowRequest`; `WorkflowService::syncStages` deep replace-all (stages + children); preview/detail resources return the full blueprint.

**8C — project-service**: Step 2 sync deep-copies the blueprint into *new* stages (`pending` tasks/deliverables); new `POST /projects/{project_id}/stages/{stage_id}/apply-template` for the S5 "Gunakan Workflow" action (replace attrs + children, preserve name/dates/PIC/approvals); activation gate `stage_{id}.approvers_missing`.

**8D — Seeder, tests, docs, postman**: enrich the ETO blueprint in `PmProjectManagementSeeder`; feature tests (deep copy, snapshot isolation, apply-template, gate, manual untouched) + master nested-sync tests; walkthrough + reference Postman updates.

**Files**: migration-service `2026_09_25_*`; master `PmWorkflowStage{Task,Deliverable}` models + factories, `WorkflowService`, `Create/UpdateWorkflowRequest`, `WorkflowStageResource` (+2 child resources), DTOs; project `ProjectStageService` (sync + `applyTemplate`), `ProjectSetupStateService` gate, `ApplyTemplateRequest`, routes.
**Acceptance**: US9 scenarios 1–6.

## Phase 9 — Barebone S3 Sync, Snapshot at First S5 Touch (superseded by Phase 10 — see below; `ensurePredefinedConfig` and `BlueprintSnapshot` removed, `apply-template` removed)

Corrects the snapshot point: Step 2 sync stores structure only (name, sequence, flags, template link); the blueprint materializes idempotently at the first Step 4 touch of each stage (`ResolvesProjectStage::resolveStage` → `ensurePredefinedConfig`, shared `BlueprintSnapshot` helper also used by `apply-template`).

**Files**: project `ResolvesProjectStage.php`, `ProjectStageService.php` (barebone sync), `Support/BlueprintSnapshot.php`, `WorkflowBlueprintTest.php` rework.
**Acceptance**: revised US9 scenarios 1–3; full suite green.

## Phase 10 — FE-Held Template, Explicit Nested Saves

The template is a read-only reference; FE merges it in its own state and submits the full configured tree. No implicit copies anywhere: `sync` and `updateStage` accept nested `tasks[]`/`deliverables[]` (id-matched replace-all when keys are present); `workflow_stage_id` is a provenance link only; `apply-template` (+ request, controller method, route) and `BlueprintSnapshot` are deleted; `resolveStage` is a plain lookup again.

**Files**: project `ProjectStageService.php` (pure-store sync, `replaceChildren`), `StageUpdateDTO` (+nested props), `SyncStagesRequest`/`UpdateStageRequest` (nested rules), `ProjectStageController`/`routes` (apply-template removed), `WorkflowBlueprintTest.php` rework.
**Acceptance**: US9 scenarios 1–6 (revised); full suite green.

## Risk Register

| Risk | Mitigation |
|---|---|
| `content_detail_projects` merge means PM queries touch a wide CMS table | Keep PM columns/indexes isolated; add index on `(status, updated_at)` and FK columns if list perf requires |
| Replace-all stage sync can orphan tasks/deliverables | Soft-delete semantics + Phase 3 dependency guard; child rows cascade on hard delete, soft-delete children with parent in service layer |
| Dependency cycle bugs | Unit tests for the DFS validator with adversarial graphs |
| No existing project-service tests | Phase 0 sets up test harness early (factories + SQLite DDL pattern) before feature phases |
| spatie activity_log volume | Log only mutations (no reads); batch_uuid unused; retention policy out of scope |
