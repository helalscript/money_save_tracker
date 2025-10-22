# Admin API Documentation

This document covers all admin-related endpoints in the API v1. These endpoints are accessible only to authenticated users with admin privileges (`is_admin` middleware).

## Base URL
```
/api/v1/admin
```

## Authentication
All admin endpoints require authentication with a valid JWT token and admin privileges:
```
Authorization: Bearer YOUR_JWT_TOKEN
```

## Admin Endpoints

### Store Details Management

#### 1. List Store Details

**Endpoint:** `GET /admin/store-details`

**Description:** Retrieve a paginated list of store details.

**Authentication:** Required (Bearer token + is_admin middleware)

**Query Parameters:**
- `page` (optional): Page number (default: 1)
- `per_page` (optional): Items per page (default: 15, max: 100)
- `search` (optional): Search term
- `sort` (optional): Sort field
- `order` (optional): Sort direction (asc, desc)

**Success Response (200):**
```json
{
    "status": true,
    "message": "Store details fetched successfully",
    "code": 200,
    "data": [
        {
            "id": 1,
            "store_name": "Main Store",
            "address": "123 Main Street",
            "phone": "+1234567890",
            "email": "store@example.com",
            "hours": "9:00 AM - 9:00 PM",
            "description": "Main store location",
            "created_at": "2024-01-01T00:00:00.000000Z",
            "updated_at": "2024-01-01T00:00:00.000000Z"
        }
    ],
    "pagination": {
        "current_page": 1,
        "per_page": 15,
        "total": 1,
        "last_page": 1,
        "from": 1,
        "to": 1
    }
}
```

#### 2. Create Store Detail

**Endpoint:** `POST /admin/store-details`

**Description:** Create a new store detail record.

**Authentication:** Required (Bearer token + is_admin middleware)

**Request Body:**
```json
{
    "store_name": "New Store",
    "address": "456 Oak Avenue",
    "phone": "+1987654321",
    "email": "newstore@example.com",
    "hours": "8:00 AM - 10:00 PM",
    "description": "New store location"
}
```

**Success Response (200):**
```json
{
    "status": true,
    "message": "Store detail created successfully",
    "code": 200,
    "data": {
        "id": 2,
        "store_name": "New Store",
        "address": "456 Oak Avenue",
        "phone": "+1987654321",
        "email": "newstore@example.com",
        "hours": "8:00 AM - 10:00 PM",
        "description": "New store location",
        "created_at": "2024-01-01T12:00:00.000000Z",
        "updated_at": "2024-01-01T12:00:00.000000Z"
    }
}
```

#### 3. Get Store Detail

**Endpoint:** `GET /admin/store-details/{id}`

**Description:** Retrieve details of a specific store.

**Authentication:** Required (Bearer token + is_admin middleware)

**Success Response (200):**
```json
{
    "status": true,
    "message": "Store detail fetched successfully",
    "code": 200,
    "data": {
        "id": 1,
        "store_name": "Main Store",
        "address": "123 Main Street",
        "phone": "+1234567890",
        "email": "store@example.com",
        "hours": "9:00 AM - 9:00 PM",
        "description": "Main store location",
        "created_at": "2024-01-01T00:00:00.000000Z",
        "updated_at": "2024-01-01T00:00:00.000000Z"
    }
}
```

#### 4. Update Store Detail

**Endpoint:** `PUT /admin/store-details/{id}`

**Description:** Update an existing store detail record.

**Authentication:** Required (Bearer token + is_admin middleware)

**Request Body:** Same as create store detail

**Success Response (200):**
```json
{
    "status": true,
    "message": "Store detail updated successfully",
    "code": 200,
    "data": {
        "id": 1,
        "store_name": "Updated Store Name",
        "address": "123 Main Street",
        "phone": "+1234567890",
        "email": "store@example.com",
        "hours": "9:00 AM - 9:00 PM",
        "description": "Updated description",
        "created_at": "2024-01-01T00:00:00.000000Z",
        "updated_at": "2024-01-01T12:00:00.000000Z"
    }
}
```

#### 5. Delete Store Detail

**Endpoint:** `DELETE /admin/store-details/{id}`

**Description:** Delete a store detail record.

**Authentication:** Required (Bearer token + is_admin middleware)

**Success Response (200):**
```json
{
    "status": true,
    "message": "Store detail deleted successfully",
    "code": 200
}
```

### Time Packages Management

#### 1. List Time Packages

**Endpoint:** `GET /admin/time-packages`

**Description:** Retrieve a paginated list of time packages.

**Authentication:** Required (Bearer token + is_admin middleware)

