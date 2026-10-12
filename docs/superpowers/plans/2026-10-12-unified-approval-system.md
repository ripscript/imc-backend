# Unified Approval System Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Menyatukan 3 jenis approval (Aktivasi Proyek, Stage Clearance, dan Change Request) ke dalam sistem dan tabel inbox terpadu (`GET /approvals`), melengkapi siklus hidup Stage Clearance (pengajuan clearance, validasi blocker, multi-approver first-come/any-one, reject dengan revisi), serta menyediakan aksi direct approve/reject dari layar antarmuka.

**Architecture:** Menggunakan model terpadu `pm_approval_requests` sebagai read/write model transaksi persetujuan lintas proyek yang sinkron secara atomik dengan entitas sumber (`content_detail_projects`, `pm_project_stages`, `pm_change_requests`). Relasi multi-approver stage ditangani oleh `pm_stage_approvers`, dengan otorisasi dual: approver yang ditugaskan atau role pusat (`super_admin`, `admin_pusat`) override.

**Tech Stack:** Laravel 10 / PHP 8.2+, MySQL / SQLite (Test Suite), Eloquent ORM, JWT Admin Authentication, Spatie Activitylog.

**Spec:** [specs/006-unified-approval-system/spec.md](../../../specs/006-unified-approval-system/spec.md)

---

## Global Constraints

- PHP 8.2+ strictly typed syntax, Laravel 10 conventions.
- Semua migrasi database baru ditempatkan di `migration-service/database/migrations/` dan di-bootstrap ke SQLite schema helper pada `project-service/tests/TestProjectCase.php`.
- Response format konsisten menggunakan helper `App\Helpers\ApiResponse::success(...)` dan `ApiResponse::error(...)`.
- Role approver fixed untuk aktivasi proyek dan change request adalah `super_admin` dan `admin_pusat`.
- Penolakan (*reject*) di semua jenis approval wajib menyertakan alasan penolakan (`notes`).
- Pengaplikasian perubahan Change Request (*apply*) berada di luar cakupan rilis ini (*out of scope*).

---

## Review Focus

1. **Non-assigned user tries to approve stage clearance:** User biasa yang bukan assigned approver di `pm_stage_approvers` dan bukan admin pusat melempar 403 Forbidden.
2. **Stage with incomplete tasks/deliverables requests clearance:** Request clearance melempar 422 Unprocessable Entity dengan rincian blocker jika masih ada task atau deliverable wajib yang belum tuntas.
3. **Already decided approval request is approved/rejected again:** Mencoba approve/reject approval request yang sudah `approved` atau `rejected` melempar 409 Conflict.
4. **Reject without notes:** Memanggil reject endpoint tanpa memberikan payload `notes` melempar 422 Validation Error.
5. **Stage Clearance auto-advances next stage:** Ketika stage terakhir disetujui, proyek menjadi `completed`; jika masih ada stage berikutnya, stage tersebut menjadi `in_progress`.

---

## File Structure Map

```
migration-service/database/migrations/
├── 2026_10_12_000001_create_pm_approval_requests_table.php
├── 2026_10_12_000002_create_pm_stage_approvers_table.php
└── 2026_10_12_000003_add_approval_columns_to_projects_and_stages.php

project-service/app/
├── Models/
│   ├── PmApprovalRequest.php
│   ├── PmStageApprover.php
│   ├── Project.php (update relations)
│   ├── ProjectStage.php (update status enum & relations)
│   └── PmChangeRequest.php (update relations)
├── Http/
│   ├── Controllers/
│   │   ├── UnifiedApprovalController.php
│   │   ├── ProjectActivationController.php (update)
│   │   └── ProjectStageController.php (update)
│   ├── Requests/
│   │   ├── BulkStageConfigurationRequest.php (update approver_user_ids)
│   │   ├── RequestStageClearanceRequest.php
│   │   ├── RejectApprovalRequest.php
│   │   └── RejectActivationRequest.php
│   └── Resources/
│       ├── UnifiedApprovalResource.php
│       └── ProjectStageResource.php (update)
├── Services/
│   ├── UnifiedApprovalService.php
│   ├── ProjectActivationService.php (update)
│   ├── ProjectStageService.php (update)
│   ├── StageLifecycleService.php (update)
│   └── PmChangeRequestService.php (update)
└── routes/
    └── api.php (update)

project-service/tests/
├── TestProjectCase.php (update schema bootstrap)
└── Feature/
    ├── UnifiedApprovalInboxTest.php
    ├── StageClearanceLifecycleTest.php
    └── ProjectActivationApprovalTest.php
```

---

## Tasks

