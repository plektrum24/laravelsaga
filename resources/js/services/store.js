/**
 * SAGA POS - Global Application Store
 * Centralized state management for application-wide data
 * Works with Alpine.js $store integration
 */

class StoreService {
    constructor() {
        // Initialize from localStorage
        this.user = null;
        this.tenant = null;
        this.token = null;
        this.branches = [];
        this.selectedBranch = null;

        // UI state
        this.darkMode = false;
        this.sidebarCollapsed = false;

        // Initialize from storage
        this.init();
    }

    /**
     * Initialize store from localStorage
     */
    init() {
        const userStr = localStorage.getItem('user');
        const tenantStr = localStorage.getItem('tenant');
        const token = localStorage.getItem('auth_token');
        const darkMode = localStorage.getItem('darkMode');

        this.user = userStr ? JSON.parse(userStr) : null;
        this.tenant = tenantStr ? JSON.parse(tenantStr) : null;
        this.token = token || null;
        this.darkMode = darkMode ? JSON.parse(darkMode) : false;

        console.log('Store initialized:', {
            hasUser: !!this.user,
            hasTenant: !!this.tenant,
            hasToken: !!this.token
        });

        return this;
    }

    /**
     * Update user state and persist to localStorage
     */
    setUser(user) {
        this.user = user;
        if (user) {
            localStorage.setItem('user', JSON.stringify(user));
        } else {
            localStorage.removeItem('user');
        }
        return this;
    }

    /**
     * Get current user
     */
    getUser() {
        return this.user;
    }

    /**
     * Update tenant state and persist to localStorage
     */
    setTenant(tenant) {
        this.tenant = tenant;
        if (tenant) {
            localStorage.setItem('tenant', JSON.stringify(tenant));
        } else {
            localStorage.removeItem('tenant');
        }
        return this;
    }

    /**
     * Get current tenant
     */
    getTenant() {
        return this.tenant;
    }

    /**
     * Set authentication token
     */
    setToken(token) {
        this.token = token;
        if (token) {
            localStorage.setItem('auth_token', token);
        } else {
            localStorage.removeItem('auth_token');
        }
        return this;
    }

    /**
     * Get authentication token
     */
    getToken() {
        return this.token;
    }

    /**
     * Check if user is authenticated
     */
    isAuthenticated() {
        return !!this.token && !!this.user;
    }

    /**
     * Check user role
     */
    hasRole(roles) {
        if (!this.user) return false;

        if (Array.isArray(roles)) {
            return roles.includes(this.user.role);
        }
        return this.user.role === roles;
    }

    /**
     * Check user permission
     */
    hasPermission(permission) {
        if (!this.user) return false;
        return this.user.permissions && this.user.permissions.includes(permission);
    }

    /**
     * Check if user is super admin
     */
    isSuperAdmin() {
        return this.hasRole('super_admin');
    }

    /**
     * Check if user is tenant admin
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
     * Get user display name
     */
    getUserName() {
        return this.user?.name || 'User';
    }

    /**
     * Get user email
     */
    getUserEmail() {
        return this.user?.email || '';
    }

    /**
     * Get user avatar
     */
    getUserAvatar() {
        return this.user?.avatar_url || null;
    }

    /**
     * Get tenant name
     */
    getTenantName() {
        return this.tenant?.name || 'SAGA POS';
    }

    /**
     * Get tenant logo
     */
    getTenantLogo() {
        return this.tenant?.logo_url || null;
    }

    /**
     * Set branches list
     */
    setBranches(branches) {
        this.branches = branches;
        return this;
    }

    /**
     * Get branches list
     */
    getBranches() {
        return this.branches;
    }

    /**
     * Set selected branch
     */
    setSelectedBranch(branch) {
        this.selectedBranch = branch;
        if (branch) {
            localStorage.setItem('selected_branch', JSON.stringify(branch));
        }
        return this;
    }

    /**
     * Get selected branch
     */
    getSelectedBranch() {
        return this.selectedBranch;
    }

    /**
     * Toggle dark mode
     */
    toggleDarkMode() {
        this.darkMode = !this.darkMode;
        this.saveDarkMode();
        return this.darkMode;
    }

    /**
     * Set dark mode
     */
    setDarkMode(enabled) {
        this.darkMode = enabled;
        this.saveDarkMode();
        return this;
    }

    /**
     * Get dark mode state
     */
    isDarkMode() {
        return this.darkMode;
    }

    /**
     * Save dark mode to localStorage
     */
    saveDarkMode() {
        localStorage.setItem('darkMode', JSON.stringify(this.darkMode));
        if (this.darkMode) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    }

    /**
     * Toggle sidebar state
     */
    toggleSidebar() {
        this.sidebarCollapsed = !this.sidebarCollapsed;
        return this.sidebarCollapsed;
    }

    /**
     * Set sidebar state
     */
    setSidebarCollapsed(collapsed) {
        this.sidebarCollapsed = collapsed;
        return this;
    }

    /**
     * Is sidebar collapsed
     */
    isSidebarCollapsed() {
        return this.sidebarCollapsed;
    }

    /**
     * Clear all data (logout)
     */
    clear() {
        this.user = null;
        this.tenant = null;
        this.token = null;
        this.branches = [];
        this.selectedBranch = null;

        localStorage.removeItem('user');
        localStorage.removeItem('tenant');
        localStorage.removeItem('auth_token');
        localStorage.removeItem('selected_branch');

        console.log('Store cleared');
        return this;
    }

    /**
     * Get all state
     */
    getState() {
        return {
            user: this.user,
            tenant: this.tenant,
            token: this.token,
            branches: this.branches,
            selectedBranch: this.selectedBranch,
            darkMode: this.darkMode,
            sidebarCollapsed: this.sidebarCollapsed,
            isAuthenticated: this.isAuthenticated(),
            userName: this.getUserName(),
            tenantName: this.getTenantName()
        };
    }

    /**
     * Log current state (for debugging)
     */
    logState() {
        console.log('=== Store State ===', this.getState());
    }
}

// Create singleton instance
const store = new StoreService();

// Export as both default and named export
export default store;
export { StoreService };

// Also make available globally for use in inline scripts and Alpine.js
if (typeof window !== 'undefined') {
    window.SagaStore = store;
}
