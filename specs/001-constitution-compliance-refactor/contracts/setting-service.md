# Setting Service API Contracts

**Service**: `setting-service`
**Base Path**: `/setting-service/api/` (via Nginx)
**Auth**: JWT (`admin` guard)
**Response Envelope**: `{success: bool, message: string, data: mixed|null, errors: mixed|null}`
**Pagination Shape**: `{data: [...], meta: {page: int, limit: int, total: int, total_page: int}}`

## Health

### GET /health
- **Auth**: None
- **Response**: `{status: "ok", message: "...", time: string}`
- **Status**: 200

## Template Email

### GET template-email/
- **Auth**: JWT (admin)
- **Query**: `search`, `sort_by`, `sort_order`, `page`, `limit`
- **Response**: Paginated list
- **Status**: 200

### PUT template-email/update-all
- **Auth**: JWT (admin)
- **Body**: `{templates: [...]}`
- **Response**: `{success: true, message: string, data: null}`
- **Status**: 200

### GET template-email/{id}
- **Auth**: JWT (admin)
- **Response**: `{success: true, message: string, data: {template}}`
- **Status**: 200

### PUT template-email/{id}
- **Auth**: JWT (admin)
- **Body**: `{subject, body, ...}`
- **Response**: `{success: true, message: string, data: {template}}`
- **Status**: 200

## Site Setting

### GET site-setting/
- **Auth**: JWT (admin)
- **Response**: `{success: true, message: string, data: {settings}}`
- **Status**: 200

### PUT site-setting/
- **Auth**: JWT (admin)
- **Body**: `{key, value, ...}`
- **Response**: `{success: true, message: string, data: {settings}}`
- **Status**: 200

## Chat Setting

### GET chat-setting/
- **Auth**: JWT (admin)
- **Response**: `{success: true, message: string, data: {settings}}`
- **Status**: 200

### PUT chat-setting/
- **Auth**: JWT (admin)
- **Body**: `{key, value, ...}`
- **Response**: `{success: true, message: string, data: {settings}}`
- **Status**: 200