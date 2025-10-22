# API Usage Examples

This directory contains practical examples of how to use the Rob Barden API v1 in various programming languages and frameworks.

## 📁 Examples Structure

```
examples/
├── README.md              # This file
├── javascript/            # JavaScript/Node.js examples
├── php/                  # PHP examples
├── python/               # Python examples
├── curl/                 # cURL command examples
├── postman/              # Postman collection
└── react/                # React/React Native examples
```

## 🚀 Quick Start Examples

### Authentication Flow

#### 1. Login
```javascript
// JavaScript
const login = async (email, password) => {
    const response = await fetch('/api/v1/login', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            email: email,
            password: password
        })
    });
    
    const data = await response.json();
    if (data.status) {
        localStorage.setItem('token', data.token);
        return data;
    }
    throw new Error(data.message);
};
```

#### 2. Make Authenticated Requests
```javascript
// JavaScript
const makeAuthenticatedRequest = async (url, options = {}) => {
    const token = localStorage.getItem('token');
    
    return fetch(url, {
        ...options,
        headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json',
            ...options.headers
        }
    });
};
```

## 📋 Complete Examples

### JavaScript/Node.js Examples

#### Authentication
```javascript
class APIClient {
    constructor(baseURL) {
        this.baseURL = baseURL;
        this.token = null;
    }

    async login(email, password) {
        const response = await fetch(`${this.baseURL}/login`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ email, password })
        });

        const data = await response.json();
        if (data.status) {
            this.token = data.token;
            return data;
        }
        throw new Error(data.message);
    }

    async logout() {
        const response = await this.makeRequest('/logout', {
            method: 'POST'
        });
        this.token = null;
        return response;
    }

    async refreshToken() {
        const response = await this.makeRequest('/refresh-token');
        if (response.status) {
            this.token = response.token;
        }
        return response;
    }

    async makeRequest(endpoint, options = {}) {
        const url = `${this.baseURL}${endpoint}`;
        const headers = {
            'Content-Type': 'application/json',
            ...options.headers
        };

        if (this.token) {
            headers['Authorization'] = `Bearer ${this.token}`;
        }

        const response = await fetch(url, {
            ...options,
            headers
        });

        return response.json();
    }
}

// Usage
const api = new APIClient('https://your-domain.com/api/v1');

// Login
try {
    const loginResult = await api.login('user@example.com', 'password123');
    console.log('Login successful:', loginResult);
} catch (error) {
    console.error('Login failed:', error.message);
}
```

#### Customer Management
```javascript
class CustomerAPI extends APIClient {
    async getCustomers(page = 1, perPage = 15, search = '') {
        const params = new URLSearchParams({
            page: page.toString(),
            per_page: perPage.toString(),
            ...(search && { search })
        });

        return this.makeRequest(`/customers?${params}`);
    }

    async createCustomer(customerData) {
        return this.makeRequest('/customers', {
            method: 'POST',
            body: JSON.stringify(customerData)
        });
    }

    async updateCustomer(id, customerData) {
        return this.makeRequest(`/customers/${id}`, {
            method: 'PUT',
            body: JSON.stringify(customerData)
        });
    }

    async deleteCustomer(id) {
        return this.makeRequest(`/customers/${id}`, {
            method: 'DELETE'
        });
    }
}

// Usage
const customerAPI = new CustomerAPI('https://your-domain.com/api/v1');

// Get customers
const customers = await customerAPI.getCustomers(1, 10, 'john');

// Create customer
const newCustomer = await customerAPI.createCustomer({
    time_package_id: 1,
    name: 'John Doe',
    rfid_sn_left: 'RFID123456',
    rfid_sn_right: 'RFID789012',
    phone: '+1234567890',
    email: 'john@example.com'
});
```

### PHP Examples

#### Authentication
```php
<?php
class APIClient {
    private $baseURL;
    private $token;

    public function __construct($baseURL) {
        $this->baseURL = $baseURL;
    }

    public function login($email, $password) {
        $data = [
            'email' => $email,
            'password' => $password
        ];

        $response = $this->makeRequest('/login', 'POST', $data);
        
        if ($response['status']) {
            $this->token = $response['token'];
        }
        
        return $response;
    }

    public function logout() {
        $response = $this->makeRequest('/logout', 'POST');
        $this->token = null;
        return $response;
    }

    public function refreshToken() {
        $response = $this->makeRequest('/refresh-token');
        if ($response['status']) {
            $this->token = $response['token'];
        }
        return $response;
    }

    private function makeRequest($endpoint, $method = 'GET', $data = null) {
        $url = $this->baseURL . $endpoint;
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            $this->token ? "Authorization: Bearer {$this->token}" : ''
        ]);

        if ($method === 'POST' || $method === 'PUT') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
            if ($data) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            }
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return json_decode($response, true);
    }
}

// Usage
$api = new APIClient('https://your-domain.com/api/v1');

// Login
$loginResult = $api->login('user@example.com', 'password123');
if ($loginResult['status']) {
    echo "Login successful\n";
} else {
    echo "Login failed: " . $loginResult['message'] . "\n";
}
?>
```

