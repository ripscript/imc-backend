# Project Service API Contracts

**Service**: `project-service`
**Base Path**: `/project-service/api/` (via Nginx)
**Auth**: JWT (`admin` guard) + `check.role:super_admin, admin_pusat, admin_pustik` on all routes below
**Response Envelope**: `{success: bool, message: string, data: mixed|null, errors: mixed|null}`
**Pagination Shape**: `{data: [...], meta: {page: int, limit: int, total: int, total_page: int}}`
**Spec**: [spec.md](spec.md) · [data-model.md](data-model.md)

Routes are keyed by `project_id` (numeric `content_detail_projects.id`; constrained to digits, so legacy `project_no` keys 404). `project_no` (`IMC.{Y}-{000}`) remains a display-only field in responses plus a sort/search column. All mutations write spatie `activity_log` with `properties.project_id`. Ids of sub-entities are their own `id`.

## Existing Routes — Contract Deltas

### GET projects/
**Deltas**: new query params `status`, `client_id`, `category_id`, `my` (bool — projects where caller is PIC/member). Each row (`ProjectListResource`) gains:
```
{ ..., current_stage: {id, name, sequence, status} | null, progress: int }
```
`sort_by` accepts: `title, project_no, client, stage, progress, status, updated_at`.

### GET projects/{project_id}
**Deltas**: replace placeholder keys with real data:
```
{
  ...existing fields,
  current_stage: {id, name, sequence, status} | null,
  stages: [ProjectStageResource],
  custom_fields: {code: value}   // via master-service custom-field-values contract
}
```
`ProjectStageResource` (used everywhere below):
```
{ id, workflow_stage_id, name, description, sequence, status, progress, pic: {id, name, email},
  start_date, end_date, completed_at, depends_on_project_stage_id, is_required, allow_skip,
  allow_repeat, requires_approval, is_configured }
```

### POST projects/ (S2 step 1 — create draft)
**Body**: `{title, client_id, partner_id?, category_id, pic_user_id, target_end_date, budget?, description?, attachments?: [{file_url}], tag_id?[], ...existing CMS fields}`
**Changes**: attachments validated — extension in `pdf,doc,docx,jpg,png`, count max 5, size max 5 MB (size known from file-service upload response; FE passes `file_size_kb?` per attachment for validation). Creates project with `status = draft`.
**Status**: 201

### PUT projects/{project_id} (S2 step 1 — free navigation update)
Partial update (existing semantics). Any step-1 field updatable while `status = draft`. **Status**: 200

## Phase 1 — S1 Project List

### GET projects/setup-state is NOT here; see Phase 2. No other new endpoints. Contract work is the `GET projects/` delta above.

## Phase 2 — S2 Wizard Step 1

### GET projects/{project_id}/setup-state
**Response** (decision 5 — free navigation):
```
{ steps: {1: bool, 2: bool, 3: bool, 4: bool},
  ready_to_activate: bool,
  missing: ["stage_3.missing_dates", ...] }
```
**Status**: 200

## Phase 3 — S3 Wizard Step 2 (Stages)

### GET projects/{project_id}/stages
**Response**: `{data: [ProjectStageResource], meta: {...}}` ordered by `sequence`. **Status**: 200

### PUT projects/{project_id}/stages
Bulk **replace-all** sync (same semantics as master-service workflow stages). One call covers both tabs:
- "Gunakan Workflow": pass `workflow_id`; `stages` entries may carry `workflow_stage_id` + overrides
- "Buat Manual": omit `workflow_id` (or null); stages are name-only manual entries