### Task 1: Migrasi Database & Model Data Approval

**Files:**
- Create: `migration-service/database/migrations/2026_10_12_000001_create_pm_approval_requests_table.php`
- Create: `migration-service/database/migrations/2026_10_12_000002_create_pm_stage_approvers_table.php`
- Create: `migration-service/database/migrations/2026_10_12_000003_add_approval_columns_to_projects_and_stages.php`
- Create: `project-service/app/Models/PmApprovalRequest.php`
- Create: `project-service/app/Models/PmStageApprover.php`
- Modify: `project-service/app/Models/Project.php`
- Modify: `project-service/app/Models/ProjectStage.php`
- Modify: `project-service/tests/TestProjectCase.php`
- Test: `project-service/tests/Feature/UnifiedApprovalInboxTest.php`

**Interfaces:**
- Produces: `PmApprovalRequest`, `PmStageApprover` Eloquent models, relasi `Project::approvalRequests()`, `ProjectStage::approvers()`, `ProjectStage::approvalRequests()`.

- [ ] **Step 1: Write the failing test for schema and models**
```php
public function test_approval_requests_and_stage_approvers_schema(): void
{
    $project = $this->createProject(['title' => 'Test Project']);
    $approval = PmApprovalRequest::create([
        'project_id' => $project->id,
        'approvable_type' => 'project_activation',
        'approvable_id' => $project->id,
        'type' => 'activation',
        'title' => $project->title,
        'status' => 'pending',
    ]);

    $this->assertDatabaseHas('pm_approval_requests', ['id' => $approval->id, 'type' => 'activation']);
}
```

- [ ] **Step 2: Run test to verify it fails**
Run: `cd project-service && ./vendor/bin/phpunit --filter test_approval_requests_and_stage_approvers_schema`  
Expected: FAIL (class/table does not exist).

- [ ] **Step 3: Implement migrations and models**
1. Buat file migrasi untuk tabel `pm_approval_requests`, `pm_stage_approvers`, dan kolom tambahan `content_detail_projects` & `pm_project_stages`.
2. Update `TestProjectCase.php` DDL helper agar schema SQLite memiliki tabel dan kolom baru.
3. Buat model `PmApprovalRequest` dan `PmStageApprover`, serta tambahkan relasi di `Project` dan `ProjectStage`.

- [ ] **Step 4: Run test to verify it passes**
Run: `cd project-service && ./vendor/bin/phpunit --filter test_approval_requests_and_stage_approvers_schema`  
Expected: PASS.

- [ ] **Step 5: Commit**
```bash
git add migration-service/ database/ project-service/app/Models/ project-service/tests/
git commit -m "feat(approval): add unified approval requests and stage approvers schema"
```

---

### Task 2: Stage Multi-Approver Configuration & Clearance Request Lifecycle

**Files:**
- Create: `project-service/app/Http/Requests/RequestStageClearanceRequest.php`
- Modify: `project-service/app/Http/Requests/BulkStageConfigurationRequest.php`
- Modify: `project-service/app/Services/ProjectStageService.php`
- Modify: `project-service/app/Services/StageLifecycleService.php`
- Modify: `project-service/app/Http/Controllers/ProjectStageController.php`
- Modify: `project-service/routes/api.php`
- Test: `project-service/tests/Feature/StageClearanceLifecycleTest.php`

**Interfaces:**
- Consumes: `PmStageApprover`, `PmApprovalRequest`
- Produces: 
  - `POST /projects/{project_id}/stages/{stage_id}/request-clearance`
  - `PUT /projects/{project_id}/stages/configuration` (mendukung `approver_user_ids`)
  - Method `StageLifecycleService::requestClearance(int $projectId, int $stageId, User $actor, ?string $notes): array`
  - Method `StageLifecycleService::rejectClearance(int $projectId, int $stageId, User $actor, string $notes): array`

- [ ] **Step 1: Write the failing test for stage clearance request and blockers**
```php
public function test_cannot_request_clearance_if_tasks_incomplete(): void
{
    $project = $this->createActiveProject();
    $stage = $this->createStageWithPendingTasks($project->id, ['requires_approval' => true]);

    $response = $this->postJson("/projects/{$project->id}/stages/{$stage->id}/request-clearance");
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['stage_' . $stage->id . '.open_tasks']);
}

public function test_can_request_clearance_when_all_done(): void
{
    $project = $this->createActiveProject();
    $stage = $this->createStageWithCompletedTasksAndDeliverables($project->id, ['requires_approval' => true]);

    $response = $this->postJson("/projects/{$project->id}/stages/{$stage->id}/request-clearance", [
        'notes' => 'Siap review clearance',
    ]);
    $response->assertStatus(200);
    $this->assertDatabaseHas('pm_project_stages', [
        'id' => $stage->id,
        'status' => 'pending_clearance',
    ]);
    $this->assertDatabaseHas('pm_approval_requests', [
        'approvable_id' => $stage->id,
        'type' => 'stage_clearance',
        'status' => 'pending',
    ]);
}
```