#### Customer Management
```php
<?php
class CustomerAPI extends APIClient {
    public function getCustomers($page = 1, $perPage = 15, $search = '') {
        $params = http_build_query([
            'page' => $page,
            'per_page' => $perPage,
            'search' => $search
        ]);

        return $this->makeRequest("/customers?{$params}");
    }

    public function createCustomer($customerData) {
        return $this->makeRequest('/customers', 'POST', $customerData);
    }

    public function updateCustomer($id, $customerData) {
        return $this->makeRequest("/customers/{$id}", 'PUT', $customerData);
    }

    public function deleteCustomer($id) {
        return $this->makeRequest("/customers/{$id}", 'DELETE');
    }
}

// Usage
$customerAPI = new CustomerAPI('https://your-domain.com/api/v1');

// Get customers
$customers = $customerAPI->getCustomers(1, 10, 'john');

// Create customer
$newCustomer = $customerAPI->createCustomer([
    'time_package_id' => 1,
    'name' => 'John Doe',
    'rfid_sn_left' => 'RFID123456',
    'rfid_sn_right' => 'RFID789012',
    'phone' => '+1234567890',
    'email' => 'john@example.com'
]);
?>
```

### Python Examples

#### Authentication
```python
import requests
import json

class APIClient:
    def __init__(self, base_url):
        self.base_url = base_url
        self.token = None
        self.session = requests.Session()

    def login(self, email, password):
        data = {
            'email': email,
            'password': password
        }
        
        response = self.make_request('/login', 'POST', data)
        
        if response['status']:
            self.token = response['token']
        
        return response

    def logout(self):
        response = self.make_request('/logout', 'POST')
        self.token = None
        return response

    def refresh_token(self):
        response = self.make_request('/refresh-token')
        if response['status']:
            self.token = response['token']
        return response

    def make_request(self, endpoint, method='GET', data=None):
        url = self.base_url + endpoint
        headers = {'Content-Type': 'application/json'}
        
        if self.token:
            headers['Authorization'] = f'Bearer {self.token}'
        
        if method == 'POST' or method == 'PUT':
            response = self.session.request(method, url, headers=headers, json=data)
        else:
            response = self.session.request(method, url, headers=headers)
        
        return response.json()

# Usage
api = APIClient('https://your-domain.com/api/v1')

# Login
login_result = api.login('user@example.com', 'password123')
if login_result['status']:
    print("Login successful")
else:
    print(f"Login failed: {login_result['message']}")
```

#### Customer Management
```python
class CustomerAPI(APIClient):
    def get_customers(self, page=1, per_page=15, search=''):
        params = {
            'page': page,
            'per_page': per_page,
            'search': search
        }
        
        # Build query string
        query_string = '&'.join([f'{k}={v}' for k, v in params.items() if v])
        endpoint = f'/customers?{query_string}'
        
        return self.make_request(endpoint)

    def create_customer(self, customer_data):
        return self.make_request('/customers', 'POST', customer_data)

    def update_customer(self, customer_id, customer_data):
        return self.make_request(f'/customers/{customer_id}', 'PUT', customer_data)

    def delete_customer(self, customer_id):
        return self.make_request(f'/customers/{customer_id}', 'DELETE')

# Usage
customer_api = CustomerAPI('https://your-domain.com/api/v1')

# Get customers
customers = customer_api.get_customers(page=1, per_page=10, search='john')

# Create customer
new_customer = customer_api.create_customer({
    'time_package_id': 1,
    'name': 'John Doe',
    'rfid_sn_left': 'RFID123456',
    'rfid_sn_right': 'RFID789012',
    'phone': '+1234567890',
    'email': 'john@example.com'
})
```

### cURL Examples

#### Authentication
```bash
# Login
curl -X POST https://your-domain.com/api/v1/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "user@example.com",
    "password": "password123"
  }'

# Logout
curl -X POST https://your-domain.com/api/v1/logout \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -H "Content-Type: application/json"

# Refresh Token
curl -X GET https://your-domain.com/api/v1/refresh-token \
  -H "Authorization: Bearer YOUR_JWT_TOKEN"
```