**Success Response (200):**
```json
{
    "status": true,
    "message": "Time packages fetched successfully",
    "code": 200,
    "data": [
        {
            "id": 1,
            "name": "Standard Package",
            "duration_hours": 8,
            "price": 50.00,
            "description": "Standard 8-hour package",
            "created_at": "2024-01-01T00:00:00.000000Z",
            "updated_at": "2024-01-01T00:00:00.000000Z"
        }
    ],
    "pagination": {
        "current_page": 1,
        "per_page": 15,
        "total": 1,
        "last_page": 1,
        "from": 1,
        "to": 1
    }
}
```

#### 2. Create Time Package

**Endpoint:** `POST /admin/time-packages`

**Description:** Create a new time package.

**Authentication:** Required (Bearer token + is_admin middleware)

**Request Body:**
```json
{
    "name": "Premium Package",
    "duration_hours": 12,
    "price": 75.00,
    "description": "Premium 12-hour package"
}
```

**Success Response (200):**
```json
{
    "status": true,
    "message": "Time package created successfully",
    "code": 200,
    "data": {
        "id": 2,
        "name": "Premium Package",
        "duration_hours": 12,
        "price": 75.00,
        "description": "Premium 12-hour package",
        "created_at": "2024-01-01T12:00:00.000000Z",
        "updated_at": "2024-01-01T12:00:00.000000Z"
    }
}
```

#### 3. Get Time Package

**Endpoint:** `GET /admin/time-packages/{id}`

**Description:** Retrieve details of a specific time package.

**Authentication:** Required (Bearer token + is_admin middleware)

**Success Response (200):**
```json
{
    "status": true,
    "message": "Time package fetched successfully",
    "code": 200,
    "data": {
        "id": 1,
        "name": "Standard Package",
        "duration_hours": 8,
        "price": 50.00,
        "description": "Standard 8-hour package",
        "created_at": "2024-01-01T00:00:00.000000Z",
        "updated_at": "2024-01-01T00:00:00.000000Z"
    }
}
```

#### 4. Update Time Package

**Endpoint:** `PUT /admin/time-packages/{id}`

**Description:** Update an existing time package.

**Authentication:** Required (Bearer token + is_admin middleware)

**Request Body:** Same as create time package

**Success Response (200):**
```json
{
    "status": true,
    "message": "Time package updated successfully",
    "code": 200,
    "data": {
        "id": 1,
        "name": "Updated Standard Package",
        "duration_hours": 8,
        "price": 55.00,
        "description": "Updated standard package",
        "created_at": "2024-01-01T00:00:00.000000Z",
        "updated_at": "2024-01-01T12:00:00.000000Z"
    }
}
```

#### 5. Delete Time Package

**Endpoint:** `DELETE /admin/time-packages/{id}`

**Description:** Delete a time package.

**Authentication:** Required (Bearer token + is_admin middleware)

**Success Response (200):**
```json
{
    "status": true,
    "message": "Time package deleted successfully",
    "code": 200
}
```

### Dynamic Pages Management

#### 1. List Dynamic Pages

**Endpoint:** `GET /admin/dynamic-pages`

**Description:** Retrieve a paginated list of dynamic pages.

**Authentication:** Required (Bearer token + is_admin middleware)

**Success Response (200):**
```json
{
    "status": true,
    "message": "Dynamic pages fetched successfully",
    "code": 200,
    "data": [
        {
            "id": 1,
            "title": "About Us",
            "slug": "about-us",
            "content": "This is the about us page content...",
            "meta_title": "About Us - Company",
            "meta_description": "Learn more about our company",
            "status": "active",
            "created_at": "2024-01-01T00:00:00.000000Z",
            "updated_at": "2024-01-01T00:00:00.000000Z"
        }
    ],
    "pagination": {
        "current_page": 1,
        "per_page": 15,
        "total": 1,
        "last_page": 1,
        "from": 1,
        "to": 1
    }
}
```

#### 2. Create Dynamic Page

**Endpoint:** `POST /admin/dynamic-pages`

**Description:** Create a new dynamic page.

**Authentication:** Required (Bearer token + is_admin middleware)

**Request Body:**
```json
{
    "title": "Privacy Policy",
    "slug": "privacy-policy",
    "content": "This is our privacy policy content...",
    "meta_title": "Privacy Policy - Company",
    "meta_description": "Our privacy policy",
    "status": "active"
}
```

