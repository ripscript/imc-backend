# Feature Specification: Project Approval & Stage Completion

**Feature Branch**: `004-project-approval-and-stage-completion`

**Created**: 2026-10-05

**Status**: Draft

**Spec 003**: [003-project-management-e2e](../003-project-management-e2e/spec.md)

## Input

> "Do we already have project approval and an update-stage process to mark stages done? The idea is: whenever a client/admin activates a new project, it should ask for an approval. And we want an update process for every project stage to mark them done."

## Current State (as-built, verified 2026-10-05)

- **Approval schema exists and is execution-ready.** `pm_approvals` already carries `approver_id` (designated), `requested_by`, `approved_by`, `status` (`pending|approved|rejected`), `notes`, `requested_at`, `approved_at`. Rows are **pre-created designated-approver rows** at wizard step 4 via `PmApprovalService::sync`. There is **no decide endpoint** — nothing writes `approved_by`/`approved_at`/`status = approved|rejected`.
- **Activation does not wait for approval.** `POST /projects/{id}/activate` → `ProjectActivationService::activate` flips `draft → active` immediately and stamps `requested_at`/`requested_by` on the first stage's approvals, but never enters `pending_approval`.
- **Stage "done" is a bare status field.** `PUT /projects/{id}/stages/{stage_id}` accepts `status ∈ pending,in_progress,completed,skipped` and stamps `completed_at`; nothing gates it (tasks, dependencies, or approvals).

Both features were explicitly deferred in 003's "Out of Scope": *"Approval monitoring (including approve/reject decision endpoints)"* and *"Task execution flows after activation (stage lifecycle automation)"*. This spec lands them on the existing schema.

## Design Decisions (confirmed 2026-10-05)

