# 005 — Approval Rework: Activation, Stage Clearance & Change Request

**Status**: Implemented (2026-10-06) in project-service + migration-service. Decisions below are confirmed.

## Problem

The current approval model (specs/004) only covers one gate, and points it in the wrong direction:

1. **Activation** — `activate` only goes `pending_approval` when stage 1 has `requires_approval`; it is not an independent project-level gate.
2. **Stage** — `requires_approval` gates a stage's *entry* (approve-before-start), but the product wants it to gate *exit* (approve-before-complete / clearance).
3. **Change request** — `pm_change_requests` is a shell (no payload, no endpoints, no approval).

This spec replaces the model with **one approver, three gates**.

## The model — one approver, three gates

- **Approver = a fixed admin role** (`super_admin`, `admin_pusat` — enforced on decision routes via `check.role:super_admin,admin_pusat`; confirmed Q1).
- **Worker = admin today, client later** (client role + limited RBAC is a future epic — out of scope here, but the design leaves room for it).

| # | Gate | Trigger | Who approves | Outcome on approve |
|---|---|---|---|---|
| 1 | Activation | `activate` | fixed admin role | `draft → active` (always via `pending_approval`) |
| 2 | Stage clearance | stage `requires_approval=true` reached completion | fixed admin role | stage → `completed` (auto-advance) |
| 3 | Change request | admin records a CR (client suggested) | fixed admin role | CR → `approved` → applied to project |

A stage with `requires_approval=false` completes directly — no clearance. A project is `completed` when **all** stages are complete; the flag only controls whether a given stage needs clearance.

## Roles

| Role | Can fill (tasks/deliverables) | Can approve | Can create CR |
|---|---|---|---|
| `super_admin`, `admin_pusat` | ✅ | ✅ | ✅ |
| `admin_pustik` | ✅ | ❌ (403) | ✅ |
| client (future) | ✅ (future) | ❌ | suggests via admin |

⚠️ **Approver role set (confirmed)**: `super_admin` + `admin_pusat` (the central roles). `admin_pustik` can fill but not approve.

## User stories

### US1 — Activation always requires approval

As an admin, activating a complete project routes it through approval before it goes live, so nothing activates without a central sign-off.

- **Given** a complete `draft` project, **When** I `activate`, **Then** status = `pending_approval` (unconditionally, regardless of stage flags).
- **Given** a `pending_approval` project, **When** an approver approves, **Then** status = `active`, first stage → `in_progress`, PIC synced, global search updated.
- **Given** a `pending_approval` project, **When** an approver rejects, **Then** status = `draft` (rework + re-activate).
- **Given** a non-approver admin, **When** they try to approve/reject, **Then** 403.

### US2 — Stage clearance for flagged stages

As an admin, a stage marked `requires_approval` cannot be marked complete by just anyone — an approver must clear it.

- **Given** a `requires_approval=true` stage in `in_progress` with all requirements done, **When** a non-approver calls `complete`, **Then** 403.
- **Given** the same stage, **When** an approver calls `complete`, **Then** stage → `completed` with `approved_by`/`approved_at` recorded, next stage auto-advances.
- **Given** a `requires_approval=false` stage, **When** any admin calls `complete`, **Then** stage → `completed` (no approval record).
- **Given** incomplete requirements, **When** `complete` is called, **Then** 422 with `missing[]` blockers (unchanged from 004).

### US3 — Change request with approval

As an admin, I record a client's requested change and it must be approved before it touches the project.

- **Given** an admin recording a CR on behalf of a client, **When** created, **Then** status = `pending_approval`, `requested_by` = client id, `proposed_changes` JSON stored.
- **Given** a `pending_approval` CR, **When** an approver approves, **Then** status = `approved`.
- **Given** an approved CR, **When** admin applies it, **Then** the proposed fields are written to the project and status = `applied`.
- **Given** an approver rejects, **Then** status = `rejected` (with reason).

## Data-model deltas

