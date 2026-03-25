<!-- ===== Sidebar Start ===== -->
<aside
  :class="($store.sidebar.open || hovered) ? 'translate-x-0 lg:w-[280px]' : '-translate-x-full lg:translate-x-0 lg:w-[80px]'"
  class="sidebar fixed top-0 left-0 z-[50] flex min-h-screen w-[280px] lg:w-[80px] flex-col overflow-y-auto border-r border-gray-200 bg-white duration-300 ease-in-out lg:static dark:border-gray-800 dark:bg-gray-900"
  x-cloak @click.outside="$store.sidebar.open = false" @mouseenter="hovered = true" @mouseleave="hovered = false"
  x-data="{
    currentUser: JSON.parse(localStorage.getItem('saga_user')) || { name: 'User', role: 'tenant_owner' },
    currentTenant: JSON.parse(localStorage.getItem('saga_tenant')) || { name: 'SAGA POS' },
    openMenu: '',
    hovered: false,
    
    toggleMenu(menu) { 
      this.openMenu = this.openMenu === menu ? '' : menu; 
    },
    
    isSuperAdmin() { 
      return this.currentUser?.role === 'super_admin'; 
    }
  }">
  
  <!-- Logo Section -->
  <div class="flex items-center justify-center h-16 border-b border-gray-100 dark:border-gray-800">
    <a :href="isSuperAdmin() ? '{{ route('admin.dashboard') }}' : '{{ route('dashboard') }}'" class="flex items-center gap-2">
      <!-- Default S Logo -->
      <div class="w-9 h-9 bg-brand-500 rounded-lg flex items-center justify-center">
        <span class="text-white font-bold text-lg">S</span>
      </div>
      <!-- Tenant Name -->
      <span x-show="$store.sidebar.open || hovered" class="text-lg font-bold text-gray-800 dark:text-white truncate"
        x-text="currentTenant.name || 'SAGA POS'">
        SAGA POS
      </span>
    </a>
  </div>

  <!-- Menu Section -->
  <div class="flex flex-col flex-1 overflow-y-auto py-4 px-3">
    <nav class="space-y-6">

      <!-- ===== SUPER ADMIN MENU ===== -->
      <template x-if="isSuperAdmin()">
        <div class="space-y-4">
          <div x-show="$store.sidebar.open || hovered" class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">
            Admin
          </div>
          <ul class="space-y-1">
            <li>
              <a href="{{ route('admin.dashboard') }}" class="sidebar-menu-item">
                <svg class="sidebar-menu-icon" viewBox="0 0 24 24" fill="currentColor">
                  <path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/>
                </svg>
                <span x-show="$store.sidebar.open || hovered" class="sidebar-menu-text">Dashboard</span>
              </a>
            </li>
            <li>
              <a href="{{ route('admin.tenants.index') }}" class="sidebar-menu-item">
                <svg class="sidebar-menu-icon" viewBox="0 0 24 24" fill="currentColor">
                  <path d="M12 7V3H2v18h20V7H12zM6 19H4v-2h2v2zm0-4H4v-2h2v2zm0-4H4V9h2v2zm0-4H4V5h2v2zm4 12H8v-2h2v2zm0-4H8v-2h2v2zm0-4H8V9h2v2zm0-4H8V5h2v2zm10 12h-8v-2h2v-2h-2v-2h2v-2h-2V9h8v10z"/>
                </svg>
                <span x-show="$store.sidebar.open || hovered" class="sidebar-menu-text">Tenants</span>
              </a>
            </li>
          </ul>
        </div>
      </template>

      <!-- ===== TENANT MENU (Default) ===== -->
      <template x-if="!isSuperAdmin()">
        <div class="space-y-1">
          
          <!-- Dashboard -->
          <a href="{{ route('dashboard') }}" class="sidebar-menu-item">
            <svg class="sidebar-menu-icon" viewBox="0 0 24 24" fill="currentColor">
              <path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/>
            </svg>
            <span x-show="$store.sidebar.open || hovered" class="sidebar-menu-text">Dashboard</span>
          </a>

          <!-- POS System -->
          <div>
            <button @click="toggleMenu('pos')" class="sidebar-menu-item w-full">
              <svg class="sidebar-menu-icon" viewBox="0 0 24 24" fill="currentColor">
                <path d="M7 18c-1.1 0-1.99.9-1.99 2S5.9 22 7 22s2-.9 2-2-.9-2-2-2zM1 2v2h2l3.6 7.59-1.35 2.45c-.16.28-.25.61-.25.96 0 1.1.9 2 2 2h12v-2H7.42c-.14 0-.25-.11-.25-.25l.03-.12.9-1.63h7.45c.75 0 1.41-.41 1.75-1.03l3.58-6.49c.08-.14.12-.31.12-.48 0-.55-.45-1-1-1H5.21l-.94-2H1zm16 16c-1.1 0-1.99.9-1.99 2s.89 2 1.99 2 2-.9 2-2-.9-2-2-2z"/>
              </svg>
              <span x-show="$store.sidebar.open || hovered" class="sidebar-menu-text flex-1 text-left">POS System</span>
              <svg x-show="$store.sidebar.open || hovered" :class="openMenu === 'pos' ? 'rotate-180' : ''" class="w-4 h-4 transition-transform" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
              </svg>
            </button>
            <ul x-show="openMenu === 'pos' && ($store.sidebar.open || hovered)" class="sidebar-submenu">
              <li><a href="{{ route('pos.index') }}" class="sidebar-submenu-item">Kasir (APP)</a></li>
              <li><a href="{{ route('pos.history') }}" class="sidebar-submenu-item">Riwayat Transaksi</a></li>
            </ul>
          </div>

          <!-- Item Receiving -->
          <div>
            <button @click="toggleMenu('receiving')" class="sidebar-menu-item w-full">
              <svg class="sidebar-menu-icon" viewBox="0 0 24 24" fill="currentColor">
                <path d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4m0 0c0 1.657 1.343 3 3 3h10c1.657 0 3-1.343 3-3m0 0V6"/>
              </svg>
              <span x-show="$store.sidebar.open || hovered" class="sidebar-menu-text flex-1 text-left">Item Receiving</span>
              <svg x-show="$store.sidebar.open || hovered" :class="openMenu === 'receiving' ? 'rotate-180' : ''" class="w-4 h-4 transition-transform" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
              </svg>
            </button>
            <ul x-show="openMenu === 'receiving' && ($store.sidebar.open || hovered)" class="sidebar-submenu">
              <li><a href="{{ route('inventory.receiving.index') }}" class="sidebar-submenu-item">Goods In</a></li>
              <li><a href="{{ route('inventory.receiving.history') }}" class="sidebar-submenu-item">Receiving History</a></li>
              <li><a href="{{ route('inventory.receiving.supplier-returns') }}" class="sidebar-submenu-item">Supplier Returns</a></li>
            </ul>
          </div>

          <!-- Inventory -->
          <div>
            <button @click="toggleMenu('inventory')" class="sidebar-menu-item w-full">
              <svg class="sidebar-menu-icon" viewBox="0 0 24 24" fill="currentColor">
                <path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
              </svg>
              <span x-show="$store.sidebar.open || hovered" class="sidebar-menu-text flex-1 text-left">Inventory</span>
              <svg x-show="$store.sidebar.open || hovered" :class="openMenu === 'inventory' ? 'rotate-180' : ''" class="w-4 h-4 transition-transform" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
              </svg>
            </button>
            <ul x-show="openMenu === 'inventory' && ($store.sidebar.open || hovered)" class="sidebar-submenu">
              <li><a href="{{ route('inventory.index') }}" class="sidebar-submenu-item">Current Stock</a></li>
              <li><a href="{{ route('inventory.stock-management') }}" class="sidebar-submenu-item">Stock Management</a></li>
              <li><a href="{{ route('inventory.stock-transfer') }}" class="sidebar-submenu-item">Stock Transfer</a></li>
              <li><a href="{{ route('inventory.movements') }}" class="sidebar-submenu-item">Stock Movements</a></li>
            </ul>
          </div>

          <!-- Partners -->
          <div>
            <button @click="toggleMenu('partners')" class="sidebar-menu-item w-full">
              <svg class="sidebar-menu-icon" viewBox="0 0 24 24" fill="currentColor">
                <path d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
              </svg>
              <span x-show="$store.sidebar.open || hovered" class="sidebar-menu-text flex-1 text-left">Partners</span>
              <svg x-show="$store.sidebar.open || hovered" :class="openMenu === 'partners' ? 'rotate-180' : ''" class="w-4 h-4 transition-transform" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
              </svg>
            </button>
            <ul x-show="openMenu === 'partners' && ($store.sidebar.open || hovered)" class="sidebar-submenu">
              <li><a href="{{ route('inventory.suppliers') }}" class="sidebar-submenu-item">Suppliers</a></li>
              <li><a href="{{ route('customers.index') }}" class="sidebar-submenu-item">Customers</a></li>
            </ul>
          </div>

          <!-- Finance -->
          <div>
            <button @click="toggleMenu('finance')" class="sidebar-menu-item w-full">
              <svg class="sidebar-menu-icon" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
              <span x-show="$store.sidebar.open || hovered" class="sidebar-menu-text flex-1 text-left">Finance</span>
              <svg x-show="$store.sidebar.open || hovered" :class="openMenu === 'finance' ? 'rotate-180' : ''" class="w-4 h-4 transition-transform" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
              </svg>
            </button>
            <ul x-show="openMenu === 'finance' && ($store.sidebar.open || hovered)" class="sidebar-submenu">
              <li><a href="{{ route('finance.debts') }}" class="sidebar-submenu-item">Supplier Debts</a></li>
              <li><a href="{{ route('finance.receivables') }}" class="sidebar-submenu-item">Customer Receivables</a></li>
            </ul>
          </div>

          <!-- Settings -->
          <div>
            <button @click="toggleMenu('settings')" class="sidebar-menu-item w-full">
              <svg class="sidebar-menu-icon" viewBox="0 0 24 24" fill="currentColor">
                <path d="M19.14 12.94c.04-.31.06-.63.06-.94 0-.31-.02-.63-.06-.94l2.03-1.58c.18-.14.23-.41.12-.61l-1.92-3.32c-.12-.22-.37-.29-.59-.22l-2.39.96c-.5-.38-1.03-.7-1.62-.94l-.36-2.54c-.04-.24-.24-.41-.48-.41h-3.84c-.24 0-.43.17-.47.41l-.36 2.54c-.59.24-1.13.57-1.62.94l-2.39-.96c-.22-.08-.47 0 .59.22L2.74 8.87c-.12.21-.08.47.12.61l2.03 1.58c-.04.31-.06.63-.06.94s.02.63.06.94l-2.03 1.58c-.18.14-.23.41-.12.61l1.92 3.32c.12.22.37.29.59.22l2.39-.96c.5.38 1.03.7 1.62.94l.36 2.54c.05.24.24.41.48.41h3.84c.24 0 .44-.17.47-.41l.36-2.54c.59-.24 1.13-.56 1.62-.94l2.39.96c.22.08.47 0 .59-.22l1.92-3.32c.12-.22.07-.47-.12-.61l-2.01-1.58zM12 15.6c-1.98 0-3.6-1.62-3.6-3.6s1.62-3.6 3.6-3.6 3.6 1.62 3.6 3.6-1.62 3.6-3.6 3.6z"/>
              </svg>
              <span x-show="$store.sidebar.open || hovered" class="sidebar-menu-text flex-1 text-left">Settings</span>
              <svg x-show="$store.sidebar.open || hovered" :class="openMenu === 'settings' ? 'rotate-180' : ''" class="w-4 h-4 transition-transform" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
              </svg>
            </button>
            <ul x-show="openMenu === 'settings' && ($store.sidebar.open || hovered)" class="sidebar-submenu">
              <li><a href="{{ route('settings.index') }}" class="sidebar-submenu-item">Store Settings</a></li>
              <li><a href="{{ route('settings.loyalty') }}" class="sidebar-submenu-item">Loyalty Program</a></li>
            </ul>
          </div>

        </div>
      </template>
    </nav>
  </div>

  <!-- User Info Footer -->
  <div class="border-t border-gray-100 dark:border-gray-800 p-3">
    <a href="{{ route('profile') }}" class="sidebar-menu-item">
      <div class="w-8 h-8 bg-brand-100 rounded-full flex items-center justify-center dark:bg-brand-900/30">
        <span class="text-brand-600 font-semibold text-sm" x-text="currentUser.name?.charAt(0) || 'U'">U</span>
      </div>
      <div x-show="$store.sidebar.open || hovered" class="flex-1 min-w-0">
        <p class="text-sm font-medium text-gray-700 truncate dark:text-gray-200" x-text="currentUser.name">User</p>
        <p class="text-xs text-gray-400 truncate" x-text="currentUser.role?.replace('_', ' ')">Role</p>
      </div>
    </a>
    <a href="{{ route('login') }}" 
       @click="localStorage.removeItem('saga_token'); localStorage.removeItem('saga_user'); localStorage.removeItem('saga_tenant');"
       class="sidebar-menu-item w-full mt-1 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20">
      <svg class="sidebar-menu-icon text-red-500" viewBox="0 0 24 24" fill="currentColor">
        <path d="M17 7l-1.41 1.41L18.17 11H8v2h10.17l-2.58 2.58L17 17l5-5zM4 5h8V3H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h8v-2H4V5z"/>
      </svg>
      <span x-show="$store.sidebar.open || hovered" class="sidebar-menu-text">Logout</span>
    </a>
  </div>
</aside>
<!-- ===== Sidebar End ===== -->
