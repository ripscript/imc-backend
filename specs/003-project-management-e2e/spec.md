# Feature Specification: Project Management E2E Creation

**Feature Branch**: `003-project-management-e2e`

**Created**: 2026-09-23

**Status**: Draft

**Input**: User description: "plan to implement full e2e project management creation. the flow will be implemented on project-service domain. analyze gap between existing implemented api (master, project-management), figma design on /design/*, and proposed ERD from ERD.md. create a phased plan for each screen from design."

## Clarifications

### Session 2026-09-23

- Q: Screen 7 (`7 ...konfigurasi setiap alert.png`, success modal) — in scope? → A: Skip — it is a FE UX feedback item with no backend behavior.
- Q: Activity log storage — spatie `activity_log` (already in DB) or the ERD's dedicated `ACTIVITY_LOG` table? → A: Adopt spatie `activity_log`.
- Q: Approver modeling — config-only list or pre-created approval records? → A: Pre-create `APPROVAL` rows at stage configuration time (designated approver pre-assigned per row).
- Q: PROJECT storage — keep the merged `content_detail_projects` (CMS + PM) or split per ERD? → A: Keep the existing merged direction.
- Q: Wizard navigation — resume at last saved step, or free navigation? → A: Free navigation between steps 1–5; each step persists independently; activation validates completeness.

### Session 2026-09-23 (continued) — Workflow Blueprint Templates

- Q: Should workflow + workflow stages act as reusable blueprint templates carrying pre-defined attributes into steps 2 and 4? → A: Yes — new Phase 8. Template attributes: stage description, requires_approval flag, task list, deliverable list.
- Q: How should the template model the S5 approver list ("Project Manager", "Sponsor")? → A: Flag only — the template carries `requires_approval`, never people. Approver assignment stays per-project.
- Q: Should template tasks/deliverables carry relative due dates? → A: No — due dates are manual per project.
- Q: How should the S5 Dokumen card relate to the template? → A: Deliverables only — document requirements are template deliverables (`document_type_id` + `is_required`); the Dokumen card stays for actual runtime uploads.
- Q: Delivery approach? → A: Specs first, then implement 8A–8D.

### Blueprint Semantics (normative for Phase 10 — supersedes Phase 9 materialization)

1. **The template is a read-only reference** — FE fetches it (`GET /workflows/{id}/stages`) and merges it in its own state. The API never copies template rows implicitly.
2. **The submitted payload is the snapshot** — `PUT /stages` (S3) and `PUT /stages/{id}` (S5) store exactly what FE sends, including nested `tasks[]`/`deliverables[]` (id-matched replace-all when the keys are present). `workflow_stage_id` is a provenance link only.
3. **No rows exist until an explicit save** — reading a stage (S3 list, S5 detail) never creates task/deliverable rows.
4. **Approvals are people, never templated** — approver assignment stays per-project via the approvers endpoints.
5. **Activation requires approvers where flagged** — a stage with `requires_approval=true` but zero approval rows blocks activation.
6. **Manual stages are unaffected** — no template linkage; the same nested payload shape works without `workflow_stage_id`.

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Project List (Daftar Proyek — Screen S1)

As an admin, I want to see all projects with their client, current stage, progress, and status so that I can find, sort, and open projects quickly.

**Why this priority**: Entry point of the whole flow — every other screen is reached from here. Mostly exists; smallest delta.

**Independent Test**: Seed projects at various stages; `GET /projects` returns rows whose `current_stage`/`progress` match the seeded stage/task data; sorting on each column, search, `limit`, and pagination match the query contract.

**Acceptance Scenarios**:

1. **Given** projects exist with stages at different completion levels, **When** the list is requested, **Then** each row shows Nama Project, Client, Stage (first non-completed stage by sequence), Progress (0–100), and Status.
2. **Given** the list request with `sort_by=stage` or another column, **When** processed, **Then** rows are ordered accordingly (`sort_order` asc/desc) and pagination meta is consistent.
3. **Given** a search term, **When** applied, **Then** rows match on project name or project number (case-insensitive).
4. **Given** `my=true`, **When** applied, **Then** only projects where the caller is PIC or member are returned.

