# Content Service API Contracts

**Service**: `content-service`
**Base Path**: `/content-service/api/` (via Nginx)
**Auth**: JWT (`admin` guard) for admin routes
**Response Envelope**: `{success: bool, message: string, data: mixed|null, errors: mixed|null}`
**Pagination Shape**: `{data: [...], meta: {page: int, limit: int, total: int, total_page: int}}`

## Health

### GET /health
- **Auth**: None
- **Response**: `{status: "ok", message: "...", time: string}`
- **Status**: 200

## Menu

### GET menu/
- **Auth**: JWT (admin)
- **Query**: `search`, `sort_by`, `sort_order`, `page`, `limit`
- **Response**: Paginated list
- **Status**: 200

### POST menu/
- **Auth**: JWT (admin)
- **Body**: `{title, slug, is_active, ...}`
- **Response**: `{success: true, message: string, data: {object}}`
- **Status**: 201

### GET menu/{id}
- **Auth**: JWT (admin)
- **Response**: `{success: true, message: string, data: {object}}`
- **Status**: 200

### PUT menu/{id}
- **Auth**: JWT (admin)
- **Body**: `{title, slug, is_active, ...}`
- **Response**: `{success: true, message: string, data: {object}}`
- **Status**: 200

### DELETE menu/{id}
- **Auth**: JWT (admin)
- **Response**: `{success: true, message: string, data: ""}`
- **Status**: 200

## Sub Menu

### GET sub-menu/
- **Auth**: JWT (admin)
- **Query**: `search`, `sort_by`, `sort_order`, `page`, `limit`
- **Response**: Paginated list
- **Status**: 200

### POST sub-menu/
- **Auth**: JWT (admin)
- **Body**: `{title, menu_id, ...}`
- **Response**: `{success: true, message: string, data: {object}}`
- **Status**: 201

### GET sub-menu/{id}
- **Auth**: JWT (admin)
- **Response**: `{success: true, message: string, data: {object}}`
- **Status**: 200

### PUT sub-menu/{id}
- **Auth**: JWT (admin)
- **Body**: `{title, menu_id, ...}`
- **Response**: `{success: true, message: string, data: {object}}`
- **Status**: 200

### DELETE sub-menu/{id}
- **Auth**: JWT (admin)
- **Response**: `{success: true, message: string, data: ""}`
- **Status**: 200

## Child Sub Menu

### GET child-sub-menu/
- **Auth**: JWT (admin)
- **Query**: `search`, `sort_by`, `sort_order`, `page`, `limit`
- **Response**: Paginated list
- **Status**: 200

### POST child-sub-menu/
- **Auth**: JWT (admin)
- **Body**: `{title, sub_menu_id, ...}`
- **Response**: `{success: true, message: string, data: {object}}`
- **Status**: 201

### GET child-sub-menu/{id}
- **Auth**: JWT (admin)
- **Response**: `{success: true, message: string, data: {object}}`
- **Status**: 200

### PUT child-sub-menu/{id}
- **Auth**: JWT (admin)
- **Body**: `{title, sub_menu_id, ...}`
- **Response**: `{success: true, message: string, data: {object}}`
- **Status**: 200

### DELETE child-sub-menu/{id}
- **Auth**: JWT (admin)
- **Response**: `{success: true, message: string, data: ""}`
- **Status**: 200

## Publication

### GET publication/side-menu
- **Auth**: JWT (admin)
- **Response**: `{success: true, message: string, data: {...}}`
- **Status**: 200

### GET publication/list/{contentId}
- **Auth**: JWT (admin)
- **Query**: `search`, `sort_by`, `sort_order`, `page`, `limit`
- **Response**: Paginated list
- **Status**: 200

### POST publication/
- **Auth**: JWT (admin)
- **Body**: Publication fields
- **Response**: `{success: true, message: string, data: {object}}`
- **Status**: 201

### GET publication/{id}
- **Auth**: JWT (admin)
- **Response**: `{success: true, message: string, data: {object}}`
- **Status**: 200

### PUT publication/{id}
- **Auth**: JWT (admin)
- **Body**: Publication fields
- **Response**: `{success: true, message: string, data: {object}}`
- **Status**: 200

### DELETE publication/{id}
- **Auth**: JWT (admin)
- **Response**: `{success: true, message: string, data: ""}`
- **Status**: 200

## Gallery

### GET gallery/side-menu
- **Auth**: JWT (admin)
- **Response**: `{success: true, message: string, data: {...}}`
- **Status**: 200

### GET gallery/list/{contentId}
- **Auth**: JWT (admin)
- **Query**: `search`, `sort_by`, `sort_order`, `page`, `limit`
- **Response**: Paginated list
- **Status**: 200

### POST gallery/
- **Auth**: JWT (admin)
- **Body**: Gallery fields
- **Response**: `{success: true, message: string, data: {object}}`
- **Status**: 201

### GET gallery/{id}
- **Auth**: JWT (admin)
- **Response**: `{success: true, message: string, data: {object}}`
- **Status**: 200

### PUT gallery/{id}
- **Auth**: JWT (admin)
- **Body**: Gallery fields
- **Response**: `{success: true, message: string, data: {object}}`
- **Status**: 200

