# Authentication

The Olympic Application API uses Laravel Sanctum for API authentication, providing a simple token-based authentication system.

## Overview

Authentication is required for most API endpoints. The API uses Bearer token authentication with stateless tokens that are stored in the database.

## Authentication Flow

### 1. User Registration
```http
POST /api/auth/register
Content-Type: application/json

{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```

**Response:**
```json
{
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "created_at": "2024-01-01T00:00:00.000000Z",
    "updated_at": "2024-01-01T00:00:00.000000Z"
  },
  "token": "1|abc123def456ghi789jkl012mno345pqr678stu901vwx234yz"
}
```

### 2. User Login
```http
POST /api/auth/login
Content-Type: application/json

{
  "email": "john@example.com",
  "password": "password123"
}
```

**Response:**
```json
{
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "role": "student",
    "created_at": "2024-01-01T00:00:00.000000Z",
    "updated_at": "2024-01-01T00:00:00.000000Z"
  },
  "token": "1|abc123def456ghi789jkl012mno345pqr678stu901vwx234yz"
}
```

### 3. Using the Token
Include the token in the Authorization header for subsequent requests:

```http
GET /api/user
Authorization: Bearer 1|abc123def456ghi789jkl012mno345pqr678stu901vwx234yz
```

### 4. User Logout
```http
POST /api/auth/logout
Authorization: Bearer 1|abc123def456ghi789jkl012mno345pqr678stu901vwx234yz
```

**Response:**
```json
{
  "message": "Successfully logged out"
}
```

## Token Management

### Token Scopes
The API uses token scopes to limit access to specific resources:

- `*`: Full access (default)
- `read`: Read-only access
- `write`: Write access only
- `admin`: Administrative access

### Token Expiration
- Tokens expire after 24 hours by default
- Refresh tokens are not currently implemented
- Users must re-authenticate after token expiration

### Token Revocation
Users can revoke their own tokens:

```http
DELETE /api/auth/tokens/{token_id}
Authorization: Bearer {token}
```

## Role-Based Authentication

The API supports multiple user roles:

### Available Roles
- `admin`: Full system access
- `student`: Student-specific access
- `teacher`: Teacher/instructor access
- `coordinator`: Program coordinator access

### Role Assignment
Roles are assigned in two ways:
1. **Direct assignment**: `role_id` field in users table
2. **Many-to-many relationship**: Through `user_roles` pivot table

### Role Checking
The system checks roles using custom middleware:

```php
// Single role
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    // Admin only routes
});

// Multiple roles
Route::middleware(['auth:sanctum', 'role:admin,teacher'])->group(function () {
    // Admin or teacher routes
});
```

## Security Considerations

### Token Security
- Store tokens securely on the client side
- Never expose tokens in URLs or logs
- Use HTTPS in production
- Implement token rotation if needed

### Rate Limiting
The API implements rate limiting:
- 60 requests per minute for authenticated users
- 10 requests per minute for unauthenticated users
- 5 login attempts per minute per IP

### Session Management
- Each login creates a new token
- Multiple active sessions are supported
- Tokens are automatically cleaned up after expiration

## Error Responses

### Authentication Errors

**401 Unauthorized - Missing Token:**
```json
{
  "message": "Unauthenticated."
}
```

**401 Unauthorized - Invalid Token:**
```json
{
  "message": "Unauthenticated."
}
```

**401 Unauthorized - Expired Token:**
```json
{
  "message": "Token has expired."
}
```

**422 Validation Error:**
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": ["The email field is required."],
    "password": ["The password field is required."]
  }
}
```

**429 Too Many Requests:**
```json
{
  "message": "Too many login attempts. Please try again later."
}
```

## Testing Authentication

### Using cURL
```bash
# Login
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"john@example.com","password":"password123"}'

# Use token
curl -X GET http://localhost:8000/api/user \
  -H "Authorization: Bearer 1|abc123def456ghi789jkl012mno345pqr678stu901vwx234yz"
```

### Using Postman
1. Create a POST request to `/api/auth/login`
2. Add email and password to request body
3. Save the token from the response
4. Add the token to the Authorization header for subsequent requests

## Implementation Details

### Database Schema
```sql
-- Personal Access Tokens (Laravel Sanctum)
CREATE TABLE personal_access_tokens (
    id BIGINT PRIMARY KEY,
    tokenable_type VARCHAR(255) NOT NULL,
    tokenable_id BIGINT NOT NULL,
    name VARCHAR(255) NOT NULL,
    token VARCHAR(64) NOT NULL UNIQUE,
    abilities TEXT,
    last_used_at TIMESTAMP,
    expires_at TIMESTAMP,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- Users table with role support
CREATE TABLE users (
    id BIGINT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role_id BIGINT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- Roles table
CREATE TABLE roles (
    id BIGINT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- User roles pivot table (many-to-many)
CREATE TABLE user_roles (
    id BIGINT PRIMARY KEY,
    user_id BIGINT NOT NULL,
    role_id BIGINT NOT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### Configuration
Sanctum configuration is located in `config/sanctum.php`:

```php
'expiration' => 24 * 60, // 24 hours
'token_prefix' => env('SANCTUM_TOKEN_PREFIX', ''),
'middleware' => [
    'encrypt_cookies' => App\Http\Middleware\EncryptCookies::class,
    'authenticate_session' => Laravel\Sanctum\Http\Middleware\AuthenticateSession::class,
],
```

## Best Practices

1. **Always use HTTPS** in production
2. **Implement proper token storage** on the client side
3. **Set appropriate token expiration** times
4. **Use role-based authorization** properly
5. **Implement rate limiting** to prevent abuse
6. **Log authentication events** for security monitoring
7. **Validate all inputs** in authentication endpoints
8. **Use strong passwords** and password policies
9. **Implement proper error handling** without exposing sensitive information
10. **Regular token cleanup** to remove expired tokens

---

*For more information, see the [Laravel Sanctum documentation](https://laravel.com/docs/sanctum).*