**Body** (Phase 10 — FE submits the full configured tree; what is sent is stored):
```
{ workflow_id?: int|null,
  stages: [ { id?: int,               // keep existing row (else create)
              workflow_stage_id?: int|null,   // provenance link only
              name: string,
              sequence: int,           // >= 1, unique within request
              is_required?: bool, allow_skip?: bool, allow_repeat?: bool,
              description?, requires_approval?,
              tasks?: [ { id?: int, title: string (required for new rows),
                          description?, status?, priority?, assignee_id?, due_date? } ],
              deliverables?: [ { id?: int, name: string (required for new rows),
                                 document_type_id?, is_required?, status?, due_date? } ] } ] }
```
**Behavior**: sets `project.workflow_id`; rows not present are soft-deleted; `sequence` reindexed; new rows get `status = pending`, `is_configured = false`; dependency links of surviving rows are preserved (422 if a surviving row depends on a deleted row). Nested `tasks[]`/`deliverables[]` replace the stage's children when the keys are present (id-matched: keep + update, create new, soft-delete missing; explicit `[]` clears). `workflow_stage_id` is exists-validated and cross-workflow-checked but never read for content.
**Status**: 200 · **Errors**: 422 on duplicate/missing `sequence`, unknown `workflow_stage_id`, foreign child `id`, child missing `title`/`name`

## Phase 4 — S4 Wizard Step 3 (Timeline)

### PUT projects/{project_id}/timeline
**Body**:
```
{ start_date: "2026-06-01",
  target_end_date: "2026-09-30",
  stages: [ { id: int, start_date: "2026-06-03", end_date: "2026-06-09",
              depends_on_project_stage_id?: int|null } ] }
```
**Behavior**: sets project dates; per-stage dates + dependency. Durasi derived, returned in response.
**Response**: `{data: {start_date, target_end_date, total_duration_days, stages: [{id, name, start_date, end_date, duration_days, depends_on_project_stage_id}]}}`
**Validation (422)**: end < start (project or stage); stage dates outside project range; `depends_on` self-reference or cycle; `depends_on` stage `start_date` later than dependent stage `start_date`.
**Status**: 200

## Phase 5 — S5 Wizard Step 4 (Per-Stage Configuration)

### GET projects/{project_id}/stages/{stage_id}
**Response**: `{data: { ...ProjectStageResource, tasks: [...], deliverables: [...], documents: [...], approvers: [...]}}`
Reads never create rows — lists are empty until an explicit save submits them.
- `task`: `{id, title, description, status, priority, assignee: {id, name}? , due_date}`
- `deliverable`: `{id, name, document_type_id, is_required, status, due_date}`
- `document`: `{id, document_type_id, file_name, file_path, file_size_kb, version, status, uploaded_by: {id, name}, uploaded_at}`
- `approvers`: `[{approval_id, sequence, approver: {id, name, email}, status}]`
**Status**: 200

### PUT projects/{project_id}/stages/{stage_id}
**Body**: `{name?, description?, start_date?, end_date?, status?, pic_user_id?, allow_skip?, allow_repeat?, requires_approval?, depends_on_project_stage_id?, tasks[]?, deliverables[]?}` (nested sets follow the same id-matched replace-all rules as S3 sync)
**Behavior**: partial update; sets `is_configured = true`. `requires_approval = false` soft-deletes the stage's `pm_approvals` rows.
**Status**: 200

### PUT projects/{project_id}/stages/configuration (bulk save — preferred for 30+ stages)
**Body**: `{stages: [{id, <same per-stage fields as single PUT>?, tasks[]?, deliverables[]?, approvers[]?}]}` — only the listed stages get content updates; `approvers[]` follows the per-stage approver sync rules (requires `requires_approval = true`, ordered 1..n, removed rows soft-deleted; explicit `[]` clears).
**Behavior** (one transaction, all-or-nothing): loops the single-PUT logic per entry, then sweeps `is_configured = true` for **all** of the project's stages — one call declares step 4 done even when only a subset was sent. Domain failures return indexed keys (`stages.{i}.…`, e.g. `stages.2.tasks.0.title`) so FE can jump to the offending stage.
**Response**: `{data: {stages: [...ProjectStageResource ordered by sequence], setup_state: {steps, ready_to_activate, missing}}}`
**Validation (422)**: `stages` required, min 1; `stages.*.id` required, distinct, must belong to the project; per-entry field rules mirror the single PUT.
**Status**: 200

### Tasks
- `GET projects/{project_id}/stages/{stage_id}/tasks` → paginated list
- `POST projects/{project_id}/stages/{stage_id}/tasks` — body `{title, description?, priority?, assignee_id?, due_date?}` → 201
- `PUT projects/{project_id}/stages/{stage_id}/tasks/{task_id}` — partial → 200
- `DELETE projects/{project_id}/stages/{stage_id}/tasks/{task_id}` → 200
- `PATCH projects/{project_id}/tasks/{task_id}/toggle` — flips `pending ↔ done`; response `{status, stage_progress, done_count, total_count}` (drives "x/y Selesai") → 200

