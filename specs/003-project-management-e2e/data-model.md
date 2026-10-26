# Data Model: Project Management E2E Creation

**Phase**: 1 — Design & Contracts
**Date**: 2026-09-23
**Spec**: [spec.md](spec.md) · [gap-analysis.md](gap-analysis.md)

## Overview

All DDL is owned by **migration-service** (shared Postgres). Models live in **project-service** (`app/Models/`), following existing conventions: `HasFactory`, `SoftDeletes`, `$guarded = ['id']`, `created_by`/`updated_by` strings, `HasActivityLog` (spatie).

Naming follows the existing PM prefix (`pm_*`). ERD entity names map 1:1 as noted per table.

## Existing Tables — Deltas Only

### content_detail_projects (ERD PROJECT)
Kept merged with CMS content detail (decision 4). **No schema change required for this spec.**

| ERD field | Column | Notes |
|---|---|---|
| project_no | `project_no` | UK, `IMC.{Y}-{000}` via `ProjectCodeGenerator` |
| name | `title` | |
| description | `description` | Rich text/HTML from S2 |
| client_id / partner_id / category_id / workflow_id / pic_user_id | same | FKs exist |
| status | `status` | `draft \| pending_approval \| active \| on_hold \| completed \| cancelled` (existing) |
| start_date / target_end_date | `start_date` / `target_end_date` | `start_date` set at step 3/activation |
| — | `progress` | existing smallint 0-100, derived (see Derived Values) |

Nilai Proyek (S2) maps to the existing `budget` column. Client Email (S2) is derived from `users.email` of `pic_user_id` (no column).

### activity_log (ERD ACTIVITY_LOG — deviation, decision 2)
Existing spatie table (`log_name`, `description`, `event`, `subject_type/subject_id`, `causer_type/causer_id`, `properties` json, `batch_uuid`). Mapping conventions:

- `subject` = the mutated model (Project, ProjectStage, Task, Deliverable, Document, Approval, …)
- `causer` = `auth('admin')->user()`
- `event` = `created \| updated \| deleted`
- `properties->project_id` = always set (allows `GET /projects/{project_id}/activities` filtering)
- `log_name` = `project-management`

Wire `spatie/laravel-activitylog` into project-service (composer) + `HasActivityLog` trait / `LogActivity` helper copied from master-service.

### pm_workflow_stages (ERD WORKFLOW_STAGE)
No change. `is_required`, `allow_skip`, `allow_repeat` here are **template defaults**, copied to `pm_project_stages` at instantiation (per-project override).

### Blueprint template children (Phase 8)

Workflow stages carry a reusable blueprint (never people, never dates) that FE reads via the master-service preview endpoints. Project rows are created exclusively from explicitly submitted payloads — the API never copies template rows implicitly (see Blueprint Semantics in spec.md).

| Table | Columns |
|---|---|
| `pm_workflow_stage_tasks` | id, `workflow_stage_id` FK → pm_workflow_stages cascadeOnDelete, `title`, `description` nullable, `priority` (`low \| medium \| high`, default `medium`), `sequence` (uint, default 1), created_by, updated_by, deleted_at, timestamps |
| `pm_workflow_stage_deliverables` | id, `workflow_stage_id` FK → pm_workflow_stages cascadeOnDelete, `document_type_id` FK → pm_document_types nullOnDelete nullable, `name`, `is_required` (bool, default true), `sequence` (uint, default 1), created_by, updated_by, deleted_at, timestamps |

`pm_workflow_stages` gains two blueprint columns: `description` (text, nullable) and `requires_approval` (bool, default false). No approver slots, no due offsets, no template documents (clarifications: flag only, manual dates, deliverables only).

## New Tables

### pm_project_stages (ERD PROJECT_STAGE + deltas)

| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| project_id | bigint FK → content_detail_projects | cascadeOnDelete |
| workflow_stage_id | bigint FK → pm_workflow_stages | **nullable**, nullOnDelete; null = "Buat Manual" |
| name | varchar(255) | delta #1; denormalized from workflow_stage or manual |
| description | text nullable | delta #1 |
| sequence | unsigned int | display order (S3 "No"); unique(project_id, sequence) |
| status | varchar(20) | `pending \| in_progress \| completed \| skipped`, default `pending` |
| progress | unsigned smallint | 0-100, default 0, derived from tasks |
| pic_user_id | bigint FK → users | nullable, nullOnDelete |
| start_date / end_date | date nullable | step 3 timeline |
| completed_at | timestamp nullable | |
| depends_on_project_stage_id | bigint FK → pm_project_stages | **nullable**, nullOnDelete; delta #2 |
| is_required | boolean default true | copied from template (manual: default true) |
| allow_skip | boolean default false | per-project copy, editable in S5 |
| allow_repeat | boolean default false | per-project copy, editable in S5 |
| requires_approval | boolean default false | delta #4 (S5 "Perlu Approval") |
| is_configured | boolean default false | delta #4; true after first save of step-4 config (single PUT or bulk `PUT stages/configuration`, which sweeps the flag for all project stages) |
| created_by, updated_by | varchar | |
| deleted_at, timestamps | | SoftDeletes |

Constraints: `unique(project_id, sequence)`; dependency validation (self-ref, cycles) in service layer.

### pm_tasks (ERD TASK)

| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| project_stage_id | bigint FK → pm_project_stages | cascadeOnDelete |
| title | varchar(255) | |
| description | text nullable | |
| status | varchar(20) | `pending \| in_progress \| done \| cancelled`, default `pending` |
| priority | varchar(10) | `low \| medium \| high`, default `medium` |
| assignee_id | bigint FK → users | nullable, nullOnDelete |
| due_date | date nullable | |
| created_by, updated_by, deleted_at, timestamps | | |

### pm_deliverables (ERD DELIVERABLE)

| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| project_stage_id | bigint FK → pm_project_stages | cascadeOnDelete |
| document_type_id | bigint FK → pm_document_types | nullable, nullOnDelete |
| name | varchar(255) | e.g. "Production Plan" |
| is_required | boolean default true | feeds S7 "Dokumen Wajib" |
| status | varchar(20) | `pending \| wip \| completed`, default `pending` (S5 badges) |
| due_date | date nullable | |
| created_by, updated_by, deleted_at, timestamps | | |

### pm_documents (ERD DOCUMENT + delta #6)

| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| project_id | bigint FK → content_detail_projects | cascadeOnDelete |
| project_stage_id | bigint FK → pm_project_stages | **nullable**, cascadeOnDelete; null = project-level doc |
| document_type_id | bigint FK → pm_document_types | nullable, nullOnDelete |
| file_name | varchar(255) | display name, versioning key within a stage |
| file_path | text | file-service `POST /upload` result |
| file_size_kb | int nullable | delta #6 |
| version | unsigned int default 1 | per (project_stage_id, file_name) |
| status | varchar(20) | `active \| superseded`, default `active` |
| uploaded_by | bigint FK → users | nullable, nullOnDelete |
| uploaded_at | timestamp | |
| created_by, updated_by, deleted_at, timestamps | | |

Versioning rule: uploading a `file_name` that already has an `active` row in the same stage creates a new row (`version = max + 1`) and marks the previous row `superseded`. S5 "Dokumen" card lists active rows ("name v{n} • size").

### pm_approvals (ERD APPROVAL + delta #5, pre-created rows per decision 3)

| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| project_stage_id | bigint FK → pm_project_stages | cascadeOnDelete |
| sequence | unsigned int default 1 | delta #5; approval order in S5 approver list |
| approver_id | bigint FK → users | nullable, nullOnDelete; **designated** approver (pre-assigned) |
| requested_by | bigint FK → users | nullable; stamped when approval is formally requested |
| approved_by | bigint FK → users | nullable; **actual** actor at decision |
| status | varchar(20) | `pending \| approved \| rejected`, default `pending` |
| notes | text nullable | |
| requested_at | timestamp nullable | stamped on project activation / stage start |
| approved_at | timestamp nullable | |
| created_by, updated_by, deleted_at, timestamps | | |

Lifecycle: rows are created at S5 approver config (`status = pending`, `approver_id` set). Replacing the approver list (bulk PUT) upserts rows by `sequence` and soft-deletes removed ones. Decision endpoints (approve/reject) belong to the future "Approval monitoring" module — schema is execution-ready.

### pm_milestones (ERD MILESTONE — schema only this spec)

| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| project_id | bigint FK → content_detail_projects | cascadeOnDelete |
| name | varchar(255) | |
| target_date | date | |
| status | varchar(20) | `pending \| completed \| missed`, default `pending` |
| completed_at | timestamp nullable | |
| created_by, updated_by, deleted_at, timestamps | | |

