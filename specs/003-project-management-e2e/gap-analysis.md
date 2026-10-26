# Gap Analysis: Project Management E2E Creation

**Phase**: 1 — Design & Contracts
**Date**: 2026-09-23
**Spec**: [spec.md](spec.md)

## Overview

Three-way gap analysis between:

1. **Existing implementation** — `master-service` (PM master data), `project-service` (projects CRUD), `migration-service` (schema owner), `file-service` (uploads)
2. **Figma design** — `design/*.png` (7 exported screens)
3. **Proposed ERD** — `ERD.md` (root of repo)

## 1. Design Screens Inventory

| # | File | Screen | Purpose |
|---|------|--------|---------|
| S1 | `2 Daftar Proyek.png` | Project list | Table: Nama Project, Client, Stage, Progress, Status. Sortable columns, search, Show N entries, pagination. Actions: edit, view |
| S2 | `3 ...Tambah Project.png` | Wizard step 1 — Usulan Proyek Kolaborasi | Nama Proyek, Client, Kategori Proyek, PIC + Email, Target Selesai, Nilai Proyek, Deskripsi (rich text), Lampiran Pendukung (upload: PDF, DOC, DOCX, JPG, PNG, max 5 MB) |
| S3 | `4 ...Tentukan Stage.png` | Wizard step 2 — Tentukan Stage | Tabs "Gunakan Workflow" / "Buat Manual"; workflow picker + Preview (x2); stage table (add/edit/delete, reorder by No) |
| S4 | `5 ...Tentukan Timeline.png` | Wizard step 3 — Tentukan Timeline | Project start/end dates; per-stage Mulai/Selesai/Durasi table; optional Dependency ("Stage X setelah Stage Y") |
| S5 | `6 ...konfigurasi setiap.png` | Wizard step 4 — Konfigurasi Setiap Stage | Per-stage sidebar (11 stages); Nama Stage, Deskripsi, Tanggal Mulai/Selesai + duration chip, Status Stage, Allow Skip / Allow Repeat toggles; four cards: **Task** (checkbox list, "x/y Selesai" badge), **Deliverable** (checklist + WIP badge), **Dokumen** (file list with version + size), **Approval** (Perlu Approval toggle + ordered approver list) |
| S6 | `7 ...konfigurasi setiap alert.png` | Success modal | "Berhasil! Data berhasil disimpan." **Out of scope** — pure FE UX feedback (clarification 2026-09-23) |
| S7 | `8 ...jalan review.png` | Wizard step 5 — Review & Activate | Summary: Nama Proyek, Client, PIC, Workflow, Total Stage, Periode, Total Durasi, Dokumen Wajib, Total PIC, Total Approval + Preview Timeline (Gantt bars per stage). Actions: **Simpan Draft** / **Activate Project** |

Sidebar references modules **not present** in `design/` (separate epics, schema unblocked by Phase 0): Proyek Saya, Project Menunggu Approval, Timeline workspace, Dokumen, Milestone, Change Request, Approval monitoring.

## 2. ERD vs. Existing Schema (migration-service)

| ERD entity | DB table today | Gap |
|---|---|---|
| PROJECT | `content_detail_projects` (PM columns merged 2026-09-17) | Covered: `project_no`, `title` (ERD `name`), `description`, `client_id`, `partner_id`, `category_id`, `workflow_id`, `pic_user_id`, `status`, `start_date`, `target_end_date`, `progress`. Direction kept per clarification |
| CLIENT | `pm_clients` | None |
| PARTNER | `pm_partners` | None |
| PROJECT_CATEGORY | `pm_project_categories` | None |
| WORKFLOW | `pm_workflows` | None |
| WORKFLOW_STAGE | `pm_workflow_stages` | None |
| DOCUMENT_TYPE | `pm_document_types` | None |
| CUSTOM_FIELD / CUSTOM_FIELD_VALUE | `pm_custom_fields` / `pm_custom_field_values` | Minor: ERD `options_json` modeled as `field_type` + JSON-encoded `value` (select/multiselect). Accepted deviation |
| USERS | `users` | None |
| **PROJECT_STAGE** | missing | New table `pm_project_stages` (see [data-model.md](data-model.md)) |
| **TASK** | missing | New table `pm_tasks` |
| **DELIVERABLE** | missing | New table `pm_deliverables` |
| **DOCUMENT** | missing | New table `pm_documents` (existing `project_attachments` is a lightweight CMS-era list; kept for step-1 Lampiran only) |
| **APPROVAL** | missing | New table `pm_approvals` (pre-created rows per clarification) |
| **MILESTONE** | missing | New table `pm_milestones` (schema only in this spec) |
| **CHANGE_REQUEST** | missing | New table `pm_change_requests` (schema only in this spec) |
| **PROJECT_MEMBER** | missing | New table `pm_project_members` |
| ACTIVITY_LOG | `activity_log` (spatie/laravel-activitylog) | Shape differs from ERD. **Adopt spatie** per clarification; no new table |