1. **Approval gates stage *start*, not a separate project-level approver list.** The existing stage-level `requires_approval` + designated approvers are reused. "Activate → asks approval" = dispatch the **first stage's** approval requests and hold the project in `pending_approval` until all of them are approved.
2. **Activation is two-step.** `activate` sets `status = pending_approval` when the first stage `requires_approval` (and has approvers); otherwise it stays the current one-step `draft → active`. On all-approved, the project auto-flips `pending_approval → active` and the first stage `pending → in_progress`.
3. **Approval decisions are independent (parallel), ordered only for display.** `sequence` is display order (003 delta #5), not a sign-off chain. A stage is approved when **every** row is `approved`; **any** `reject` rejects it.
4. **Only the designated approver decides** (`approval.approver_id === caller`); `super_admin` may override.
5. **Reject reverts the project to `draft`** and resets the first stage's approval rows to `pending` (rejection history lives in the activity log); the admin edits and re-activates.
6. **Stage completion is a dedicated, gated action** (`complete`), separate from the wizard's manual `status` field. After activation (`status ≠ draft`), `status` is no longer writable through `PUT .../stages/{id}`; execution transitions go through `complete`/`skip`/`reopen`.
7. **Completion auto-advances** to the next stage (dispatching its approval requests when it requires approval); completing the last required stage completes the project.
8. **No schema changes.** `pm_approvals` and `pm_project_stages` are execution-ready (003). Project completion is `status = completed`; `completed_at` is **derived** (max stage `completed_at`).
9. **Reopen for repeatable stages.** A stage with `allow_repeat = true` may be reopened after `completed` (back to `in_progress`, clearing `completed_at`); reopening does not rewind later stages, reset children, or re-dispatch approvals.

## User Scenarios & Testing *(mandatory)*

### User Story 1 — Activate requests approval

As an admin, I activate a complete draft project whose first stage requires approval, and the project moves to `pending_approval` (not `active`) with approval requests dispatched, so it cannot start until approved.

**Why this priority**: The approval gate is the entry point of execution; everything else (decisions, completion) depends on the project being in the right state.

**Independent Test**: Seed a complete draft with first stage `requires_approval=true` + 2 approvers; activate; assert `status = pending_approval`, both approval rows have `requested_at`/`requested_by`; activate again → 409.

**Acceptance Scenarios**:

1. **Given** a complete draft whose first stage `requires_approval = true` with approval rows, **When** `POST /activate` runs, **Then** `status = pending_approval`, the first stage stays `pending`, and every first-stage approval row gets `requested_at = now()` and `requested_by = caller`.
2. **Given** a complete draft whose first stage does **not** require approval (or has no approvers), **When** activated, **Then** `status = active` and the first stage is `in_progress` (unchanged from 003).
3. **Given** an incomplete project, **When** activated, **Then** 422 with `missing[]` (unchanged from 003).
4. **Given** a project not in `draft`, **When** activated again, **Then** 409 and nothing changes.

---

### User Story 2 — Approver decides (approve / reject)

As a designated approver, I approve or reject a requested stage approval with optional notes, so the stage's start is gated on my decision.

**Why this priority**: The decision is the actual "asks for approval" payload; without it the `pending_approval` state is a dead end.

**Independent Test**: Request an approval, approve it as the approver, assert `status = approved`, `approved_by`, `approved_at` set; reject a second, assert `status = rejected` and notes persisted.

**Acceptance Scenarios**:

1. **Given** a requested approval row, **When** the designated approver approves it (`notes` optional), **Then** the row gets `status = approved`, `approved_by = caller`, `approved_at = now()`.
2. **Given** a requested approval row, **When** the designated approver rejects it (`notes` required), **Then** the row gets `status = rejected`, `approved_by`, `approved_at`, and `notes`.
3. **Given** a caller who is not the designated approver (and not `super_admin`), **When** they attempt a decision, **Then** 403.
4. **Given** an approval row that is not `pending`, **When** decided again, **Then** 409 and nothing changes.

---

### User Story 3 — Auto-activation on all approved

As a project, when every first-stage approval is approved, I automatically become `active` and my first stage becomes `in_progress`.

**Why this priority**: Closes the loop on activation; no extra admin action should be needed once approval is unanimous.

**Independent Test**: Activate a project (→ `pending_approval`), approve all first-stage rows, assert `status = active` and first stage `in_progress`.

**Acceptance Scenarios**:

1. **Given** a `pending_approval` project whose first stage has N approval rows, **When** the last of them is approved, **Then** `status = active` and the first stage becomes `in_progress` in the same transaction.
2. **Given** a `pending_approval` project, **When** any approval is approved but others remain pending, **Then** the project stays `pending_approval`.

---

### User Story 4 — Reject reverts to draft

As an admin, when a first-stage approval is rejected, the project reverts to `draft` so I can correct the proposal and re-activate.

**Why this priority**: A rejected activation must not leave the project stranded in a non-editable state.

**Independent Test**: Activate (→ `pending_approval`), reject one row, assert `status = draft` and all first-stage approval rows reset to `pending`.

**Acceptance Scenarios**:

1. **Given** a `pending_approval` project, **When** any first-stage approval is rejected, **Then** `status = draft`, and every first-stage approval row is reset to `status = pending` with `requested_*`/`approved_*`/`notes` cleared (rejection recorded in the activity log).
2. **Given** the reverted draft, **When** re-activated after edits, **Then** the approval requests are re-dispatched (US1).

---

### User Story 5 — Complete a stage (gated)

As an admin, I mark an in-progress stage done only after its prerequisites are satisfied, so a stage cannot be completed prematurely.

**Why this priority**: This is the "mark a stage done" process the input asks for, with the guardrails that were missing.

**Independent Test**: With an in-progress stage whose tasks/deliverables/approvals are incomplete, complete → 422 naming the blockers; fix them; complete → `status = completed`, `completed_at` set.

**Acceptance Scenarios**:

1. **Given** an `in_progress` stage whose dependency stage is not yet `completed`/`skipped`, **When** complete is requested, **Then** 422 names the unsatisfied dependency.
2. **Given** an `in_progress` stage with any task not `done` (or `cancelled`), **When** complete is requested, **Then** 422 names the open tasks.
3. **Given** an `in_progress` stage with a required deliverable not `completed`, **When** complete is requested, **Then** 422 names the open deliverables.
4. **Given** an `in_progress` stage with `requires_approval = true` and any approval not `approved`, **When** complete is requested, **Then** 422 names the pending approvals.
5. **Given** an `in_progress` stage satisfying all gates, **When** complete is requested, **Then** `status = completed`, `completed_at = now()`, activity logged.
6. **Given** a project not `active`, **When** complete is requested, **Then** 422 (execution transitions only after activation).

---

### User Story 6 — Skip a stage

As an admin, I skip a stage that allows skipping, so optional stages do not block progress.

**Why this priority**: `allow_skip` was captured in the data model but never acted on at runtime.

**Independent Test**: A stage with `allow_skip = true` in progress → skip → `status = skipped`; a stage with `allow_skip = false` → 422.

**Acceptance Scenarios**:

1. **Given** an `in_progress` stage with `allow_skip = true`, **When** skip is requested, **Then** `status = skipped` (no task/approval gates), `completed_at` not set.
2. **Given** a stage with `allow_skip = false`, **When** skip is requested, **Then** 422.

---

### User Story 7 — Auto-advance & project completion

As a project, when a stage completes or is skipped, execution advances to the next stage (dispatching its approval requests when required), and completing the last stage completes the project.

**Why this priority**: Ties individual stage transitions into the whole-project lifecycle.

**Independent Test**: Two-stage project; complete stage 1 → stage 2 becomes `in_progress` (its approvals requested if required); complete stage 2 → project `status = completed`.

**Acceptance Scenarios**:

1. **Given** a stage is completed/skipped and a later stage exists, **When** the transition commits, **Then** the next stage (by `sequence`, not yet `completed`/`skipped`) becomes `in_progress`, and its approval rows (if `requires_approval`) get `requested_at`/`requested_by`.
2. **Given** a later stage that requires approval, **When** it becomes current, **Then** it stays `pending` until all its approvals are `approved`, then becomes `in_progress` (reusing the US3 auto-transition).
3. **Given** the last remaining stage completes/skips, **When** the transition commits, **Then** the project becomes `status = completed`.
4. **Given** a completed project, **When** its detail is read, **Then** project `completed_at` is derived as the max stage `completed_at`.

---

### User Story 8 — Reopen a repeatable stage

As an admin, I reopen a completed stage that allows repetition so the team can run it again.

**Why this priority**: `allow_repeat` was captured in the data model but had no runtime action; reopen closes that gap (confirmed Q5).

**Independent Test**: A completed stage with `allow_repeat = true` → reopen → `status = in_progress`, `completed_at` cleared; a completed stage with `allow_repeat = false` → 422.

**Acceptance Scenarios**:

1. **Given** a `completed` stage with `allow_repeat = true`, **When** reopen is requested, **Then** `status = in_progress` and `completed_at` is cleared.
2. **Given** a `completed` stage with `allow_repeat = false`, **When** reopen is requested, **Then** 422.
3. **Given** a stage not `completed`, **When** reopen is requested, **Then** 422.

---

## Assumptions

- Approval actors are `users` (admin users). A client-facing approval portal is a separate epic.
- `sequence` on `pm_approvals` remains display order; it is not enforced as a sign-off chain (confirmed Q3).
- Rejection does not delete approval rows; the history is the activity log and the reset-on-revert rule (US4).
- Reopening an `allow_repeat` stage sets it back to `in_progress` and clears `completed_at`; it does not rewind later stages, reset children, or re-dispatch approvals (confirmed Q5).
- Stage status enum is unchanged (`pending,in_progress,completed,skipped`); "awaiting approval" is represented by a `pending` stage with outstanding `pending` approvals, not a new enum value.

## Out of Scope

- Client/portal approvers (separate epic).
- Notifications / reminders to approvers and PICs.
- Sequential (chained) approval enforcement (Q3).
- Milestone and change-request decisions (schema-only in 003; separate epics).

## Decisions (confirmed 2026-10-05)

| # | Decision |
|---|---|
| Q1 | Reject reverts the project to `draft` (admin edits and re-activates) |
| Q2 | Approval actors are admin `users` only (client portal is a separate epic) |
| Q3 | Approval decisions are parallel (done when all approve; any reject rejects) |
| Q4 | `super_admin` may decide on another approver's behalf |
| Q5 | `reopen` is in scope for `allow_repeat = true` stages (US8) |

## Progression

- Data model: [data-model.md](data-model.md)
- API contracts: [contracts/project-service.md](contracts/project-service.md)
- Implementation plan: [plan.md](plan.md)
- Task checklist: [tasks.md](tasks.md)
