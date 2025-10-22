# User API Documentation

This document covers all user-related endpoints in the API v1. These endpoints are accessible to authenticated users with the `is_user` middleware.

## Base URL
```
/api/v1
```

## Authentication
All user endpoints require authentication with a valid JWT token:
```
Authorization: Bearer YOUR_JWT_TOKEN
```

## User Endpoints

### Customer Management

The customer management endpoints allow users to manage customer records with RFID tracking capabilities.

#### 1. List Customers

**Endpoint:** `GET /customers`

**Description:** Retrieve a paginated list of customers.

**Authentication:** Required (Bearer token + is_user middleware)

**Query Parameters:**
- `page` (optional): Page number (default: 1)
- `per_page` (optional): Items per page (default: 15, max: 100)
- `search` (optional): Search term for name, email, or phone
- `sort` (optional): Sort field (name, email, phone, created_at)
- `order` (optional): Sort direction (asc, desc)

**Example Request:**
```
GET /customers?page=1&per_page=10&search=john&sort=name&order=asc
```

**Success Response (200):**
```json
{
    "status": true,
    "message": "Customers fetched successfully",
    "code": 200,
    "data": [
        {
            "id": 1,
            "time_package_id": 1,
            "name": "John Doe",
            "rfid_sn_left": "RFID123456",
            "rfid_sn_right": "RFID789012",
            "phone": "+1234567890",
            "email": "john@example.com",
            "start_time": "2024-01-01T09:00:00.000000Z",
            "end_time": "2024-01-01T17:00:00.000000Z",
            "status": "active",
            "created_at": "2024-01-01T00:00:00.000000Z",
            "updated_at": "2024-01-01T00:00:00.000000Z",
            "time_package": {
                "id": 1,
                "name": "Standard Package",
                "duration_hours": 8,
                "price": 50.00
            }
        }
    ],
    "pagination": {
        "current_page": 1,
        "per_page": 10,
        "total": 25,
        "last_page": 3,
        "from": 1,
        "to": 10
    }
}
```

#### 2. Create Customer

**Endpoint:** `POST /customers`

**Description:** Create a new customer record.

**Authentication:** Required (Bearer token + is_user middleware)

**Request Body:**
```json
{
    "time_package_id": 1,           // Required
    "name": "John Doe",              // Required
    "rfid_sn_left": "RFID123456",    // Required
    "rfid_sn_right": "RFID789012",   // Required
    "phone": "+1234567890",          // Required
    "email": "john@example.com"      // Optional
}
```

**Validation Rules:**
- `time_package_id`: Required, integer, must exist in time_packages table
- `name`: Required, string, max 255 characters
- `rfid_sn_left`: Required, string, max 255 characters
- `rfid_sn_right`: Required, string, max 255 characters
- `phone`: Required, string, max 20 characters
- `email`: Optional, valid email format, max 255 characters

**Success Response (200):**
```json
{
    "status": true,
    "message": "Customer created successfully",
    "code": 200,
    "data": {
        "id": 1,
        "time_package_id": 1,
        "name": "John Doe",
        "rfid_sn_left": "RFID123456",
        "rfid_sn_right": "RFID789012",
        "phone": "+1234567890",
        "email": "john@example.com",
        "start_time": "2024-01-01T09:00:00.000000Z",
        "end_time": "2024-01-01T17:00:00.000000Z",
        "status": "active",
        "created_at": "2024-01-01T00:00:00.000000Z",
        "updated_at": "2024-01-01T00:00:00.000000Z",
        "time_package": {
            "id": 1,
            "name": "Standard Package",
            "duration_hours": 8,
            "price": 50.00
        }
    }
}
```

#### 3. Get Customer Details

**Endpoint:** `GET /customers/{id}`

**Description:** Retrieve details of a specific customer.

**Authentication:** Required (Bearer token + is_user middleware)

**Path Parameters:**
- `id`: Customer ID (required)

**Success Response (200):**
```json
{
    "status": true,
    "message": "Customer fetched successfully",
    "code": 200,
    "data": {
        "id": 1,
        "time_package_id": 1,
        "name": "John Doe",
        "rfid_sn_left": "RFID123456",
        "rfid_sn_right": "RFID789012",
        "phone": "+1234567890",
        "email": "john@example.com",
        "start_time": "2024-01-01T09:00:00.000000Z",
        "end_time": "2024-01-01T17:00:00.000000Z",
        "status": "active",
        "created_at": "2024-01-01T00:00:00.000000Z",
        "updated_at": "2024-01-01T00:00:00.000000Z",
        "time_package": {
            "id": 1,
            "name": "Standard Package",
            "duration_hours": 8,
            "price": 50.00,
            "description": "Standard 8-hour package"
        }
    }
}
```

