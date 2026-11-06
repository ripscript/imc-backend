# Spec 008: Multi-Tier Parent-Child Organizational Structure with Sample Seeder

## Problem Statement

When managing content of type `organizational_structure`, administrators need to represent real-world hierarchical organizations (such as Ministries, Directorates, Balai, Teams, and Sub-teams). Currently, although the database table `content_detail_organizational_structure_members` contains `parent_id`, `level`, `is_child`, and `have_child` columns, the backend implementation forcibly overwrites the `parent_id` of every member after the first one to point directly to member index 0. This restricts organizations to a rigid 1-level flat structure and prevents building multi-tier organizational trees (e.g. Menteri → Dirjen → Kepala Balai → Tim → Subtim).

Furthermore, when newly created members (both parent and child) are submitted in the same HTTP request, children cannot point to their intended parent because the parent does not yet have a persistent database ID. In addition, the public API (`public-content-service`) hides both `id` and `parent_id` in its serialized model attributes, rendering it impossible for frontend clients to reconstruct or render the organizational tree diagram. Finally, the existing database seeder populates flat dummy records rather than a realistic multi-level organizational structure.

## Solution

1. **Adjacency List with Two-Pass Temporary ID Resolution**:
   - Maintain the flat payload array structure (`groups[].members[]`) adhering to ADR 0003.
   - For existing records, clients supply standard integer `parent_id` values.
   - For new records created within the same request that do not yet have a database ID, clients optionally supply a temporary client identifier (`temp_id`) on the new parent node, and child nodes reference it via `parent_temp_id`.
   - The persistence service performs a two-pass save: first creating/updating records and mapping `temp_id` to generated database IDs, then resolving all `parent_temp_id` references to true foreign keys before saving.
2. **Automatic Hierarchy Derivation**:
   - Backend automatically calculates and validates:
     - `is_child`: `true` if `parent_id !== null`, else `false`.
     - `have_child`: `true` if any other member references this member's `id`, else `false`.
     - `level`: `parent.level + 1` (root member is level 1).
   - Eliminate the hardcoded loop in `SaveContentsService` that assigned `$resultMembers[0]->id` to all members.
3. **Model & Eloquent Enhancements**:
   - Define recursive self-referencing relationships in `ContentDetailOrganizationalStructureMember`:
     - `parent()`: `belongsTo(self::class, 'parent_id')`
     - `children()`: `hasMany(self::class, 'parent_id')->orderBy('order', 'asc')`
4. **Public Exposure of Hierarchy Identifiers**:
   - Remove `'id'` and `'parent_id'` from `$hidden` in `public-content-service/app/Models/ContentDetailOrganizationalStructureMember.php` so public consumers can render the hierarchy graph.
5. **Realistic Sample Seeder**:
   - Update `ContentDetailOrganizationalStructureSeeder` to construct a 5-tier realistic organizational structure matching the provided sample diagram:
     - **Level 1**: Menteri Perindustrian (*Budi Santoso*)
     - **Level 2**: Dirjen ILMATE (*Aditya Wijaya*)
     - **Level 3**: Kepala Balai Teknologi Industri Manufaktur (BTIM) (*Farhan Maulana*)
     - **Level 4**:
       - Tim Inovasi, Pengembangan Produk dan Transfer Teknologi (*Siti Nurhaliza*)
       - Tim Pengembangan Talenta (*Anisa Rahmawati*)
       - Tim Pengembangan Teknologi Manufaktur (*Putri Lestari*)
       - Tim Jejaring Kerjasama dan Pemasaran (*Dewi Maharani*)
     - **Level 5**:
       - Subtim Inovasi dan Transfer Teknologi (*Ahmad Dahlan*) [Parent: Siti Nurhaliza]
       - Subtim Desain dan Prototipe (*Amanda Citra*) [Parent: Siti Nurhaliza]
       - Subtim Pengembangan Talenta Manufaktur (*Michael Scott*) [Parent: Anisa Rahmawati]
       - Subtim Inkubasi dan Technopreneur (*Daniel Vance*) [Parent: Anisa Rahmawati]
       - Subtim Teknologi Proses (*Liam Hudson*) [Parent: Putri Lestari]
       - Subtim Efisiensi dan Transformasi Digital (*Emily Watson*) [Parent: Putri Lestari]
       - Subtim Pengembangan Jejaring Industri (*Sophia Bennett*) [Parent: Dewi Maharani]
       - Subtim Kerjasama dan Pemasaran (*Olivia Harris*) [Parent: Dewi Maharani]

