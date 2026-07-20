# User Service API Contracts

**Service**: `user-service`
**Base Path**: `/user-service/api/` (via Nginx)
**Auth**: JWT (`admin` guard) for admin routes; JWT (`pemohon` guard) for pemohon routes
**Response Envelope**: `{success: bool, message: string, data: mixed|null, errors: mixed|null}`
**Pagination Shape**: `{data: [...], meta: {page: int, limit: int, total: int, total_page: int}}`

## Health

### GET /health
- **Auth**: None
- **Response**: `{status: "ok", message: "User service berhasil diakses", time: string}`
- **Status**: 200

## Admin Auth

### POST auth/login
- **Auth**: None
- **Body**: `{email: string, password: string}`
- **Response**: `{success: true, message: string, data: {token: string, user: {...}}}`
- **Status**: 200

### POST auth/logout
- **Auth**: JWT (admin)
- **Middleware**: `jwt.error:admin`
- **Response**: `{success: true, message: string, data: null}`
- **Status**: 200

### GET auth/profile
- **Auth**: JWT (admin)
- **Middleware**: `jwt.error:admin`
- **Response**: `{success: true, message: string, data: {user object}}`
- **Status**: 200

## User Management

### GET user/
- **Auth**: JWT (admin)
- **Middleware**: `jwt.error:admin`, `check.role:super_admin,admin_pusat,admin_pustik`
- **Query**: `search`, `sort_by`, `sort_order`, `page`, `limit`
- **Response**: Paginated list with `{data: [...], meta: {...}}`
- **Status**: 200

### POST user/
- **Auth**: JWT (admin)
- **Middleware**: `jwt.error:admin`, `check.role:super_admin,admin_pusat,admin_pustik`
- **Body**: `{email, username, name, password, role_id, gender, phone_number?, status, satuan_kerja_id?, sub_satuan_kerja_id?, perwakilan_id?}`
- **Validation**: email unique (soft delete), username unique (soft delete), password min:8, gender in:1,2
- **Response**: `{success: true, message: string, data: {user object}}`
- **Status**: 201

### GET user/{id}
- **Auth**: JWT (admin)
- **Middleware**: `jwt.error:admin`, `check.role:super_admin,admin_pusat,admin_pustik`
- **Response**: `{success: true, message: string, data: {user object}}`
- **Status**: 200

### PUT user/{id}
- **Auth**: JWT (admin)
- **Middleware**: `jwt.error:admin`, `check.role:super_admin,admin_pusat,admin_pustik`
- **Body**: `{email, username, name, password?, role_id, gender, phone_number?, status, satuan_kerja_id?, sub_satuan_kerja_id?, perwakilan_id?}`
- **Validation**: email unique (soft delete, exclude self), username unique (soft delete, exclude self), password nullable min:8
- **Response**: `{success: true, message: string, data: {user object}}`
- **Status**: 200

### DELETE user/{id}
- **Auth**: JWT (admin)
- **Middleware**: `jwt.error:admin`, `check.role:super_admin,admin_pusat,admin_pustik`
- **Response**: `{success: true, message: string, data: ""}`
- **Status**: 200

## Pemohon Management (Admin)

### GET management-pemohon/
- **Auth**: JWT (admin)
- **Middleware**: `jwt.error:admin`, `check.role:super_admin,admin_pusat,admin_pustik`
- **Query**: `search`, `sort_by`, `sort_order`, `page`, `limit`
- **Response**: Paginated list
- **Status**: 200

### GET management-pemohon/{id}
- **Auth**: JWT (admin)
- **Middleware**: `jwt.error:admin`, `check.role:super_admin,admin_pusat,admin_pustik`
- **Response**: `{success: true, message: string, data: {pemohon object}}`
- **Status**: 200

### PUT management-pemohon/{id}
- **Auth**: JWT (admin)
- **Middleware**: `jwt.error:admin`, `check.role:super_admin,admin_pusat,admin_pustik`
- **Response**: `{success: true, message: string, data: {pemohon object}}`
- **Status**: 200