**Error Responses:**
- `404` - Customer not found
- `500` - Server error

#### 4. Update Customer

**Endpoint:** `PUT /customers/{id}`

**Description:** Update an existing customer record.

**Authentication:** Required (Bearer token + is_user middleware)

**Path Parameters:**
- `id`: Customer ID (required)

**Request Body:**
```json
{
    "time_package_id": 2,           // Required
    "name": "John Smith",            // Required
    "rfid_sn_left": "RFID123456",    // Required
    "rfid_sn_right": "RFID789012",   // Required
    "phone": "+1234567890",          // Required
    "email": "johnsmith@example.com" // Optional
}
```

**Validation Rules:**
- Same as create customer endpoint

**Success Response (200):**
```json
{
    "status": true,
    "message": "Customer updated successfully",
    "code": 200,
    "data": {
        "id": 1,
        "time_package_id": 2,
        "name": "John Smith",
        "rfid_sn_left": "RFID123456",
        "rfid_sn_right": "RFID789012",
        "phone": "+1234567890",
        "email": "johnsmith@example.com",
        "start_time": "2024-01-01T09:00:00.000000Z",
        "end_time": "2024-01-01T17:00:00.000000Z",
        "status": "active",
        "created_at": "2024-01-01T00:00:00.000000Z",
        "updated_at": "2024-01-01T12:00:00.000000Z",
        "time_package": {
            "id": 2,
            "name": "Premium Package",
            "duration_hours": 12,
            "price": 75.00
        }
    }
}
```

#### 5. Delete Customer

**Endpoint:** `DELETE /customers/{id}`

**Description:** Delete a customer record.

**Authentication:** Required (Bearer token + is_user middleware)

**Path Parameters:**
- `id`: Customer ID (required)

**Success Response (200):**
```json
{
    "status": true,
    "message": "Customer deleted successfully",
    "code": 200,
    "data": {
        "id": 1,
        "name": "John Doe",
        "deleted_at": "2024-01-01T12:00:00.000000Z"
    }
}
```

**Error Responses:**
- `404` - Customer not found
- `500` - Server error

## Data Models

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
    "deleted_at": "datetime|null"
}
```

### Time Package Model (Related)
```json
{
    "id": "integer",
    "name": "string",
    "duration_hours": "integer",
    "price": "decimal",
    "description": "string|null",
    "created_at": "datetime",
    "updated_at": "datetime"
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
    "message": "Access denied. User role required.",
    "code": 403
}
```

**404 Not Found:**
```json
{
    "status": false,
    "message": "Customer not found",
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
        "name": ["The name field is required."],
        "time_package_id": ["The selected time package id is invalid."]
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

## Business Logic

### Customer Status
- **active**: Customer is currently active
- **inactive**: Customer is temporarily inactive
- **expired**: Customer's time package has expired

### RFID Tracking
- Each customer has two RFID serial numbers (left and right)
- These are used for access control and time tracking
- RFID numbers must be unique across all customers

### Time Packages
- Customers are associated with time packages
- Time packages define duration and pricing
- Start and end times are automatically calculated based on the package

## Usage Examples

### JavaScript/Fetch
```javascript
// Get customers
const response = await fetch('/api/v1/customers', {
    headers: {
        'Authorization': 'Bearer YOUR_JWT_TOKEN',
        'Content-Type': 'application/json'
    }
});
const customers = await response.json();

// Create customer
const newCustomer = await fetch('/api/v1/customers', {
    method: 'POST',
    headers: {
        'Authorization': 'Bearer YOUR_JWT_TOKEN',
        'Content-Type': 'application/json'
    },
    body: JSON.stringify({
        time_package_id: 1,
        name: 'John Doe',
        rfid_sn_left: 'RFID123456',
        rfid_sn_right: 'RFID789012',
        phone: '+1234567890',
        email: 'john@example.com'
    })
});
```

### PHP/cURL
```php
// Get customers
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://your-domain.com/api/v1/customers');
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer YOUR_JWT_TOKEN',
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);
```

## Security Notes

1. **Authentication**: All endpoints require valid JWT token
2. **Authorization**: Only users with `is_user` middleware can access these endpoints
3. **Data Validation**: All input data is validated before processing
4. **Soft Deletes**: Customer records are soft deleted (not permanently removed)
5. **RFID Uniqueness**: RFID serial numbers must be unique across all customers
6. **Rate Limiting**: Subject to server capacity limits

## Related Documentation

- [Authentication API](../auth/README.md)
- [Admin API](../admin/README.md)
- [Public API](../public/README.md)
- [API Examples](../../examples/README.md)
