# Master Service API Contracts

**Service**: `master-service`
**Base Path**: `/master-service/api/` (via Nginx)
**Auth**: JWT (`admin` guard) for admin routes
**Response Envelope**: `{success: bool, message: string, data: mixed|null, errors: mixed|null}`
**Pagination Shape**: `{data: [...], meta: {page: int, limit: int, total: int, total_page: int}}`

## Health

### GET /health
- **Auth**: None
- **Response**: `{status: "ok", message: "...", time: string}`
- **Status**: 200

## Negara (Country)

### GET negara/
- **Auth**: JWT (admin)
- **Query**: `search`, `sort_by`, `sort_order`, `page`, `limit`
- **Response**: Paginated list
- **Status**: 200

### POST negara/
- **Auth**: JWT (admin)
- **Body**: `{name, ...}`
- **Response**: `{success: true, message: string, data: {object}}`
- **Status**: 201

### GET negara/{id}
- **Auth**: JWT (admin)
- **Response**: `{success: true, message: string, data: {object}}`
- **Status**: 200

### PUT negara/{id}
- **Auth**: JWT (admin)
- **Body**: `{name, ...}`
- **Response**: `{success: true, message: string, data: {object}}`
- **Status**: 200

### DELETE negara/{id}
- **Auth**: JWT (admin)
- **Response**: `{success: true, message: string, data: ""}`
- **Status**: 200

## Perwakilan (Representative)

### GET perwakilan/
- **Auth**: JWT (admin)
- **Query**: `search`, `sort_by`, `sort_order`, `page`, `limit`
- **Response**: Paginated list
- **Status**: 200

### POST perwakilan/
- **Auth**: JWT (admin)
- **Body**: `{name, type, ...}`
- **Response**: `{success: true, message: string, data: {object}}`
- **Status**: 201

### GET perwakilan/{id}
- **Auth**: JWT (admin)
- **Response**: `{success: true, message: string, data: {object}}`
- **Status**: 200

### PUT perwakilan/{id}
- **Auth**: JWT (admin)
- **Body**: `{name, type, ...}`
- **Response**: `{success: true, message: string, data: {object}}`
- **Status**: 200

### DELETE perwakilan/{id}
- **Auth**: JWT (admin)
- **Response**: `{success: true, message: string, data: ""}`
- **Status**: 200

## Sifat Informasi (Information Type)

### GET sifat-informasi/
- **Auth**: JWT (admin)
- **Query**: `search`, `sort_by`, `sort_order`, `page`, `limit`
- **Response**: Paginated list
- **Status**: 200

### POST sifat-informasi/
- **Auth**: JWT (admin)
- **Body**: `{name, ...}`
- **Response**: `{success: true, message: string, data: {object}}`
- **Status**: 201

### GET sifat-informasi/{id}
- **Auth**: JWT (admin)
- **Response**: `{success: true, message: string, data: {object}}`
- **Status**: 200

### PUT sifat-informasi/{id}
- **Auth**: JWT (admin)
- **Body**: `{name, ...}`
- **Response**: `{success: true, message: string, data: {object}}`
- **Status**: 200

### DELETE sifat-informasi/{id}
- **Auth**: JWT (admin)
- **Response**: `{success: true, message: string, data: ""}`
- **Status**: 200

## Kategori FAQ

### GET kategori-faq/
- **Auth**: JWT (admin)
- **Query**: `search`, `sort_by`, `sort_order`, `page`, `limit`
- **Response**: Paginated list
- **Status**: 200

### POST kategori-faq/
- **Auth**: JWT (admin)
- **Body**: `{name, ...}`
- **Response**: `{success: true, message: string, data: {object}}`
- **Status**: 201

### GET kategori-faq/{id}
- **Auth**: JWT (admin)
- **Response**: `{success: true, message: string, data: {object}}`
- **Status**: 200

### PUT kategori-faq/{id}
- **Auth**: JWT (admin)
- **Body**: `{name, ...}`
- **Response**: `{success: true, message: string, data: {object}}`
- **Status**: 200

