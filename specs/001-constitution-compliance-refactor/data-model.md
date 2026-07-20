# Data Model: Constitution Compliance Refactor

**Phase**: 1 — Design & Contracts
**Date**: 2026-07-20
**Spec**: [spec.md](spec.md)

## Overview

This document inventories all Eloquent models across the 6 active services, organized by service. For each model, it documents:
- Current `$fillable`/`$guarded` status
- Fields that are manually assigned in controllers (candidates for `$fillable`)
- Relationships to other models
- Whether a Policy is needed (CRUD endpoints exist)
- Whether a factory is needed

## user-service Models

### User
- **Table**: `users`
- **$fillable**: EXISTS (check existing model)
- **Relationships**: belongsTo(MsRole), belongsTo(MsSatuanKerja), belongsTo(MsSubSatuanKerja), belongsTo(MsPerwakilan)
- **Policy needed**: YES (CRUD endpoints)
- **Factory needed**: EXISTS (UserFactory)
- **Manual assignments in controllers**: email, username, name, password, role_id, gender, phone_number, status, created_by, updated_by, perwakilan_id, satuan_kerja_id, sub_satuan_kerja_id

### Pemohon
- **Table**: `pemohons`
- **$fillable**: MISSING
- **Relationships**: belongsTo(MsClassificationPemohon)
- **Policy needed**: YES (CRUD + admin management)
- **Factory needed**: YES
- **Manual assignments in controllers**: (from PemohonController and PemohonAuthController)

### MsRole
- **Table**: `ms_roles`
- **$fillable**: MISSING
- **Relationships**: hasMany(User)
- **Policy needed**: NO (read-only via RoleController)
- **Factory needed**: YES

### MsSatuanKerja
- **Table**: `ms_satuan_kerja`
- **$fillable**: EXISTS (check existing)
- **Relationships**: hasMany(User), hasMany(MsSubSatuanKerja)
- **Policy needed**: NO (read-only in user-service; CRUD in master-service)
- **Factory needed**: YES

### MsSubSatuanKerja
- **Table**: `ms_sub_satuan_kerja`
- **$fillable**: EXISTS (check existing)
- **Relationships**: belongsTo(MsSatuanKerja)
- **Policy needed**: NO
- **Factory needed**: YES

### MsPerwakilan
- **Table**: `ms_perwakilan`
- **$fillable**: MISSING
- **Relationships**: hasMany(User)
- **Policy needed**: NO (read-only in user-service; CRUD in master-service)
- **Factory needed**: YES

### MsClassificationPemohon
- **Table**: `ms_classification_pemohon`
- **$fillable**: MISSING
- **Relationships**: hasMany(Pemohon)
- **Policy needed**: NO
- **Factory needed**: YES

### TemplateEmail
- **Table**: `template_emails`
- **$fillable**: MISSING
- **Relationships**: none
- **Policy needed**: NO
- **Factory needed**: YES

### ChatLog
- **Table**: `chat_logs`
- **$fillable**: EXISTS (check existing)
- **Relationships**: belongsTo(ChatSession)
- **Policy needed**: NO
- **Factory needed**: YES

### ChatSession
- **Table**: `chat_sessions`
- **$fillable**: EXISTS (check existing)
- **Relationships**: hasMany(ChatLog)
- **Policy needed**: NO
- **Factory needed**: YES

## master-service Models

### MsFaq
- **Table**: `ms_faq`
- **$fillable**: MISSING
- **Relationships**: belongsTo(MsKategoriFaq)
- **Policy needed**: YES (CRUD endpoints)
- **Factory needed**: YES
- **Manual assignments in controllers**: title, description, is_active, kategori_faq_id, created_by, updated_by

### MsKategoriFaq
- **Table**: `ms_kategori_faq`
- **$fillable**: MISSING
- **Relationships**: hasMany(MsFaq)
- **Policy needed**: YES (CRUD endpoints)
- **Factory needed**: YES

### MsSifatInformasi
- **Table**: `ms_sifat_informasi`
- **$fillable**: MISSING
- **Relationships**: none
- **Policy needed**: YES (CRUD endpoints)
- **Factory needed**: YES

### MsTag
- **Table**: `ms_tag`
- **$fillable**: MISSING
- **Relationships**: many-to-many with Content via ContentTag
- **Policy needed**: YES (CRUD endpoints)
- **Factory needed**: YES

### MsKategoriContent
- **Table**: `ms_kategori_content`
- **$fillable**: MISSING
- **Relationships**: hasMany(Content)
- **Policy needed**: YES (CRUD endpoints)
- **Factory needed**: YES