#### Customer Management
```bash
# Get customers
curl -X GET "https://your-domain.com/api/v1/customers?page=1&per_page=10&search=john" \
  -H "Authorization: Bearer YOUR_JWT_TOKEN"

# Create customer
curl -X POST https://your-domain.com/api/v1/customers \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "time_package_id": 1,
    "name": "John Doe",
    "rfid_sn_left": "RFID123456",
    "rfid_sn_right": "RFID789012",
    "phone": "+1234567890",
    "email": "john@example.com"
  }'

# Update customer
curl -X PUT https://your-domain.com/api/v1/customers/1 \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "time_package_id": 1,
    "name": "John Smith",
    "rfid_sn_left": "RFID123456",
    "rfid_sn_right": "RFID789012",
    "phone": "+1234567890",
    "email": "johnsmith@example.com"
  }'

# Delete customer
curl -X DELETE https://your-domain.com/api/v1/customers/1 \
  -H "Authorization: Bearer YOUR_JWT_TOKEN"
```

#### Admin Endpoints
```bash
# Get system settings
curl -X GET https://your-domain.com/api/v1/admin/system/settings \
  -H "Authorization: Bearer YOUR_JWT_TOKEN"

# Update system settings
curl -X POST https://your-domain.com/api/v1/admin/system/settings \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Updated Company Name",
    "system_name": "RobBarden",
    "email": "admin@example.com",
    "address": "123 Main Street",
    "description": "Updated description"
  }'

# Get time packages
curl -X GET https://your-domain.com/api/v1/admin/time-packages \
  -H "Authorization: Bearer YOUR_JWT_TOKEN"

# Create time package
curl -X POST https://your-domain.com/api/v1/admin/time-packages \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Premium Package",
    "duration_hours": 12,
    "price": 75.00,
    "description": "Premium 12-hour package"
  }'
```

#### Public Endpoints
```bash
# Get system information
curl -X GET https://your-domain.com/api/v1/system-info

# Get dynamic pages
curl -X GET "https://your-domain.com/api/v1/dynamic-pages?page=1&per_page=10"

# Get specific dynamic page
curl -X GET https://your-domain.com/api/v1/dynamic-pages/about-us
```

## 🔧 Error Handling Examples

### JavaScript
```javascript
const handleAPIError = (error, response) => {
    if (response.status === 401) {
        // Token expired, redirect to login
        localStorage.removeItem('token');
        window.location.href = '/login';
    } else if (response.status === 403) {
        // Access denied
        alert('Access denied. You do not have permission to perform this action.');
    } else if (response.status === 422) {
        // Validation errors
        const errors = response.errors;
        Object.keys(errors).forEach(field => {
            console.error(`${field}: ${errors[field].join(', ')}`);
        });
    } else if (response.status >= 500) {
        // Server error
        alert('Server error. Please try again later.');
    }
};

// Usage
try {
    const response = await api.makeRequest('/customers');
    if (!response.status) {
        handleAPIError(null, response);
    }
} catch (error) {
    console.error('Request failed:', error);
}
```

### PHP
```php
function handleAPIError($response) {
    if ($response['code'] == 401) {
        // Token expired
        session_destroy();
        header('Location: /login');
        exit;
    } elseif ($response['code'] == 403) {
        // Access denied
        echo "Access denied: " . $response['message'];
    } elseif ($response['code'] == 422) {
        // Validation errors
        foreach ($response['errors'] as $field => $messages) {
            echo "$field: " . implode(', ', $messages) . "\n";
        }
    } elseif ($response['code'] >= 500) {
        // Server error
        echo "Server error: " . $response['message'];
    }
}
```

## 📱 React/React Native Examples

### React Hook for API
```javascript
import { useState, useEffect } from 'react';

const useAPI = (baseURL) => {
    const [token, setToken] = useState(localStorage.getItem('token'));
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState(null);

    const makeRequest = async (endpoint, options = {}) => {
        setLoading(true);
        setError(null);

        try {
            const url = `${baseURL}${endpoint}`;
            const headers = {
                'Content-Type': 'application/json',
                ...options.headers
            };

            if (token) {
                headers['Authorization'] = `Bearer ${token}`;
            }

            const response = await fetch(url, {
                ...options,
                headers
            });

            const data = await response.json();

            if (!data.status) {
                throw new Error(data.message);
            }

            return data;
        } catch (err) {
            setError(err.message);
            throw err;
        } finally {
            setLoading(false);
        }
    };

    const login = async (email, password) => {
        const response = await makeRequest('/login', {
            method: 'POST',
            body: JSON.stringify({ email, password })
        });

        if (response.status) {
            setToken(response.token);
            localStorage.setItem('token', response.token);
        }

        return response;
    };

    const logout = async () => {
        await makeRequest('/logout', { method: 'POST' });
        setToken(null);
        localStorage.removeItem('token');
    };

    return {
        token,
        loading,
        error,
        makeRequest,
        login,
        logout
    };
};

export default useAPI;
```