---

### User Story 2 - Wizard Step 1: Usulan Proyek Kolaborasi (Screen S2)

As an admin, I want to create a draft project with its core information and supporting attachments so that the project proposal is recorded before stages are defined.

**Why this priority**: Creates the aggregate every later step attaches to.

**Independent Test**: POST step-1 payload; GET the project back and compare fields; attempt invalid attachment types/sizes and observe 422s.

**Acceptance Scenarios**:

1. **Given** valid input (Nama Proyek, Client, Kategori Proyek, PIC, Target Selesai, optional Email-derived PIC email, Nilai Proyek, Deskripsi), **When** saved, **Then** a project with `status = draft` and an auto-generated `project_no` (`IMC.{Y}-{000}`) is returned.
2. **Given** a PIC is selected, **When** the project is read back, **Then** the PIC's email is returned (derived from `users.email`, not client-entered).
3. **Given** uploaded attachments (via file-service) referencing `pdf,doc,docx,jpg,png` files of at most 5 MB, **When** saved, **Then** the attachment list persists with file name/size and can be removed on update.
4. **Given** an attachment with a disallowed extension or over 5 MB, **When** saved, **Then** a 422 identifies the offending file.
5. **Given** an existing draft project, **When** any step-1 field is updated later (free navigation), **Then** only the provided fields change.

---

### User Story 3 - Wizard Step 2: Tentukan Stage (Screen S3)

As an admin, I want to define the project's stages either by instantiating a workflow template or manually, and to add/edit/delete/reorder them, so that the project structure matches the agreed process.

**Why this priority**: Stages are the backbone of timeline, configuration, and review steps.

**Independent Test**: Instantiate from a workflow and verify stage rows (names, sequence, template flags); then edit/delete/reorder via bulk sync and verify the resulting order and rows.

**Acceptance Scenarios**:

1. **Given** a chosen workflow (`GET workflows/{id}/stages` preview works), **When** stages are saved, **Then** `pm_project_stages` rows are created with `workflow_stage_id`, names, and sequence copied from the template, and `project.workflow_id` is set.
2. **Given** the "Buat Manual" tab, **When** stages are saved, **Then** rows are created with `workflow_stage_id = null` and the entered names/sequence.
3. **Given** edits (rename, add, delete, reorder) to the instantiated list, **When** saved, **Then** the stored list matches exactly (replace-all semantics) and `sequence` values are 1..n.
4. **Given** duplicate or missing sequence values in the request, **When** saved, **Then** a 422 explains the conflict.
5. **Given** a stage removal, **When** another stage depends on it (timeline step), **Then** the request is rejected with 422 until the dependency is cleared.

---

### User Story 4 - Wizard Step 3: Tentukan Timeline (Screen S4)

As an admin, I want to set the project period, per-stage start/end dates, and optional inter-stage dependencies so that durations and the timeline preview are accurate.

**Why this priority**: Dates drive the review Gantt, total duration, and later monitoring.

**Independent Test**: Set dates and dependencies; verify computed durations (inclusive day count) and rejection of invalid ranges/cycles.

**Acceptance Scenarios**:

1. **Given** project start/end dates and per-stage dates, **When** saved, **Then** each stage's Durasi = `(end_date - start_date).days + 1` and is returned in the response.
2. **Given** a stage whose dates fall outside the project period, **When** saved, **Then** a 422 names the stage and the violation.
3. **Given** a dependency "Stage X setelah Stage Y", **When** saved, **Then** `depends_on_project_stage_id` is persisted and the dependency stage's start is not later than the dependent stage's start.
4. **Given** a dependency cycle or self-reference, **When** saved, **Then** a 422 rejects the request.
5. **Given** an omitted dependency (optional field), **When** saved, **Then** the stage stores `depends_on_project_stage_id = null` and the request succeeds.