### MsVariableTemplateEmail
- **Table**: `ms_variable_template_email`
- **$fillable**: MISSING
- **Relationships**: none
- **Policy needed**: NO (read-only)
- **Factory needed**: YES

### MsRole (master-service)
- **Table**: `ms_roles`
- **$fillable**: MISSING
- **Relationships**: hasMany(User)
- **Policy needed**: NO (read-only)
- **Factory needed**: YES

### Pemohon (master-service)
- **Table**: `pemohons`
- **$fillable**: MISSING
- **Relationships**: none
- **Policy needed**: NO
- **Factory needed**: YES

### Negara
- **Table**: `negara`
- **$fillable**: EXISTS (check existing)
- **Relationships**: none
- **Policy needed**: YES (CRUD endpoints)
- **Factory needed**: YES

### Perwakilan
- **Table**: `ms_perwakilan`
- **$fillable**: EXISTS (check existing)
- **Relationships**: none
- **Policy needed**: YES (CRUD endpoints)
- **Factory needed**: YES

### SatuanKerja
- **Table**: `ms_satuan_kerja`
- **$fillable**: EXISTS (check existing)
- **Relationships**: hasMany(MsSubSatuanKerja)
- **Policy needed**: YES (CRUD endpoints)
- **Factory needed**: YES

### Holiday
- **Table**: `holidays`
- **$fillable**: EXISTS (check existing)
- **Relationships**: none
- **Policy needed**: YES (CRUD endpoints)
- **Factory needed**: YES

### SurveyQuestion
- **Table**: `survey_questions`
- **$fillable**: EXISTS (check existing)
- **Relationships**: none
- **Policy needed**: YES (CRUD endpoints)
- **Factory needed**: YES

### AlasanKeberatan
- **Table**: `alasan_keberatan`
- **$fillable**: EXISTS (check existing)
- **Relationships**: none
- **Policy needed**: YES (CRUD endpoints, except delete)
- **Factory needed**: YES

## content-service Models

### Content
- **Table**: `contents`
- **$fillable**: EXISTS (check existing)
- **Relationships**: morphTo parent, hasMany(ContentDetail*), hasMany(ContentAdditional*), hasMany(ContentTag)
- **Policy needed**: YES (CRUD via multiple controllers)
- **Factory needed**: YES

### Menu
- **Table**: `menus`
- **$fillable**: EXISTS (check existing)
- **Relationships**: hasMany(SubMenu)
- **Policy needed**: YES (CRUD endpoints)
- **Factory needed**: YES

### SubMenu
- **Table**: `sub_menus`
- **$fillable**: EXISTS (check existing)
- **Relationships**: belongsTo(Menu), hasMany(ChildSubMenu)
- **Policy needed**: YES (CRUD endpoints)
- **Factory needed**: YES

### ChildSubMenu
- **Table**: `child_sub_menus`
- **$fillable**: EXISTS (check existing)
- **Relationships**: belongsTo(SubMenu)
- **Policy needed**: YES (CRUD endpoints)
- **Factory needed**: YES

### LatestInformation
- **Table**: `latest_information`
- **$fillable**: EXISTS (check existing)
- **Relationships**: none
- **Policy needed**: YES (CRUD endpoints)
- **Factory needed**: YES

### QuickLink
- **Table**: `quick_links`
- **$fillable**: EXISTS (check existing)
- **Relationships**: none
- **Policy needed**: YES (CRUD endpoints)
- **Factory needed**: YES

### MsTag (content-service)
- **Table**: `ms_tags`
- **$fillable**: MISSING
- **Relationships**: many-to-many with Content
- **Policy needed**: NO
- **Factory needed**: YES

### MsLayout
- **Table**: `ms_layouts`
- **$fillable**: MISSING
- **Relationships**: none
- **Policy needed**: NO
- **Factory needed**: YES

### MsRole (content-service)
- **Table**: `ms_roles`
- **$fillable**: MISSING
- **Relationships**: none
- **Policy needed**: NO
- **Factory needed**: YES

### SiteSetting (content-service)
- **Table**: `site_settings`
- **$fillable**: MISSING
- **Relationships**: none
- **Policy needed**: NO
- **Factory needed**: YES

