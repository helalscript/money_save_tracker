# Public API Documentation

This document covers all public endpoints in the API v1. These endpoints are accessible without authentication and provide general information about the system.

## Base URL
```
/api/v1
```

## Authentication
Public endpoints do not require authentication. They are accessible to all users.

## Public Endpoints

### 1. Get System Information

**Endpoint:** `GET /system-info`

**Description:** Retrieve general system information including company details, contact information, and basic settings.

**Authentication:** Not required

**Request Body:** None

**Success Response (200):**
```json
{
    "status": true,
    "message": "System info fetched successfully",
    "code": 200,
    "data": {
        "title": "Company Name",
        "system_name": "RobBarden",
        "email": "info@example.com",
        "contact_number": "+1234567890",
        "company_open_hour": "9:00 AM - 9:00 PM",
        "copyright_text": "© 2024 Company Name. All rights reserved.",
        "logo": "uploads/logo/logo.png",
        "favicon": "uploads/favicon/favicon.ico",
        "footer_logo": "uploads/footer_logo/footer.png",
        "address": "123 Main Street, City, State",
        "description": "Company description",
        "social_links": {
            "facebook": "https://facebook.com/company",
            "twitter": "https://twitter.com/company",
            "instagram": "https://instagram.com/company",
            "linkedin": "https://linkedin.com/company"
        },
        "features": [
            "RFID Access Control",
            "Time Tracking",
            "Customer Management",
            "Package Management"
        ]
    }
}
```

**Use Cases:**
- Display company information on public websites
- Show contact details and business hours
- Display logos and branding elements
- Provide system feature information

### 2. Get Dynamic Pages

**Endpoint:** `GET /dynamic-pages`

**Description:** Retrieve a list of all published dynamic pages (like About Us, Privacy Policy, Terms of Service, etc.).

**Authentication:** Not required

**Query Parameters:**
- `page` (optional): Page number (default: 1)
- `per_page` (optional): Items per page (default: 15, max: 100)
- `search` (optional): Search term for title or content
- `status` (optional): Filter by status (active, inactive)

**Example Request:**
```
GET /dynamic-pages?page=1&per_page=10&search=privacy&status=active
```

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
        },
        {
            "id": 2,
            "title": "Privacy Policy",
            "slug": "privacy-policy",
            "content": "This is our privacy policy content...",
            "meta_title": "Privacy Policy - Company",
            "meta_description": "Our privacy policy",
            "status": "active",
            "created_at": "2024-01-01T00:00:00.000000Z",
            "updated_at": "2024-01-01T00:00:00.000000Z"
        }
    ],
    "pagination": {
        "current_page": 1,
        "per_page": 10,
        "total": 2,
        "last_page": 1,
        "from": 1,
        "to": 2
    }
}
```

**Use Cases:**
- Display list of available pages on website
- Build navigation menus
- Show page previews
- Search functionality for pages

### 3. Get Specific Dynamic Page

**Endpoint:** `GET /dynamic-pages/{page_slug}`

**Description:** Retrieve the full content of a specific dynamic page by its slug.

**Authentication:** Not required

**Path Parameters:**
- `page_slug`: The slug of the page (e.g., "about-us", "privacy-policy")

**Example Request:**
```
GET /dynamic-pages/about-us
```

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
        "content": "<h1>About Our Company</h1><p>This is the about us page content with full HTML formatting...</p>",
        "meta_title": "About Us - Company",
        "meta_description": "Learn more about our company and our mission to provide excellent RFID access control solutions.",
        "meta_keywords": "about, company, RFID, access control",
        "status": "active",
        "created_at": "2024-01-01T00:00:00.000000Z",
        "updated_at": "2024-01-01T00:00:00.000000Z",
        "related_pages": [
            {
                "id": 2,
                "title": "Our Services",
                "slug": "our-services"
            },
            {
                "id": 3,
                "title": "Contact Us",
                "slug": "contact-us"
            }
        ]
    }
}
```

**Error Responses:**
- `404` - Page not found or inactive

**Use Cases:**
- Display full page content on website
- SEO meta information
- Related pages suggestions
- Content management for public pages

## Data Models

### System Information Model
```json
{
    "title": "string",
    "system_name": "string",
    "email": "string",
    "contact_number": "string",
    "company_open_hour": "string",
    "copyright_text": "string",
    "logo": "string",
    "favicon": "string",
    "footer_logo": "string",
    "address": "string",
    "description": "string",
    "social_links": {
        "facebook": "string",
        "twitter": "string",
        "instagram": "string",
        "linkedin": "string"
    },
    "features": ["string"]
}
```

