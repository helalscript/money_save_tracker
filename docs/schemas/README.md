# API Data Schemas

This document defines the data schemas and models used throughout the Rob Barden API v1. These schemas provide a clear understanding of the data structures, validation rules, and relationships between different entities.

## 📁 Schema Structure

```
schemas/
├── README.md              # This file
├── models/                # Data model definitions
├── requests/              # Request body schemas
├── responses/             # Response body schemas
└── validation/            # Validation rules
```

## 🔧 Core Data Models

### User Model
```json
{
    "id": "integer",
    "name": "string",
    "email": "string",
    "user_name": "string",
    "role": "string",
    "avatar": "string|null",
    "cover_photo": "string|null",
    "phone": "string|null",
    "age": "integer|null",
    "weight": "string|null",
    "height": "string|null",
    "is_physical_distress": "boolean|null",
    "gender": "string|null",
    "brithdate": "date|null",
    "email_verified_at": "datetime|null",
    "created_at": "datetime",
    "updated_at": "datetime",
    "deleted_at": "datetime|null"
}
```

**Validation Rules:**
- `name`: Required, string, max 100 characters
- `email`: Required, unique, valid email format, max 255 characters
- `user_name`: Required, unique, string, max 50 characters
- `role`: Required, enum (user, admin)
- `avatar`: Optional, image file (jpeg, png, jpg, gif), max 10MB
- `cover_photo`: Optional, image file (jpeg, png, jpg, gif), max 10MB
- `phone`: Optional, unique, string, 10-20 characters
- `age`: Optional, integer, min 1, max 120
- `weight`: Optional, string, max 50 characters
- `height`: Optional, string, max 50 characters
- `is_physical_distress`: Optional, boolean
- `gender`: Optional, enum (male, female, others)
- `brithdate`: Optional, date, before today

### Customer Model
```json
{
    "id": "integer",
    "time_package_id": "integer",
    "name": "string",
    "rfid_sn_left": "string",
    "rfid_sn_right": "string",
    "phone": "string",
    "email": "string|null",
    "start_time": "datetime",
    "end_time": "datetime",
    "status": "string",
    "created_at": "datetime",
    "updated_at": "datetime",
    "deleted_at": "datetime|null",
    "time_package": {
        "id": "integer",
        "name": "string",
        "duration_hours": "integer",
        "price": "decimal",
        "description": "string|null"
    }
}
```

**Validation Rules:**
- `time_package_id`: Required, integer, exists in time_packages table
- `name`: Required, string, max 255 characters
- `rfid_sn_left`: Required, string, max 255 characters, unique
- `rfid_sn_right`: Required, string, max 255 characters, unique
- `phone`: Required, string, max 20 characters
- `email`: Optional, valid email format, max 255 characters
- `status`: Auto-generated, enum (active, inactive, expired)

### Time Package Model
```json
{
    "id": "integer",
    "name": "string",
    "duration_hours": "integer",
    "price": "decimal",
    "description": "string|null",
    "created_at": "datetime",
    "updated_at": "datetime",
    "deleted_at": "datetime|null"
}
```

**Validation Rules:**
- `name`: Required, string, max 255 characters
- `duration_hours`: Required, integer, min 1, max 24
- `price`: Required, decimal, min 0, max 999999.99
- `description`: Optional, string, max 1000 characters

### Store Details Model
```json
{
    "id": "integer",
    "store_name": "string",
    "address": "string",
    "phone": "string",
    "email": "string",
    "hours": "string",
    "description": "string",
    "created_at": "datetime",
    "updated_at": "datetime",
    "deleted_at": "datetime|null"
}
```

**Validation Rules:**
- `store_name`: Required, string, max 255 characters
- `address`: Required, string, max 500 characters
- `phone`: Required, string, max 20 characters
- `email`: Required, valid email format, max 255 characters
- `hours`: Required, string, max 255 characters
- `description`: Required, string, max 1000 characters

### Dynamic Page Model
```json
{
    "id": "integer",
    "title": "string",
    "slug": "string",
    "content": "text",
    "meta_title": "string|null",
    "meta_description": "string|null",
    "meta_keywords": "string|null",
    "status": "string",
    "created_at": "datetime",
    "updated_at": "datetime",
    "deleted_at": "datetime|null"
}
```

**Validation Rules:**
- `title`: Required, string, max 255 characters
- `slug`: Required, string, max 255 characters, unique, URL-safe
- `content`: Required, text, max 65535 characters
- `meta_title`: Optional, string, max 255 characters
- `meta_description`: Optional, string, max 500 characters
- `meta_keywords`: Optional, string, max 500 characters
- `status`: Required, enum (active, inactive)

