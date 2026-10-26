# Tasks: Project Management E2E Creation

**Spec**: [spec.md](spec.md) · **Plan**: [plan.md](plan.md)
**Status**: Phases 0–10 implemented (2026-09-23). Full project-service suite: 35 Feature + 5 Unit tests green; master-service blueprint tests green (sqlite).

## Phase 0 — Schema Foundation

- [x] migration-service: create `pm_project_stages`, `pm_tasks`, `pm_deliverables`, `pm_documents`, `pm_approvals`, `pm_milestones`, `pm_change_requests`, `pm_project_members` (DDL per [data-model.md](data-model.md))
- [x] migration-service: seeders — default workflow + template stages, document types, sample client/partner/category
- [x] project-service: models + factories + relations (`ProjectStage`, `PmTask`, `PmDeliverable`, `PmDocument`, `PmApproval`, `PmMilestone`, `PmChangeRequest`, `PmProjectMember`)
- [x] project-service: add `spatie/laravel-activitylog`; port `HasActivityLog` trait + `LogActivity` helper from master-service
- [x] project-service: test harness (factories usable on SQLite; DDL bootstrap pattern from master-service tests)
- [x] Update root `ERD.md` with deltas ([gap-analysis.md](gap-analysis.md) §3)

## Phase 1 — S1 Daftar Proyek List

- [x] `ProjectService::index` — derive `current_stage`, `progress`; filters `status`, `client_id`, `category_id`, `my`
- [x] Extend `sort_by` whitelist (title, project_no, client, stage, progress, status, updated_at)
- [x] `ProjectListResource` — real `current_stage`; `ProjectResource` — populate `stages`, `current_stage`
- [x] Feature tests: US1 scenarios 1–4

## Phase 2 — S2 Wizard Step 1

- [x] Align `Create/UpdateProjectRequest` + `ProjectDTO` to S2 fields (title, client_id, category_id, pic_user_id, target_end_date, budget, description, attachments)
- [x] Attachment validation: `pdf,doc,docx,jpg,png`, max 5 MB, max 5 files; `file_size_kb` support
- [x] PIC email derived in `ProjectResource`
- [x] `GET /projects/{project_id}/setup-state` (derived completeness, free navigation)
- [x] Feature tests: US2, US7 scenarios

## Phase 3 — S3 Wizard Step 2 (Stages)

- [x] `GET /projects/{project_id}/stages`
- [x] `PUT /projects/{project_id}/stages` — bulk replace-all; workflow instantiation vs manual; sequence reindex; dependency guard on removal
- [x] `ProjectStageResource`, `SyncStagesRequest`, `ProjectStageDTO`
- [x] Feature tests: US3 scenarios

## Phase 4 — S4 Wizard Step 3 (Timeline)

- [x] `PUT /projects/{project_id}/timeline` — project + stage dates, dependency links
- [x] Validators: range, stage-within-project, self-ref, cycle (DFS), dependency start ordering
- [x] Inclusive duration derivation in response
- [x] Unit tests: dependency graph validator; feature tests: US4 scenarios

## Phase 5 — S5 Wizard Step 4 (Stage Config)

- [x] `GET|PUT /projects/{project_id}/stages/{stage_id}` (is_configured, requires_approval clears rows)
- [x] Tasks: CRUD + `PATCH /projects/{project_id}/tasks/{task_id}/toggle` with counters
- [x] Deliverables: CRUD
- [x] Documents: CRUD + versioning (n+1 / superseded) + document-type extension/size validation
- [x] Approvers: `GET|PUT .../approvers` — bulk upsert pre-created `pm_approvals`
- [x] Setup-state step-4 rule wired to `is_configured`
- [x] Feature tests: US5 scenarios

## Phase 6 — S8 Wizard Step 5 (Review & Activate)

- [x] `GET /projects/{project_id}/review` — summary + `timeline_preview`
- [x] `POST /projects/{project_id}/activate` — completeness gate, transaction side effects, 409 guard
- [x] Feature tests: US6 scenarios

## Phase 7 — Cross-Cutting & Hardening

- [x] `GET /projects/{project_id}/activities` (spatie, filter by `properties.project_id`)
- [x] Members: `GET|POST|DELETE /projects/{project_id}/members` + PIC auto-sync rule
- [x] Custom fields merged into `ProjectResource.custom_fields` via master-service contract
- [x] `destroy` cascade review for new child tables
- [x] Postman collection updates (`postman/`) — 6 folders, 32 requests, 0 saved examples
- [ ] Scramble spec regen (`public/specs/api.json`) — **blocked in this environment**: `scramble:export` introspects the shared Postgres at export time and the DB host is unreachable here; the committed `api.json` is already stale (3 paths, predates even the original 7 routes). Regen via `make swagger` with the docker DB up.
- [x] Full regression run + checklist review ([checklist.md](checklist.md))

## Phase 8 — Workflow Blueprint Templates

- [x] 8A migration-service: `description` + `requires_approval` on `pm_workflow_stages`; create `pm_workflow_stage_tasks`, `pm_workflow_stage_deliverables`; ERD.md deltas
- [x] 8B master-service: nested blueprint CRUD (`Create/UpdateWorkflowRequest`, `WorkflowService::syncStages` deep replace-all, blueprint preview/detail resources, DTOs, factories)
- [x] 8C project-service: Step 2 deep copy on create; `POST .../stages/{stage_id}/apply-template`; activation gate `stage_{id}.approvers_missing`
- [x] 8D seeder enrichment, feature + master tests (US9), Postman walkthrough + reference updates

## Phase 9 — Barebone S3 Sync, Snapshot at First S5 Touch (superseded by Phase 10)

- [x] `sync` stores structure only (no description/approval-flag prefill, no children)
- [x] `ensurePredefinedConfig` in `ResolvesProjectStage` + shared `BlueprintSnapshot` helper (both removed in Phase 10)
- [x] `WorkflowBlueprintTest` rework (barebone-until-touch, flow-through-before-touch, snapshot-after-touch)
- [x] Spec/contract/plan docs updated; walkthrough journey reworked (Part B below)

## Phase 10 — FE-Held Template, Explicit Nested Saves

- [x] `sync` + `updateStage` accept nested `tasks[]`/`deliverables[]` (id-matched replace-all; explicit `[]` clears)
- [x] `apply-template` endpoint, request, controller method removed; `BlueprintSnapshot` deleted; `resolveStage` plain lookup
- [x] Nested validation rules in `SyncStagesRequest` + `UpdateStageRequest`; `tasks`/`deliverables` props on `StageUpdateDTO`
- [x] `WorkflowBlueprintTest` rework (explicit-save semantics); walkthrough journey from blueprint creation (Part B below)