### Dynamic Page Model
```json
{
    "id": "integer",
    "title": "string",
    "slug": "string",
    "content": "string",
    "meta_title": "string",
    "meta_description": "string",
    "meta_keywords": "string",
    "status": "string",
    "created_at": "datetime",
    "updated_at": "datetime",
    "related_pages": [
        {
            "id": "integer",
            "title": "string",
            "slug": "string"
        }
    ]
}
```

## Error Handling

### Common Error Responses

**404 Not Found:**
```json
{
    "status": false,
    "message": "Page not found",
    "code": 404
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

## Caching

Public endpoints are typically cached for better performance:

- **System Info**: Cached for 1 hour
- **Dynamic Pages List**: Cached for 30 minutes
- **Specific Dynamic Page**: Cached for 1 hour

Cache can be cleared by admins through the admin panel.

## Rate Limiting

Public endpoints have generous rate limits:
- **System Info**: 100 requests per minute per IP
- **Dynamic Pages**: 200 requests per minute per IP

## SEO Considerations

### Meta Tags
Dynamic pages include comprehensive SEO meta tags:
- `meta_title`: Page title for search engines
- `meta_description`: Page description for search results
- `meta_keywords`: Keywords for SEO (optional)

### URL Structure
- Clean, SEO-friendly URLs using slugs
- Example: `/dynamic-pages/about-us` instead of `/dynamic-pages/1`

### Content Formatting
- Content supports HTML formatting
- Images and media are properly referenced
- Responsive design considerations

## Usage Examples

### JavaScript/Fetch
```javascript
// Get system information
const systemInfo = await fetch('/api/v1/system-info')
    .then(response => response.json());

// Get dynamic pages list
const pages = await fetch('/api/v1/dynamic-pages')
    .then(response => response.json());

// Get specific page
const aboutPage = await fetch('/api/v1/dynamic-pages/about-us')
    .then(response => response.json());
```

### PHP/cURL
```php
// Get system information
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://your-domain.com/api/v1/system-info');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$systemInfo = curl_exec($ch);
curl_close($ch);

// Get dynamic pages
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://your-domain.com/api/v1/dynamic-pages');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$pages = curl_exec($ch);
curl_close($ch);
```

### Python/Requests
```python
import requests

# Get system information
response = requests.get('https://your-domain.com/api/v1/system-info')
system_info = response.json()

# Get dynamic pages
response = requests.get('https://your-domain.com/api/v1/dynamic-pages')
pages = response.json()

# Get specific page
response = requests.get('https://your-domain.com/api/v1/dynamic-pages/about-us')
about_page = response.json()
```

## Integration Examples

### Website Integration
```html
<!-- Display system information -->
<div id="company-info">
    <h1 id="company-name"></h1>
    <p id="company-description"></p>
    <p id="company-address"></p>
    <p id="company-hours"></p>
</div>

<script>
fetch('/api/v1/system-info')
    .then(response => response.json())
    .then(data => {
        document.getElementById('company-name').textContent = data.data.title;
        document.getElementById('company-description').textContent = data.data.description;
        document.getElementById('company-address').textContent = data.data.address;
        document.getElementById('company-hours').textContent = data.data.company_open_hour;
    });
</script>
```

### Mobile App Integration
```javascript
// React Native example
const fetchSystemInfo = async () => {
    try {
        const response = await fetch('https://your-domain.com/api/v1/system-info');
        const data = await response.json();
        return data.data;
    } catch (error) {
        console.error('Error fetching system info:', error);
    }
};

const fetchDynamicPages = async () => {
    try {
        const response = await fetch('https://your-domain.com/api/v1/dynamic-pages');
        const data = await response.json();
        return data.data;
    } catch (error) {
        console.error('Error fetching dynamic pages:', error);
    }
};
```

## Security Notes

1. **No Authentication Required**: These endpoints are public and accessible to everyone
2. **Rate Limiting**: Implemented to prevent abuse
3. **Caching**: Responses are cached for performance
4. **Content Filtering**: Only active/published content is returned
5. **SEO Safe**: All content is safe for public consumption

## Related Documentation

- [Authentication API](../auth/README.md)
- [User API](../user/README.md)
- [Admin API](../admin/README.md)
- [API Examples](../../examples/README.md)
