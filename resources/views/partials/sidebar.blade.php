<!-- ===== Sidebar Start ===== -->
<aside
  :class="($store.sidebar.open || hovered) ? 'translate-x-0 lg:w-[280px]' : '-translate-x-full lg:translate-x-0 lg:w-[80px]'"
  class="sidebar fixed top-0 left-0 z-[99999] flex h-screen w-[280px] lg:w-[80px] flex-col overflow-y-hidden border-r border-gray-200 bg-white duration-300 ease-in-out lg:static dark:border-gray-800 dark:bg-gray-900"
  @click.outside="$store.sidebar.open = false" @mouseenter="hovered = true" @mouseleave="hovered = false" x-data="{
    currentUser: JSON.parse(localStorage.getItem('saga_user')) || { name: 'Demo User', role: 'tenant_owner', email: 'demo@sagatoko.com' },
    currentTenant: JSON.parse(localStorage.getItem('saga_tenant')) || {},
    openMenu: '',
    hovered: false,
    toggleMenu(menu) { this.openMenu = this.openMenu === menu ? '' : menu; },
    isSuperAdmin() { return this.currentUser.role === 'super_admin'; },
    isOwner() { return this.currentUser.role === 'tenant_owner'; },
    isManager() { return this.currentUser.role === 'manager'; },
    isCashier() { return this.currentUser.role === 'cashier'; },
    canAccessInventory() { return this.isOwner() || this.isManager(); },
    canAccessReports() { return this.isOwner() || this.isManager(); },
    canAccessSettings() { return this.isOwner() || this.isManager(); },
    canAccessDebt() { return this.isOwner() || this.isManager(); },
    get isExpanded() { return $store.sidebar.open || this.hovered; }
  }">
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

      <!-- ===== TENANT MENU ===== -->
      <template x-if="!isSuperAdmin()">
        <div class="space-y-6">

          <!-- MENU Section -->
          <div>
            <div x-show="$store.sidebar.open || hovered"
              class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">
              Menu</div>
            <ul class="space-y-1">

              <!-- Dashboard -->
              <li>
                <a href="{{ route('dashboard') }}" class="sidebar-menu-item"
                  :class="page === 'dashboard' ? 'sidebar-menu-active' : ''">
                  <svg class="sidebar-menu-icon" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z" />
                  </svg>
                  <span x-show="$store.sidebar.open || hovered" class="sidebar-menu-text">Dashboard</span>
                </a>
              </li>

              <!-- POS / Cashier -->
              <li>
                <a href="{{ route('pos.index') }}" class="sidebar-menu-item"
                  :class="page === 'pos' ? 'sidebar-menu-active' : ''">
                  <svg class="sidebar-menu-icon" viewBox="0 0 24 24" fill="currentColor">
                    <path
                      d="M7 18c-1.1 0-1.99.9-1.99 2S5.9 22 7 22s2-.9 2-2-.9-2-2-2zM1 2v2h2l3.6 7.59-1.35 2.45c-.16.28-.25.61-.25.96 0 1.1.9 2 2 2h12v-2H7.42c-.14 0-.25-.11-.25-.25l.03-.12.9-1.63h7.45c.75 0 1.41-.41 1.75-1.03l3.58-6.49c.08-.14.12-.31.12-.48 0-.55-.45-1-1-1H5.21l-.94-2H1zm16 16c-1.1 0-1.99.9-1.99 2s.89 2 1.99 2 2-.9 2-2-.9-2-2-2z" />
                  </svg>
                  <span x-show="$store.sidebar.open || hovered" class="sidebar-menu-text">POS / Kasir</span>
                </a>
              </li>

              <!-- Inventory -->
              <template x-if="canAccessInventory()">
                <li>
                  <button @click="toggleMenu('inventory')" class="sidebar-menu-item w-full"
                    :class="openMenu === 'inventory' ? 'sidebar-menu-active' : ''">
                    <svg class="sidebar-menu-icon" viewBox="0 0 24 24" fill="currentColor">
                      <path
                        d="M20 3H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H4V5h16v14zM6 7h12v2H6zm0 4h12v2H6zm0 4h8v2H6z" />
                    </svg>
                    <span x-show="$store.sidebar.open || hovered"
                      class="sidebar-menu-text flex-1 text-left">Inventory</span>
                    <svg x-show="$store.sidebar.open || hovered" class="w-4 h-4 transition-transform"
                      :class="openMenu === 'inventory' ? 'rotate-180' : ''" fill="currentColor" viewBox="0 0 20 20">
                      <path
                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" />
                    </svg>
                  </button>
                  <ul x-show="openMenu === 'inventory' && ($store.sidebar.open || hovered)" class="sidebar-submenu">
                    <li><a href="{{ route('inventory.index') }}" class="sidebar-submenu-item"
                        :class="page === 'inventory' ? 'sidebar-submenu-active' : ''">Items</a>
                    </li>
                    <li><a href="{{ route('inventory.categories') }}" class="sidebar-submenu-item"
                        :class="page === 'categories' ? 'sidebar-submenu-active' : ''">Categories</a>
                    </li>
                    <li><a href="{{ route('inventory.stock-management') }}" class="sidebar-submenu-item"
                        :class="page === 'stockManagement' ? 'sidebar-submenu-active' : ''">Stock
                        Management</a></li>
                    <li><a href="{{ route('inventory.deadstock') }}" class="sidebar-submenu-item"
                        :class="page === 'deadstock' ? 'sidebar-submenu-active' : ''">Deadstock</a>
                    </li>
                    <li><a href="{{ route('inventory.transfer') }}" class="sidebar-submenu-item"
                        :class="page === 'transferItem' ? 'sidebar-submenu-active' : ''">Transfer
                        Item</a></li>
                  </ul>
                </li>
              </template>

              <!-- Item Receiving -->
              <template x-if="canAccessInventory()">
                <li>
                  <button @click="toggleMenu('receiving')" class="sidebar-menu-item w-full"
                    :class="openMenu === 'receiving' ? 'sidebar-menu-active' : ''">
                    <svg class="sidebar-menu-icon" viewBox="0 0 24 24" fill="currentColor">
                      <path
                        d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-2 10h-4v4h-2v-4H7v-2h4V7h2v4h4v2z" />
                    </svg>
                    <span x-show="$store.sidebar.open || hovered" class="sidebar-menu-text flex-1 text-left">Item
                      Receiving</span>
                    <svg x-show="$store.sidebar.open || hovered" class="w-4 h-4 transition-transform"
                      :class="openMenu === 'receiving' ? 'rotate-180' : ''" fill="currentColor" viewBox="0 0 20 20">
                      <path
                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" />
                    </svg>
                  </button>
                  <ul x-show="openMenu === 'receiving' && ($store.sidebar.open || hovered)" class="sidebar-submenu">
                    <li><a href="{{ route('inventory.receiving.goods-in') }}" class="sidebar-submenu-item"
                        :class="page === 'goodsIn' ? 'sidebar-submenu-active' : ''">Goods In</a>
                    </li>
                    <li><a href="{{ route('inventory.receiving.supplier-returns') }}" class="sidebar-submenu-item"
                        :class="page === 'supplierReturns' ? 'sidebar-submenu-active' : ''">Return
                        Supplier</a></li>
                    <li><a href="{{ route('inventory.receiving.customer-returns') }}" class="sidebar-submenu-item"
                        :class="page === 'returns' ? 'sidebar-submenu-active' : ''">Returns
                        (Customer)</a></li>
                  </ul>
                </li>
              </template>

              <!-- Sales Force -->
              <template x-if="canAccessInventory()">
                <li>
                  <button @click="toggleMenu('salesforce')" class="sidebar-menu-item w-full"
                    :class="openMenu === 'salesforce' ? 'sidebar-menu-active' : ''">
                    <svg class="sidebar-menu-icon" viewBox="0 0 24 24" fill="currentColor">
                      <path
                        d="M15 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm-9-2V7H4v3H1v2h3v3h2v-3h3v-2H6zm9 4c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z" />
                    </svg>
                    <span x-show="$store.sidebar.open || hovered" class="sidebar-menu-text flex-1 text-left">Sales Force
                      (Canvas)</span>
                    <svg x-show="$store.sidebar.open || hovered" class="w-4 h-4 transition-transform"
                      :class="openMenu === 'salesforce' ? 'rotate-180' : ''" fill="currentColor" viewBox="0 0 20 20">
                      <path
                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" />
                    </svg>
                  </button>
                  <ul x-show="openMenu === 'salesforce' && ($store.sidebar.open || hovered)" class="sidebar-submenu">
                    <li><a href="salesmen.html" class="sidebar-submenu-item"
                        :class="page === 'salesmen' ? 'sidebar-submenu-active' : ''">Salesmen Data</a></li>
                    <li><a href="sales-orders.html" class="sidebar-submenu-item"
                        :class="page === 'salesOrders' ? 'sidebar-submenu-active' : ''">Sales Orders</a></li>
                    <li><a href="visit-plans.html" class="sidebar-submenu-item"
                        :class="page === 'visitPlans' ? 'sidebar-submenu-active' : ''">Visit Plans</a></li>
                  </ul>
                </li>
              </template>

              <!-- Suppliers & Customers -->
              <template x-if="canAccessInventory()">
                <li>
                  <button @click="toggleMenu('partners')" class="sidebar-menu-item w-full"
                    :class="openMenu === 'partners' ? 'sidebar-menu-active' : ''">
                    <svg class="sidebar-menu-icon" viewBox="0 0 24 24" fill="currentColor">
                      <path
                        d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z" />
                    </svg>
                    <span x-show="$store.sidebar.open || hovered" class="sidebar-menu-text flex-1 text-left">Suppliers &
                      Customers</span>
                    <svg x-show="$store.sidebar.open || hovered" class="w-4 h-4 transition-transform"
                      :class="openMenu === 'partners' ? 'rotate-180' : ''" fill="currentColor" viewBox="0 0 20 20">
                      <path
                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" />
                    </svg>
                  </button>
                  <ul x-show="openMenu === 'partners' && ($store.sidebar.open || hovered)" class="sidebar-submenu">
                    <li><a href="{{ route('inventory.suppliers') }}" class="sidebar-submenu-item"
                        :class="page === 'suppliers' ? 'sidebar-submenu-active' : ''">Suppliers</a></li>
                    <li><a href="{{ route('customers.index') }}" class="sidebar-submenu-item"
                        :class="page === 'customers' ? 'sidebar-submenu-active' : ''">Customers</a></li>
                  </ul>
                </li>
              </template>

              <!-- Debt & Receivables -->
              <template x-if="canAccessDebt()">
                <li>
                  <button @click="toggleMenu('debt')" class="sidebar-menu-item w-full"
                    :class="openMenu === 'debt' ? 'sidebar-menu-active' : ''">
                    <svg class="sidebar-menu-icon" viewBox="0 0 24 24" fill="currentColor">
                      <path
                        d="M11.8 10.9c-2.27-.59-3-1.2-3-2.15 0-1.09 1.01-1.85 2.7-1.85 1.78 0 2.44.85 2.5 2.1h2.21c-.07-1.72-1.12-3.3-3.21-3.81V3h-3v2.16c-1.94.42-3.5 1.68-3.5 3.61 0 2.31 1.91 3.46 4.7 4.13 2.5.6 3 1.48 3 2.41 0 .69-.49 1.79-2.7 1.79-2.06 0-2.87-.92-2.98-2.1h-2.2c.12 2.19 1.76 3.42 3.68 3.83V21h3v-2.15c1.95-.37 3.5-1.5 3.5-3.55 0-2.84-2.43-3.81-4.7-4.4z" />
                    </svg>
                    <span x-show="$store.sidebar.open || hovered" class="sidebar-menu-text flex-1 text-left">Debt &
                      Receivables</span>
                    <svg x-show="$store.sidebar.open || hovered" class="w-4 h-4 transition-transform"
                      :class="openMenu === 'debt' ? 'rotate-180' : ''" fill="currentColor" viewBox="0 0 20 20">
                      <path
                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" />
                    </svg>
                  </button>
                  <ul x-show="openMenu === 'debt' && ($store.sidebar.open || hovered)" class="sidebar-submenu">
                    <li><a href="{{ route('finance.debts') }}" class="sidebar-submenu-item"
                        :class="page === 'supplierDebts' ? 'sidebar-submenu-active' : ''">Supplier Debts</a></li>
                    <li><a href="{{ route('finance.receivables') }}" class="sidebar-submenu-item"
                        :class="page === 'receivables' ? 'sidebar-submenu-active' : ''">Receivables</a></li>
                  </ul>
                </li>
              </template>
            </ul>
          </div>

          <!-- OTHERS Section -->
          <div>
            <div x-show="$store.sidebar.open || hovered"
              class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">
              Others</div>
            <ul class="space-y-1">

              <!-- Payroll (New) -->
              <template x-if="isOwner() || isManager()">
                <li>
                  <a href="{{ route('payroll.index') }}" class="sidebar-menu-item"
                    :class="page === 'payroll' ? 'sidebar-menu-active' : ''">
                    <svg class="sidebar-menu-icon" viewBox="0 0 24 24" fill="currentColor">
                      <path
                        d="M11.8 10.9c-2.27-.59-3-1.2-3-2.15 0-1.09 1.01-1.85 2.7-1.85 1.78 0 2.44.85 2.5 2.1h2.21c-.07-1.72-1.12-3.3-3.21-3.81V3h-3v2.16c-1.94.42-3.5 1.68-3.5 3.61 0 2.31 1.91 3.46 4.7 4.13 2.5.6 3 1.48 3 2.41 0 .69-.49 1.79-2.7 1.79-2.06 0-2.87-.92-2.98-2.1h-2.2c.12 2.19 1.76 3.42 3.68 3.83V21h3v-2.15c1.95-.37 3.5-1.5 3.5-3.55 0-2.84-2.43-3.81-4.7-4.4z" />
                    </svg>
                    <span x-show="$store.sidebar.open || hovered" class="sidebar-menu-text">Payroll (Gaji)</span>
                  </a>
                </li>
              </template>

              <!-- User Management (Owner only) -->
              <template x-if="isOwner()">
                <li>
                  <a href="{{ route('users.index') }}" class="sidebar-menu-item"
                    :class="page === 'users' ? 'sidebar-menu-active' : ''">
                    <svg class="sidebar-menu-icon" viewBox="0 0 24 24" fill="currentColor">
                      <path
                        d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z" />
                    </svg>
                    <span x-show="$store.sidebar.open || hovered" class="sidebar-menu-text">User Management</span>
                  </a>
                </li>
              </template>

              <!-- Branch Management (Owner only) -->
              <template x-if="isOwner()">
                <li>
                  <a href="{{ route('branches.index') }}" class="sidebar-menu-item"
                    :class="page === 'branches' ? 'sidebar-menu-active' : ''">
                    <svg class="sidebar-menu-icon" viewBox="0 0 24 24" fill="currentColor">
                      <path
                        d="M17 1H7C5.9 1 5 1.9 5 3v18c0 1.1.9 2 2 2h10c1.1 0 2-.9 2-2V3c0-1.1-.9-2-2-2zm0 18H7V5h10v14zm-6-8h2v-2h-2v2zm0 4h2v-2h-2v2z" />
                    </svg>
                    <span x-show="$store.sidebar.open || hovered" class="sidebar-menu-text">Branches</span>
                  </a>
                </li>
              </template>

              <!-- Settings -->
              <template x-if="canAccessSettings()">
                <li>
                  <button @click="toggleMenu('settings')" class="sidebar-menu-item w-full"
                    :class="openMenu === 'settings' ? 'sidebar-menu-active' : ''">
                    <svg class="sidebar-menu-icon" viewBox="0 0 24 24" fill="currentColor">
                      <path
                        d="M19.14 12.94c.04-.31.06-.63.06-.94 0-.31-.02-.63-.06-.94l2.03-1.58c.18-.14.23-.41.12-.61l-1.92-3.32c-.12-.22-.37-.29-.59-.22l-2.39.96c-.5-.38-1.03-.7-1.62-.94l-.36-2.54c-.04-.24-.24-.41-.48-.41h-3.84c-.24 0-.43.17-.47.41l-.36 2.54c-.59.24-1.13.57-1.62.94l-2.39-.96c-.22-.08-.47 0-.59.22L2.74 8.87c-.12.21-.08.47.12.61l2.03 1.58c-.04.31-.06.63-.06.94s.02.63.06.94l-2.03 1.58c-.18.14-.23.41-.12.61l1.92 3.32c.12.22.37.29.59.22l2.39-.96c.5.38 1.03.7 1.62.94l.36 2.54c.05.24.24.41.48.41h3.84c.24 0 .44-.17.47-.41l.36-2.54c.59-.24 1.13-.56 1.62-.94l2.39.96c.22.08.47 0 .59-.22l1.92-3.32c.12-.22.07-.47-.12-.61l-2.01-1.58zM12 15.6c-1.98 0-3.6-1.62-3.6-3.6s1.62-3.6 3.6-3.6 3.6 1.62 3.6 3.6-1.62 3.6-3.6 3.6z" />
                    </svg>
                    <span x-show="$store.sidebar.open || hovered"
                      class="sidebar-menu-text flex-1 text-left">Settings</span>
                    <svg x-show="$store.sidebar.open || hovered" class="w-4 h-4 transition-transform"
                      :class="openMenu === 'settings' ? 'rotate-180' : ''" fill="currentColor" viewBox="0 0 20 20">
                      <path
                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" />
                    </svg>
                  </button>
                  <ul x-show="openMenu === 'settings' && ($store.sidebar.open || hovered)" class="sidebar-submenu">
                    <template x-if="isOwner()">
                      <li><a href="{{ route('settings.index') }}" class="sidebar-submenu-item"
                          :class="page === 'settingsGeneral' ? 'sidebar-submenu-active' : ''">Store Settings</a></li>
                    </template>
                    <li><a href="{{ route('settings.index') }}?tab=backup" class="sidebar-submenu-item"
                        :class="page === 'settings' ? 'sidebar-submenu-active' : ''">Backup & Export</a></li>
                  </ul>
                </li>
              </template>

              <!-- Reports -->
              <template x-if="canAccessReports()">
                <li>
                  <a href="{{ route('reports.index') }}" class="sidebar-menu-item"
                    :class="page === 'reports' ? 'sidebar-menu-active' : ''">
                    <svg class="sidebar-menu-icon" viewBox="0 0 24 24" fill="currentColor">
                      <path
                        d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zM9 17H7v-7h2v7zm4 0h-2V7h2v10zm4 0h-2v-4h2v4z" />
                    </svg>
                    <span x-show="$store.sidebar.open || hovered" class="sidebar-menu-text">Reports</span>
                  </a>
                </li>
              </template>
            </ul>
          </div>
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