---

## User Stories

1. As an administrator, I want to create a multi-level organizational structure (e.g. 5 tiers: Minister down to Sub-teams), so that our organization chart faithfully represents our real-world administrative hierarchy.
2. As an administrator, I want to submit both parent and child members in a single create/update request using temporary client IDs (`temp_id` and `parent_temp_id`), so that I don't have to save parent nodes first before adding child nodes.
3. As an administrator, I want to assign any existing member as the parent of another member within the same group using their database `parent_id`, so that reorganization and reporting line changes can be performed freely.
4. As an administrator, I want to create multiple independent root-level positions within a group (e.g. Board of Commissioners / Co-directors) without the backend forcing one to be the child of another.
5. As an administrator, I want member metadata (`level`, `is_child`, and `have_child`) to be computed automatically by the backend, so that human input errors cannot desynchronize node levels or leaf statuses.
6. As a frontend client developer, I want to send and receive members in a flat list with `parent_id`, so that our charting components (OrgChart, D3, ReactFlow) can ingest the nodes directly without transformation boilerplate.
7. As a frontend client developer consuming `public-content-service`, I want `id` and `parent_id` to be present in the public response JSON, so that public visitors can view the interactive tree structure.
8. As a backend developer, I want `ContentDetailOrganizationalStructureMember` to provide `parent()` and `children()` Eloquent relations, so that relationship navigation and eager loading are expressive and efficient.
9. As a backend developer, I want circular references (e.g. Node A being parent to Node B while Node B is parent to Node A) to be rejected or prevented, so that graph traversals do not cause infinite loops.
10. As a tester or demo presenter, I want `php artisan db:seed --class=ContentDetailOrganizationalStructureSeeder` to seed the full 15-member BTIM organizational structure across 5 levels, so that test environments immediately have realistic visual tree data.

---

## Implementation Decisions

### Modules Modified

1. **`content-service`**:
   - `app/Models/ContentDetailOrganizationalStructureMember.php`:
     - Add `parent(): BelongsTo` relationship.
     - Add `children(): HasMany` relationship.
   - `app/Services/SaveContentsService.php`:
     - Refactor `case 'organizational_structure'`:
       - First pass: Persist all `ContentDetailOrganizationalStructureMember` records. Maintain an in-memory mapping `temp_id => id` for any members created with `temp_id`.
       - Second pass: Resolve `parent_id` for members that specified `parent_temp_id` using the map.
       - Third pass: Compute `have_child`, `is_child`, and `level` (recursive traversal or topological pass from root nodes down).
       - Remove previous hardcoded loop forcing `parent_id = $resultMembers[0]->id`.
2. **`public-content-service`**:
   - `app/Models/ContentDetailOrganizationalStructureMember.php`:
     - Remove `'id'` and `'parent_id'` from `$hidden`.
     - Add `parent()` and `children()` relationships.
3. **`migration-service`**:
   - `database/seeders/ContentDetailOrganizationalStructureSeeder.php`:
     - Rewrite seeder to generate the 15-member hierarchy matching the user's diagram under an `organizational_structure` content.

---

## Testing Decisions

### Test Seams

1. **High-Level API Seam (`content-service`)**:
   - Test `POST /menu` and `PUT /menu/{id}` with `type: 'organizational_structure'`.
   - Verify payload with 3+ tiers, mixed existing IDs and `temp_id` / `parent_temp_id`.
   - Assert in database and response that:
     - `parent_id` accurately links to the target parent.
     - `level` is computed correctly (`1` for root, `2` for direct child, `3` for grandchild, etc.).
     - `is_child` and `have_child` flags are accurately derived.
2. **Public API Seam (`public-content-service`)**:
   - Test `GET` endpoint returning organizational structure content.
   - Assert `id` and `parent_id` are included in the JSON payload and not stripped by `$hidden`.
3. **Seeder Verification**:
   - Execute `ContentDetailOrganizationalStructureSeeder` and assert that 15 members are created with correct hierarchical links across 5 levels.

---

## Out of Scope

- Drag-and-drop frontend UI components (backend contract only).
- Cross-group parent-child links (all parent-child relationships exist within the same `ContentDetailOrganizationalStructureGroup`).
- Historical versioning / audit logs of previous tree structures.

---

## Further Notes

- Based on ADR 0003 (`docs/adr/0003-organizational-structure-parent-child-adjacency.md`).
- Adheres to existing monorepo coding guidelines and response formatting conventions.