- [ ] **Step 2: Run test to verify it fails**
Run: `cd project-service && ./vendor/bin/phpunit --filter StageClearanceLifecycleTest`  
Expected: FAIL (route / method not found).

- [ ] **Step 3: Implement request-clearance, reject-clearance, and stage approver config**
1. Update `BulkStageConfigurationRequest` untuk validasi `stages.*.approver_user_ids` (`array`, `exists:users,id`).
2. Update `ProjectStageService::configureAll` untuk menyinkronkan array ID approver ke `pm_stage_approvers`.
3. Tambahkan method `requestClearance` dan `rejectClearance` di `StageLifecycleService`:
   - Validasi status `in_progress` dan `completionBlockers`.
   - Update stage `status = 'pending_clearance'`, `clearance_requested_by`, `clearance_requested_at`.
   - Buat row di `pm_approval_requests`.
4. Pasang route `POST .../stages/{stage_id}/request-clearance` di `routes/api.php`.

- [ ] **Step 4: Run test to verify it passes**
Run: `cd project-service && ./vendor/bin/phpunit --filter StageClearanceLifecycleTest`  
Expected: PASS.

- [ ] **Step 5: Commit**
```bash
git add project-service/
git commit -m "feat(approval): implement stage clearance request lifecycle and multi-approver configuration"
```

---

### Task 3: Sinkronisasi Approval Aktivasi Proyek & Change Request ke Inbox

**Files:**
- Create: `project-service/app/Http/Requests/RejectActivationRequest.php`
- Modify: `project-service/app/Services/ProjectActivationService.php`
- Modify: `project-service/app/Services/PmChangeRequestService.php`
- Modify: `project-service/app/Http/Controllers/ProjectActivationController.php`
- Test: `project-service/tests/Feature/ProjectActivationApprovalTest.php`

**Interfaces:**
- Consumes: `PmApprovalRequest`
- Produces:
  - `ProjectActivationService::activate`: creates `pm_approval_requests` (`type = 'activation'`).
  - `ProjectActivationService::approveActivation`: updates `pm_approval_requests` to `approved`.
  - `ProjectActivationService::rejectActivation`: updates `pm_approval_requests` to `rejected` with notes.
  - `PmChangeRequestService::create`: creates `pm_approval_requests` (`type = 'change_request'`).
  - `PmChangeRequestService::approve`: updates `pm_approval_requests` to `approved`.
  - `PmChangeRequestService::reject`: updates `pm_approval_requests` to `rejected` with notes.

- [ ] **Step 1: Write the failing test for activation & CR approval inbox sync**
```php
public function test_activate_creates_pending_approval_request(): void
{
    $project = $this->createCompleteDraftProject();
    $this->postJson("/projects/{$project->id}/activate")->assertStatus(200);

    $this->assertDatabaseHas('pm_approval_requests', [
        'project_id' => $project->id,
        'type' => 'activation',
        'status' => 'pending',
    ]);
}

public function test_reject_activation_stores_rejection_notes(): void
{
    $project = $this->createPendingApprovalProject();
    $this->actingAsAdminPusat();

    $response = $this->postJson("/projects/{$project->id}/activate/reject", [
        'notes' => 'RAB belum ditandatangani oleh pemohon.',
    ]);
    $response->assertStatus(200);
    $this->assertDatabaseHas('content_detail_projects', [
        'id' => $project->id,
        'status' => 'draft',
        'rejection_notes' => 'RAB belum ditandatangani oleh pemohon.',
    ]);
}
```

- [ ] **Step 2: Run test to verify it fails**
Run: `cd project-service && ./vendor/bin/phpunit --filter ProjectActivationApprovalTest`  
Expected: FAIL.

- [ ] **Step 3: Implement activation and CR inbox sync**
1. Update `ProjectActivationService`: simpan `activation_requested_by`, `activation_requested_at`, buat entri `pm_approval_requests` saat `activate`.
2. Update `approveActivation` dan `rejectActivation` (terima string `$notes`), perbarui entitas `Project` dan baris `pm_approval_requests`.
3. Update `PmChangeRequestService`: buat entri `pm_approval_requests` saat `create`, update saat `approve` dan `reject`.