### React Component Example
```javascript
import React, { useState, useEffect } from 'react';
import useAPI from './hooks/useAPI';

const CustomerList = () => {
    const { makeRequest, loading, error } = useAPI('https://your-domain.com/api/v1');
    const [customers, setCustomers] = useState([]);
    const [pagination, setPagination] = useState({});

    useEffect(() => {
        fetchCustomers();
    }, []);

    const fetchCustomers = async (page = 1) => {
        try {
            const response = await makeRequest(`/customers?page=${page}`);
            setCustomers(response.data);
            setPagination(response.pagination);
        } catch (err) {
            console.error('Failed to fetch customers:', err);
        }
    };

    const createCustomer = async (customerData) => {
        try {
            const response = await makeRequest('/customers', {
                method: 'POST',
                body: JSON.stringify(customerData)
            });
            
            // Refresh the list
            fetchCustomers();
            return response;
        } catch (err) {
            console.error('Failed to create customer:', err);
        }
    };

    if (loading) return <div>Loading...</div>;
    if (error) return <div>Error: {error}</div>;

    return (
        <div>
            <h2>Customers</h2>
            {customers.map(customer => (
                <div key={customer.id}>
                    <h3>{customer.name}</h3>
                    <p>Phone: {customer.phone}</p>
                    <p>Email: {customer.email}</p>
                    <p>RFID Left: {customer.rfid_sn_left}</p>
                    <p>RFID Right: {customer.rfid_sn_right}</p>
                </div>
            ))}
            
            {pagination.last_page > 1 && (
                <div>
                    {Array.from({ length: pagination.last_page }, (_, i) => i + 1).map(page => (
                        <button key={page} onClick={() => fetchCustomers(page)}>
                            {page}
                        </button>
                    ))}
                </div>
            )}
        </div>
    );
};

export default CustomerList;
```

## 🔐 Security Best Practices

### Token Management
```javascript
// Store token securely
const storeToken = (token) => {
    // Use secure storage in production
    localStorage.setItem('token', token);
};

// Check token expiration
const isTokenExpired = (token) => {
    try {
        const payload = JSON.parse(atob(token.split('.')[1]));
        return Date.now() >= payload.exp * 1000;
    } catch {
        return true;
    }
};

// Auto-refresh token
const autoRefreshToken = async (apiClient) => {
    const token = localStorage.getItem('token');
    if (token && isTokenExpired(token)) {
        try {
            await apiClient.refreshToken();
        } catch (error) {
            // Redirect to login
            window.location.href = '/login';
        }
    }
};
```

### Input Validation
```javascript
const validateCustomerData = (data) => {
    const errors = {};

    if (!data.name || data.name.trim().length === 0) {
        errors.name = 'Name is required';
    }

    if (!data.phone || data.phone.trim().length < 10) {
        errors.phone = 'Phone number must be at least 10 characters';
    }

    if (data.email && !/\S+@\S+\.\S+/.test(data.email)) {
        errors.email = 'Email must be valid';
    }

    if (!data.rfid_sn_left || data.rfid_sn_left.trim().length === 0) {
        errors.rfid_sn_left = 'RFID Left is required';
    }

    if (!data.rfid_sn_right || data.rfid_sn_right.trim().length === 0) {
        errors.rfid_sn_right = 'RFID Right is required';
    }

    return {
        isValid: Object.keys(errors).length === 0,
        errors
    };
};
```

## 📊 Testing Examples

### Unit Tests (JavaScript)
```javascript
// Using Jest
describe('APIClient', () => {
    let apiClient;

    beforeEach(() => {
        apiClient = new APIClient('https://your-domain.com/api/v1');
    });

    test('should login successfully', async () => {
        const mockResponse = {
            status: true,
            token: 'mock-token',
            data: { id: 1, name: 'Test User' }
        };

        global.fetch = jest.fn().mockResolvedValue({
            json: () => Promise.resolve(mockResponse)
        });

        const result = await apiClient.login('test@example.com', 'password');
        
        expect(result.status).toBe(true);
        expect(result.token).toBe('mock-token');
        expect(apiClient.token).toBe('mock-token');
    });

    test('should handle login failure', async () => {
        const mockResponse = {
            status: false,
            message: 'Invalid credentials'
        };

        global.fetch = jest.fn().mockResolvedValue({
            json: () => Promise.resolve(mockResponse)
        });

        await expect(apiClient.login('test@example.com', 'wrong-password'))
            .rejects.toThrow('Invalid credentials');
    });
});
```

## 📚 Additional Resources

- [Authentication API](../api/v1/auth/README.md)
- [User API](../api/v1/user/README.md)
- [Admin API](../api/v1/admin/README.md)
- [Public API](../api/v1/public/README.md)
- [Data Schemas](../schemas/README.md)

## 🤝 Contributing

If you have additional examples or improvements, please feel free to contribute by:

1. Adding new language examples
2. Improving existing examples
3. Adding more comprehensive error handling
4. Including testing examples
5. Adding framework-specific examples

## 📞 Support

For questions about these examples or the API, please refer to the main documentation or contact the development team.
