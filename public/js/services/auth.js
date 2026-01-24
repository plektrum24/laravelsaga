/**
 * SAGA TOKO Authentication Service
 * Handles login, logout, and session management
 */

import api from './api.js';

class AuthService {
    /**
     * Login user
     * @param {string} email 
     * @param {string} password 
     */
    async login(email, password) {
        try {
            const response = await api.post('/auth/login', { email, password });

            if (response.success) {
                // Store token and user info
                api.setToken(response.data.token);
                localStorage.setItem('saga_user', JSON.stringify(response.data.user));

                if (response.data.tenant) {
                    localStorage.setItem('saga_tenant', JSON.stringify(response.data.tenant));
                }

                return {
                    success: true,
                    user: response.data.user,
                    tenant: response.data.tenant,
                    redirectPath: response.data.redirectPath
                };
            }

            return { success: false, message: response.message };
        } catch (error) {
            return { success: false, message: error.message || 'Login failed' };
        }
    }

    /**
     * Logout user
     */
    logout() {
        api.removeToken();
        localStorage.removeItem('saga_user');
        localStorage.removeItem('saga_tenant');
        window.location.href = '/signin.html';
    }

    /**
     * Get current user from storage
     */
    getCurrentUser() {
        const userStr = localStorage.getItem('saga_user');
        return userStr ? JSON.parse(userStr) : null;
    }

    /**
     * Get current tenant from storage
     */
    getCurrentTenant() {
        const tenantStr = localStorage.getItem('saga_tenant');
        return tenantStr ? JSON.parse(tenantStr) : null;
    }

    /**
     * Check if user is authenticated
     */
    isAuthenticated() {
        return !!api.getToken();
    }

    /**
     * Check if user has specific role
     * @param {string|string[]} roles 
     */
    hasRole(roles) {
        const user = this.getCurrentUser();
        if (!user) return false;

        if (Array.isArray(roles)) {
            return roles.includes(user.role);
        }
        return user.role === roles;
    }

    /**
     * Check if user is super admin
     */
    isSuperAdmin() {
        return this.hasRole('super_admin');
    }

    /**
     * Check if user is tenant owner or manager
     */
    isTenantAdmin() {
        return this.hasRole(['tenant_owner', 'manager']);
    }

    /**
     * Check if user is cashier
     */
    isCashier() {
        return this.hasRole('cashier');
    }

    /**
     * Verify and refresh session (call on page load)
     */
    async verifySession() {
        if (!this.isAuthenticated()) {
            return false;
        }

        try {
            const response = await api.get('/auth/me');
            if (response.success) {
                localStorage.setItem('saga_user', JSON.stringify(response.data.user));
                if (response.data.tenant) {
                    localStorage.setItem('saga_tenant', JSON.stringify(response.data.tenant));
                }
                return true;
            }
            return false;
        } catch (error) {
            console.error('Session verification failed:', error);
            return false;
        }
    }

    /**
     * Change password
     * @param {string} currentPassword 
     * @param {string} newPassword 
     */
    async changePassword(currentPassword, newPassword) {
        try {
            const response = await api.post('/auth/change-password', {
                currentPassword,
                newPassword
            });
            return response;
        } catch (error) {
            return { success: false, message: error.message };
        }
    }

    /**
     * Get redirect path based on user role
     */
    getRedirectPath() {
        const user = this.getCurrentUser();
        if (!user) return '/signin.html';

        switch (user.role) {
            case 'super_admin':
                return '/admin-dashboard.html';
            case 'cashier':
                return '/pos.html';
            default:
                return '/dashboard.html';
        }
    }
}

// Create singleton instance
const auth = new AuthService();

export default auth;