### ContentDetail* Models (content-service)
- **Tables**: `content_detail_accordion_groups`, `content_detail_accordion_lists`, `content_detail_agendas`, `content_detail_card_links`, `content_detail_consumes`, `content_detail_galleries`, `content_detail_gallery_lists`, `content_detail_image_carousels`, `content_detail_image_links`, `content_detail_organizational_structure_groups`, `content_detail_organizational_structure_members`, `content_detail_publications`, `content_detail_repositories`, `content_detail_texts`, `content_detail_video_carousels`
- **$fillable**: EXISTS (check existing)
- **Relationships**: belongsTo(Content)
- **Policy needed**: NO (managed through Content)
- **Factory needed**: YES

### ContentAdditional* Models (content-service)
- **Tables**: `content_additional_files`, `content_additional_images`, `content_additional_links`, `content_additional_texts`, `content_additional_videos`
- **$fillable**: EXISTS (check existing)
- **Relationships**: belongsTo(Content)
- **Policy needed**: NO (managed through Content)
- **Factory needed**: YES

### ContentTag
- **Table**: `content_tags`
- **$fillable**: EXISTS (check existing)
- **Relationships**: belongsTo(Content), belongsTo(MsTag)
- **Policy needed**: NO
- **Factory needed**: YES

## public-content-service Models

### Content (public-content-service)
- **Table**: `contents`
- **$fillable**: MISSING
- **Relationships**: same as content-service (read-only mirror)
- **Policy needed**: NO (public endpoints)
- **Factory needed**: YES

### Menu (public-content-service)
- **Table**: `menus`
- **$fillable**: MISSING
- **Relationships**: hasMany(SubMenu)
- **Policy needed**: NO (public endpoints)
- **Factory needed**: YES

### SubMenu (public-content-service)
- **Table**: `sub_menus`
- **$fillable**: MISSING
- **Relationships**: belongsTo(Menu), hasMany(ChildSubMenu)
- **Policy needed**: NO (public endpoints)
- **Factory needed**: YES

### ChildSubMenu (public-content-service)
- **Table**: `child_sub_menus`
- **$fillable**: MISSING
- **Relationships**: belongsTo(SubMenu)
- **Policy needed**: NO (public endpoints)
- **Factory needed**: YES

### LatestInformation (public-content-service)
- **Table**: `latest_information`
- **$fillable**: MISSING
- **Relationships**: none
- **Policy needed**: NO (public endpoints)
- **Factory needed**: YES

### QuickLink (public-content-service)
- **Table**: `quick_links`
- **$fillable**: MISSING
- **Relationships**: none
- **Policy needed**: NO (public endpoints)
- **Factory needed**: YES

### SiteSetting (public-content-service)
- **Table**: `site_settings`
- **$fillable**: MISSING
- **Relationships**: none
- **Policy needed**: NO (public endpoints)
- **Factory needed**: YES

### ChatSetting (public-content-service)
- **Table**: `chat_settings`
- **$fillable**: MISSING
- **Relationships**: none
- **Policy needed**: NO (public endpoints)
- **Factory needed**: YES

### Pemohon (public-content-service)
- **Table**: `pemohons`
- **$fillable**: MISSING
- **Relationships**: none
- **Policy needed**: NO (public endpoints)
- **Factory needed**: YES

### All ContentDetail* and ContentAdditional* Models (public-content-service)
- **$fillable**: MISSING (all)
- **Policy needed**: NO (public endpoints)
- **Factory needed**: YES

## setting-service Models

### TemplateEmail (setting-service)
- **Table**: `template_emails`
- **$fillable**: EXISTS (check existing)
- **Relationships**: none
- **Policy needed**: YES (CRUD endpoints)
- **Factory needed**: YES

### SiteSetting (setting-service)
- **Table**: `site_settings`
- **$fillable**: EXISTS (check existing)
- **Relationships**: none
- **Policy needed**: YES (CRUD endpoints)
- **Factory needed**: YES

### ChatSetting (setting-service)
- **Table**: `chat_settings`
- **$fillable**: EXISTS (check existing)
- **Relationships**: none
- **Policy needed**: YES (CRUD endpoints)
- **Factory needed**: YES

### MsRole (setting-service)
- **Table**: `ms_roles`
- **$fillable**: MISSING
- **Relationships**: none
- **Policy needed**: NO
- **Factory needed**: YES

## file-service Models

- **Note**: File service has no Eloquent models. It handles file operations directly.

## Summary

| Metric | Count |
|--------|-------|
| Total models across all services | ~60+ |
| Models missing $fillable | ~50 |
| Models needing Policy (CRUD endpoints) | ~25 |
| Models needing factory | ~55 |
| Models with existing factory | 6 (UserFactory only) |