### pm_change_requests (ERD CHANGE_REQUEST — schema only this spec)

| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| project_id | bigint FK → content_detail_projects | cascadeOnDelete |
| project_stage_id | bigint FK → pm_project_stages | nullable, nullOnDelete |
| change_no | varchar(50) UK | `CR.IMC.{Y}-{000}` |
| title | varchar(255) | |
| reason | text nullable | |
| impact_scope | text nullable | |
| status | varchar(20) | `draft \| submitted \| approved \| rejected \| implemented`, default `draft` |
| requested_by | bigint FK → users | nullable, nullOnDelete |
| approved_by | bigint FK → users | nullable, nullOnDelete |
| created_by, updated_by, deleted_at, timestamps | | |

### pm_project_members (ERD PROJECT_MEMBER)

| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| project_id | bigint FK → content_detail_projects | cascadeOnDelete |
| user_id | bigint FK → users | cascadeOnDelete; unique(project_id, user_id) |
| role | varchar(30) | `pic \| member \| approver` |
| status | varchar(20) | `active \| inactive`, default `active` |
| created_by, updated_by, deleted_at, timestamps | | |

Sync rule: `pic_user_id` of the project and every `pm_project_stages.pic_user_id` are auto-upserted as `role = pic` rows (keeps S7 "Total PIC" accurate without manual entry). Manual CRUD allowed on top.

## Enums (canonical)

| Enum | Values |
|---|---|
| project status | `draft, pending_approval, active, on_hold, completed, cancelled` |
| project_stage status | `pending, in_progress, completed, skipped` |
| task status | `pending, in_progress, done, cancelled` |
| task priority | `low, medium, high` |
| deliverable status | `pending, wip, completed` |
| document status | `active, superseded` |
| approval status | `pending, approved, rejected` |
| milestone status | `pending, completed, missed` |
| change_request status | `draft, submitted, approved, rejected, implemented` |
| member role | `pic, member, approver` |

## Derived Values (no stored columns)

| Value | Derivation | Consumed by |
|---|---|---|
| `current_stage` | first `pm_project_stages` ordered by `sequence` with `status != 'completed'` (null when all done) | S1 Stage column |
| project `progress` | average of stage `progress`; stage `progress` = `tasks done / tasks total * 100` (100 when stage `completed`; 0 when no tasks and `pending`) | S1 Progress column |
| Duration (stage & project) | `(end_date - start_date).days + 1` (inclusive — matches S4 "7 Hari" and S7 "122 Hari") | S4 Durasi, S7 Total Durasi |
| Setup completeness | see below | S7 activation gate, wizard resumability |
| S7 `dokumen_wajib` | count of `pm_deliverables` where `is_required = true` | review endpoint |
| S7 `total_pic` | distinct `users` across `pm_project_members(role = pic)` | review endpoint |
| S7 `total_approval` | count of `pm_approvals` rows on the project | review endpoint |
| S7 `timeline_preview` | `[{no, name, start_date, end_date}]` per stage ordered by `sequence` | Gantt |

### Setup completeness (free navigation, decision 5)

Returned by `GET /projects/{project_id}/setup-state`. No wizard-step column; all rules derived:

| Step | Complete when |
|---|---|
| 1 | `title`, `client_id`, `category_id`, `pic_user_id`, `target_end_date` all set |
| 2 | at least one `pm_project_stages` row |
| 3 | project `start_date` + `target_end_date` set **and** every stage has `start_date` + `end_date` |
| 4 | every stage has `is_configured = true` |

`ready_to_activate` = steps 1-4 all complete; `missing[]` lists machine-readable gaps (e.g. `stage_3.missing_dates`) so the FE can navigate the user to the right step.

## Relationship Diagram (delta subset)

```
content_detail_projects ||--o{ pm_project_stages : has
content_detail_projects ||--o{ pm_documents : has
content_detail_projects ||--o{ pm_milestones : has
content_detail_projects ||--o{ pm_change_requests : has
content_detail_projects ||--o{ pm_project_members : has
pm_workflow_stages      ||--o{ pm_project_stages : instantiates (nullable)
pm_project_stages       ||--o{ pm_tasks : has
pm_project_stages       ||--o{ pm_deliverables : has
pm_project_stages       ||--o{ pm_documents : has (nullable)
pm_project_stages       ||--o{ pm_approvals : requires
pm_project_stages       ||--o{ pm_project_stages : depends_on (self, nullable)
```