### System Settings Model
```json
{
    "id": "integer",
    "title": "string",
    "system_name": "string",
    "email": "string",
    "contact_number": "string|null",
    "company_open_hour": "string|null",
    "copyright_text": "string",
    "logo": "string|null",
    "favicon": "string|null",
    "footer_logo": "string|null",
    "address": "string",
    "description": "string",
    "created_at": "datetime",
    "updated_at": "datetime"
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

## 📝 Request Schemas

### Authentication Requests

#### Login Request
```json
{
    "email": "string",
    "password": "string",
    "user_name": "string"
}
```
**Note:** Either `email` OR `user_name` is required, not both.

#### Register Request
```json
{
    "name": "string",
    "email": "string",
    "user_name": "string",
    "password": "string",
    "password_confirmation": "string",
    "phone": "string|null"
}
```

#### Password Reset Request
```json
{
    "email": "string"
}
```

#### Verify OTP Request
```json
{
    "email": "string",
    "otp": "string"
}
```

#### Reset Password Request
```json
{
    "email": "string",
    "otp": "string",
    "password": "string",
    "password_confirmation": "string"
}
```

#### Update Profile Request
```json
{
    "name": "string",
    "avatar": "file|null",
    "cover_photo": "file|null",
    "phone": "string|null",
    "password": "string|null",
    "password_confirmation": "string|null",
    "age": "integer|null",
    "weight": "string|null",
    "height": "string|null",
    "is_physical_distress": "boolean|null",
    "gender": "string|null",
    "brithdate": "date|null"
}
```

#### Change Password Request
```json
{
    "current_password": "string",
    "new_password": "string",
    "new_password_confirmation": "string"
}
```

### Customer Requests

#### Create Customer Request
```json
{
    "time_package_id": "integer",
    "name": "string",
    "rfid_sn_left": "string",
    "rfid_sn_right": "string",
    "phone": "string",
    "email": "string|null"
}
```

#### Update Customer Request
```json
{
    "time_package_id": "integer",
    "name": "string",
    "rfid_sn_left": "string",
    "rfid_sn_right": "string",
    "phone": "string",
    "email": "string|null"
}
```

### Admin Requests

#### Create Time Package Request
```json
{
    "name": "string",
    "duration_hours": "integer",
    "price": "decimal",
    "description": "string|null"
}
```

#### Create Store Detail Request
```json
{
    "store_name": "string",
    "address": "string",
    "phone": "string",
    "email": "string",
    "hours": "string",
    "description": "string"
}
```

#### Create Dynamic Page Request
```json
{
    "title": "string",
    "slug": "string",
    "content": "text",
    "meta_title": "string|null",
    "meta_description": "string|null",
    "meta_keywords": "string|null",
    "status": "string"
}
```

#### Update System Settings Request
```json
{
    "title": "string",
    "system_name": "string",
    "email": "string",
    "contact_number": "string|null",
    "company_open_hour": "string|null",
    "copyright_text": "string",
    "logo": "file|null",
    "favicon": "file|null",
    "footer_logo": "file|null",
    "address": "string",
    "description": "string"
}
```

#### Update Mail Settings Request
```json
{
    "mail_mailer": "string|null",
    "mail_host": "string|null",
    "mail_port": "string|null",
    "mail_username": "string|null",
    "mail_password": "string|null",
    "mail_encryption": "string|null",
    "mail_from_address": "string|null"
}
```

## 📤 Response Schemas

### Standard Success Response
```json
{
    "status": "boolean",
    "message": "string",
    "code": "integer",
    "data": "object|array",
    "pagination": "object|null"
}
```

### Standard Error Response
```json
{
    "status": "boolean",
    "message": "string",
    "code": "integer",
    "errors": "object|null"
}
```

### Pagination Schema
```json
{
    "current_page": "integer",
    "per_page": "integer",
    "total": "integer",
    "last_page": "integer",
    "from": "integer",
    "to": "integer"
}
```

### Authentication Response
```json
{
    "status": "boolean",
    "message": "string",
    "code": "integer",
    "token_type": "string",
    "token": "string",
    "expires_in": "integer",
    "data": {
        "id": "integer",
        "name": "string",
        "email": "string",
        "user_name": "string",
        "role": "string",
        "avatar": "string|null",
        "created_at": "datetime",
        "updated_at": "datetime"
    }
}
```

## 🔍 Query Parameters

### Pagination Parameters
```json
{
    "page": "integer|null",
    "per_page": "integer|null",
    "search": "string|null",
    "sort": "string|null",
    "order": "string|null"
}
```

**Validation Rules:**
- `page`: Optional, integer, min 1
- `per_page`: Optional, integer, min 1, max 100
- `search`: Optional, string, max 255 characters
- `sort`: Optional, string, must be a valid field name
- `order`: Optional, enum (asc, desc)

### Filter Parameters
```json
{
    "status": "string|null",
    "role": "string|null",
    "date_from": "date|null",
    "date_to": "date|null"
}
```

## 🔗 Relationships

### User Relationships
- **One-to-Many**: User has many Customers (if user role)
- **One-to-Many**: User has many Store Details (if admin role)
- **One-to-Many**: User has many Time Packages (if admin role)
- **One-to-Many**: User has many Dynamic Pages (if admin role)

### Customer Relationships
- **Many-to-One**: Customer belongs to Time Package
- **Many-to-One**: Customer belongs to User (creator)

### Time Package Relationships
- **One-to-Many**: Time Package has many Customers
- **Many-to-One**: Time Package belongs to User (creator)

### Store Details Relationships
- **Many-to-One**: Store Details belongs to User (creator)

### Dynamic Page Relationships
- **Many-to-One**: Dynamic Page belongs to User (creator)

## 📊 Data Types

### Primitive Types
- `string`: Text data
- `integer`: Whole numbers
- `decimal`: Decimal numbers (stored as decimal in database)
- `boolean`: True/false values
- `date`: Date in YYYY-MM-DD format
- `datetime`: Date and time in ISO 8601 format
- `text`: Long text data
- `file`: File upload (multipart/form-data)

### Enum Values

#### User Role
- `user`: Regular user
- `admin`: Administrator

#### User Gender
- `male`: Male
- `female`: Female
- `others`: Other/Non-binary

#### Customer Status
- `active`: Active customer
- `inactive`: Inactive customer
- `expired`: Expired customer

#### Dynamic Page Status
- `active`: Published page
- `inactive`: Draft/unpublished page

#### Sort Order
- `asc`: Ascending order
- `desc`: Descending order

## 🔒 Security Considerations

### Sensitive Fields
- `password`: Never returned in responses, always hashed
- `email`: Can be returned but should be handled carefully
- `phone`: Can be returned but should be handled carefully
- `mail_password`: Encrypted in database, never returned in responses

### File Upload Constraints
- **Images**: jpeg, jpg, png, gif, ico, svg
- **Max Size**: 10MB for user images, varies for system files
- **Validation**: File type and size validation on upload

### Data Validation
- All input data is validated on the server side
- Client-side validation is recommended but not sufficient
- SQL injection protection through Eloquent ORM
- XSS protection through proper output escaping

## 📈 Performance Considerations

### Indexing
- Primary keys are automatically indexed
- Foreign keys should be indexed
- Frequently searched fields should be indexed
- Composite indexes for complex queries

### Caching
- Public endpoints are cached
- System settings are cached
- Dynamic pages are cached
- Cache can be cleared through admin endpoints

### Pagination
- All list endpoints support pagination
- Default page size is 15 items
- Maximum page size is 100 items
- Pagination metadata is included in responses

## 🧪 Testing Schemas

### Test Data Examples

#### Valid User Data
```json
{
    "name": "John Doe",
    "email": "john@example.com",
    "user_name": "johndoe",
    "password": "password123",
    "password_confirmation": "password123",
    "phone": "+1234567890",
    "age": 25,
    "gender": "male"
}
```

#### Valid Customer Data
```json
{
    "time_package_id": 1,
    "name": "Jane Smith",
    "rfid_sn_left": "RFID123456",
    "rfid_sn_right": "RFID789012",
    "phone": "+1987654321",
    "email": "jane@example.com"
}
```

#### Valid Time Package Data
```json
{
    "name": "Premium Package",
    "duration_hours": 12,
    "price": 75.00,
    "description": "Premium 12-hour access package"
}
```

### Invalid Data Examples

#### Invalid User Data
```json
{
    "name": "",  // Empty name
    "email": "invalid-email",  // Invalid email format
    "password": "123",  // Too short password
    "age": 150  // Invalid age
}
```

#### Invalid Customer Data
```json
{
    "time_package_id": 999,  // Non-existent package
    "name": "",  // Empty name
    "rfid_sn_left": "",  // Empty RFID
    "phone": "123"  // Too short phone
}
```

## 📚 Related Documentation

- [Authentication API](../api/v1/auth/README.md)
- [User API](../api/v1/user/README.md)
- [Admin API](../api/v1/admin/README.md)
- [Public API](../api/v1/public/README.md)
- [API Examples](../examples/README.md)

## 🔄 Schema Versioning

Current version: **v1**

Schema changes will be versioned and documented. Breaking changes will result in a new API version.

## 📞 Support

For questions about data schemas or validation rules, please refer to the specific API documentation or contact the development team.