### DELETE kategori-faq/{id}
- **Auth**: JWT (admin)
- **Response**: `{success: true, message: string, data: ""}`
- **Status**: 200

## FAQ

### GET faq/
- **Auth**: JWT (admin)
- **Query**: `search`, `sort_by`, `sort_order`, `page`, `limit`
- **Response**: Paginated list
- **Status**: 200

### POST faq/
- **Auth**: JWT (admin)
- **Body**: `{title, description, is_active, kategori_faq_id}`
- **Validation**: title unique (soft delete)
- **Response**: `{success: true, message: string, data: {object}}`
- **Status**: 201

### GET faq/{id}
- **Auth**: JWT (admin)
- **Response**: `{success: true, message: string, data: {object}}`
- **Status**: 200

### PUT faq/{id}
- **Auth**: JWT (admin)
- **Body**: `{title, description, is_active, kategori_faq_id}`
- **Validation**: title unique (soft delete, exclude self)
- **Response**: `{success: true, message: string, data: {object}}`
- **Status**: 200

### DELETE faq/{id}
- **Auth**: JWT (admin)
- **Response**: `{success: true, message: string, data: ""}`
- **Status**: 200

## Satuan Kerja (Work Unit)

### GET satuan-kerja/
- **Auth**: JWT (admin)
- **Query**: `search`, `sort_by`, `sort_order`, `page`, `limit`
- **Response**: Paginated list
- **Status**: 200

### POST satuan-kerja/
- **Auth**: JWT (admin)
- **Body**: `{name, ...}`
- **Response**: `{success: true, message: string, data: {object}}`
- **Status**: 201

### GET satuan-kerja/{id}
- **Auth**: JWT (admin)
- **Response**: `{success: true, message: string, data: {object}}`
- **Status**: 200

### GET satuan-kerja/sub/{id}
- **Auth**: JWT (admin)
- **Response**: `{success: true, message: string, data: {sub units}}`
- **Status**: 200

### PUT satuan-kerja/{id}
- **Auth**: JWT (admin)
- **Body**: `{name, ...}`
- **Response**: `{success: true, message: string, data: {object}}`
- **Status**: 200

### DELETE satuan-kerja/{id}
- **Auth**: JWT (admin)
- **Response**: `{success: true, message: string, data: ""}`
- **Status**: 200

## Kategori Content

### GET kategori-content/
- **Auth**: JWT (admin)
- **Query**: `search`, `sort_by`, `sort_order`, `page`, `limit`
- **Response**: Paginated list
- **Status**: 200

### POST kategori-content/
- **Auth**: JWT (admin)
- **Body**: `{name, ...}`
- **Response**: `{success: true, message: string, data: {object}}`
- **Status**: 201

### GET kategori-content/{id}
- **Auth**: JWT (admin)
- **Response**: `{success: true, message: string, data: {object}}`
- **Status**: 200

### PUT kategori-content/{id}
- **Auth**: JWT (admin)
- **Body**: `{name, ...}`
- **Response**: `{success: true, message: string, data: {object}}`
- **Status**: 200

### DELETE kategori-content/{id}
- **Auth**: JWT (admin)
- **Response**: `{success: true, message: string, data: ""}`
- **Status**: 200

## Tag

### GET tag/
- **Auth**: JWT (admin)
- **Query**: `search`, `sort_by`, `sort_order`, `page`, `limit`
- **Response**: Paginated list
- **Status**: 200

### POST tag/
- **Auth**: JWT (admin)
- **Body**: `{name, ...}`
- **Response**: `{success: true, message: string, data: {object}}`
- **Status**: 201

### GET tag/{id}
- **Auth**: JWT (admin)
- **Response**: `{success: true, message: string, data: {object}}`
- **Status**: 200

### PUT tag/{id}
- **Auth**: JWT (admin)
- **Body**: `{name, ...}`
- **Response**: `{success: true, message: string, data: {object}}`
- **Status**: 200

### DELETE tag/{id}
- **Auth**: JWT (admin)
- **Response**: `{success: true, message: string, data: ""}`
- **Status**: 200

## Alasan Keberatan (Objection Reason)