## 3. ERD Deltas (things the design needs that ERD.md does not model)

1. **`pm_project_stages.name` / `description`** — required for "Buat Manual" tab (S3) and editable in S5 ("Nama Stage", "Deskripsi"). ERD assumed the name always comes from `workflow_stage`.
2. **`pm_project_stages.depends_on_project_stage_id`** — S4 Dependency block. Self-FK, nullable.
3. **Per-project copies of `is_required`, `allow_skip`, `allow_repeat`** — S5 exposes Allow Skip / Allow Repeat toggles per instantiated stage; ERD only has them on `WORKFLOW_STAGE`.
4. **`pm_project_stages.requires_approval` + `is_configured`** — S5 "Perlu Approval" toggle; `is_configured` marks step-4 completion (free navigation needs a per-stage "saved" marker).
5. **`pm_approvals.approver_id` + `sequence`** — S5 shows an ordered approver list. ERD's `approved_by` conflates "designated approver" with "actual approver at decision time"; split into `approver_id` (pre-assigned) and `approved_by` (actual actor).
6. **`pm_documents.file_size_kb`** — S2/S5 show file size ("1.2 MB", "850 KB").
7. **Wizard state under free navigation** — no `draft_step` column; completeness is **derived** per step (clarification: free navigation between steps 1-5). Served by `GET /projects/{project_id}/setup-state`.
8. **Alert/notification config (S6 filename "konfigurasi setiap alert")** — no UI visible in the export and no ERD entity. **Skipped** (clarification: FE UX item). Revisit if a notification spec appears.

## 4. API Gaps (project-service)

Implemented today (7 routes): `GET /projects`, `GET /projects/side-menu`, `POST /projects`, `GET|PUT|DELETE /projects/{project_id}`.
`ProjectResource` already reserves placeholder keys to fill: `current_stage`, `stages`, `milestones`, `open_change_requests`, `custom_fields`.

| Needed by design | Status |
|---|---|
| S1 list with stage/progress + sorting | Partial (list/search/paginate via `QueryService`; `current_stage` placeholder) |
| S2 project create/update + attachments | Partial (exists; needs PIC email mapping, 5 MB/extension validation) |
| S3 stage instantiate/manual CRUD | Missing |
| S4 timeline + dependency | Missing |
| S5 stage config, tasks, deliverables, documents, approvers | Missing |
| S7 review summary + activate | Missing |
| Setup-state (free nav resumability) | Missing |
| Activity log queries | Missing (spatie table exists, package not wired in project-service) |
| Milestone / Change Request / Project Member CRUD | Missing (schema in Phase 0; member CRUD minimal in Phase 6; rest deferred) |

Master-service PM masters (clients, partners, project-categories, document-types, workflows + nested stages, custom-fields + values) are **complete** and directly serve S2/S3 dropdowns. Master-service gaps: no PM seeders (Phase 0 adds them in migration-service), no PM module tests.

## 5. Decisions (Clarifications 2026-09-23)

| # | Question | Decision |
|---|---|---|
| 1 | "Konfigurasi setiap alert" (S6) scope | Skip — FE UX only |
| 2 | Activity log storage | Adopt spatie `activity_log`; no ERD-style dedicated table |
| 3 | Approval modeling | Pre-create `pm_approvals` rows at step-4 config time (designated approver per row) |
| 4 | PROJECT storage direction | Keep merged `content_detail_projects` (CMS + PM) |
| 5 | Wizard navigation | Free navigation between steps 1-5; derived setup-state; activation validates completeness |
