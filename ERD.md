
```mermaid
erDiagram
  CLIENT ||--o{ PROJECT : "has"
  PARTNER ||--o{ PROJECT : "has"
  PROJECT_CATEGORY ||--o{ PROJECT : "classifies"
  WORKFLOW ||--o{ WORKFLOW_STAGE : "defines"
  WORKFLOW_STAGE ||--o{ WORKFLOW_STAGE_TASK : "templates"
  WORKFLOW_STAGE ||--o{ WORKFLOW_STAGE_DELIVERABLE : "templates"
  WORKFLOW ||--o{ PROJECT : "used by"
  WORKFLOW_STAGE ||--o{ PROJECT_STAGE : "instantiates"
  PROJECT ||--o{ PROJECT_STAGE : "has"
  PROJECT ||--o{ MILESTONE : "has"
  PROJECT ||--o{ PROJECT_MEMBER : "has"
  PROJECT ||--o{ ACTIVITY_LOG : "logs"
  PROJECT ||--o{ DOCUMENT : "has"
  PROJECT ||--o{ CHANGE_REQUEST : "has"
  PROJECT_STAGE ||--o{ TASK : "has"
  PROJECT_STAGE ||--o{ DOCUMENT : "has"
  PROJECT_STAGE ||--o{ APPROVAL : "requires"
  PROJECT_STAGE ||--o{ DELIVERABLE : "requires"
  PROJECT_STAGE ||--o{ CHANGE_REQUEST : "raised in"
  DOCUMENT_TYPE ||--o{ DOCUMENT : "categorizes"
  DOCUMENT_TYPE ||--o{ DELIVERABLE : "categorizes"
  CUSTOM_FIELD ||--o{ CUSTOM_FIELD_VALUE : "defines"
  USERS ||--o{ PROJECT : "PIC"
  USERS ||--o{ PROJECT_STAGE : "PIC"
  USERS ||--o{ TASK : "assignee"
  USERS ||--o{ DOCUMENT : "uploads"
  USERS ||--o{ APPROVAL : "requests/approves"
  USERS ||--o{ CHANGE_REQUEST : "requests/approves"
  USERS ||--o{ PROJECT_MEMBER : "is member"
  USERS ||--o{ ACTIVITY_LOG : "acts"

  PROJECT {
    bigint id PK
    varchar project_no UK
    varchar name
    bigint client_id FK
    bigint partner_id FK
    bigint category_id FK
    bigint workflow_id FK
    varchar status
    text description
    bigint pic_user_id FK
    date start_date
    date target_end_date
  }
  CLIENT { bigint id PK  varchar name  varchar identifier  varchar status }
  PARTNER { bigint id PK  varchar name  varchar identifier  varchar status }
  PROJECT_CATEGORY { bigint id PK  varchar name  varchar code UK  varchar status }
  USERS { bigint id PK  varchar name  varchar email UK  varchar status }
  WORKFLOW { bigint id PK  varchar name  varchar code UK  int version  varchar status }
  WORKFLOW_STAGE {
    bigint id PK
    bigint workflow_id FK
    varchar name
    varchar code
    text description "blueprint, Phase 8"
    int sequence
    boolean is_required
    boolean allow_skip
    boolean allow_repeat
    boolean requires_approval "blueprint flag only, Phase 8"
  }
  WORKFLOW_STAGE_TASK {
    bigint id PK
    bigint workflow_stage_id FK
    varchar title
    text description
    varchar priority
    int sequence
  }
  WORKFLOW_STAGE_DELIVERABLE {
    bigint id PK
    bigint workflow_stage_id FK
    bigint document_type_id FK
    varchar name
    boolean is_required
    int sequence
  }
  PROJECT_STAGE {
    bigint id PK
    bigint project_id FK
    bigint workflow_stage_id FK "nullable, manual stages"
    varchar name "denormalized, manual + editable"
    text description
    int sequence
    varchar status
    decimal progress
    bigint pic_user_id FK
    date start_date
    date end_date
    timestamp completed_at
    bigint depends_on_project_stage_id FK "self, nullable"
    boolean is_required "per-project copy"
    boolean allow_skip "per-project copy"
    boolean allow_repeat "per-project copy"
    boolean requires_approval
    boolean is_configured
  }
  TASK {
    bigint id PK
    bigint project_stage_id FK
    varchar title
    text description
    varchar status
    varchar priority
    bigint assignee_id FK
    date due_date
  }
  DOCUMENT_TYPE {
    bigint id PK
    varchar name
    varchar code UK
    varchar allowed_extension
    int max_size_mb
    varchar status
  }
  DOCUMENT {
    bigint id PK
    bigint project_id FK
    bigint project_stage_id FK
    bigint document_type_id FK
    varchar file_name
    text file_path
    int file_size_kb
    int version
    varchar status
    bigint uploaded_by FK
    timestamp uploaded_at
  }
  APPROVAL {
    bigint id PK
    bigint project_stage_id FK
    int sequence "approval order"
    bigint approver_id FK "designated approver, pre-assigned"
    bigint requested_by FK
    bigint approved_by FK "actual actor at decision"
    varchar status
    text notes
    timestamp requested_at
    timestamp approved_at
  }
  MILESTONE {
    bigint id PK
    bigint project_id FK
    varchar name
    date target_date
    varchar status
    timestamp completed_at
  }
  DELIVERABLE {
    bigint id PK
    bigint project_stage_id FK
    bigint document_type_id FK
    varchar name
    boolean is_required
    varchar status
    date due_date
  }
  CHANGE_REQUEST {
    bigint id PK
    bigint project_id FK
    bigint project_stage_id FK
    varchar change_no UK
    varchar title
    text reason
    text impact_scope
    varchar status
    bigint requested_by FK
    bigint approved_by FK
  }
  PROJECT_MEMBER { bigint id PK  bigint project_id FK  bigint user_id FK  varchar role  varchar status }
  ACTIVITY_LOG {
    bigint id PK
    bigint project_id FK
    bigint project_stage_id FK
    bigint user_id FK
    varchar activity_type
    text description
    timestamp created_at
  }
  CUSTOM_FIELD { 
    bigint id PK
    varchar name
    varchar code UK
    varchar entity_type
    varchar field_type
    json options_json
    boolean is_required
  }
  CUSTOM_FIELD_VALUE {
    bigint id PK
    bigint custom_field_id FK
    bigint entity_id
    text value
  }
```

> Implementation notes (`specs/003-project-management-e2e`):
> - `PROJECT` stays merged into `content_detail_projects`; `name` maps to `title`.
> - `CUSTOM_FIELD.options_json` is modeled as `field_type` + JSON-encoded `value` (select/multiselect).
> - `ACTIVITY_LOG` is implemented with the existing spatie `activity_log` table (`log_name = project-management`, `properties.project_id`); no dedicated table.
> - Wizard state is derived per step (no `draft_step` column); see `GET /projects/{project_no}/setup-state`.