**Success Response (200):**
```json
{
    "status": true,
    "message": "Dynamic page created successfully",
    "code": 200,
    "data": {
        "id": 2,
        "title": "Privacy Policy",
        "slug": "privacy-policy",
        "content": "This is our privacy policy content...",
        "meta_title": "Privacy Policy - Company",
        "meta_description": "Our privacy policy",
        "status": "active",
        "created_at": "2024-01-01T12:00:00.000000Z",
        "updated_at": "2024-01-01T12:00:00.000000Z"
    }
}
```

#### 3. Get Dynamic Page

**Endpoint:** `GET /admin/dynamic-pages/{id}`

**Description:** Retrieve details of a specific dynamic page.

**Authentication:** Required (Bearer token + is_admin middleware)

**Success Response (200):**
```json
{
    "status": true,
    "message": "Dynamic page fetched successfully",
    "code": 200,
    "data": {
        "id": 1,
        "title": "About Us",
        "slug": "about-us",
        "content": "This is the about us page content...",
        "meta_title": "About Us - Company",
        "meta_description": "Learn more about our company",
        "status": "active",
        "created_at": "2024-01-01T00:00:00.000000Z",
        "updated_at": "2024-01-01T00:00:00.000000Z"
    }
}
```

#### 4. Update Dynamic Page

**Endpoint:** `PUT /admin/dynamic-pages/{id}`

**Description:** Update an existing dynamic page.

**Authentication:** Required (Bearer token + is_admin middleware)

**Request Body:** Same as create dynamic page

**Success Response (200):**
```json
{
    "status": true,
    "message": "Dynamic page updated successfully",
    "code": 200,
    "data": {
        "id": 1,
        "title": "Updated About Us",
        "slug": "about-us",
        "content": "Updated about us content...",
        "meta_title": "About Us - Company",
        "meta_description": "Learn more about our company",
        "status": "active",
        "created_at": "2024-01-01T00:00:00.000000Z",
        "updated_at": "2024-01-01T12:00:00.000000Z"
    }
}
```

#### 5. Delete Dynamic Page

**Endpoint:** `DELETE /admin/dynamic-pages/{id}`

**Description:** Delete a dynamic page.

**Authentication:** Required (Bearer token + is_admin middleware)

**Success Response (200):**
```json
{
    "status": true,
    "message": "Dynamic page deleted successfully",
    "code": 200
}
```

### System Settings Management

#### 1. Get System Settings

**Endpoint:** `GET /admin/system/settings`

**Description:** Retrieve current system settings.

**Authentication:** Required (Bearer token + is_admin middleware)

**Success Response (200):**
```json
{
    "status": true,
    "message": "System settings fetched successfully",
    "code": 200,
    "data": {
        "id": 1,
        "title": "Company Name",
        "system_name": "RobBarden",
        "email": "admin@example.com",
        "contact_number": "+1234567890",
        "company_open_hour": "9:00 AM - 9:00 PM",
        "copyright_text": "© 2024 Company Name. All rights reserved.",
        "logo": "uploads/logo/logo.png",
        "favicon": "uploads/favicon/favicon.ico",
        "footer_logo": "uploads/footer_logo/footer.png",
        "address": "123 Main Street, City, State",
        "description": "Company description",
        "created_at": "2024-01-01T00:00:00.000000Z",
        "updated_at": "2024-01-01T00:00:00.000000Z"
    }
}
```

#### 2. Update System Settings

**Endpoint:** `POST /admin/system/settings`

**Description:** Update system settings.

**Authentication:** Required (Bearer token + is_admin middleware)

**Request Body:**
```json
{
    "title": "Updated Company Name",
    "system_name": "RobBarden",
    "email": "admin@example.com",
    "contact_number": "+1234567890",
    "company_open_hour": "9:00 AM - 9:00 PM",
    "copyright_text": "© 2024 Updated Company Name. All rights reserved.",
    "logo": "file",
    "favicon": "file",
    "footer_logo": "file",
    "address": "456 Oak Avenue, City, State",
    "description": "Updated company description"
}
```

**Validation Rules:**
- `title`: Required, string, max 100 characters
- `system_name`: Required, string, max 50 characters
- `email`: Required, valid email format, max 255 characters
- `contact_number`: Optional, string, max 20 characters
- `company_open_hour`: Optional, string, max 255 characters
- `copyright_text`: Required, string, max 255 characters
- `logo`: Optional, image file (jpeg, jpg, png, ico, svg)
- `favicon`: Optional, image file (jpeg, jpg, png, ico, svg)
- `footer_logo`: Optional, image file (jpeg, jpg, png, ico, svg)
- `address`: Required, string, max 255 characters
- `description`: Required, string, max 1000 characters