- [ ] **Step 4: Run test to verify it passes**
Run: `cd project-service && ./vendor/bin/phpunit --filter ProjectActivationApprovalTest`  
Expected: PASS.

- [ ] **Step 5: Commit**
```bash
git add project-service/
git commit -m "feat(approval): sync project activation and change requests with approval inbox"
```

---

### Task 4: Unified Approval Inbox API & Direct Actions (`/approvals`)

**Files:**
- Create: `project-service/app/Http/Requests/RejectApprovalRequest.php`
- Create: `project-service/app/Http/Resources/UnifiedApprovalResource.php`
- Create: `project-service/app/Services/UnifiedApprovalService.php`
- Create: `project-service/app/Http/Controllers/UnifiedApprovalController.php`
- Modify: `project-service/routes/api.php`
- Test: `project-service/tests/Feature/UnifiedApprovalInboxTest.php`

**Interfaces:**
- Produces:
  - `GET /approvals` (listing with pagination, filters, search, and authorization scoping)
  - `POST /approvals/{id}/approve` (direct action from table)
  - `POST /approvals/{id}/reject` (direct action with rejection notes)

- [ ] **Step 1: Write the failing test for inbox list and direct actions**
```php
public function test_approver_can_view_inbox_and_approve_directly(): void
{
    $admin = $this->createAdminPusatUser();
    $project = $this->createPendingApprovalProject();
    $approval = PmApprovalRequest::where('project_id', $project->id)->first();

    $this->actingAs($admin, 'admin')
        ->getJson('/approvals')
        ->assertStatus(200)
        ->assertJsonPath('data.0.id', $approval->id)
        ->assertJsonPath('data.0.project_name', $project->title);

    $this->actingAs($admin, 'admin')
        ->postJson("/approvals/{$approval->id}/approve")
        ->assertStatus(200);

    $this->assertDatabaseHas('pm_approval_requests', [
        'id' => $approval->id,
        'status' => 'approved',
    ]);
    $this->assertEquals('active', $project->fresh()->status);
}

public function test_stage_approver_only_sees_assigned_stages_if_not_admin_pusat(): void
{
    $userA = $this->createUser();
    $userB = $this->createUser();
    $stageA = $this->createStageWithApprover($userA);
    $stageB = $this->createStageWithApprover($userB);

    $this->actingAs($userA, 'admin')
        ->getJson('/approvals')
        ->assertStatus(200)
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.approvable_id', $stageA->id);
}
```

- [ ] **Step 2: Run test to verify it fails**
Run: `cd project-service && ./vendor/bin/phpunit --filter UnifiedApprovalInboxTest`  
Expected: FAIL (route `/approvals` not defined).

- [ ] **Step 3: Implement UnifiedApprovalService and Controller**
1. Buat `UnifiedApprovalService`:
   - `list(User $caller, QueryFilterDTO $filter)`: query `pm_approval_requests` dengan eager loading `project`, `requester`, `decider`. Terapkan scoping: jika caller bukan `super_admin`/`admin_pusat`, batasi hanya `stage_clearance` di mana caller terdaftar di `pm_stage_approvers`.
   - `approve(int $id, User $caller)`: cek otorisasi, dispatch ke `ProjectActivationService::approveActivation`, `StageLifecycleService::complete`, atau `PmChangeRequestService::approve`.
   - `reject(int $id, User $caller, string $notes)`: cek otorisasi, dispatch ke service masing-masing.
2. Buat `UnifiedApprovalController` dan `UnifiedApprovalResource` format sesuai schema UI di Spec 006.
3. Daftarkan routes di `routes/api.php`:
   - `GET /approvals`
   - `POST /approvals/{id}/approve`
   - `POST /approvals/{id}/reject`

- [ ] **Step 4: Run test to verify it passes**
Run: `cd project-service && ./vendor/bin/phpunit --filter UnifiedApprovalInboxTest`  
Expected: PASS.

- [ ] **Step 5: Commit**
```bash
git add project-service/
git commit -m "feat(approval): add unified approval inbox API and direct action endpoints"
```

---

### Task 5: Full Regression & Integration Verification

**Files:**
- Modify: `project-service/tests/Feature/StageLifecycleTest.php`
- Modify: `project-service/tests/Feature/ProjectActivationTest.php`
- Modify: `project-service/tests/Feature/ChangeRequestTest.php`

- [ ] **Step 1: Run complete project-service test suite**
Run: `cd project-service && ./vendor/bin/phpunit`  
Expected: All tests PASS with zero regressions across activation, stage transitions, and change requests.

- [ ] **Step 2: Commit final suite refinements**
```bash
git add project-service/
git commit -m "test(approval): verify end-to-end regression across unified approval suite"
```