### `content_detail_projects` (project-service)
- Add `activated_by` (nullable FK users) + `activated_at` (timestamp) — records the activation decision (gate 1).
- `status` enum unchanged (`draft, pending_approval, active, on_hold, completed, cancelled`) — `pending_approval` is now *always* the post-activate state.

### `pm_project_stages`
- **Repurpose** `requires_approval`: now "requires clearance approval to complete" (exit gate), not entry gate.
- Add `approved_by` (nullable FK users) + `approved_at` (timestamp) — the clearance decision (gate 2).

### `pm_change_requests`
- Add `proposed_changes` (JSON) — `{field: new_value}` mapping to the project's configured fields (e.g. `target_end_date`, `budget`, `pic_user_id`, `description`, stage dates).
- Add `client_id` (nullable FK `pm_clients`) — the suggesting client (gate 3), alongside `requested_by` (admin who recorded it).
- Add `approved_at` (timestamp) + `applied_at` (timestamp).
- `status` flow: `draft → pending_approval → approved | rejected → applied`.

### `pm_approvals` — removed (confirmed)
With "fixed role, no designated approver", the per-stage approver-assignment table (and the step-4 "Kartu Approval" UI) was redundant. Removed: migration `2026_09_24_000005`, model `PmApproval`, `PmApprovalService`, `StageApproverController`, `ProjectApprovalController`, `PmApprovalResource`, `ProjectApprovalResource`, `SyncApproversRequest`, `ApproveApprovalRequest`, `RejectApprovalRequest`, approval routes, and the setup-state `approvers_missing` gate. Decision history lives on the entities (`activated_by`, `approved_by`) + the spatie activity log. This reverses the designated-approver part of 003/004.

## API contract (project-service, admin JWT guard)

Activation approval uses a new `check.role:super_admin,admin_pusat` middleware (⚠️ role set TBD); fill endpoints stay under the existing 3-role guard.

| Action | Method + Path | Notes |
|---|---|---|
| Activate | `POST /projects/{id}/activate` | always → `pending_approval` (setup must be complete) |
| Approve activation | `POST /projects/{id}/activate/approve` | approver role; → `active` + side effects |
| Reject activation | `POST /projects/{id}/activate/reject` | approver role; → `draft` |
| Complete stage | `POST /projects/{id}/stages/{sid}/complete` | 403 if `requires_approval` and caller not approver; records `approved_by`/`approved_at` |
| Skip / Reopen | unchanged from 004 | |
| Create CR | `POST /projects/{id}/change-requests` | body: `{title, reason, impact_scope, client_id?, proposed_changes}` → `pending_approval` |
| Approve CR | `POST /change-requests/{id}/approve` | approver role |
| Reject CR | `POST /change-requests/{id}/reject` | approver role; `{notes}` required |
| Apply CR | `POST /change-requests/{id}/apply` | ⚠️ proposed dedicated apply (vs manual `PUT /projects`) |
| List CRs | `GET /projects/{id}/change-requests` | optional `?status=` |

## Open items (resolved during implementation 2026-10-06)

1. **Approver role set** — `super_admin, admin_pusat` (route middleware `check.role` on all decision endpoints; service-level check in `StageLifecycleService::complete` since its route is shared).
2. **`pm_approvals` deprecation** — dropped entirely (see above).
3. **CR apply** — dedicated `POST /change-requests/{id}/apply` (approver role); writes the diff + marks `applied`.
4. **"Awaiting clearance" status** — no new enum; the stage stays `in_progress` until the approver completes it. Reopen clears `approved_by`/`approved_at` so a re-run needs fresh clearance.
5. **CR field whitelist** — `title, description, start_date, target_end_date, budget, location, duration, organization_name, organization_address, client_id, partner_id, category_id, pic_user_id` + `stages: [{id, start_date?, end_date?}]` (unknown keys → 422).
6. **`requested_by` vs client** — `requested_by` = recording admin (FK users, unchanged); suggesting client rides on the new `client_id` (FK `pm_clients`).

## Out of scope

- Client portal / client login with limited RBAC (future epic — the design leaves `client_id` + role hooks ready).
- Sequential/sign-off-chain approvals (decisions are parallel/role-based).
- Milestones (separate feature).
