# Master Service API Contracts — Workflow Blueprints (Phase 8)

**Service**: `master-service`
**Base Path**: `/master-service/api/` (via Nginx)
**Auth**: JWT (`admin` guard) + `check.role:super_admin,admin_pusat,admin_pustik`
**Response Envelope**: `{success: bool, message: string, data: mixed|null, errors: mixed|null}`
**Spec**: [spec.md](../spec.md) · [data-model.md](../data-model.md)

Only the workflow endpoints change; all other master-service contracts are untouched.

## Workflows with blueprint children

### POST workflows/
**Body** (additions to existing fields):
```
{ name, code?, status?,
  stages: [ { name, code?, sequence, is_required?, allow_skip?, allow_repeat?,
              description?, requires_approval?,
              tasks?: [ { title, description?, priority? } ],
              deliverables?: [ { name, document_type_id?, is_required? } ] } ] }
```
- `tasks[].priority` in `low|medium|high` (default `medium`); ordered by array position (stored `sequence` 1..n)
- `deliverables[].document_type_id` must exist in `pm_document_types`; `is_required` default true
- **Status**: 201 · **Errors**: 422 on duplicate `sequence`/`code` (existing rules), unknown `document_type_id`, bad `priority`

### PUT workflows/{id}
Same nested shape, all nullable; when `stages` is present it is a **deep replace-all**: stages not present are soft-deleted **with their template tasks/deliverables**; everything is recreated. → 200

### GET workflows/{id} and GET workflows/{id}/stages
Stage objects gain blueprint fields:
```
{ id, code, name, sequence, is_required, allow_skip, allow_repeat,
  description, requires_approval,
  tasks: [ { id, title, description, priority, sequence } ],
  deliverables: [ { id, name, document_type_id, is_required, sequence } ] }
```
This is the S3 Preview payload and the S5 "Gunakan Workflow" prefill source. → 200

### DELETE workflows/{id}
Unchanged (soft delete). Template rows of deleted workflows remain as snapshots inside already-instantiated projects.
