/**
 * SAGA POS - API Service for Laravel
 * HTTP client for API requests with CSRF and Token authentication
 */

class ApiService {
    constructor() {
        this.baseUrl = '/api';
        this.timeout = 30000; // 30 seconds
    }

    /**
     * Get stored authentication token (Laravel Sanctum/Passport)
     */
    getToken() {
        return localStorage.getItem('auth_token');
    }

    /**
     * Set authentication token
     */
    setToken(token) {
        localStorage.setItem('auth_token', token);
        // Also set in cookie for Laravel to read
        document.cookie = `auth_token=${token}; path=/`;
    }

    /**
     * Remove authentication token
     */
    removeToken() {
        localStorage.removeItem('auth_token');
        document.cookie = 'auth_token=; path=/; expires=Thu, 01 Jan 1970 00:00:01 GMT;';
    }

    /**
     * Get CSRF token from meta tag
     */
    getCsrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    }

    /**
     * Get default headers with CSRF and Authorization
     */
    getHeaders() {
        const headers = {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': this.getCsrfToken()
        };

        const token = this.getToken();
        if (token) {
            headers['Authorization'] = `Bearer ${token}`;
        }

        return headers;
    }

    /**
     * Make HTTP request with error handling
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
            const response = await this._fetchWithTimeout(url, config);
            const data = await response.json();

            if (!response.ok) {
                // Handle 401 - Unauthorized (token expired)
                if (response.status === 401) {
                    this.removeToken();
                    localStorage.removeItem('user');
                    localStorage.removeItem('tenant');
                    window.location.href = '/login';
                    throw new Error('Session expired. Please log in again.');
                }

                // Handle 403 - Forbidden
                if (response.status === 403) {
                    throw new Error(data.message || 'You do not have permission to perform this action.');
                }

                // Handle 422 - Validation errors
                if (response.status === 422) {
                    throw new Error(JSON.stringify(data.errors || { message: 'Validation failed' }));
                }

                throw new Error(data.message || `Request failed with status ${response.status}`);
            }

            return data;
        } catch (error) {
            console.error('API Error:', error);
            throw error;
        }
    }

    /**
     * Fetch with timeout
     */
    _fetchWithTimeout(url, options) {
        return Promise.race([
            fetch(url, options),
            new Promise((_, reject) =>
                setTimeout(() => reject(new Error('Request timeout')), this.timeout)
            )
        ]);
    }

    /**
     * GET request
     * @param {string} endpoint
     * @param {Object} params - Query parameters
     */
    async get(endpoint, params = {}) {
        const queryString = new URLSearchParams(params).toString();
        const url = queryString ? `${endpoint}?${queryString}` : endpoint;
        return this.request(url, { method: 'GET' });
    }

    /**
     * POST request
     * @param {string} endpoint
     * @param {Object} data - Request body
     */
    async post(endpoint, data = {}) {
        return this.request(endpoint, {
            method: 'POST',
            body: JSON.stringify(data)
        });
    }

    /**
     * PUT request
     * @param {string} endpoint
     * @param {Object} data - Request body
     */
    async put(endpoint, data = {}) {
        return this.request(endpoint, {
            method: 'PUT',
            body: JSON.stringify(data)
        });
    }

    /**
     * PATCH request
     * @param {string} endpoint
     * @param {Object} data - Request body
     */
    async patch(endpoint, data = {}) {
        return this.request(endpoint, {
            method: 'PATCH',
            body: JSON.stringify(data)
        });
    }

    /**
     * DELETE request
     * @param {string} endpoint
     */
    async delete(endpoint) {
        return this.request(endpoint, { method: 'DELETE' });
    }

    /**
     * Upload file with multipart/form-data
     * @param {string} endpoint
     * @param {FormData} formData
     */
    async upload(endpoint, formData) {
        const url = `${this.baseUrl}${endpoint}`;

        const config = {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': this.getCsrfToken()
            },
            body: formData
        };

        const token = this.getToken();
        if (token) {
            config.headers['Authorization'] = `Bearer ${token}`;
        }

        try {
            const response = await this._fetchWithTimeout(url, config);
            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || 'Upload failed');
            }

            return data;
        } catch (error) {
            console.error('Upload Error:', error);
            throw error;
        }
    }
}

// Export as both default and named export for flexibility
export default new ApiService();
export { ApiService };
