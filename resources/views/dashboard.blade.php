<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport"
        content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <title>Dashboard | SAGA TOKO APP</title>
    <!-- Html5 Qrcode Scanner for Mobile -->
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <style>
        .scanner-active video {
            border-radius: 0.75rem;
        }

        [x-cloak] {
            display: none !important;
        }
    </style>
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/css/dashboard.css'])
    <script>
        function priceCheckerMixin() {
            return {
                showPriceCheckModal: false,
                priceCheckQuery: '',
                priceCheckResult: null,
                searchResults: [],
                isScanning: false,
                html5QrcodeScanner: null,

                openPriceChecker() {
                    this.showPriceCheckModal = true;
                    this.priceCheckQuery = '';
                    this.priceCheckResult = null;
                    this.searchResults = [];
                    this.isScanning = false;
                    this.$nextTick(() => {
                        this.$refs.searchInput.focus();
                    });
                },

                closePriceChecker() {
                    this.showPriceCheckModal = false;
                    this.stopScanner();
                },

                selectProduct(product) {
                    this.priceCheckResult = product;
                    this.searchResults = [];
                },

                async searchProduct(query) {
                    if (!query || query.length < 2) return;

                    this.isLoading = true;
                    this.priceCheckResult = null;
                    this.searchResults = [];
                    console.log('Searching for:', query);

                    try {
                        const token = localStorage.getItem('saga_token');
                        const response = await fetch(`/api/products?search=${encodeURIComponent(query)}&page=1&limit=50`, {
                            headers: { 'Authorization': 'Bearer ' + token }
                        });

                        if (!response.ok) {
                            throw new Error(`API Error: ${response.status}`);
                        }

                        const data = await response.json();
                        console.log('Search Result:', data);

                        if (data.success && data.data && data.data.products && data.data.products.length > 0) {
                            const products = data.data.products;
                            if (products.length === 1) {
                                this.selectProduct(products[0]);
                            } else {
                                this.searchResults = products;
                            }
                        } else {
                            console.warn('No products found');
                            Swal.fire({
                                icon: 'info',
                                title: 'Product Not Found',
                                text: 'No product matched your search.',
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 3000
                            });
                        }
                    } catch (error) {
                        console.error('Search error detail:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Failed to search product: ' + error.message,
                        });
                    } finally {
                        this.isLoading = false;
                    }
                },

                toggleScanner() {
                    if (this.isScanning) {
                        this.stopScanner();
                    } else {
                        this.startScanner();
                    }
                },

                startScanner() {
                    this.isScanning = true;
                    this.$nextTick(() => {
                        const element = document.getElementById("price-check-reader");
                        if (!element) {
                            console.error("Scanner element not found!");
                            Swal.fire('Error', 'Scanner viewport not found.', 'error');
                            this.isScanning = false;
                            return;
                        }

                        if (this.html5QrcodeScanner) {
                            try { this.html5QrcodeScanner.clear(); } catch (e) { }
                        }

                        this.html5QrcodeScanner = new Html5Qrcode("price-check-reader");
                        const config = { fps: 10, qrbox: { width: 250, height: 250 } };

                        this.html5QrcodeScanner.start(
                            { facingMode: "environment" },
                            config,
                            (decodedText, decodedResult) => {
                                console.log(`Scan result: ${decodedText}`);
                                this.priceCheckQuery = decodedText;
                                this.searchProduct(decodedText);
                                this.stopScanner();
                                new Audio('{{ asset("assets/beep.mp3") }}').play().catch(e => { });
                            },
                            (errorMessage) => {
                            }
                        ).catch(err => {
                            console.error("Error starting scanner", err);
                            this.isScanning = false;
                            Swal.fire('Error', 'Could not start camera: ' + err, 'error');
                        });
                    });
                },

                stopScanner() {
                    if (this.html5QrcodeScanner && this.isScanning) {
                        this.html5QrcodeScanner.stop().then(() => {
                            this.html5QrcodeScanner.clear();
                            this.isScanning = false;
                        }).catch(err => {
                            console.error("Failed to stop scanner", err);
                        });
                    } else {
                        this.isScanning = false;
                    }
                }
            };
        }
    </script>

