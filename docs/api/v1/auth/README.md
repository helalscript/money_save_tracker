# Authentication API Documentation

This document covers all authentication-related endpoints in the API v1.

## Base URL
```
/api/v1
```

## Authentication Endpoints

### 1. User Login

**Endpoint:** `POST /login`

**Description:** Authenticate a user and receive a JWT token.

**Rate Limit:** 2 requests per minute

**Request Body:**
```json
{
    "email": "user@example.com",        // Required (or user_name)
    "password": "password123",          // Required
    "user_name": "username"             // Required (or email)
}
```

**Validation Rules:**
- `email` OR `user_name` is required
- `password` is required
- Email must be valid format if provided
- User must exist and be verified

**Success Response (200):**
```json
{
    "status": true,
    "message": "User logged in successfully.",
    "code": 200,
    "token_type": "bearer",
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
    "expires_in": 3600,
    "data": {
        "id": 1,
        "name": "John Doe",
        "email": "user@example.com",
        "user_name": "johndoe",
        "role": "user",
        "avatar": "uploads/user/avatar/avatar.jpg",
        "created_at": "2024-01-01T00:00:00.000000Z",
        "updated_at": "2024-01-01T00:00:00.000000Z"
    }
}
```

**Error Responses:**
- `401` - Invalid password
- `403` - Email not verified
- `422` - Validation errors
- `500` - Server error

### 2. User Logout

**Endpoint:** `POST /logout`

**Description:** Logout the authenticated user and invalidate the JWT token.

**Authentication:** Required (Bearer token)

**Request Body:** None

**Success Response (200):**
```json
{
    "status": true,
    "message": "User logged out successfully.",
    "code": 200
}
```

### 3. Refresh Token

**Endpoint:** `GET /refresh-token`

**Description:** Refresh the JWT access token for the authenticated user.

**Authentication:** Required (Bearer token)

**Request Body:** None

**Success Response (200):**
```json
{
    "status": true,
    "message": "Access token refreshed successfully.",
    "code": 200,
    "token_type": "bearer",
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
    "expires_in": 3600,
    "data": {
        "id": 1,
        "name": "John Doe",
        "email": "user@example.com",
        "user_name": "johndoe",
        "role": "user"
    }
}
```

### 4. Get Current User

**Endpoint:** `GET /me`

**Description:** Get the authenticated user's details.

**Authentication:** Required (Bearer token)

**Request Body:** None

**Success Response (200):**
```json
{
    "status": true,
    "message": "User details fetched successfully",
    "code": 200,
    "data": {
        "id": 1,
        "name": "John Doe",
        "email": "user@example.com",
        "user_name": "johndoe",
        "role": "user",
        "avatar": "uploads/user/avatar/avatar.jpg",
        "cover_photo": "uploads/user/cover_photo/cover.jpg",
        "phone": "+1234567890",
        "age": 25,
        "weight": "70kg",
        "height": "175cm",
        "is_physical_distress": false,
        "gender": "male",
        "brithdate": "1999-01-01",
        "email_verified_at": "2024-01-01T00:00:00.000000Z",
        "created_at": "2024-01-01T00:00:00.000000Z",
        "updated_at": "2024-01-01T00:00:00.000000Z"
    }
}
```

### 5. Update Profile

**Endpoint:** `POST /update-profile`

**Description:** Update the authenticated user's profile information.

**Authentication:** Required (Bearer token)

**Request Body:**
```json
{
    "name": "John Doe",                    // Required
    "avatar": "file",                      // Optional (image file)
    "cover_photo": "file",                 // Optional (image file)
    "phone": "+1234567890",                // Optional
    "password": "newpassword",             // Optional
    "password_confirmation": "newpassword", // Required if password provided
    "age": 25,                             // Optional
    "weight": "70kg",                      // Optional
    "height": "175cm",                     // Optional
    "is_physical_distress": false,         // Optional
    "gender": "male",                      // Optional (male/female/others)
    "brithdate": "1999-01-01"              // Optional
}
```

**Validation Rules:**
- `name`: Required, string, max 100 characters
- `avatar`: Optional, image file (jpeg, png, jpg, gif), max 10MB
- `cover_photo`: Optional, image file (jpeg, png, jpg, gif), max 10MB
- `phone`: Optional, string, unique, 10-20 characters
- `password`: Optional, string, min 6 characters, must be confirmed
- `age`: Optional, integer
- `weight`: Optional, string
- `height`: Optional, string
- `is_physical_distress`: Optional, boolean
- `gender`: Optional, enum (male, female, others)
- `brithdate`: Optional, date, before today