### POST management-pemohon/resend-verification/{id}
- **Auth**: JWT (admin)
- **Middleware**: `jwt.error:admin`, `check.role:super_admin,admin_pusat,admin_pustik`
- **Response**: `{success: true, message: string, data: null}`
- **Status**: 200

### DELETE management-pemohon/delete/{id}
- **Auth**: JWT (admin)
- **Middleware**: `jwt.error:admin`, `check.role:super_admin,admin_pusat,admin_pustik`
- **Response**: `{success: true, message: string, data: null}`
- **Status**: 200

## Logs

### GET log/activity
- **Auth**: JWT (admin)
- **Middleware**: `jwt.error:admin`, `check.role:super_admin,admin_pusat,admin_pustik`
- **Query**: `search`, `sort_by`, `sort_order`, `page`, `limit`
- **Response**: Paginated list
- **Status**: 200

### GET log/chat
- **Auth**: JWT (admin)
- **Middleware**: `jwt.error:admin`, `check.role:super_admin,admin_pusat,admin_pustik`
- **Response**: Paginated list
- **Status**: 200

### GET log/chat/{session}
- **Auth**: JWT (admin)
- **Middleware**: `jwt.error:admin`, `check.role:super_admin,admin_pusat,admin_pustik`
- **Response**: `{success: true, message: string, data: {...}}`
- **Status**: 200

## Bulk Password

### GET bulk-password/template
- **Auth**: JWT (admin)
- **Middleware**: `jwt.error:admin`, `check.role:super_admin,admin_pusat,admin_pustik`
- **Response**: File download
- **Status**: 200

### POST bulk-password/import
- **Auth**: JWT (admin)
- **Middleware**: `jwt.error:admin`, `check.role:super_admin,admin_pusat,admin_pustik`
- **Body**: Multipart file upload
- **Response**: `{success: true, message: string, data: {...}}`
- **Status**: 200

## Pemohon Auth

### POST pemohon/auth/login
- **Auth**: None
- **Body**: `{email, password}`
- **Response**: `{success: true, message: string, data: {token, user}}`
- **Status**: 200

### POST pemohon/auth/register
- **Auth**: None
- **Body**: Registration fields
- **Response**: `{success: true, message: string, data: {...}}`
- **Status**: 201

### POST pemohon/auth/forgot-password
- **Auth**: None
- **Body**: `{email}`
- **Response**: `{success: true, message: string, data: null}`
- **Status**: 200

### POST pemohon/auth/reset-password
- **Auth**: None
- **Body**: `{token, email, password}`
- **Response**: `{success: true, message: string, data: null}`
- **Status**: 200

### POST pemohon/auth/verify-email
- **Auth**: None
- **Body**: `{email, otp}`
- **Response**: `{success: true, message: string, data: null}`
- **Status**: 200

### GET pemohon/auth/verify-email
- **Auth**: None
- **Query**: `{email}`
- **Response**: `{success: true, message: string, data: {is_verified}}`
- **Status**: 200

### GET pemohon/auth/resend-otp
- **Auth**: None
- **Query**: `{email}`
- **Response**: `{success: true, message: string, data: null}`
- **Status**: 200

### POST pemohon/auth/logout
- **Auth**: JWT (pemohon)
- **Middleware**: `jwt.error:pemohon`
- **Response**: `{success: true, message: string, data: null}`
- **Status**: 200

### GET pemohon/auth/profile
- **Auth**: JWT (pemohon)
- **Middleware**: `jwt.error:pemohon`
- **Response**: `{success: true, message: string, data: {user object}}`
- **Status**: 200

### PUT pemohon/profile
- **Auth**: JWT (pemohon)
- **Middleware**: `jwt.error:pemohon`
- **Body**: Profile fields
- **Response**: `{success: true, message: string, data: {user object}}`
- **Status**: 200