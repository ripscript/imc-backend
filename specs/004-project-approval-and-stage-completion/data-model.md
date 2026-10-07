# Data Model: Project Approval & Stage Completion

**Phase**: 1 — Design & Contracts

**Date**: 2026-10-05

**Spec**: [spec.md](spec.md)

## Overview

No DDL is added by this spec — `pm_approvals` and `pm_project_stages` were shipped execution-ready in 003. This document records the **runtime lifecycles** and the single derived value the new endpoints rely on. All DDL remains owned by **migration-service**; domain logic lives in **project-service**.

## Existing Tables — No DDL Deltas

### pm_approvals (ERD APPROVAL)

Schema already has every column the decision flow needs:

| Column | Role in this spec |
|---|---|
| `approver_id` | designated approver (pre-created at step 4) |
| `requested_by` / `requested_at` | stamped when the stage's work is gated on approval (activation for stage 1; auto-advance for later stages) |
| `approved_by` / `approved_at` | **now written** by the decide endpoint (previously only cast/relation) |
| `status` | `pending → approved | rejected` (enum already canonical) |
| `notes` | reject reason (optional on approve, required on reject) |

### pm_project_stages (ERD PROJECT_STAGE)

Status enum unchanged (`pending,in_progress,completed,skipped`). The completion flow merely enforces transitions instead of letting `status` be set freely.

### content_detail_projects (ERD PROJECT)

Status enum unchanged (`draft,pending_approval,active,on_hold,completed,cancelled`). `pending_approval` — present in the enum and validation since 003 but never written — becomes a real state.

## Lifecycles (canonical)

### Project status

```
draft ──activate──▶ pending_approval ──all first-stage approvals approved──▶ active ──last stage completes──▶ completed
  ▲                      │
  └────── any reject ────┘        (revert; approval rows reset to pending)
```

- `activate` from `draft`: `pending_approval` **iff** the first stage `requires_approval = true` and has approval rows; otherwise `active` (first stage → `in_progress`).
- `pending_approval → active` is automatic (no endpoint), triggered by the last approval decision on the first stage.
- Any first-stage reject → `draft` (US4); admin edits and re-activates.

### Stage status

```
pending ──(if requires_approval) all approvals approved──▶ in_progress ──complete──▶ completed ──(allow_repeat) reopen──▶ in_progress
   │                                                          │
   │                                                        skip ──▶ skipped
   └── (if !requires_approval) becomes current ─────────────────┘
```

- A `requires_approval` stage stays `pending` while any of its approvals is `pending` (or rejected → stays blocked until resolved).
- `complete` is gated on: dependency stage completed/skipped, all tasks `done|cancelled`, required deliverables `completed`, all approvals `approved` (when required).
- `skip` is allowed only when `allow_skip = true`; it bypasses all gates and does not set `completed_at`.
- `reopen` is allowed only when `allow_repeat = true` and the stage is `completed`; it returns the stage to `in_progress` and clears `completed_at` (children and approvals untouched).

## Derived Values (no stored columns)

| Value | Derivation | Consumed by |
|---|---|---|
| Project `completed_at` | `max(pm_project_stages.completed_at)` over the project's stages (null until `status = completed`) | project detail reporting |
| Stage approval state ("awaiting approval") | `status = pending` + `requires_approval = true` + any `pm_approvals.status = pending` | UI badge; stage completion gate |
| Project is "awaiting approval" | `status = pending_approval` | existing `GET /projects?status=pending_approval` |

## Relationship Diagram (delta subset — unchanged from 003)

```
content_detail_projects ||--o{ pm_project_stages : has
pm_project_stages        ||--o{ pm_approvals : requires (requires_approval=true)
pm_project_stages        ||--o{ pm_project_stages : depends_on (self, nullable)
```
