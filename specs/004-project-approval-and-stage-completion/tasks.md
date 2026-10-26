# Tasks: Project Approval & Stage Completion

**Spec**: [spec.md](spec.md) · **Plan**: [plan.md](plan.md)

**Status**: Implemented (2026-10-05). project-service suite: **64 Feature + 0 Unit tests green** (594 assertions), including 11 `ProjectApprovalTest` + 9 `StageLifecycleTest`. No schema changes required.

## Phase 1 — Approval Decision & Two-Step Activation

- [x] Extract `PmApprovalService::requestAll()` from `ProjectActivationService::activate` stamping logic
- [x] `PmApprovalService::approve()` / `reject()` — 409 already-decided guard, designated-approver check (super_admin override), auto-transition hooks
- [x] `ProjectActivationService::activate` — branch `pending_approval` (dispatch first-stage approvals) vs `active` per first-stage `requires_approval`
- [x] `ProjectApprovalController` + `POST .../approvals/{approval_id}/approve|reject` routes
- [x] `ApproveApprovalRequest` / `RejectApprovalRequest` (`notes` optional/required)
- [x] `PmApprovalResource` — expose `notes`, `requested_at`, `approved_at`, `decider`
- [x] Feature tests: US1–US4

## Phase 2 — Gated Stage Completion / Skip / Reopen + Auto-Advance

- [x] `StageLifecycleService` — `complete()` / `skip()` with `missing[]` gates (dependency, tasks, deliverables, approvals)
- [x] `reopen()` — `allow_repeat = true` + `completed` → `in_progress`, clears `completed_at`
- [x] `advanceToNextStage()` — next stage → `in_progress` (or awaits approval); last stage → project `completed`
- [x] Gate `status` in `ProjectStageService::updateStage` when `project.status != draft`
- [x] `ProjectStageController::complete` / `skip` / `reopen` + routes
- [x] Response additions for `next_stage` / `project_status`
- [x] Feature tests: US5–US8

## Phase 3 — Cross-Cutting & Hardening

- [x] Centralize designated-approver permission check (super_admin override)
- [x] `GET /projects/{project_id}/approvals` aggregate list (`ProjectApprovalResource`)
- [x] Activity log: `PmApproval.project_id` accessor so decisions carry `properties.project_id`; stage/project transitions log via `HasActivityLog`
- [x] Update existing `ProjectActivationTest` for branched activation (no-approval path unchanged)
- [x] Postman collection additions (`postman/ProjectManagement_E2E_Walkthrough.postman_collection.json` → "Step 7 - Eksekusi")
- [ ] Scramble `public/specs/api.json` regen (`make swagger`) — **blocked in this environment**: `scramble:export` introspects the shared Postgres at export time and the DB host is unreachable here. Regen with the docker DB up.
- [x] Full regression run + Pint clean

## Decisions

Q1–Q5 confirmed 2026-10-05 (see [spec.md](spec.md) §Decisions): revert-to-draft, admin-only approvers, parallel, `super_admin` override, `reopen` in scope.