---

### User Story 5 - Wizard Step 4: Konfigurasi Setiap Stage (Screen S5)

As an admin, I want to configure each stage's details, tasks, deliverables, documents, and approval requirements so that the team knows exactly what each stage needs.

**Why this priority**: Largest new surface; produces the working data (tasks/deliverables/documents/approvals) of the domain.

**Independent Test**: Per stage, save each card's data and GET the stage detail back; verify counters ("3/5 Selesai", "3 File", WIP badge) and document versioning.

**Acceptance Scenarios**:

1. **Given** a selected stage, **When** its name, description, dates, status, Allow Skip, Allow Repeat, and Perlu Approval are saved, **Then** the stage detail reflects them and `is_configured = true`.
2. **Given** tasks are added and toggled, **When** read back, **Then** the task list shows title/assignee/due date and the "x/y Selesai" counter equals done/total.
3. **Given** deliverables are added, **When** read back, **Then** each shows name, required flag, and status badge (pending/wip/completed).
4. **Given** a document uploaded whose file name already exists active in the stage, **When** saved, **Then** a new row with `version = n+1` is created and the previous row becomes `superseded`; the card shows "name v{n} • size".
5. **Given** "Perlu Approval = Ya" and an approver list, **When** saved, **Then** one `pm_approvals` row per approver exists with `status = pending`, designated `approver_id`, and `sequence` order.
6. **Given** "Perlu Approval = Tidak", **When** saved, **Then** the stage's approval rows are removed.
7. **Given** a document violating `pm_document_types.allowed_extension` / `max_size_mb`, **When** uploaded, **Then** a 422 identifies the rule violated.

---

### User Story 6 - Wizard Step 5: Review & Activate (Screen S8)

As an admin, I want to review a full summary and timeline preview before activating, and to either keep the project as draft or activate it, so that only complete projects go live.

**Why this priority**: Closes the E2E creation flow; enforces data completeness.

**Independent Test**: Fill an incomplete project and verify activation is refused with the missing items listed; complete it, activate, and verify status/stage/member/approval side effects.

**Acceptance Scenarios**:

1. **Given** a draft project, **When** review is requested, **Then** the summary returns Nama Proyek, Client, PIC, Workflow (+version), Total Stage, Periode, Total Durasi, Dokumen Wajib, Total PIC, Total Approval, and `timeline_preview` bars per stage.
2. **Given** steps 1–4 are incomplete, **When** activate is requested, **Then** 422 lists machine-readable missing items mapped to their wizard steps.
3. **Given** a complete project, **When** activated, **Then** `status = active`, the first stage becomes `in_progress`, its approval rows get `requested_at`/`requested_by`, and project + stage PICs appear in `pm_project_members` (role `pic`).
4. **Given** an already active project, **When** activate is requested again, **Then** a 409 is returned and nothing changes.
5. **Given** "Simpan Draft" on the review step, **When** clicked, **Then** the project remains `draft` with all entered data intact and resumable.

---

### User Story 7 - Free Navigation & Draft Resumability (cross-cutting, decision 5)

As an admin, I want to move freely between wizard steps 1–5 and resume a half-built draft later so that I can complete the proposal at my own pace.

**Why this priority**: UX requirement that shapes the API design (no step-gating column; derived completeness).

**Independent Test**: Save steps in arbitrary order on one project; fetch setup-state at each point and verify the derived step flags.

**Acceptance Scenarios**:

1. **Given** a draft project, **When** steps are saved in any order (e.g. 1 → 4 → 2), **Then** every save succeeds regardless of later/earlier steps being incomplete (each endpoint validates only its own fields).
2. **Given** partially filled steps, **When** `GET setup-state` is called, **Then** `steps` reflects derived completeness and `missing[]` names the gaps.
3. **Given** a draft revisited later, **When** any step is read, **Then** previously saved data is returned unchanged.