### Deliverables
- `GET|POST projects/{project_id}/stages/{stage_id}/deliverables` — body `{name, document_type_id?, is_required?, due_date?, status?}`
- `PUT|DELETE projects/{project_id}/stages/{stage_id}/deliverables/{deliverable_id}`

### Documents
- `GET|POST projects/{project_id}/stages/{stage_id}/documents` — POST body `{document_type_id?, file_name, file_path, file_size_kb?, deliverable_id?}`. **Versioning** (data-model rule): same `file_name` + existing `active` row → new row `version = n+1`, previous → `superseded`.
- `DELETE projects/{project_id}/stages/{stage_id}/documents/{document_id}`
- Uploads go through **file-service** `POST /upload` first; this API stores the returned URL in `file_path`. Extension/size enforced against `pm_document_types.allowed_extension` / `max_size_mb` when `document_type_id` is given (default rule: `pdf,doc,docx,jpg,png`, max 5 MB).

### Approvers (pre-created approval rows — decision 3)
- `GET projects/{project_id}/stages/{stage_id}/approvers` → list as in stage detail
- `PUT projects/{project_id}/stages/{stage_id}/approvers` — body `{approvers: [{approver_id, sequence?}]}` → bulk upsert `pm_approvals` (`status = pending`, `approver_id` set), soft-delete removed. **Requires** `requires_approval = true` (else 422). → 200

## Phase 6 — S8 Wizard Step 5 (Review & Activate)

### GET projects/{project_id}/review
**Response**:
```
{ summary: {project_no, name, client: {id, name}, pic: {id, name},
            workflow: {id, name, version} | null, total_stage: int,
            period: {start_date, target_end_date}, total_duration_days: int,
            dokumen_wajib: int, total_pic: int, total_approval: int},
  timeline_preview: [ {no, name, start_date, end_date} ],
  setup: {steps: {...}, ready_to_activate: bool, missing: [...]} }
```
**Status**: 200 (always 200; incomplete setup reported via `setup`)

### POST projects/{project_id}/activate
**Behavior** (transaction):
1. Re-derive completeness; 422 with `missing[]` when incomplete — including `stage_{id}.approvers_missing` when a stage has `requires_approval = true` but zero approval rows (Phase 8 gate)
2. `status: draft → active`; stamp `start_date` if unset (use min stage start)
3. First stage → `in_progress`; its `pm_approvals` rows get `requested_at = now()`, `requested_by = auth user`
4. Project + all stage PICs upserted into `pm_project_members` (role `pic`)
5. Activity log entries
**Response**: `{data: ProjectResource}` → 200 · **Errors**: 422 (incomplete/invalid state), 409 (already active)

Simpan Draft = existing `PUT projects/{project_id}` (or just closing the wizard); no dedicated endpoint.

## Phase 7 — Cross-Cutting

### GET projects/{project_id}/activities
**Query**: `page`, `limit`
**Response**: paginated `[{id, activity_type (event), description, user: {id, name}, project_stage_id, created_at}]` from spatie `activity_log` filtered by `properties.project_id`. **Status**: 200

### GET projects/{project_id}/members · POST projects/{project_id}/members · DELETE projects/{project_id}/members/{member_id}
Body: `{user_id, role?}`. Auto-synced `pic` rows are not deletable while referenced (422).

### Custom fields on projects
Values read/write via existing master-service endpoints `GET custom-field-values?entity_type=project&entity_id={id}` and `PUT custom-field-values`. Project detail merges them into `custom_fields`. No new project-service endpoints.

## Master-Service Dependencies (existing, read-only for this spec)

| Endpoint | Used by |
|---|---|
| `GET clients/, partners/, project-categories/, document-types/` | S2 dropdowns |
| `GET workflows/`, `GET workflows/{id}/stages` | S3 workflow picker + Preview |
| `GET custom-field-values?entity_type=project&entity_id=` | project detail |