### DELETE gallery/{id}
- **Auth**: JWT (admin)
- **Response**: `{success: true, message: string, data: ""}`
- **Status**: 200

## Repository

### GET repository/side-menu
- **Auth**: JWT (admin)
- **Response**: `{success: true, message: string, data: {...}}`
- **Status**: 200

### GET repository/list/{contentId}
- **Auth**: JWT (admin)
- **Query**: `search`, `sort_by`, `sort_order`, `page`, `limit`
- **Response**: Paginated list
- **Status**: 200

### POST repository/
- **Auth**: JWT (admin)
- **Body**: Repository fields
- **Response**: `{success: true, message: string, data: {object}}`
- **Status**: 201

### GET repository/{id}
- **Auth**: JWT (admin)
- **Response**: `{success: true, message: string, data: {object}}`
- **Status**: 200

### PUT repository/{id}
- **Auth**: JWT (admin)
- **Body**: Repository fields
- **Response**: `{success: true, message: string, data: {object}}`
- **Status**: 200

### DELETE repository/{id}
- **Auth**: JWT (admin)
- **Response**: `{success: true, message: string, data: ""}`
- **Status**: 200

## Agenda

### GET agenda/side-menu
- **Auth**: JWT (admin)
- **Response**: `{success: true, message: string, data: {...}}`
- **Status**: 200

### GET agenda/list/{contentId}
- **Auth**: JWT (admin)
- **Query**: `search`, `sort_by`, `sort_order`, `page`, `limit`
- **Response**: Paginated list
- **Status**: 200

### POST agenda/
- **Auth**: JWT (admin)
- **Body**: Agenda fields
- **Response**: `{success: true, message: string, data: {object}}`
- **Status**: 201

### GET agenda/{id}
- **Auth**: JWT (admin)
- **Response**: `{success: true, message: string, data: {object}}`
- **Status**: 200

### PUT agenda/{id}
- **Auth**: JWT (admin)
- **Body**: Agenda fields
- **Response**: `{success: true, message: string, data: {object}}`
- **Status**: 200

### DELETE agenda/{id}
- **Auth**: JWT (admin)
- **Response**: `{success: true, message: string, data: ""}`
- **Status**: 200

## Home (Content Type)

### GET home/
- **Auth**: JWT (admin)
- **Query**: `search`, `sort_by`, `sort_order`, `page`, `limit`
- **Response**: Paginated list
- **Status**: 200

### POST home/
- **Auth**: JWT (admin)
- **Body**: Home content fields
- **Response**: `{success: true, message: string, data: {object}}`
- **Status**: 201

### GET home/{id}
- **Auth**: JWT (admin)
- **Response**: `{success: true, message: string, data: {object}}`
- **Status**: 200

### PUT home/{id}
- **Auth**: JWT (admin)
- **Body**: Home content fields
- **Response**: `{success: true, message: string, data: {object}}`
- **Status**: 200

## Latest Information

### GET latest-information/
- **Auth**: JWT (admin)
- **Query**: `search`, `sort_by`, `sort_order`, `page`, `limit`
- **Response**: Paginated list
- **Status**: 200

### POST latest-information/
- **Auth**: JWT (admin)
- **Body**: Latest info fields
- **Response**: `{success: true, message: string, data: {object}}`
- **Status**: 201

### GET latest-information/{id}
- **Auth**: JWT (admin)
- **Response**: `{success: true, message: string, data: {object}}`
- **Status**: 200

### PUT latest-information/{id}
- **Auth**: JWT (admin)
- **Body**: Latest info fields
- **Response**: `{success: true, message: string, data: {object}}`
- **Status**: 200

### DELETE latest-information/{id}
- **Auth**: JWT (admin)
- **Response**: `{success: true, message: string, data: ""}`
- **Status**: 200

## Quick Link

### GET quick-link/
- **Auth**: JWT (admin)
- **Query**: `search`, `sort_by`, `sort_order`, `page`, `limit`
- **Response**: Paginated list
- **Status**: 200

### POST quick-link/
- **Auth**: JWT (admin)
- **Body**: `{title, url, ...}`
- **Response**: `{success: true, message: string, data: {object}}`
- **Status**: 201

### GET quick-link/{id}
- **Auth**: JWT (admin)
- **Response**: `{success: true, message: string, data: {object}}`
- **Status**: 200

### PUT quick-link/{id}
- **Auth**: JWT (admin)
- **Body**: `{title, url, ...}`
- **Response**: `{success: true, message: string, data: {object}}`
- **Status**: 200

### DELETE quick-link/{id}
- **Auth**: JWT (admin)
- **Response**: `{success: true, message: string, data: ""}`
- **Status**: 200

## Content (List)

### GET content
- **Auth**: JWT (admin)
- **Query**: `type`, `is_show_home`, `is_active`
- **Response**: `{success: true, message: string, data: {content list}}`
- **Status**: 200

## Dashboard

### GET jumlah-perangkat
- **Auth**: JWT (admin)
- **Response**: `{success: true, message: string, data: {...}}`
- **Status**: 200

### GET grafik-pengunjung
- **Auth**: JWT (admin)
- **Response**: `{success: true, message: string, data: {...}}`
- **Status**: 200