# Public Content Service API Contracts

**Service**: `public-content-service`
**Base Path**: `/public-content-service/api/` (via Nginx)
**Auth**: None (public endpoints)
**Response Envelope**: `{success: bool, message: string, data: mixed|null, errors: mixed|null}`
**Pagination Shape**: `{data: [...], meta: {page: int, limit: int, total: int, total_page: int}}`

## Health

### GET /health
- **Auth**: None
- **Response**: `{status: "ok", message: "...", time: string}`
- **Status**: 200

## Menu

### GET menu
- **Auth**: None
- **Response**: `{success: true, message: string, data: {menu tree}}`
- **Status**: 200

## Site Setting

### GET site-setting
- **Auth**: None
- **Response**: `{success: true, message: string, data: {settings}}`
- **Status**: 200

## Quick Link

### GET quick-link
- **Auth**: None
- **Response**: `{success: true, message: string, data: {links}}`
- **Status**: 200

## Visitor

### POST visitor
- **Auth**: None
- **Body**: `{ip_address, user_agent, page, ...}`
- **Response**: `{success: true, message: string, data: {object}}`
- **Status**: 201

## Home

### GET home/
- **Auth**: None
- **Response**: `{success: true, message: string, data: {home content}}`
- **Status**: 200

### GET home/latest-information
- **Auth**: None
- **Response**: `{success: true, message: string, data: {latest info}}`
- **Status**: 200

### GET home/summary-permohonan
- **Auth**: None
- **Response**: `{success: true, message: string, data: {summary}}`
- **Status**: 200

### GET home/sifat-informasi
- **Auth**: None
- **Response**: `{success: true, message: string, data: {info types}}`
- **Status**: 200

### GET home/{id}
- **Auth**: None
- **Response**: `{success: true, message: string, data: {detail}}`
- **Status**: 200

## Content

### GET content/
- **Auth**: None
- **Query**: `type`, `menu_id`, `sub_menu_id`, `child_sub_menu_id`, `page`, `limit`
- **Response**: Paginated list
- **Status**: 200

### GET content/publication
- **Auth**: None
- **Query**: `slug`, `id`
- **Response**: `{success: true, message: string, data: {publication detail}}`
- **Status**: 200

### GET content/repository
- **Auth**: None
- **Query**: `slug`, `id`
- **Response**: `{success: true, message: string, data: {repository detail}}`
- **Status**: 200

### GET content/gallery
- **Auth**: None
- **Query**: `slug`, `id`
- **Response**: `{success: true, message: string, data: {gallery detail}}`
- **Status**: 200

### GET content/search
- **Auth**: None
- **Query**: `q` (search keyword), `page`, `limit`
- **Response**: Paginated search results
- **Status**: 200

## Chatbot

### POST chatbot/
- **Auth**: None
- **Body**: `{message, session_id, ...}`
- **Response**: `{success: true, message: string, data: {reply}}`
- **Status**: 200

### POST chatbot/start
- **Auth**: None
- **Body**: `{...}`
- **Response**: `{success: true, message: string, data: {session}}`
- **Status**: 200

### POST chatbot/end
- **Auth**: None
- **Body**: `{session_id}`
- **Response**: `{success: true, message: string, data: null}`
- **Status**: 200

### GET chatbot/history/{uuid}
- **Auth**: None
- **Response**: `{success: true, message: string, data: {history}}`
- **Status**: 200

### GET chatbot/test-websocket
- **Auth**: None
- **Response**: Test response
- **Status**: 200