**Success Response (200):**
```json
{
    "status": true,
    "message": "Profile updated successfully",
    "code": 200,
    "data": {
        "id": 1,
        "name": "John Doe",
        "email": "user@example.com",
        "user_name": "johndoe",
        "role": "user",
        "avatar": "uploads/user/avatar/new_avatar.jpg",
        "cover_photo": "uploads/user/cover_photo/new_cover.jpg",
        "phone": "+1234567890",
        "age": 25,
        "weight": "70kg",
        "height": "175cm",
        "is_physical_distress": false,
        "gender": "male",
        "brithdate": "1999-01-01",
        "updated_at": "2024-01-01T12:00:00.000000Z"
    }
}
```

### 6. Change Password

**Endpoint:** `POST /update-password`

**Description:** Update the authenticated user's password.

**Authentication:** Required (Bearer token)

**Request Body:**
```json
{
    "current_password": "oldpassword",     // Required
    "new_password": "newpassword",         // Required
    "new_password_confirmation": "newpassword" // Required
}
```

**Validation Rules:**
- `current_password`: Required, string
- `new_password`: Required, string, min 6 characters, must be confirmed
- `new_password_confirmation`: Required, must match new_password

**Success Response (200):**
```json
{
    "status": true,
    "message": "Password updated successfully",
    "code": 200
}
```

**Error Responses:**
- `422` - Current password is incorrect
- `422` - Validation errors

### 7. Forgot Password

**Endpoint:** `POST /forget-password`

**Description:** Request a password reset OTP.

**Rate Limit:** 2 requests per minute

**Request Body:**
```json
{
    "email": "user@example.com"  // Required
}
```

**Validation Rules:**
- `email`: Required, valid email format

**Success Response (200):**
```json
{
    "status": true,
    "message": "OTP sent to your email successfully",
    "code": 200
}
```

### 8. Verify OTP

**Endpoint:** `POST /verify-otp`

**Description:** Verify the OTP sent for password reset.

**Rate Limit:** 2 requests per minute

**Request Body:**
```json
{
    "email": "user@example.com",  // Required
    "otp": "123456"              // Required
}
```

**Validation Rules:**
- `email`: Required, valid email format
- `otp`: Required, string

**Success Response (200):**
```json
{
    "status": true,
    "message": "OTP verified successfully",
    "code": 200
}
```

### 9. Reset Password

**Endpoint:** `POST /reset-password`

**Description:** Reset password using verified OTP.

**Rate Limit:** 2 requests per minute

**Request Body:**
```json
{
    "email": "user@example.com",           // Required
    "otp": "123456",                       // Required
    "password": "newpassword",             // Required
    "password_confirmation": "newpassword"  // Required
}
```

**Validation Rules:**
- `email`: Required, valid email format
- `otp`: Required, string
- `password`: Required, string, min 6 characters, must be confirmed
- `password_confirmation`: Required, must match password

**Success Response (200):**
```json
{
    "status": true,
    "message": "Password reset successfully",
    "code": 200
}
```

## Error Handling

### Common Error Responses

**401 Unauthorized:**
```json
{
    "status": false,
    "message": "Invalid password",
    "code": 401
}
```

**403 Forbidden:**
```json
{
    "status": false,
    "message": "Your email address has not been verified yet. Please check your inbox for the verification email and verify your account.",
    "code": 403
}
```

**422 Validation Error:**
```json
{
    "status": false,
    "message": "Validation failed",
    "code": 422,
    "errors": {
        "email": ["The email field is required."],
        "password": ["The password field is required."]
    }
}
```

**500 Server Error:**
```json
{
    "status": false,
    "message": "Internal server error",
    "code": 500
}
```

## Security Notes

1. **JWT Tokens**: Tokens expire after 1 hour (3600 seconds)
2. **Rate Limiting**: Login and password reset endpoints are rate limited
3. **Email Verification**: Users must verify their email before login
4. **Password Security**: Passwords are hashed using bcrypt
5. **File Uploads**: Image uploads are validated for type and size
6. **HTTPS**: Always use HTTPS in production

## Usage Examples

See the [examples directory](../examples/) for complete usage examples in various programming languages.