**Success Response (200):**
```json
{
    "status": true,
    "message": "System settings updated successfully",
    "code": 200,
    "data": {
        "id": 1,
        "title": "Updated Company Name",
        "system_name": "RobBarden",
        "email": "admin@example.com",
        "contact_number": "+1234567890",
        "company_open_hour": "9:00 AM - 9:00 PM",
        "copyright_text": "© 2024 Updated Company Name. All rights reserved.",
        "logo": "uploads/logo/updated_logo.png",
        "favicon": "uploads/favicon/updated_favicon.ico",
        "footer_logo": "uploads/footer_logo/updated_footer.png",
        "address": "456 Oak Avenue, City, State",
        "description": "Updated company description",
        "created_at": "2024-01-01T00:00:00.000000Z",
        "updated_at": "2024-01-01T12:00:00.000000Z"
    }
}
```

#### 3. Get Mail Settings

**Endpoint:** `GET /admin/system/mail-settings`

**Description:** Retrieve current mail settings.

**Authentication:** Required (Bearer token + is_admin middleware)

**Success Response (200):**
```json
{
    "status": true,
    "message": "Mail settings fetched successfully",
    "code": 200,
    "data": {
        "mail_mailer": "smtp",
        "mail_host": "smtp.gmail.com",
        "mail_port": "587",
        "mail_username": "noreply@example.com",
        "mail_password": "encrypted_password",
        "mail_encryption": "tls",
        "mail_from_address": "noreply@example.com"
    }
}
```

#### 4. Update Mail Settings

**Endpoint:** `POST /admin/system/mail-settings`

**Description:** Update mail settings.

**Authentication:** Required (Bearer token + is_admin middleware)

**Request Body:**
```json
{
    "mail_mailer": "smtp",
    "mail_host": "smtp.gmail.com",
    "mail_port": "587",
    "mail_username": "noreply@example.com",
    "mail_password": "new_password",
    "mail_encryption": "tls",
    "mail_from_address": "noreply@example.com"
}
```

**Validation Rules:**
- All fields are optional strings
- `mail_mailer`: SMTP mailer type
- `mail_host`: SMTP host address
- `mail_port`: SMTP port number
- `mail_username`: SMTP username
- `mail_password`: SMTP password
- `mail_encryption`: Encryption type (tls, ssl)
- `mail_from_address`: From email address

**Success Response (200):**
```json
{
    "status": true,
    "message": "Mail settings updated successfully",
    "code": 200
}
```

#### 5. Clear Cache

**Endpoint:** `GET /admin/system/clear-cache`

**Description:** Clear system cache.

**Authentication:** Required (Bearer token + is_admin middleware)

**Success Response (200):**
```json
{
    "status": true,
    "message": "Cache cleared successfully",
    "code": 200
}
```

## Error Handling

### Common Error Responses

**401 Unauthorized:**
```json
{
    "status": false,
    "message": "Unauthenticated",
    "code": 401
}
```

**403 Forbidden:**
```json
{
    "status": false,
    "message": "Access denied. Admin role required.",
    "code": 403
}
```

**404 Not Found:**
```json
{
    "status": false,
    "message": "Resource not found",
    "code": 404
}
```

**422 Validation Error:**
```json
{
    "status": false,
    "message": "Validation failed",
    "code": 422,
    "errors": {
        "title": ["The title field is required."],
        "email": ["The email must be a valid email address."]
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

1. **Admin Only**: All endpoints require admin privileges
2. **File Uploads**: Image uploads are validated for type and size
3. **Data Validation**: All input data is validated before processing
4. **Soft Deletes**: Most resources use soft deletes
5. **Cache Management**: Cache clearing is available for system maintenance
6. **Mail Configuration**: Mail settings are encrypted and secure

## Usage Examples

### JavaScript/Fetch
```javascript
// Get system settings
const response = await fetch('/api/v1/admin/system/settings', {
    headers: {
        'Authorization': 'Bearer YOUR_JWT_TOKEN',
        'Content-Type': 'application/json'
    }
});
const settings = await response.json();

// Update system settings
const updateResponse = await fetch('/api/v1/admin/system/settings', {
    method: 'POST',
    headers: {
        'Authorization': 'Bearer YOUR_JWT_TOKEN',
        'Content-Type': 'application/json'
    },
    body: JSON.stringify({
        title: 'New Company Name',
        system_name: 'RobBarden',
        email: 'admin@example.com',
        address: '123 Main Street',
        description: 'Company description'
    })
});
```

## Related Documentation

- [Authentication API](../auth/README.md)
- [User API](../user/README.md)
- [Public API](../public/README.md)
- [API Examples](../../examples/README.md)
