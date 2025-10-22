# API Documentation

This directory contains comprehensive documentation for the Rob Barden API v1. The API is built with Laravel and provides endpoints for authentication, user management, admin operations, and public access.

## 📁 Documentation Structure

```
docs/
├── README.md                 # This file - Main documentation overview
├── api/
│   └── v1/
│       ├── auth/             # Authentication endpoints
│       ├── admin/             # Admin-only endpoints
│       ├── user/              # User endpoints
│       └── public/            # Public endpoints
├── examples/                 # API usage examples
└── schemas/                  # Data schemas and models
```

## 🚀 Quick Start

### Base URL
```
https://your-domain.com/api/v1
```

### Authentication
The API uses JWT (JSON Web Token) authentication. Include the token in the Authorization header:
```
Authorization: Bearer YOUR_JWT_TOKEN
```

### Rate Limiting
- Guest endpoints: 2 requests per minute
- Authenticated endpoints: No specific limit (subject to server capacity)

## 📋 API Endpoints Overview

### Authentication (`/auth`)
- `POST /login` - User login
- `POST /logout` - User logout
- `GET /refresh-token` - Refresh JWT token
- `GET /me` - Get current user details
- `POST /update-profile` - Update user profile
- `POST /update-password` - Change password
- `POST /forget-password` - Request password reset
- `POST /verify-otp` - Verify OTP for password reset
- `POST /reset-password` - Reset password with OTP

### User Endpoints (`/user`)
- `GET /customers` - List customers
- `POST /customers` - Create customer
- `GET /customers/{id}` - Get customer details
- `PUT /customers/{id}` - Update customer
- `DELETE /customers/{id}` - Delete customer

### Admin Endpoints (`/admin`)
- `GET /store-details` - List store details
- `POST /store-details` - Create store detail
- `GET /time-packages` - List time packages
- `POST /time-packages` - Create time package
- `GET /dynamic-pages` - List dynamic pages
- `POST /dynamic-pages` - Create dynamic page
- `GET /system/settings` - Get system settings
- `POST /system/settings` - Update system settings
- `GET /system/mail-settings` - Get mail settings
- `POST /system/mail-settings` - Update mail settings
- `GET /system/clear-cache` - Clear system cache

### Public Endpoints (`/public`)
- `GET /system-info` - Get system information
- `GET /dynamic-pages` - Get public dynamic pages
- `GET /dynamic-pages/{slug}` - Get specific dynamic page

## 🔧 Response Format

All API responses follow a consistent format:

### Success Response
```json
{
    "status": true,
    "message": "Operation completed successfully",
    "code": 200,
    "data": { ... },
    "pagination": { ... } // Only for paginated responses
}
```

### Error Response
```json
{
    "status": false,
    "message": "Error description",
    "code": 400,
    "errors": { ... } // Optional validation errors
}
```

## 📊 HTTP Status Codes

- `200` - Success
- `201` - Created
- `400` - Bad Request
- `401` - Unauthorized
- `403` - Forbidden
- `404` - Not Found
- `422` - Validation Error
- `500` - Internal Server Error

## 🔐 Authentication Flow

1. **Login**: Send credentials to `/login` endpoint
2. **Token**: Receive JWT token in response
3. **Authorization**: Include token in `Authorization: Bearer {token}` header
4. **Refresh**: Use `/refresh-token` to get new token before expiration
5. **Logout**: Call `/logout` to invalidate token

## 📝 Data Validation

All endpoints include comprehensive validation:
- Required fields are enforced
- Data types are validated
- String lengths are limited
- Email formats are validated
- File uploads have size and type restrictions

## 🔍 Pagination

Paginated endpoints support:
- `page` - Page number (default: 1)
- `per_page` - Items per page (default: 15, max: 100)
- `search` - Search term
- `sort` - Sort field
- `order` - Sort direction (asc/desc)

## 📁 File Uploads

File uploads are supported for:
- User avatars (images: jpeg, png, jpg, gif, max 10MB)
- Cover photos (images: jpeg, png, jpg, gif, max 10MB)
- System logos (images: jpeg, jpg, png, ico, svg)
- Favicons (images: jpeg, jpg, png, ico, svg)

## 🚨 Error Handling

The API provides detailed error messages for:
- Validation failures
- Authentication errors
- Authorization issues
- Resource not found
- Server errors

## 📚 Additional Resources

- [Authentication Guide](api/v1/auth/README.md)
- [User API Guide](api/v1/user/README.md)
- [Admin API Guide](api/v1/admin/README.md)
- [Public API Guide](api/v1/public/README.md)
- [API Examples](examples/README.md)
- [Data Schemas](schemas/README.md)

## 🔄 API Versioning

Current version: **v1**

The API uses URL-based versioning. Future versions will be available at `/api/v2`, `/api/v3`, etc.

## 📞 Support

For API support and questions, please contact the development team or refer to the specific endpoint documentation in the respective folders.
