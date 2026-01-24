/**
 * SAGA POS - Authentication Service
 * Handles login, logout, registration, and session management
 */

import api from './api.js';

class AuthService {
    /**
     * Login user with email and password
     * @param {string} email - User email
     * @param {string} password - User password
     */
    async login(email, password) {
        try {
            const response = await api.post('/auth/login', { email, password });

            if (response.success || response.data) {
                const { token, user, tenant } = response.data;

                // Store token
                api.setToken(token);

                // Store user info
                localStorage.setItem('user', JSON.stringify(user));

                // Store tenant if provided
                if (tenant) {
                    localStorage.setItem('tenant', JSON.stringify(tenant));
                }

                return {
                    success: true,
                    user,
                    tenant,
                    token
                };
            }

            return {
                success: false,
                message: response.message || 'Login failed'
            };
        } catch (error) {
            console.error('Login error:', error);
            return {
                success: false,
                message: error.message || 'Login failed. Please try again.'
            };
        }
    }

    /**
     * Register new user
     * @param {Object} userData - User registration data
     */
    async register(userData) {
        try {
            const response = await api.post('/auth/register', userData);

            if (response.success || response.data) {
                const { token, user } = response.data;

                // Store token
                api.setToken(token);

                // Store user info
                localStorage.setItem('user', JSON.stringify(user));

                return {
                    success: true,
                    user,
                    token
                };
            }

            return {
                success: false,
                message: response.message || 'Registration failed'
            };
        } catch (error) {
            console.error('Registration error:', error);
            return {
                success: false,
                message: error.message || 'Registration failed. Please try again.'
            };
        }
    }

    /**
     * Logout current user
     */
    logout() {
        try {
            // Optionally call logout endpoint to invalidate token on server
            api.post('/auth/logout').catch(() => {
                // Continue logout even if API call fails
            });
        } finally {
            // Clear local data
            api.removeToken();
            localStorage.removeItem('user');
            localStorage.removeItem('tenant');

            // Redirect to login
            window.location.href = '/login';
        }
    }

    /**
     * Get current user from local storage
     */
    getCurrentUser() {
        const userStr = localStorage.getItem('user');
        return userStr ? JSON.parse(userStr) : null;
    }

    /**
     * Update current user data
     */
    setCurrentUser(user) {
        localStorage.setItem('user', JSON.stringify(user));
    }

    /**
     * Get current tenant from local storage
     */
    getCurrentTenant() {
        const tenantStr = localStorage.getItem('tenant');
        return tenantStr ? JSON.parse(tenantStr) : null;
    }

    /**
     * Update current tenant data
     */
    setCurrentTenant(tenant) {
        if (tenant) {
            localStorage.setItem('tenant', JSON.stringify(tenant));
        } else {
            localStorage.removeItem('tenant');
        }
    }

    /**
     * Check if user is authenticated
     */
    isAuthenticated() {
        return !!api.getToken() && !!this.getCurrentUser();
    }

    /**
     * Check if user has specific role
     * @param {string|string[]} roles - Role name(s) to check
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
     * Check if user has specific permission
     * @param {string} permission - Permission name
     */
    hasPermission(permission) {
        const user = this.getCurrentUser();
        if (!user) return false;

        return user.permissions && user.permissions.includes(permission);
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
     * Check if user is sales staff
     */
    isSalesStaff() {
        return this.hasRole(['cashier', 'sales_staff']);
    }

    /**
     * Change user password
     * @param {string} currentPassword
     * @param {string} newPassword
     * @param {string} newPasswordConfirmation
     */
    async changePassword(currentPassword, newPassword, newPasswordConfirmation) {
        try {
            const response = await api.post('/auth/change-password', {
                current_password: currentPassword,
                new_password: newPassword,
                new_password_confirmation: newPasswordConfirmation
            });

            if (response.success) {
                return { success: true, message: 'Password changed successfully' };
            }

            return {
                success: false,
                message: response.message || 'Failed to change password'
            };
        } catch (error) {
            return {
                success: false,
                message: error.message || 'Failed to change password'
            };
        }
    }

    /**
     * Request password reset
     * @param {string} email
     */
    async requestPasswordReset(email) {
        try {
            const response = await api.post('/auth/forgot-password', { email });

            if (response.success) {
                return { success: true, message: 'Password reset link sent to your email' };
            }

            return {
                success: false,
                message: response.message || 'Failed to request password reset'
            };
        } catch (error) {
            return {
                success: false,
                message: error.message || 'Failed to request password reset'
            };
        }
    }

    /**
     * Reset password with token
     * @param {string} token
     * @param {string} email
     * @param {string} password
     * @param {string} passwordConfirmation
     */
    async resetPassword(token, email, password, passwordConfirmation) {
        try {
            const response = await api.post('/auth/reset-password', {
                token,
                email,
                password,
                password_confirmation: passwordConfirmation
            });

            if (response.success) {
                return { success: true, message: 'Password reset successfully' };
            }

            return {
                success: false,
                message: response.message || 'Failed to reset password'
            };
        } catch (error) {
            return {
                success: false,
                message: error.message || 'Failed to reset password'
            };
        }
    }

    /**
     * Get user profile
     */
    async getProfile() {
        try {
            const response = await api.get('/auth/profile');

            if (response.success) {
                const user = response.data;
                this.setCurrentUser(user);
                return { success: true, user };
            }

            return {
                success: false,
                message: response.message || 'Failed to fetch profile'
            };
        } catch (error) {
            return {
                success: false,
                message: error.message || 'Failed to fetch profile'
            };
        }
    }

    /**
     * Update user profile
     * @param {Object} profileData
     */
    async updateProfile(profileData) {
        try {
            const response = await api.put('/auth/profile', profileData);

            if (response.success) {
                const user = response.data;
                this.setCurrentUser(user);
                return { success: true, user };
            }

            return {
                success: false,
                message: response.message || 'Failed to update profile'
            };
        } catch (error) {
            return {
                success: false,
                message: error.message || 'Failed to update profile'
            };
        }
    }
}

// Export as both default and named export
export default new AuthService();
export { AuthService };