---

### User Story 8 - Activity Logging (decision 2)

As an auditor, I want every project-management mutation recorded in the spatie activity log and queryable per project so that the project history is traceable.

**Why this priority**: Required for compliance and the future activity feed; cheap if wired at creation time.

**Independent Test**: Perform create/update/delete across entities; query `GET projects/{project_id}/activities` and verify one entry per mutation with actor, event, and subject.

**Acceptance Scenarios**:

1. **Given** any mutation of project/stage/task/deliverable/document/approval/member, **When** committed, **Then** one `activity_log` row exists with `log_name = project-management`, `event`, `subject`, `causer`, and `properties.project_id`.
2. **Given** the activities endpoint, **When** called, **Then** entries are returned newest-first, paginated, with user and description.

### User Story 9 - Workflow Blueprint Templates (Phase 8, save model revised in Phase 10)

As an admin defining workflows, I want each workflow stage to carry a reusable blueprint (description, approval flag, task list, deliverable list) so that choosing a workflow pre-fills steps 2 and 4 instead of starting blank.

**Why this priority**: Turns workflows from bare stage names into true process templates; removes repetitive per-project setup while keeping per-project freedom (FE-held merge, explicit saves).

**Independent Test**: Create a workflow with blueprint children; submit a configured tree at step 2/4 and verify exactly those rows are stored; verify reads never create rows; verify nested replace-all.

**Acceptance Scenarios**:

1. **Given** a workflow whose stages define descriptions, `requires_approval`, tasks, and deliverables, **When** FE fetches the blueprint and submits a configured tree at step 2 or step 4, **Then** the stored stages, tasks, and deliverables match the submitted payload exactly (not the template rows).
2. **Given** a stage saved without nested keys, **When** it is read back (list or detail), **Then** its task/deliverable lists are empty — no rows are created implicitly.
3. **Given** a stage with existing children, **When** a nested set is submitted (entries with `id` to keep, new entries without, omitted rows dropped), **Then** the stored set matches exactly (id-matched replace-all).
4. **Given** a nested new task without `title` (or deliverable without `name`), **When** submitted, **Then** a 422 names the offending entry; a foreign child `id` is also rejected with 422.
5. **Given** a manual stage (no `workflow_stage_id`), **When** saved with the same nested shape, **Then** it behaves identically — no template linkage is needed.
6. **Given** a stage with `requires_approval=true` but zero approval rows, **When** activate is requested, **Then** a 422 lists `stage_{id}.approvers_missing` in `missing[]`.

## Assumptions

- "Client Email" (S2) is display-only, derived from the selected PIC's `users.email`; it is not a stored project field.
- Stage "Status Stage" dropdown (S5) uses the `pm_project_stages.status` enum; automatic lifecycle transitions during execution are out of scope (future execution/monitoring module).
- S5 approver dropdown values ("Project Manager", "Sponsor") are user selections from `users`, not a separate role entity.
- "Dokumen Wajib" (S7) = count of deliverables with `is_required = true`.
- File storage/CDN behavior lives in `file-service`; this spec stores URLs + metadata only.

## Out of Scope (deferred — schema unblocked by Phase 0)

- Sidebar modules without design exports: Proyek Saya, Project Menunggu Approval, Timeline workspace, Dokumen, Milestone, Change Request, Approval monitoring (including approve/reject decision endpoints).
- Alert/notification configuration (S6 filename mention; no UI/ERD basis — clarification 1).
- Task execution flows after activation (stage lifecycle automation, reminders).
- Public-facing project content behavior of merged CMS fields.

## Progression

- Research/gap analysis: [gap-analysis.md](gap-analysis.md)
- Data model: [data-model.md](data-model.md)
- API contracts: [contracts/project-service.md](contracts/project-service.md)
- Implementation plan: [plan.md](plan.md)
- Task checklist: [tasks.md](tasks.md)
