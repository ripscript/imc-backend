# Implementation Plan: Project Approval & Stage Completion

**Phase**: 1 — Design & Contracts

**Date**: 2026-10-05

**Spec**: [spec.md](spec.md) · Contracts: [contracts/project-service.md](contracts/project-service.md)

## Overview

Three phases, all in **project-service** (no migration-service changes — schema is execution-ready). Follow the existing `Controller → FormRequest → DTO → Service → Resource → ApiResponse` pattern with `jwt.error:admin` + `check.role` guards.

| Phase | Theme | Depends on |
|---|---|---|
| 1 | Approval decision + two-step activation | — |
| 2 | Gated stage completion / skip + auto-advance | 1 |
| 3 | Cross-cutting: permission model, activity log, tests, postman, scramble | 1–2 |

Phases 1 and 2 touch the same services (`PmApprovalService`, `ProjectActivationService`, `ProjectStageService`); sequence them to avoid conflicts.

## Phase 1 — Approval Decision & Two-Step Activation

- **Extract a shared dispatch helper** `PmApprovalService::requestAll(int $projectId, int $stageId, ?int $userId, ?string $userName)` from the stamping logic currently inlined in `ProjectActivationService::activate`.
- **`PmApprovalService::approve` / `reject`** — decision methods with the 409 already-decided guard, designated-approver check (Q4 override), and the auto-transition hooks:
  - approve → if all first-stage rows approved and project `pending_approval` → flip to `active` + first stage `in_progress`.
  - reject → revert project to `draft`, reset first-stage rows to `pending`.
- **`ProjectActivationService::activate`** — branch to `pending_approval` (dispatch first-stage approvals) vs `active` (current behavior) per the first stage's `requires_approval`.
- **Controller/route/request** — `ProjectApprovalController` (or extend `StageApproverController`) with `POST .../approvals/{approval_id}/approve|reject`; `ApproveApprovalRequest` / `RejectApprovalRequest` (`notes` optional/required); `PmApprovalResource` gains `requested_at`, `approved_at`, `status`.

**Files**: `PmApprovalService.php`, `ProjectActivationService.php`, new `ProjectApprovalController.php`, `ApproveApprovalRequest.php` / `RejectApprovalRequest.php`, `PmApprovalResource.php`, `routes/api.php`.

**Acceptance**: US1–US4 scenarios.

## Phase 2 — Gated Stage Completion / Skip + Auto-Advance

- **New `StageLifecycleService`** (or methods on `ProjectStageService`) — `complete(...)`, `skip(...)`, and `reopen(...)`, encoding the gates from US5/US6/US8 and the auto-advance from US7:
  - gates return `missing[]` (`dependency`, `tasks`, `deliverables`, `approvals`) as `ValidationException` with machine-readable keys.
  - `advanceToNextStage(Project $project)` shared by `complete`/`skip`: next non-completed/non-skipped stage → `in_progress` + dispatch approvals; no next → `project.status = completed`.
  - `reopen`: `allow_repeat = true` + `completed` → `in_progress`, clear `completed_at` (no rewind/children/approval changes).
- **Gate `status` in `updateStage`** — `ProjectStageService::updateStage` rejects `status` when `project.status ≠ draft`; validation is semantic (in service), `UpdateStageRequest` unchanged.
- **Controller/route** — `ProjectStageController::complete` / `skip` / `reopen`; `POST .../stages/{stage_id}/complete|skip|reopen`; resource additions for `next_stage`/`project_status`.

**Files**: `ProjectStageService.php` (or new `StageLifecycleService.php`), `ProjectStageController.php`, `ProjectStageResource.php`, `routes/api.php`.

**Acceptance**: US5–US8 scenarios.

## Phase 3 — Cross-Cutting & Hardening

- **Permission model** — designated-approver check centralized (Q4); super_admin override if agreed.
- **Aggregate list** — `GET /projects/{project_id}/approvals` (`ProjectApprovalController::index`) for the "awaiting approval" view.
- **Activity log** — confirm `HasActivityLog` records the `status` change on approve/reject (PmApproval) and stage transitions (ProjectStage); add an explicit log for the `pending_approval → draft` revert if needed.
- **Tests** — feature tests in `project-service/tests/Feature/`: `ProjectApprovalTest` (US1–US4), `StageLifecycleTest` (US5–US7); reuse `TestProjectCase` SQLite DDL bootstrap (already declares `pm_approvals`/`pm_project_stages`).
- **Docs** — Postman collection additions under `postman/`; regenerate Scramble `public/specs/api.json` via `make swagger` (blocked in this environment when the shared Postgres host is unreachable — see 003 tasks Phase 7).

**Acceptance**: full regression green; new tests cover all scenarios.

## Risk Register

| Risk | Mitigation |
|---|---|
| Two-step activation breaks existing 003 activation tests (`ProjectActivationTest`) | Update those tests to the branched behavior; keep the no-approval path identical so approval-free projects are unaffected |
| Auto-transition on decision/complete leaves a stage or project stuck | Single transaction per decision/transition; `missing[]` + 409 guards make each state reachable only from a valid one |
| `status` field on `PUT .../stages/{id}` still lets FE bypass completion gates | Service-level rejection of `status` when `status ≠ draft` (Phase 2), plus FE contract note |
| Reject→draft reset loses the reject signal | Reject is logged in `activity_log` before reset; reset only clears `pm_approvals` columns |
| Concurrency: two approvers decide simultaneously | Row-level transaction + `status = pending` guard re-checked inside the transaction; second decision hits 409 |
