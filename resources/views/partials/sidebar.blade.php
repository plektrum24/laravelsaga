<!-- ===== Sidebar Start ===== -->
<aside
  :class="($store.sidebar.open || hovered) ? 'translate-x-0 lg:w-[280px]' : '-translate-x-full lg:translate-x-0 lg:w-[80px]'"
  class="sidebar fixed top-0 left-0 z-[99999] flex min-h-full w-[280px] lg:w-[80px] flex-col overflow-y-auto border-r border-gray-200 bg-white duration-300 ease-in-out lg:static dark:border-gray-800 dark:bg-gray-900"
  x-cloak @click.outside="$store.sidebar.open = false" @mouseenter="hovered = true" @mouseleave="hovered = false"
  x-data="{
    currentUser: JSON.parse(localStorage.getItem('saga_user')) || { name: 'Demo User', role: 'tenant_owner', email: 'demo@sagatoko.com' },
    currentTenant: JSON.parse(localStorage.getItem('saga_tenant')) || {},
    openMenu: '',
    hovered: false,
    
    async init() {
         if (!this.currentUser || !this.currentUser.role) {
             await this.fetchUserProfile();
         }
         await this.fetchMenus();
    },

    menus: [],

    async fetchMenus() {
        try {
            const token = localStorage.getItem('saga_token');
            if (!token) return;
            const res = await fetch('/api/user/menus', { headers: { 'Authorization': 'Bearer ' + token } });
            if (res.ok) {
                const data = await res.json();
                if (data.success) {
                    this.menus = data.data;
                }
            }
        } catch (e) { console.error('Menu load error', e); }
    },

    async fetchUserProfile() {
        try {
            const token = localStorage.getItem('saga_token');
            if (!token) return;
            const res = await fetch('/api/user', { headers: { 'Authorization': 'Bearer ' + token } });
            if (res.ok) {
                const data = await res.json();
                if (data && data.role) { 
                    this.currentUser = data;
                    localStorage.setItem('saga_user', JSON.stringify(data));
                }
            }
        } catch (e) { console.error('Profile sync error', e); }
    },

    toggleMenu(menu) { this.openMenu = this.openMenu === menu ? '' : menu; },
    isSuperAdmin() { return this.currentUser?.role === 'super_admin'; },
    isOwner() { return this.currentUser?.role === 'tenant_owner' || this.currentUser?.role === 'owner'; },
    isManager() { return this.currentUser?.role === 'manager'; },
    isCashier() { return this.currentUser?.role === 'cashier'; },
    canAccessInventory() { return this.isOwner() || this.isManager(); },
    canAccessReports() { return this.isOwner() || this.isManager(); },
    canAccessSettings() { return this.isOwner() || this.isManager(); },
    canAccessDebt() { return this.isOwner() || this.isManager(); },
    get isExpanded() { return $store.sidebar.open || this.hovered; }
  }" x-init="init()">
  <!-- Logo -->
  <div class="flex items-center justify-center h-16 border-b border-gray-100 dark:border-gray-800">
    <a href="{{ route('dashboard') }}" class="flex items-center gap-2">
      <!-- Show tenant logo if available, otherwise show default S icon -->
      <img x-show="currentTenant.logo_url" :src="currentTenant.logo_url" alt="Logo"
        class="w-9 h-9 rounded-lg object-contain bg-white dark:bg-gray-800">
      <div x-show="!currentTenant.logo_url" class="w-9 h-9 bg-brand-500 rounded-lg flex items-center justify-center">
        <span class="text-white font-bold text-lg">S</span>
      </div>
      <span x-show="$store.sidebar.open || hovered" class="text-lg font-bold text-gray-800 dark:text-white"
        x-text="currentTenant.name || 'SAGA TOKO'">SAGA TOKO</span>
    </a>
  </div>

  <!-- Menu -->
  <div class="flex flex-col flex-1 overflow-y-auto no-scrollbar py-4 px-3">
    <nav class="space-y-6">

      <!-- ===== SUPER ADMIN MENU ===== -->
      <template x-if="isSuperAdmin()">
        <div class="space-y-4">
          <div x-show="$store.sidebar.open || hovered"
            class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Admin
          </div>

          <ul class="space-y-1">
            <li>
              <a href="{{ route('dashboard') }}" class="sidebar-menu-item"
                :class="page === 'adminDashboard' ? 'sidebar-menu-active' : ''">
                <svg class="sidebar-menu-icon" viewBox="0 0 24 24" fill="currentColor">
                  <path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z" />
                </svg>
                <span x-show="$store.sidebar.open || hovered" class="sidebar-menu-text">Dashboard</span>
              </a>
            </li>
            <li>
              <a href="admin-tenants.html" class="sidebar-menu-item"
                :class="page === 'adminTenants' ? 'sidebar-menu-active' : ''">
                <svg class="sidebar-menu-icon" viewBox="0 0 24 24" fill="currentColor">
                  <path
                    d="M12 7V3H2v18h20V7H12zM6 19H4v-2h2v2zm0-4H4v-2h2v2zm0-4H4V9h2v2zm0-4H4V5h2v2zm4 12H8v-2h2v2zm0-4H8v-2h2v2zm0-4H8V9h2v2zm0-4H8V5h2v2zm10 12h-8v-2h2v-2h-2v-2h2v-2h-2V9h8v10z" />
                </svg>
                <span x-show="$store.sidebar.open || hovered" class="sidebar-menu-text">Tenants</span>
              </a>
            </li>
            <li>
              <a href="admin-users.html" class="sidebar-menu-item"
                :class="page === 'adminUsers' ? 'sidebar-menu-active' : ''">
                <svg class="sidebar-menu-icon" viewBox="0 0 24 24" fill="currentColor">
                  <path
                    d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5z" />
                </svg>
                <span x-show="$store.sidebar.open || hovered" class="sidebar-menu-text">Users</span>
              </a>
            </li>
            <li>
              <a href="admin-reports.html" class="sidebar-menu-item"
                :class="page === 'adminReports' ? 'sidebar-menu-active' : ''">
                <svg class="sidebar-menu-icon" viewBox="0 0 24 24" fill="currentColor">
                  <path
                    d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zM9 17H7v-7h2v7zm4 0h-2V7h2v10zm4 0h-2v-4h2v4z" />
                </svg>
                <span x-show="$store.sidebar.open || hovered" class="sidebar-menu-text">Reports</span>
              </a>
            </li>
            <li>
              <a href="admin-license.html" class="sidebar-menu-item"
                :class="page === 'adminLicense' ? 'sidebar-menu-active' : ''">
                <svg class="sidebar-menu-icon" viewBox="0 0 24 24" fill="currentColor">
                  <path
                    d="M20 6h-2.18c-.11-.31-.26-.59-.44-.86C16.89 3.99 15.6 3.12 14.12 2.6L14.5 1H9.5l.38 1.6C7.5 3.5 6 5.5 6 8c0 1.5.6 2.9 1.6 4h-.1L6 14v6.5l3.5 2.5 3.5-2.5 3.5 2.5V16l-1-2h2c1.1 0 2-.9 2-2V8c0-1.1-.9-2-2-2zm-6.5-1c1.4 0 2.5 1.1 2.5 2.5S14.9 10 13.5 10s-2.5-1.1-2.5-2.5S12.1 5 13.5 5zm5 9H9v-2h9.5v2z" />
                </svg>
                <span x-show="$store.sidebar.open || hovered" class="sidebar-menu-text">License
                  Gen.</span>
              </a>
            </li>
          </ul>
        </div>
      </template>

      <!-- ===== TENANT MENU (DYNAMIC) ===== -->
      <template x-if="!isSuperAdmin()">
        <div class="space-y-6">
          <template x-for="section in menus" :key="section.title">
            <div>
              <div x-show="$store.sidebar.open || hovered"
                class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3" x-text="section.title">
              </div>

              <ul class="space-y-1">
                <template x-for="item in section.items" :key="item.label">
                  <li>
                    <!-- Single Link -->
                    <template x-if="!item.submenu">
                      <a :href="item.route ? `/${item.route.replace('index', '').replace('.', '/')}` : '#'"
                        class="sidebar-menu-item"
                        :class="window.location.href.includes(item.route?.replace('.index', '')) ? 'sidebar-menu-active' : ''">
                        <svg class="sidebar-menu-icon" viewBox="0 0 24 24" fill="currentColor" x-html="item.icon"></svg>
                        <span x-show="$store.sidebar.open || hovered" class="sidebar-menu-text"
                          x-text="item.label"></span>
                      </a>
                    </template>

                    <!-- Submenu dropdown -->
                    <template x-if="item.submenu">
                      <div>
                        <button @click="toggleMenu(item.id)" class="sidebar-menu-item w-full"
                          :class="openMenu === item.id ? 'sidebar-menu-active' : ''">
                          <svg class="sidebar-menu-icon" viewBox="0 0 24 24" fill="currentColor"
                            x-html="item.icon"></svg>
                          <span x-show="$store.sidebar.open || hovered" class="sidebar-menu-text flex-1 text-left"
                            x-text="item.label"></span>
                          <svg x-show="$store.sidebar.open || hovered" class="w-4 h-4 transition-transform"
                            :class="openMenu === item.id ? 'rotate-180' : ''" fill="currentColor" viewBox="0 0 20 20">
                            <path
                              d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" />
                          </svg>
                        </button>
                        <ul x-show="openMenu === item.id && ($store.sidebar.open || hovered)" class="sidebar-submenu">
                          <template x-for="sub in item.submenu" :key="sub.label">
                            <li>
                              <a :href="`/${sub.route.replace('inventory.', 'inventory/').replace(/\./g, '/')}`"
                                class="sidebar-submenu-item" x-text="sub.label">
                              </a>
                            </li>
                          </template>
                        </ul>
                      </div>
                    </template>
                  </li>
                </template>
              </ul>
            </div>
          </template>
        </div>
      </template>
    </nav>
  </div>

  <!-- User Info Footer -->
  <div class="border-t border-gray-100 dark:border-gray-800 p-3">
    <a href="{{ route('profile') }}" class="sidebar-menu-item" :class="page === 'profile' ? 'sidebar-menu-active' : ''">
      <div class="w-8 h-8 bg-brand-100 rounded-full flex items-center justify-center dark:bg-brand-900/30">
        <span class="text-brand-600 font-semibold text-sm" x-text="currentUser.name?.charAt(0) || 'U'">U</span>
      </div>
      <div x-show="$store.sidebar.open || hovered" class="flex-1 min-w-0">
        <p class="text-sm font-medium text-gray-700 truncate dark:text-gray-200" x-text="currentUser.name">User</p>
        <p class="text-xs text-gray-400 truncate" x-text="currentUser.role?.replace('_', ' ')">Role</p>
      </div>
    </a>
    <button
      @click="localStorage.removeItem('saga_token'); localStorage.removeItem('saga_user'); localStorage.removeItem('saga_tenant'); localStorage.removeItem('saga_selected_branch'); window.location.href = '{{ route('dashboard') }}'"
      class="sidebar-menu-item w-full mt-1 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20">
      <svg class="sidebar-menu-icon text-red-500" viewBox="0 0 24 24" fill="currentColor">
        <path
          d="M17 7l-1.41 1.41L18.17 11H8v2h10.17l-2.58 2.58L17 17l5-5zM4 5h8V3H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h8v-2H4V5z" />
      </svg>
      <span x-show="$store.sidebar.open || hovered" class="sidebar-menu-text">Logout</span>
    </button>
  </div>
</aside>
<!-- ===== Sidebar End ===== -->