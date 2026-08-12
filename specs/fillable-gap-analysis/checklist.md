# $fillable Gap Analysis & Fix Checklist

> Pilot: **content-service** (COMPLETED 2026-08-10)
> Root cause: `$guarded → $fillable` refactor introduced wrong column names in `$fillable` arrays
> Reference: `migration-service/database/migrations/` (all table schemas)

---

## Cross-Service Audit Guide

Each service needs the same 4-gap analysis. Execute in order.

---

### G1. Model `$fillable` vs DB Columns

- [ ] Read ALL migration files for the service's tables (from `migration-service/database/migrations/`)
- [ ] List actual DB columns per table
- [ ] Compare to model `$fillable` arrays
- [ ] Flag: missing columns, wrong column names, phantom columns
- [ ] Fix `$fillable` to match actual DB schema

**Verification (per model):**
```bash
# For each model, verify fillable matches DB schema
php artisan migrate:status
# Cross-reference with model $fillable arrays
```

---

### G2. Mass-Assignment Usage

- [ ] Find all `::create(`, `->fill(`, `->update(`, `->updateOrCreate(`, `->firstOrCreate(` in services
- [ ] For each, trace the payload keys being passed
- [ ] Verify ALL payload keys are in model's `$fillable`
- [ ] Fix any mismatches

**Verification:**
```bash
rg "::create\(" app/Services/ --include "*.php" -n
rg "->fill\(" app/Services/ --include "*.php" -n
rg "->updateOrCreate\(" app/Services/ --include "*.php" -n
rg "->firstOrCreate\(" app/Services/ --include "*.php" -n
```

---

### G3. DTO Properties vs FormRequest Rules

- [ ] For each DTO: list all declared properties
- [ ] For each FormRequest: list all validated keys
- [ ] Flag: DTO property NOT in FormRequest rules → never reaches DTO via `$request->validated()`
- [ ] Flag: FormRequest rule key NOT in DTO constructor → silently dropped
- [ ] Fix DTOs to declare all needed properties
- [ ] Fix FormRequests to validate all needed fields

**Verification:**
```bash
# For each entity, compare:
rg "function rules" app/Http/Requests/ -A 20
# vs DTO constructor properties
```

---

### G4. DTO `toArray()` Consumers

- [ ] Find all `$dto->toArray()` calls in services
- [ ] Verify DTO declares ALL keys the consumer expects
- [ ] Flag missing keys → silently returned as null
- [ ] Fix DTOs to declare all consumer-expected keys

---

## content-service Implementation

### Phase 1: Fix Detail Content Models (SaveContentsService mass-assignment)

| Model | Status | Fix |
|---|---|---|
| ContentDetailText | ✅ | `$fillable: ['content_id', 'content', 'created_by', 'updated_by']` |
| ContentDetailCardLink | ✅ | `$fillable: ['content_id', 'title', 'color_code', 'link', 'order', 'created_by', 'updated_by']` |
| ContentDetailImageLink | ✅ | `$fillable: ['content_id', 'title', 'image_path', 'link', 'order', 'created_by', 'updated_by']` |
| ContentDetailImageCarousel | ✅ | `$fillable: ['content_id', 'title', 'image_path', 'description', 'order', 'link', 'created_by', 'updated_by']` |
| ContentDetailVideoCarousel | ✅ | `$fillable: ['content_id', 'title', 'type', 'url', 'video_path', 'order', 'created_by', 'updated_by']` |
| ContentDetailAccordionList | ✅ | `$fillable: ['content_detail_accordion_group_id', 'title', 'description', 'file_path', 'link', 'order', 'created_by', 'updated_by']` |

### Phase 2: Fix Additional Content Models (SaveContentsService mass-assignment)

| Model | Status | Fix |
|---|---|---|
| ContentAdditionalImage | ✅ | `$fillable: ['content_type', 'content_id', 'image_path', 'description', 'order', 'created_by', 'updated_by']` |
| ContentAdditionalVideo | ✅ | `$fillable: ['content_type', 'content_id', 'title', 'type', 'url', 'video_path', 'order', 'created_by', 'updated_by']` |
| ContentAdditionalFile | ✅ | `$fillable: ['content_type', 'content_id', 'title', 'file_path', 'description', 'order', 'created_by', 'updated_by']` |
| ContentAdditionalLink | ✅ | `$fillable: ['content_type', 'content_id', 'title', 'link', 'order', 'created_by', 'updated_by']` |

### Phase 3: Fix Primary Entity Models (individual assignment — $fillable for consistency)

| Model | Status | Fix |
|---|---|---|
| ContentDetailPublication | ✅ | Added: `slug, headline, description, is_publicated, thumbnail_path, publish_date, has_additional_image, has_additional_video, has_additional_file, has_additional_link, has_additional_text, layout_id, is_slider, kategori_content_id` |
| ContentDetailGallery | ✅ | Added: `slug, publish_date, is_publicated, layout_id`. Removed: `date` (not a column) |
| ContentDetailRepository | ✅ | Added: `slug, file_path, thumbnail_path, publish_date, is_publicated, layout_id`. Fixed: `file_url→file_path`, removed `date` |
| ContentDetailAgenda | ✅ | Replaced: `['content_id', 'is_range', 'datetime_start', 'datetime_end', 'event_name', 'is_active', 'created_by', 'updated_by']` |
| LatestInformation | ✅ | Replaced: `['title', 'publish_date', 'url', 'is_active', 'created_by', 'updated_by']`. Removed phantom: `description, image_url, date, order` |
| ContentDetailGalleryList | ✅ | Replaced: `['content_detail_gallery_id', 'title', 'description', 'image_path', 'video_type', 'video_path', 'video_url', 'order', 'created_by', 'updated_by']` |

### Phase 4: Fix DTO + FormRequest Gaps

| Gap | Status | Fix |
|---|---|---|
| GalleryDTO: missing `deleted_content_detail_id` | ✅ | Added property + constructor mapping |
| QuickLink FormRequests: `order` not validated | ✅ | Added `order` rule to Create + Update |

### Phase 5: Fix Organizational Structure Models (discovered during review)

| Model | Status | Fix |
|---|---|---|
| ContentDetailOrganizationalStructureGroup | ✅ | Fixed: `title→name` |
| ContentDetailOrganizationalStructureMember | ✅ | Fixed: `group_id→content_detail_organizational_structure_group_id`, `image_url→image_path`. Added: `level, description, is_child, have_child, parent_id` |

---

## Services Audit Queue

| Service | Models | Status |
|---|---|---|
| **content-service** | 33 | ✅ Completed |
| **public-content-service** | 38 | Pending |
| **user-service** | 10+ | Pending |
| **master-service** | 15+ | Pending |
| **file-service** | ~5 | Pending |
| **setting-service** | ~3 | Pending |
| **migration-service** | ~5 | Pending |

---

## Completed Services

- ✅ **content-service** — 18 models fixed, 1 DTO fixed, 2 FormRequests fixed (2026-08-11)
