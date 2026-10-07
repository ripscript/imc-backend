# Project Service API Contracts — Approval & Stage Completion

**Service**: `project-service`

**Base Path**: `/project-service/api/` (via Nginx)

**Auth**: JWT (`admin` guard) + `check.role:super_admin,admin_pusat,admin_pustik` (unchanged from 003). Decision endpoints additionally enforce the designated-approver rule (Q4).

**Response Envelope**: `{success: bool, message: string, data: mixed|null, errors: mixed|null}` (unchanged).

**Spec**: [spec.md](spec.md) · [data-model.md](data-model.md)

All mutations write spatie `activity_log` with `properties.project_id`.

## Changed Endpoints

### POST projects/{project_id}/activate

**Behavior change** (transaction):

1. Re-derive completeness (unchanged — 422 with `missing[]` when incomplete, including `stage_{id}.approvers_missing`).
2. If the first stage `requires_approval = true` **and** has approval rows → `status = pending_approval`; stamp `requested_at = now()`, `requested_by = caller` on every first-stage approval row; sync PIC members. **First stage stays `pending`.**
3. Otherwise → `status = active`; first stage → `in_progress` (current behavior).
4. Activity log.

**Response**: `{data: ProjectResource}` → 200 · **Errors**: 422 (incomplete), 409 (not `draft`).

### PUT projects/{project_id}/stages/{stage_id}

**Behavior change**: when the project's `status ≠ draft`, the `status` field is rejected (422). `status` transitions during execution go through `complete`/`skip` only. All other fields (name, description, dates, pic, allow_skip, allow_repeat, requires_approval, depends_on, nested tasks/deliverables) remain editable as before.

## New Endpoints

### POST projects/{project_id}/stages/{stage_id}/approvals/{approval_id}/approve

**Auth**: caller `id === approval.approver_id`, or `super_admin` (Q4).

**Body**: `{notes?: string}`

**Behavior** (transaction):
1. Resolve approval scoped to the stage.
2. 409 if `status !== pending`.
3. Set `status = approved`, `approved_by = caller`, `approved_at = now()`, `notes` (optional), `updated_by`.
4. If this stage is the first stage of a `pending_approval` project and **all** its approvals are now `approved` → `project.status = active`, first stage `status = in_progress` (US3). If this stage is a later `pending` stage and all its approvals are now `approved` → `stage.status = in_progress` (US7.2).
5. Activity log.

**Response**: `{data: PmApprovalResource, project_status?, stage_status?}` → 200 · **Errors**: 403 (not approver), 409 (already decided), 404.

### POST projects/{project_id}/stages/{stage_id}/approvals/{approval_id}/reject

**Auth**: same as approve.

**Body**: `{notes: string}` (required)

**Behavior** (transaction):
1. Resolve approval scoped to the stage.
2. 409 if `status !== pending`.
3. Set `status = rejected`, `approved_by = caller`, `approved_at = now()`, `notes`, `updated_by`.
4. If this stage is the first stage of a `pending_approval` project → `project.status = draft`; reset **every** first-stage approval row to `status = pending` with `requested_*`/`approved_*`/`notes` cleared (rejection recorded in the activity log) (US4).
5. Activity log.

**Response**: `{data: PmApprovalResource, project_status}` → 200 · **Errors**: 403, 409, 422 (missing notes), 404.

### GET projects/{project_id}/approvals

**Query**: `page`, `limit`, optional `status` filter (`pending|approved|rejected`).

**Response**: paginated `[{approval_id, project_stage_id, stage_name, stage_sequence, approver: {id, name}, status, requested_at, approved_at}]` across the project's stages — backs a "Project Menunggu Approval" view.

**Status**: 200

### POST projects/{project_id}/stages/{stage_id}/complete

**Auth**: existing role guard.

**Body**: none.

**Behavior** (transaction) — US5/US7:
1. Project must be `active` (else 422).
2. Stage must be `in_progress` (else 422).
3. Gates, each yielding a machine-readable `missing[]` entry when violated:
   - dependency: `depends_on_project_stage_id` must be `completed`/`skipped`
   - tasks: none may be `pending`/`in_progress` (only `done`/`cancelled`)
   - deliverables: every `is_required = true` deliverable must be `completed`
   - approvals: if `requires_approval = true`, all rows must be `approved`
4. On pass: `status = completed`, `completed_at = now()`, `updated_by`, activity log.
5. Auto-advance: next stage by `sequence` not `completed`/`skipped` → `in_progress`; if it `requires_approval`, stamp its approval rows `requested_at`/`requested_by`. If none remain → `project.status = completed`.

**Response**: `{data: {stage: ProjectStageResource, project_status, next_stage: ProjectStageResource|null}}` → 200 · **Errors**: 422 (with `missing[]`), 404.

### POST projects/{project_id}/stages/{stage_id}/skip

**Auth**: existing role guard.

**Behavior** (transaction):
1. Project `active`; stage `in_progress` (else 422).
2. `allow_skip = true` (else 422).
3. `status = skipped` (no `completed_at`), activity log.
4. Auto-advance identical to `complete`.

**Response**: `{data: {stage, project_status, next_stage}}` → 200 · **Errors**: 422, 404.

### POST projects/{project_id}/stages/{stage_id}/reopen

**Auth**: existing role guard.

**Behavior** (transaction):
1. Project `active`; stage `completed` (else 422).
2. `allow_repeat = true` (else 422).
3. `status = in_progress`, `completed_at = null`, activity log. Children and approvals untouched.

**Response**: `{data: {stage, project_status}}` → 200 · **Errors**: 422, 404.
## State Machine (endpoint → transition)

| Endpoint | Guard | From → To |
|---|---|---|
| `POST .../activate` | complete, `draft`, first-stage `requires_approval` | `draft → pending_approval` |
| `POST .../activate` | complete, `draft`, no first-stage approval | `draft → active` |
| `POST .../approvals/{id}/approve` | last pending first-stage approval | `pending_approval → active` |
| `POST .../approvals/{id}/reject` | first-stage approval | `pending_approval → draft` |
| `POST .../stages/{id}/complete` | gates pass | stage `in_progress → completed` (→ next stage `in_progress` / project `completed`) |
| `POST .../stages/{id}/skip` | `allow_skip` | stage `in_progress → skipped` (→ advance) |
| `POST .../stages/{id}/reopen` | `allow_repeat`, `completed` | stage `completed → in_progress` |

## Master-Service Dependencies

None new — approvers are `users` (user-service) and document types already flow through 003's endpoints.