<body x-data="{ 
      page: 'dashboard', 
      loaded: true, 
      darkMode: false, 
      stickyMenu: false, 
      sidebarToggle: false, 
      scrollTop: false,
      ...priceCheckerMixin(), // Mixin for Price Checker Logic
      stats: { today: { orders: 0, sales: 0 }, week: 0, month: 0, lowStockCount: 0, weeklyChart: [] },
      priceLogs: [],
      isLoading: true,
      user: null,
      branches: [],
      selectedBranch: null,
      currentBranchName: '',
      
      async fetchBranches() {
        if (this.user?.role !== 'tenant_owner') return;
        try {
          const token = localStorage.getItem('saga_token');
          const response = await fetch('/api/branches', {
            headers: { 'Authorization': 'Bearer ' + token }
          });
          const data = await response.json();
          if (data.success) {
            this.branches = data.data || [];
          }
        } catch (error) {
          console.error('Branches fetch error:', error);
        }
      },
      
      async fetchDashboard() {
        try {
          const token = localStorage.getItem('saga_token');
          let url = '/api/reports/dashboard';
          
          // Get selected branch from header dropdown (stored in localStorage)
          const selectedBranch = localStorage.getItem('saga_selected_branch');
          
          // Owner uses selected branch, staff uses their assigned branch
          if (this.user?.role === 'tenant_owner' && selectedBranch) {
            url += '?branch_id=' + selectedBranch;
            const branch = this.branches.find(b => b.id == selectedBranch);
            this.currentBranchName = branch ? branch.name : 'Branch Selected';
          } else if (this.user?.branch_id) {
            url += '?branch_id=' + this.user.branch_id;
            this.currentBranchName = this.user.branch_name || 'My Branch';
          } else if (this.user?.role === 'tenant_owner') {
            this.currentBranchName = 'Semua Cabang';
          }
          
          const response = await fetch(url, {
            headers: { 'Authorization': 'Bearer ' + token }
          });
          const data = await response.json();
          if (data.success) {
            this.stats = data.data;
          }
        } catch (error) {
          console.error('Dashboard fetch error:', error);
        } finally {
          this.isLoading = false;
        }
      },
      
      onBranchChange() {
        this.isLoading = true;
        this.fetchDashboard();
      },

      async fetchPriceLogs() {
          try {
              const token = localStorage.getItem('saga_token');
              const response = await fetch('/api/products/reports/price-logs?limit=5', {
                  headers: { 'Authorization': 'Bearer ' + token }
              });
              const data = await response.json();
              if (data.success) {
                  this.priceLogs = data.data;
              }
          } catch (error) {
              console.error('Fetch price logs error:', error);
          }
      },
      
      formatCurrency(amount) {
        return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(amount);
      }
    }" x-init="
      darkMode = JSON.parse(localStorage.getItem('darkMode'));
      $watch('darkMode', value => localStorage.setItem('darkMode', JSON.stringify(value)));
      // if (!localStorage.getItem('saga_token')) { window.location.href = '/signin'; } // Disabled for Dev
      user = JSON.parse(localStorage.getItem('saga_user'));
      user = JSON.parse(localStorage.getItem('saga_user'));
      await fetchBranches();
      fetchDashboard();
      fetchPriceLogs();
    " :class="{'dark bg-gray-900': darkMode === true}">
    <!-- ===== Preloader Start ===== -->
    @include('partials.preloader')
    <!-- ===== Preloader End ===== -->

    <!-- ===== Page Wrapper Start ===== -->
    <div class="flex h-screen overflow-hidden">
        <!-- ===== Sidebar Start ===== -->
        @include('partials.sidebar')
        <!-- ===== Sidebar End ===== -->

        <!-- ===== Content Area Start ===== -->
        <div class="relative flex flex-1 flex-col overflow-y-auto overflow-x-hidden">
            <!-- Small Device Overlay Start -->
            @include('partials.overlay')
            <!-- Small Device Overlay End -->

            <!-- ===== Header Start ===== -->
            @include('partials.header')
            <!-- ===== Header End ===== -->

            <!-- ===== Main Content Start ===== -->
            <main>
                <div class="mx-auto max-w-(--breakpoint-2xl) p-4 md:p-6">
                    <!-- Subscription Expiry Warning Banner -->
                    <div x-data="{ 
                        tenant: JSON.parse(localStorage.getItem('saga_tenant')),
                        get daysLeft() {
                            if (!this.tenant?.subscription?.days_left) return -1;
                            return this.tenant.subscription.days_left;
                        },
                        get showWarning() {
                            return this.daysLeft >= 0 && this.daysLeft <= 7;
                        },
                        get warningColor() {
                            if (this.daysLeft <= 1) return 'bg-red-500';
                            if (this.daysLeft <= 3) return 'bg-orange-500';
                            return 'bg-yellow-500';
                        },
                        formatDate(dateStr) {
                            if (!dateStr) return '-';
                            return new Date(dateStr).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
                        }
                    }" x-show="showWarning" class="mb-4">
                        <div :class="warningColor" class="rounded-xl p-4 text-white flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                                    </path>
                                </svg>
                                <div>
                                    <p class="font-semibold"
                                        x-text="daysLeft === 0 ? '⚠️ Langganan Anda berakhir hari ini!' : '⚠️ Langganan akan berakhir dalam ' + daysLeft + ' hari'">
                                    </p>
                                    <p class="text-sm opacity-90"
                                        x-text="'Berlaku hingga: ' + formatDate(tenant?.subscription?.subscription_end)">
                                    </p>
                                </div>
                            </div>
                            <a href="mailto:admin@sagatoko.com"
                                class="px-4 py-2 bg-white/20 hover:bg-white/30 rounded-lg text-sm font-medium transition-colors">
                                Hubungi Admin
                            </a>
                        </div>
                    </div>
                    <!-- Page Title with Branch Info -->
                    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Dashboard</h1>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                <span x-show="currentBranchName" class="inline-flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                                        </path>
                                    </svg>
                                    <span x-text="currentBranchName"></span>
                                </span>
                                <span x-show="!currentBranchName">Welcome back! Here's your store overview.</span>
                            </p>
                        </div>
                        <!-- Branch selector moved to header -->
                    </div>

                    <!-- Stats Cards -->
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4 mb-6">
                        <!-- Today's Orders -->
                        <div
                            class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Today's Orders</p>
                                    <h3 class="text-2xl font-bold text-gray-800 dark:text-white mt-1"
                                        x-text="stats.today.orders">0</h3>
                                </div>
                                <div
                                    class="flex items-center justify-center w-12 h-12 bg-blue-50 rounded-xl dark:bg-blue-900/20">
                                    <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <!-- Today's Sales -->
                        <div
                            class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Today's Sales</p>
                                    <h3 class="text-2xl font-bold text-gray-800 dark:text-white mt-1"
                                        x-text="formatCurrency(stats.today.sales)">Rp 0</h3>
                                </div>
                                <div
                                    class="flex items-center justify-center w-12 h-12 bg-green-50 rounded-xl dark:bg-green-900/20">
                                    <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                        </path>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <!-- This Week -->
                        <div
                            class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">This Week</p>
                                    <h3 class="text-2xl font-bold text-gray-800 dark:text-white mt-1"
                                        x-text="formatCurrency(stats.week)">Rp 0</h3>
                                </div>
                                <div
                                    class="flex items-center justify-center w-12 h-12 bg-purple-50 rounded-xl dark:bg-purple-900/20">
                                    <svg class="w-6 h-6 text-purple-500" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                                        </path>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <!-- Low Stock Alert -->
                        <div x-show="user?.role !== 'cashier'"
                            class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Low Stock Items</p>
                                    <h3 class="text-2xl font-bold"
                                        :class="stats.lowStockCount > 0 ? 'text-red-500' : 'text-gray-800 dark:text-white'"
                                        x-text="stats.lowStockCount">0</h3>
                                </div>
                                <div class="flex items-center justify-center w-12 h-12 rounded-xl"
                                    :class="stats.lowStockCount > 0 ? 'bg-red-50 dark:bg-red-900/20' : 'bg-gray-50 dark:bg-gray-800'">
                                    <svg class="w-6 h-6"
                                        :class="stats.lowStockCount > 0 ? 'text-red-500' : 'text-gray-400'" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                                        </path>
                                    </svg>
                                </div>
                            </div>
                            <a href="inventory.html?low_stock=true"
                                class="text-sm text-brand-500 hover:underline mt-2 inline-block"
                                x-show="stats.lowStockCount > 0">View Items →</a>
                        </div>
                    </div>

                    <!-- Debt & Receivable Alerts -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6" x-show="user?.role !== 'cashier'">
                        <!-- Supplier Debt Alert -->
                        <a href="supplier-debts.html"
                            class="rounded-2xl border border-gray-200 bg-gradient-to-br from-red-500 to-rose-600 p-5 text-white hover:from-red-600 hover:to-rose-700 transition-all">
                            <div class="flex items-center justify-between mb-3">
                                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z">
                                        </path>
                                    </svg>
                                </div>
                                <div x-show="(stats.debts?.overdueCount || 0) > 0"
                                    class="bg-white/30 px-2 py-1 rounded-full text-xs font-medium">
                                    <span x-text="stats.debts?.overdueCount || 0"></span> Overdue!
                                </div>
                            </div>
                            <p class="text-sm text-white/80">Hutang ke Supplier</p>
                            <p class="text-2xl font-bold mt-1"
                                x-text="formatCurrency(stats.debts?.totalOutstanding || 0)">Rp 0</p>
                            <p class="text-xs text-white/60 mt-2" x-show="(stats.debts?.upcomingCount || 0) > 0"
                                x-text="(stats.debts?.upcomingCount || 0) + ' jatuh tempo minggu ini'"></p>
                        </a>

                        <!-- Customer Receivable Alert -->
                        <a href="receivables.html"
                            class="rounded-2xl border border-gray-200 bg-gradient-to-br from-emerald-500 to-green-600 p-5 text-white hover:from-emerald-600 hover:to-green-700 transition-all">
                            <div class="flex items-center justify-between mb-3">
                                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                        </path>
                                    </svg>
                                </div>
                                <div x-show="(stats.receivables?.overdueCount || 0) > 0"
                                    class="bg-white/30 px-2 py-1 rounded-full text-xs font-medium">
                                    <span x-text="stats.receivables?.overdueCount || 0"></span> Overdue!
                                </div>
                            </div>
                            <p class="text-sm text-white/80">Piutang dari Customer</p>
                            <p class="text-2xl font-bold mt-1"
                                x-text="formatCurrency(stats.receivables?.totalOutstanding || 0)">Rp 0</p>
                            <p class="text-xs text-white/60 mt-2" x-show="(stats.receivables?.upcomingCount || 0) > 0"
                                x-text="(stats.receivables?.upcomingCount || 0) + ' jatuh tempo minggu ini'"></p>
                        </a>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                        <!-- Quick POS Access -->
                        <a href="pos.html"
                            class="group rounded-2xl border border-gray-200 bg-gradient-to-br from-brand-500 to-brand-600 p-6 text-white hover:from-brand-600 hover:to-brand-700 transition-all dark:border-gray-800">
                            <div class="flex items-center gap-4">
                                <div class="flex items-center justify-center w-14 h-14 bg-white/20 rounded-xl">
                                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z">
                                        </path>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold">Open POS</h3>
                                    <p class="text-sm text-white/80">Start selling</p>
                                </div>
                            </div>
                        </a>

                        <!-- Quick Inventory -->
                        <a href="inventory.html" x-show="user?.role !== 'cashier'"
                            class="group rounded-2xl border border-gray-200 bg-white p-6 hover:border-brand-300 hover:shadow-lg transition-all dark:border-gray-800 dark:bg-white/[0.03] dark:hover:border-brand-800">
                            <div class="flex items-center gap-4">
                                <div
                                    class="flex items-center justify-center w-14 h-14 bg-orange-50 rounded-xl dark:bg-orange-900/20">
                                    <svg class="w-7 h-7 text-orange-500" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Inventory</h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Manage products</p>
                                </div>
                            </div>
                        </a>

                        <!-- Quick Reports -->
                        <a href="reports.html" x-show="user?.role !== 'cashier'"
                            class="group rounded-2xl border border-gray-200 bg-white p-6 hover:border-brand-300 hover:shadow-lg transition-all dark:border-gray-800 dark:bg-white/[0.03] dark:hover:border-brand-800">
                            <div class="flex items-center gap-4">
                                <div
                                    class="flex items-center justify-center w-14 h-14 bg-indigo-50 rounded-xl dark:bg-indigo-900/20">
                                    <svg class="w-7 h-7 text-indigo-500" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                        </path>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Reports</h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">View analytics</p>
                                </div>
                            </div>
                        </a>

                        <!-- Quick Price Check (New) -->
                        <button @click="openPriceChecker()"
                            class="group rounded-2xl border border-gray-200 bg-white p-6 hover:border-brand-300 hover:shadow-lg transition-all dark:border-gray-800 dark:bg-white/[0.03] dark:hover:border-brand-800 text-left">
                            <div class="flex items-center gap-4">
                                <div
                                    class="flex items-center justify-center w-14 h-14 bg-teal-50 rounded-xl dark:bg-teal-900/20">
                                    <svg class="w-7 h-7 text-teal-500" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 17h.01M9 17h.01M5 11h2m-6-6v2a2 2 0 002 2h2v-4H3zm2 14v-2a2 2 0 00-2-2v4h4v-2H5zm14-14h-2v4h2a2 2 0 002-2v-2z">
                                        </path>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Cek Harga</h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Scan barcode</p>
                                </div>
                            </div>
                        </button>
                    </div>
                    <!-- Recent Price Changes Widget -->
                    <div x-show="priceLogs && priceLogs.length > 0"
                        class="mb-6 rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-white flex items-center gap-2">
                                <svg class="w-5 h-5 text-orange-500" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Recent Price Changes
                            </h3>
                            <span class="text-xs text-gray-500 bg-gray-100 dark:bg-gray-800 px-2 py-1 rounded-full">Last
                                5 updates</span>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left">
                                <thead class="text-gray-500 font-medium border-b border-gray-100 dark:border-gray-700">
                                    <tr>
                                        <th class="py-2 pl-2">Product</th>
                                        <th class="py-2">Unit</th>
                                        <th class="py-2 text-right">Old</th>
                                        <th class="py-2 text-right">New</th>
                                        <th class="py-2 text-right">By</th>
                                        <th class="py-2 text-right pr-2">Time</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                    <template x-for="log in priceLogs" :key="log.id">
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                                            <td class="py-3 pl-2 font-medium text-gray-800 dark:text-white"
                                                x-text="log.product_name"></td>
                                            <td class="py-3 text-gray-500" x-text="log.unit_name"></td>
                                            <td class="py-3 text-right text-gray-400 line-through"
                                                x-text="formatCurrency(log.old_price)"></td>
                                            <td class="py-3 text-right font-bold"
                                                :class="log.new_price > log.old_price ? 'text-red-500' : 'text-green-500'"
                                                x-text="formatCurrency(log.new_price)"></td>
                                            <td class="py-3 text-right text-gray-500"
                                                x-text="log.user_name || 'System'"></td>
                                            <td class="py-3 text-right text-xs text-gray-400 pr-2"
                                                x-text="new Date(log.created_at).toLocaleDateString('id-ID') + ' ' + new Date(log.created_at).toLocaleTimeString('id-ID', {hour: '2-digit', minute:'2-digit'})">
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Sales Chart Placeholder -->
                    <!-- Statistics Chart (Replaces Weekly Sales) -->
                    <div x-data="{
                        timeRange: 'Jan 18 - Jan 24',
                        activeTab: 'Overview', // Overview, Sales, Revenue
                        chart: null,
                        initChart() {
                            const options = {
                                series: [{
                                    name: 'Revenue',
                                    data: [180, 190, 170, 160, 175, 165, 170, 205, 230, 210, 240, 235]
                                }, {
                                    name: 'Sales',
                                    data: [40, 30, 50, 40, 55, 40, 70, 100, 110, 120, 150, 140]
                                }],
                                chart: {
                                    height: 350,
                                    type: 'area',
                                    fontFamily: 'Inter, sans-serif',
                                    toolbar: { show: false },
                                    zoom: { enabled: false }
                                },
                                colors: ['#4F46E5', '#93C5FD'], // Brand Indigo & Light Blue
                                dataLabels: { enabled: false },
                                stroke: { curve: 'smooth', width: 2 },
                                fill: {
                                    type: 'gradient',
                                    gradient: {
                                        shadeIntensity: 1,
                                        opacityFrom: 0.4,
                                        opacityTo: 0.05,
                                        stops: [0, 100]
                                    }
                                },
                                xaxis: {
                                    categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                                    axisBorder: { show: false },
                                    axisTicks: { show: false },
                                    labels: { style: { colors: '#9CA3AF', fontSize: '12px' } }
                                },
                                yaxis: {
                                    labels: { style: { colors: '#9CA3AF', fontSize: '12px' } }
                                },
                                grid: {
                                    borderColor: '#F3F4F6',
                                    strokeDashArray: 4,
                                    xaxis: { lines: { show: false } }
                                },
                                legend: { show: false },
                                tooltip: {
                                    theme: 'light',
                                    y: { formatter: function (val) { return '$' + val } }
                                }
                            };
                            
                            if (document.querySelector('#statisticsChart')) {
                                this.chart = new ApexCharts(document.querySelector('#statisticsChart'), options);
                                this.chart.render();
                            }
                        }
                    }" x-init="initChart()" x-show="user?.role !== 'cashier'"
                        class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">

                        <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
                            <div>
                                <h3 class="text-xl font-bold text-gray-800 dark:text-white">Statistics</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Target you've set for each month</p>
                            </div>
                            <div class="flex items-center gap-3">
                                <!-- Tabs -->
                                <div class="flex bg-gray-100 dark:bg-gray-800 rounded-lg p-1">
                                    <button @click="activeTab = 'Overview'"
                                        :class="activeTab === 'Overview' ? 'bg-white text-gray-800 shadow-sm dark:bg-gray-700 dark:text-white' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400'"
                                        class="px-3 py-1.5 text-sm font-medium rounded-md transition-all">Overview</button>
                                    <button @click="activeTab = 'Sales'"
                                        :class="activeTab === 'Sales' ? 'bg-white text-gray-800 shadow-sm dark:bg-gray-700 dark:text-white' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400'"
                                        class="px-3 py-1.5 text-sm font-medium rounded-md transition-all">Sales</button>
                                    <button @click="activeTab = 'Revenue'"
                                        :class="activeTab === 'Revenue' ? 'bg-white text-gray-800 shadow-sm dark:bg-gray-700 dark:text-white' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400'"
                                        class="px-3 py-1.5 text-sm font-medium rounded-md transition-all">Revenue</button>
                                </div>

                                <!-- Date Picker (Mockup) -->
                                <div
                                    class="flex items-center gap-2 px-3 py-1.5 border border-gray-200 rounded-lg bg-white dark:bg-gray-800 dark:border-gray-700">
                                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                                        </path>
                                    </svg>
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300"
                                        x-text="timeRange"></span>
                                </div>
                            </div>
                        </div>

                        <div id="statisticsChart" class="-ml-2"></div>
                    </div>

                </div>
            </main>
            <!-- ===== Main Content End ===== -->
        </div>
        <!-- ===== Content Area End ===== -->
    </div>
    <!-- ===== Page Wrapper End ===== -->
    <!-- Price Checker Modal -->
    <div x-show="showPriceCheckModal"
        class="fixed inset-0 z-[99999] flex items-center justify-center bg-gray-900/80 backdrop-blur-sm p-4" x-cloak
        x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div class="bg-white dark:bg-gray-800 rounded-2xl w-full max-w-2xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh]"
            @click.away="closePriceChecker()">

            <!-- Modal Modal Header -->
            <div
                class="p-4 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center bg-gray-50 dark:bg-gray-900/50">
                <h3 class="text-lg font-bold text-gray-800 dark:text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    Cek Harga Produk
                </h3>
                <button @click="closePriceChecker()"
                    class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>

            <!-- Modal Content -->
            <div class="p-4 overflow-y-auto flex-1">
                <!-- Search & Scan Controls -->
                <div class="mb-6">
                    <div class="flex gap-2 items-start">
                        <div class="relative flex-1">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>
                            <input type="text" x-model="priceCheckQuery" @keyup.enter="searchProduct(priceCheckQuery)"
                                class="block w-full pl-10 pr-3 py-2.5 border border-gray-300 rounded-xl leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 sm:text-sm dark:bg-gray-900 dark:border-gray-600 dark:text-white dark:placeholder-gray-400"
                                placeholder="Scan barcode or type name..." x-ref="searchInput">
                        </div>
                        <button @click="toggleScanner()"
                            class="px-4 py-2.5 bg-brand-500 text-white rounded-xl hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-500 transition-colors flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 17h.01M9 17h.01M5 11h2m-6-6v2a2 2 0 002 2h2v-4H3zm2 14v-2a2 2 0 00-2-2v4h4v-2H5zm14-14h-2v4h2a2 2 0 002-2v-2z">
                                </path>
                            </svg>
                            <span class="hidden sm:inline">Scan</span>
                        </button>
                    </div>

                    <!-- Search Results Dropdown (Full Width) -->
                    <div x-show="searchResults.length > 0 && !priceCheckResult"
                        class="w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl mt-2 overflow-y-auto max-h-[60vh] divide-y divide-gray-100 dark:divide-gray-700">
                        <template x-for="product in searchResults" :key="product.id">
                            <div @click="selectProduct(product)"
                                class="flex items-center p-3 hover:bg-gray-50 dark:hover:bg-gray-700/50 cursor-pointer transition-colors">
                                <div
                                    class="h-10 w-10 flex-shrink-0 bg-gray-100 rounded-lg overflow-hidden mr-3 border border-gray-200 dark:border-gray-600">
                                    <img :src="product.image_url || 'assets/images/product/product-01.png'"
                                        class="h-full w-full object-cover">
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-semibold text-gray-900 dark:text-white truncate"
                                        x-text="product.name"></p>
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs text-gray-500"
                                            x-text="product.barcode || product.sku"></span>
                                        <span
                                            class="text-[10px] px-1.5 py-0.5 rounded-full bg-blue-50 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400 font-medium"
                                            x-text="'Stk: ' + (product.stock || 0)"></span>
                                    </div>
                                </div>
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5l7 7-7 7"></path>
                                </svg>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Scanner Viewport -->
                <div x-show="isScanning" class="mb-6 rounded-xl overflow-hidden bg-black relative scanner-active">
                    <div id="price-check-reader" class="w-full"></div>
                    <button @click="stopScanner()"
                        class="absolute top-2 right-2 bg-black/50 text-white rounded-full p-1 hover:bg-black/70">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                    <p
                        class="text-center text-white/70 text-sm py-2 absolute bottom-0 w-full bg-gradient-to-t from-black/80 to-transparent">
                        Point camera at barcode
                    </p>
                </div>

                <!-- Product Details Result -->
                <div x-show="priceCheckResult" class="animate-fade-in-up">
                    <div class="flex flex-col sm:flex-row gap-4 mb-6">
                        <!-- Product Image -->
                        <div
                            class="w-full sm:w-1/3 aspect-square bg-gray-100 dark:bg-gray-700 rounded-xl flex items-center justify-center overflow-hidden border border-gray-200 dark:border-gray-600">
                            <template x-if="priceCheckResult?.image_url">
                                <img :src="priceCheckResult.image_url" class="w-full h-full object-cover">
                            </template>
                            <template x-if="!priceCheckResult?.image_url">
                                <svg class="w-12 h-12 text-gray-300 dark:text-gray-500" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                                    </path>
                                </svg>
                            </template>
                        </div>

                        <!-- Product Info -->
                        <div class="flex-1 space-y-3">
                            <div>
                                <h4 class="text-xl font-bold text-gray-800 dark:text-white"
                                    x-text="priceCheckResult?.name"></h4>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                    SKU: <span class="font-mono text-gray-700 dark:text-gray-300"
                                        x-text="priceCheckResult?.sku"></span>
                                </p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    Barcode: <span class="font-mono text-gray-700 dark:text-gray-300"
                                        x-text="priceCheckResult?.barcode || '-'"></span>
                                </p>
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div
                                    class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-xl border border-blue-100 dark:border-blue-800">
                                    <p class="text-xs text-blue-600 dark:text-blue-400 font-medium uppercase">Stock</p>
                                    <p class="text-lg font-bold text-blue-700 dark:text-blue-300"
                                        x-text="priceCheckResult?.stock || 0"></p>
                                </div>
                                <div
                                    class="p-3 bg-green-50 dark:bg-green-900/20 rounded-xl border border-green-100 dark:border-green-800">
                                    <p class="text-xs text-green-600 dark:text-green-400 font-medium uppercase">Category
                                    </p>
                                    <p class="text-sm font-bold text-green-700 dark:text-green-300 truncate"
                                        x-text="priceCheckResult?.category_name || '-'"></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pricing Table -->
                    <div class="border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-gray-50 dark:bg-gray-700/50">
                                <tr>
                                    <th class="px-4 py-3 font-medium text-gray-500 dark:text-gray-400">Unit</th>
                                    <th class="px-4 py-3 font-medium text-gray-500 dark:text-gray-400 text-right">Price
                                    </th>
                                    <th class="px-4 py-3 font-medium text-gray-500 dark:text-gray-400 text-center">Conv.
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                <template x-for="unit in priceCheckResult?.units" :key="unit.id">
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                                        <td class="px-4 py-3">
                                            <div class="font-medium text-gray-800 dark:text-white"
                                                x-text="unit.unit_name"></div>
                                            <span x-show="unit.is_base_unit"
                                                class="text-[10px] bg-brand-100 text-brand-600 px-1.5 py-0.5 rounded">Base</span>
                                        </td>
                                        <td class="px-4 py-3 text-right font-bold text-brand-600 dark:text-brand-400"
                                            x-text="formatCurrency(unit.sell_price)"></td>
                                        <td class="px-4 py-3 text-center text-gray-500 dark:text-gray-400">
                                            <span
                                                x-text="'1 ' + unit.unit_name + ' = ' + unit.conversion_qty + ' Base'"></span>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Empty State / Start State -->
                <div x-show="!priceCheckResult && searchResults.length === 0 && !isLoading && !isScanning"
                    class="text-center py-12">
                    <div
                        class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 dark:bg-gray-800 mb-4">
                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <p class="text-gray-500 dark:text-gray-400 max-w-xs mx-auto">
                        Scan a barcode or type a product name to see prices and stock details.
                    </p>
                </div>

                <!-- Loading State -->
                <div x-show="isLoading" class="text-center py-12">
                    <div
                        class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-brand-500 border-t-transparent">
                    </div>
                    <p class="text-gray-500 mt-2">Searching product...</p>
                </div>

            </div>

            <div class="p-4 border-t border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/30 text-center">
                <button @click="closePriceChecker()"
                    class="w-full py-2.5 bg-gray-200 text-gray-700 rounded-xl hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 font-medium">Close</button>
            </div>
        </div>
    </div>
</body>

</html>