### GET alasan-keberatan/
- **Auth**: JWT (admin)
- **Query**: `search`, `sort_by`, `sort_order`, `page`, `limit`
- **Response**: Paginated list
- **Status**: 200

### POST alasan-keberatan/
- **Auth**: JWT (admin)
- **Body**: `{name, ...}`
- **Response**: `{success: true, message: string, data: {object}}`
- **Status**: 201

### GET alasan-keberatan/{id}
- **Auth**: JWT (admin)
- **Response**: `{success: true, message: string, data: {object}}`
- **Status**: 200

### PUT alasan-keberatan/{id}
- **Auth**: JWT (admin)
- **Body**: `{name, ...}`
- **Response**: `{success: true, message: string, data: {object}}`
- **Status**: 200

## Holiday

### GET holiday/
- **Auth**: JWT (admin)
- **Query**: `search`, `sort_by`, `sort_order`, `page`, `limit`
- **Response**: Paginated list
- **Status**: 200

### POST holiday/
- **Auth**: JWT (admin)
- **Body**: `{date, name, ...}`
- **Response**: `{success: true, message: string, data: {object}}`
- **Status**: 201

### GET holiday/template-import
- **Auth**: JWT (admin)
- **Response**: File download
- **Status**: 200

### POST holiday/import
- **Auth**: JWT (admin)
- **Body**: Multipart file upload
- **Response**: `{success: true, message: string, data: {...}}`
- **Status**: 200

### GET holiday/{id}
- **Auth**: JWT (admin)
- **Response**: `{success: true, message: string, data: {object}}`
- **Status**: 200

### PUT holiday/{id}
- **Auth**: JWT (admin)
- **Body**: `{date, name, ...}`
- **Response**: `{success: true, message: string, data: {object}}`
- **Status**: 200

### DELETE holiday/{id}
- **Auth**: JWT (admin)
- **Response**: `{success: true, message: string, data: ""}`
- **Status**: 200

## Survey Question

### GET survey-question/
- **Auth**: JWT (admin)
- **Query**: `search`, `sort_by`, `sort_order`, `page`, `limit`
- **Response**: Paginated list
- **Status**: 200

### POST survey-question/
- **Auth**: JWT (admin)
- **Body**: `{question, ...}`
- **Response**: `{success: true, message: string, data: {object}}`
- **Status**: 201

### GET survey-question/{id}
- **Auth**: JWT (admin)
- **Response**: `{success: true, message: string, data: {object}}`
- **Status**: 200

### PUT survey-question/{id}
- **Auth**: JWT (admin)
- **Body**: `{question, ...}`
- **Response**: `{success: true, message: string, data: {object}}`
- **Status**: 200

### DELETE survey-question/{id}
- **Auth**: JWT (admin)
- **Response**: `{success: true, message: string, data: ""}`
- **Status**: 200

## Read-Only Endpoints

### GET role
- **Auth**: JWT (admin)
- **Response**: `{success: true, message: string, data: {roles}}`
- **Status**: 200

### GET variable-template-email
- **Auth**: JWT (admin)
- **Response**: `{success: true, message: string, data: {variables}}`
- **Status**: 200

### GET layout
- **Auth**: JWT (admin)
- **Response**: `{success: true, message: string, data: {layouts}}`
- **Status**: 200

### GET permohonan/akses-data
- **Auth**: JWT (admin)
- **Response**: `{success: true, message: string, data: {...}}`
- **Status**: 200

### GET permohonan/format-data
- **Auth**: JWT (admin)
- **Response**: `{success: true, message: string, data: {...}}`
- **Status**: 200

### GET permohonan/salinan-informasi
- **Auth**: JWT (admin)
- **Response**: `{success: true, message: string, data: {...}}`
- **Status**: 200

### GET list-pertanyaan-survei
- **Auth**: None (public)
- **Response**: `{success: true, message: string, data: {questions}}`
- **Status**: 200

### GET classification-pemohon
- **Auth**: JWT (admin)
- **Response**: `{success: true, message: string, data: {classifications}}`
- **Status**: 200