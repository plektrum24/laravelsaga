/**
 * SAGA TOKO Global Store
 * State management using Alpine.js $persist
 */

// Store initial state and methods
window.SagaStore = {
    // User state
    user: null,
    tenant: null,
    token: null,

    /**
     * Initialize store from localStorage
     */
    init() {
        const userStr = localStorage.getItem('saga_user');
        const tenantStr = localStorage.getItem('saga_tenant');
        const token = localStorage.getItem('saga_token');

        this.user = userStr ? JSON.parse(userStr) : null;
        this.tenant = tenantStr ? JSON.parse(tenantStr) : null;
        this.token = token;

        return this;
    },

    /**
     * Update user state
     */
    setUser(user) {
        this.user = user;
        if (user) {
            localStorage.setItem('saga_user', JSON.stringify(user));
        } else {
            localStorage.removeItem('saga_user');
        }
    },

    /**
     * Update tenant state
     */
    setTenant(tenant) {
        this.tenant = tenant;
        if (tenant) {
            localStorage.setItem('saga_tenant', JSON.stringify(tenant));
        } else {
            localStorage.removeItem('saga_tenant');
        }
    },

    /**
     * Check if user is authenticated
     */
    isAuthenticated() {
        return !!this.token;
    },

    /**
     * Check user role
     */
    hasRole(role) {
        if (!this.user) return false;
        if (Array.isArray(role)) {
            return role.includes(this.user.role);
        }
        return this.user.role === role;
    },

    /**
     * Get user display name
     */
    getUserName() {
        return this.user?.name || 'User';
    },

    /**
     * Get tenant name
     */
    getTenantName() {
        return this.tenant?.name || 'SAGA TOKO';
    },

    /**
     * Get tenant logo URL
     */
    getTenantLogo() {
        return this.tenant?.logo_url || './images/logo/logo-icon.svg';
    },

    /**
     * Clear all state (logout)
     */
    clear() {
        this.user = null;
        this.tenant = null;
        this.token = null;
        localStorage.removeItem('saga_user');
        localStorage.removeItem('saga_tenant');
        localStorage.removeItem('saga_token');
    }
};

// Initialize on load
document.addEventListener('DOMContentLoaded', () => {
    window.SagaStore.init();
});

export default window.SagaStore;
