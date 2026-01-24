/**
 * SAGA TOKO API Service
 * HTTP client for API requests with JWT authentication
 */

const API_BASE_URL = '/api';

class ApiService {
    constructor() {
        this.baseUrl = API_BASE_URL;
    }

    /**
     * Get stored JWT token
     */
    getToken() {
        return localStorage.getItem('saga_token');
    }

    /**
     * Set JWT token
     */
    setToken(token) {
        localStorage.setItem('saga_token', token);
    }

    /**
     * Remove JWT token
     */
    removeToken() {
        localStorage.removeItem('saga_token');
    }

    /**
     * Get default headers
     */
    getHeaders() {
        const headers = {
            'Content-Type': 'application/json'
        };

        const token = this.getToken();
        if (token) {
            headers['Authorization'] = `Bearer ${token}`;
        }

        return headers;
    }

    /**
     * Make HTTP request
     * @param {string} endpoint - API endpoint
     * @param {Object} options - Fetch options
     */
    async request(endpoint, options = {}) {
        const url = `${this.baseUrl}${endpoint}`;

        const config = {
            ...options,
            headers: {
                ...this.getHeaders(),
                ...options.headers
            }
        };

        try {
            const response = await fetch(url, config);
            const data = await response.json();

            if (!response.ok) {
                // Handle 401 - Token expired
                if (response.status === 401) {
                    this.removeToken();
                    localStorage.removeItem('saga_user');
                    localStorage.removeItem('saga_tenant');
                    window.location.href = '/signin.html';
                    throw new Error('Session expired');
                }
                throw new Error(data.message || 'Request failed');
            }

            return data;
        } catch (error) {
            console.error('API Error:', error);
            throw error;
        }
    }

    /**
     * GET request
     */
    async get(endpoint, params = {}) {
        const queryString = new URLSearchParams(params).toString();
        const url = queryString ? `${endpoint}?${queryString}` : endpoint;
        return this.request(url, { method: 'GET' });
    }

    /**
     * POST request
     */
    async post(endpoint, data = {}) {
        return this.request(endpoint, {
            method: 'POST',
            body: JSON.stringify(data)
        });
    }

    /**
     * PUT request
     */
    async put(endpoint, data = {}) {
        return this.request(endpoint, {
            method: 'PUT',
            body: JSON.stringify(data)
        });
    }

    /**
     * PATCH request
     */
    async patch(endpoint, data = {}) {
        return this.request(endpoint, {
            method: 'PATCH',
            body: JSON.stringify(data)
        });
    }

    /**
     * DELETE request
     */
    async delete(endpoint) {
        return this.request(endpoint, { method: 'DELETE' });
    }
}

// Create singleton instance
const api = new ApiService();

export default api;
