# Specification Quality Checklist: Project Approval & Stage Completion

**Spec**: [spec.md](spec.md) · **Date**: 2026-10-05

## Requirement Quality

- [x] All 8 user stories have Given/When/Then acceptance scenarios
- [x] Each user story states why its priority was chosen
- [x] Each user story is independently testable
- [x] Non-functional assumptions are explicit (Assumptions section)
- [x] Out-of-scope items are explicit (portal approvers, notifications, sequential approval, milestone/CR decisions)
- [x] Open questions recorded and resolved (Q1–Q5 confirmed 2026-10-05)

## Completeness

- [x] Current as-built state verified against code (approval schema, activation, manual stage status)
- [x] Every changed/new endpoint is specified in [contracts/project-service.md](contracts/project-service.md)
- [x] State machine transitions are canonical and enumerated (contracts §State Machine, data-model §Lifecycles)
- [x] Derived values (project `completed_at`, "awaiting approval" states) have single definitions
- [x] No new schema required — explicitly documented in [data-model.md](data-model.md)

## Consistency

- [x] Terminology matches existing codebase (`pm_approvals`, `pm_project_stages`, `pending_approval`, `requires_approval`)
- [x] Enums unchanged and canonical (approval `pending|approved|rejected`, stage `pending|in_progress|completed|skipped`)
- [x] Design decisions applied consistently across spec/data-model/contracts/plan
- [x] No contract requires an endpoint owned by another service without noting the dependency

## Feasibility

- [x] Plan phases have explicit dependencies and acceptance criteria
- [x] Risks with mitigations identified ([plan.md](plan.md) Risk Register)
- [x] Two-step activation keeps the approval-free path unchanged (no regression to 003 activation)
- [x] Task checklist covers every phase and traces to user stories

## Decisions (confirmed 2026-10-05)

- [x] Q1 — reject reverts to `draft`
- [x] Q2 — admin `users` only
- [x] Q3 — parallel decisions
- [x] Q4 — `super_admin` override allowed
- [x] Q5 — `reopen` in scope for `allow_repeat` stages

## Open Items Requiring Future Decision

- [ ] Scramble `api.json` regen — run `make swagger` with docker DB up (environment-blocked)

## Implementation Review (2026-10-05)

- [x] US1–US4 implemented in `PmApprovalService` + `ProjectActivationService` + `ProjectApprovalController`
- [x] US5–US8 implemented in `StageLifecycleService` + `ProjectStageController`
- [x] New endpoints follow the existing Controller → FormRequest → DTO → Service → Resource → `ApiResponse` pattern with `jwt.error:admin` + `check.role` guards
- [x] Activation branch keeps the approval-free path byte-for-byte compatible with 003 (existing activation test rewritten to the no-approval path)
- [x] project-service suite green: 64 tests / 594 assertions; `pint --dirty` clean
- [x] Postman walkthrough updated (Step 7)

