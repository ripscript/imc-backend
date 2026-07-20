# File Service API Contracts

**Service**: `file-service`
**Base Path**: `/file-service/api/` (via Nginx)
**Auth**: JWT (`admin` guard)
**Response Envelope**: `{success: bool, message: string, data: mixed|null, errors: mixed|null}`

## Health

### GET /health
- **Auth**: None
- **Response**: `{status: "ok", message: "...", time: string}`
- **Status**: 200

## File Operations

### GET show
- **Auth**: JWT (admin)
- **Query**: `path`, `disk`
- **Response**: File stream
- **Status**: 200

### GET download
- **Auth**: JWT (admin)
- **Query**: `path`, `disk`
- **Response**: File download
- **Status**: 200

### POST base64
- **Auth**: JWT (admin)
- **Body**: `{path, disk}`
- **Response**: `{success: true, message: string, data: {base64, mime_type}}`
- **Status**: 200

### POST upload
- **Auth**: JWT (admin)
- **Body**: Multipart file upload
- **Response**: `{success: true, message: string, data: {path, url, ...}}`
- **Status**: 200

### POST compress-image
- **Auth**: JWT (admin)
- **Body**: Multipart image upload
- **Response**: `{success: true, message: string, data: {path, url, ...}}`
- **Status**: 200

### POST lock-pdf
- **Auth**: JWT (admin)
- **Body**: Multipart PDF upload + password
- **Response**: `{success: true, message: string, data: {path, url, ...}}`
- **Status**: 200

### POST lock-image
- **Auth**: JWT (admin)
- **Body**: Multipart image upload + password
- **Response**: `{success: true, message: string, data: {path, url, ...}}`
- **Status**: 200