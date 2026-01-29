@extends('layouts.app')

@section('title', 'Dashboard | SAGA TOKO APP')

@section('content')
    <div x-data="{
                                    stats: { today: { orders: 0, sales: 0 }, week: 0, month: 0, lowStockCount: 0, weeklyChart: [] },
                                    priceLogs: [],
                                    isLoading: true,
                                    user: @json(auth()->user()),

                                    async init() {
                                        const token = localStorage.getItem('saga_token');
                                        if (!token) {
                                            window.location.href = '{{ route('login') }}';
                                            return;
                                        }
                                        await this.fetchDashboard();
                                        await this.fetchPriceLogs();
                                    },

                                    async fetchDashboard() {
                                        try {
                                            const token = localStorage.getItem('saga_token');
                                            let url = '/api/reports/dashboard';

                                            // Handle branch selection if implemented in API
                                            const selectedBranch = localStorage.getItem('saga_selected_branch');
                                            const user = this.user || JSON.parse(localStorage.getItem('saga_user') || '{}');

                                            if (user.role === 'tenant_owner' && selectedBranch) {
                                                url += '?branch_id=' + selectedBranch;
                                            } else if (user.branch_id) {
                                                url += '?branch_id=' + user.branch_id;
                                            }

                                            // Mocking data if API fails or for demo (Remove in production)
                                            // In exact migration we assume API works, but basic structure is needed.
                                            /*
                                            this.stats = {
                                                today: { orders: 12, sales: 15600000 },
                                                week: 45000000,
                                                lowStockCount: 3,
                                                weeklyChart: [
                                                   { date: '2023-10-01', total: 500000 },
                                                   { date: '2023-10-02', total: 1500000 },
                                                   // ...
                                                ]
                                            };
                                            */

                                            const response = await fetch(url, {
                                                headers: { 'Authorization': 'Bearer ' + token }
                                            });

                                            if (response.ok) {
                                                const data = await response.json();
                                                if (data.success) {
                                                    this.stats = data.data;
                                                }
                                            }
                                        } catch (error) {
                                            console.error('Dashboard fetch error:', error);
                                        } finally {
                                            this.isLoading = false;
                                        }
                                    },

                                    async fetchPriceLogs() {
                                        try {
                                            const token = localStorage.getItem('saga_token');
                                            const response = await fetch('/api/products/reports/price-logs?limit=5', {
                                                headers: { 'Authorization': 'Bearer ' + token }
                                            });
                                            if (response.ok) {
                                                const data = await response.json();
                                                if (data.success) {
                                                    this.priceLogs = data.data;
                                                }
                                            }
                                        } catch (error) {
                                            console.error('Fetch price logs error:', error);
                                        }
                                    },

                                    formatCurrency(amount) {
                                        return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(amount || 0);
                                    },

                                    checkRole(allowedRoles) {
                                        const userRole = this.user?.role_name || this.user?.role || 'guest'; 
                                        // Note: Depending on backend, it might be role_name (Spatie) or role (column). 
                                        // My seeder used 'role' column as fallback. checking both.
                                        return allowedRoles.includes(userRole);
                                    },

                                    // Cash Expense Logic
                                    showExpenseModal: false,
                                    expenseForm: { amount: '', note: '' },
                                    isSubmittingExpense: false,

                                    openExpenseModal() {
                                        this.expenseForm = { amount: '', note: '' };
                                        this.showExpenseModal = true;
                                    },

                                    async submitExpense() {
                                        if (!this.expenseForm.amount || !this.expenseForm.note) {
                                            alert('Mohon isi jumlah dan catatan.');
                                            return;
                                        }

                                        this.isSubmittingExpense = true;
                                        try {
                                            const token = localStorage.getItem('saga_token');
                                            const response = await fetch('/api/cash-register/expense', {
                                                method: 'POST',
                                                headers: { 
                                                    'Authorization': 'Bearer ' + token,
                                                    'Content-Type': 'application/json'
                                                },
                                                body: JSON.stringify(this.expenseForm)
                                            });

                                            const data = await response.json();
                                            if (data.success) {
                                                alert('Pengeluaran berhasil dicatat!');
                                                this.showExpenseModal = false;
                                            } else {
                                                alert('Gagal: ' + (data.message || 'Terjadi kesalahan'));
                                            }
                                        } catch (error) {
                                                alert('Error sistem: ' + error.message);
                                        } finally {
                                            this.isSubmittingExpense = false;
                                        }
                                    }
                                }">
        <!-- Subscription Expiry Warning Banner -->
        <div x-data="{ 
                                        tenant: JSON.parse(localStorage.getItem('saga_tenant') || '{}'),
                                        get daysLeft() {
                                            if (!this.tenant?.valid_until) return 999;
                                            const expiry = new Date(this.tenant.valid_until);
                                            const now = new Date();
                                            const diffTime = expiry - now;
                                            return Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                                        },
                                        get showWarning() {
                                            return this.daysLeft >= -5 && this.daysLeft <= 7; // Show if expired recently or expiring soon
                                        },
                                        get warningColor() {
                                            if (this.daysLeft <= 0) return 'bg-red-600';
                                            if (this.daysLeft <= 3) return 'bg-red-500';
                                            return 'bg-orange-500';
                                        },
                                        formatDate(dateStr) {
                                            if (!dateStr) return '-';
                                            return new Date(dateStr).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
                                        }
                                    }" x-show="showWarning" class="mb-4" x-cloak>
            <div :class="warningColor" class="rounded-xl p-4 text-white flex items-center justify-between shadow-lg">
                <div class="flex items-center gap-3">
                    <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                        </path>
                    </svg>
                    <div>
                        <p class="font-bold text-lg"
                            x-text="daysLeft <= 0 ? '⚠️ Langganan Telah Berakhir!' : '⚠️ Langganan berakhir dalam ' + daysLeft + ' hari'">
                        </p>
                        <p class="text-sm opacity-90" x-text="'Berlaku hingga: ' + formatDate(tenant?.valid_until)">
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
                    <span x-show="localStorage.getItem('saga_selected_branch')" class="inline-flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                            </path>
                        </svg>
                        <span>Branch Selected</span>
                    </span>
                    <span x-show="!localStorage.getItem('saga_selected_branch')">Welcome back! Here's your store
                        overview.</span>
                </p>
            </div>
        </div>


        <!-- Stats Cards -->
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4 mb-6">
            <!-- Today's Orders -->
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Today's Orders</p>
                        <h3 class="text-2xl font-bold text-gray-800 dark:text-white mt-1" x-text="stats.today?.orders || 0">
                            0</h3>
                    </div>
                    <div class="flex items-center justify-center w-12 h-12 bg-blue-50 rounded-xl dark:bg-blue-900/20">
                        <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Today's Sales -->
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Today's Sales</p>
                        <h3 class="text-2xl font-bold text-gray-800 dark:text-white mt-1"
                            x-text="formatCurrency(stats.today?.sales)">Rp 0</h3>
                    </div>
                    <div class="flex items-center justify-center w-12 h-12 bg-green-50 rounded-xl dark:bg-green-900/20">
                        <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                            </path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- This Week -->
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">This Week</p>
                        <h3 class="text-2xl font-bold text-gray-800 dark:text-white mt-1"
                            x-text="formatCurrency(stats.week)">Rp 0</h3>
                    </div>
                    <div class="flex items-center justify-center w-12 h-12 bg-purple-50 rounded-xl dark:bg-purple-900/20">
                        <svg class="w-6 h-6 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                        <svg class="w-6 h-6" :class="stats.lowStockCount > 0 ? 'text-red-500' : 'text-gray-400'" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                            </path>
                        </svg>
                    </div>
                </div>
                <a href="{{ route('inventory.index') }}?low_stock=true"
                    class="text-sm text-brand-500 hover:underline mt-2 inline-block" x-show="stats.lowStockCount > 0">View
                    Items →</a>
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
                <p class="text-2xl font-bold mt-1" x-text="formatCurrency(stats.debts?.totalOutstanding || 0)">Rp 0</p>
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
                <p class="text-2xl font-bold mt-1" x-text="formatCurrency(stats.receivables?.totalOutstanding || 0)">Rp 0
                </p>
                <p class="text-xs text-white/60 mt-2" x-show="(stats.receivables?.upcomingCount || 0) > 0"
                    x-text="(stats.receivables?.upcomingCount || 0) + ' jatuh tempo minggu ini'"></p>
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
            <!-- Quick POS Access -->
            <a href="{{ route('sales.create') }}"
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

            <!-- Quick Expenses (New Feature - Kasir & Owner) -->
            <button @click="openExpenseModal()" x-show="checkRole(['Kasir', 'Owner'])"
                class="group rounded-2xl border border-gray-200 bg-white p-6 hover:border-red-300 hover:shadow-lg transition-all dark:border-gray-800 dark:bg-white/[0.03] dark:hover:border-red-800 text-left">
                <div class="flex items-center gap-4">
                    <div class="flex items-center justify-center w-14 h-14 bg-red-50 rounded-xl dark:bg-red-900/20">
                        <svg class="w-7 h-7 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z">
                            </path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Uang Keluar</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Catat pengeluaran</p>
                    </div>
                </div>
            </button>

            <!-- Quick Inventory (Hidden for Kasir) -->
            <a href="{{ route('inventory.index') }}" x-show="!checkRole(['Kasir'])"
                class="group rounded-2xl border border-gray-200 bg-white p-6 hover:border-brand-300 hover:shadow-lg transition-all dark:border-gray-800 dark:bg-white/[0.03] dark:hover:border-brand-800">
                <div class="flex items-center gap-4">
                    <div class="flex items-center justify-center w-14 h-14 bg-orange-50 rounded-xl dark:bg-orange-900/20">
                        <svg class="w-7 h-7 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
            <a href="{{ route('reports.index') }}" x-show="!checkRole(['Gudang'])"
                class="group rounded-2xl border border-gray-200 bg-white p-6 hover:border-brand-300 hover:shadow-lg transition-all dark:border-gray-800 dark:bg-white/[0.03] dark:hover:border-brand-800">
                <div class="flex items-center gap-4">
                    <div class="flex items-center justify-center w-14 h-14 bg-indigo-50 rounded-xl dark:bg-indigo-900/20">
                        <svg class="w-7 h-7 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                    <div class="flex items-center justify-center w-14 h-14 bg-teal-50 rounded-xl dark:bg-teal-900/20">
                        <svg class="w-7 h-7 text-teal-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
        <div x-show="checkRole(['Owner', 'Manager']) && priceLogs && priceLogs.length > 0"
            class="mb-6 rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                                <td class="py-3 pl-2 font-medium text-gray-800 dark:text-white" x-text="log.product_name">
                                </td>
                                <td class="py-3 text-gray-500" x-text="log.unit_name"></td>
                                <td class="py-3 text-right text-gray-400 line-through"
                                    x-text="formatCurrency(log.old_price)"></td>
                                <td class="py-3 text-right font-bold"
                                    :class="log.new_price > log.old_price ? 'text-red-500' : 'text-green-500'"
                                    x-text="formatCurrency(log.new_price)"></td>
                                <td class="py-3 text-right text-gray-500" x-text="log.user_name || 'System'"></td>
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
        <div x-show="checkRole(['Owner', 'Manager'])"
            class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">Weekly Sales</h3>
            <div class="h-64 flex items-center justify-center text-gray-400" id="weeklyChart">
                <div x-show="isLoading" class="animate-pulse">Loading chart...</div>
                <div x-show="!isLoading && stats.weeklyChart.length === 0">No sales data available</div>
                <div x-show="!isLoading && stats.weeklyChart.length > 0" class="w-full h-full">
                    <!-- Chart will be rendered here -->
                    <div class="flex items-end justify-around h-full gap-2 pt-4">
                        <template x-for="(item, index) in stats.weeklyChart" :key="index">
                            <div class="flex flex-col items-center flex-1">
                                <div class="w-full bg-brand-500 rounded-t-lg min-h-[4px] transition-all"
                                    :style="'height: ' + (item.total / Math.max(...stats.weeklyChart.map(i => i.total || 1)) * 180) + 'px'">
                                </div>
                                <span class="text-xs text-gray-500 mt-2" x-text="item.date.slice(5)"></span>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
        <!-- EXPENSE MODAL -->
        <div x-show="showExpenseModal" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
            x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
            <div @click.away="showExpenseModal = false"
                class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-md p-6 transform transition-all"
                x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100">

                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-bold text-gray-800 dark:text-white">Catat Pengeluaran Kas</h3>
                    <button @click="showExpenseModal = false"
                        class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                            </path>
                        </svg>
                    </button>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Jumlah Uang
                            (Rp)</label>
                        <input type="number" x-model="expenseForm.amount" placeholder="Contoh: 50000"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-brand-500 focus:border-brand-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Keterangan /
                            Catatan</label>
                        <textarea x-model="expenseForm.note" rows="3" placeholder="Contoh: Beli sabun pel lantai"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-brand-500 focus:border-brand-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"></textarea>
                    </div>

                    <button @click="submitExpense()" :disabled="isSubmittingExpense"
                        class="w-full py-3 bg-red-600 text-white rounded-xl font-bold hover:bg-red-700 transition flex items-center justify-center gap-2">
                        <span x-show="isSubmittingExpense"
                            class="animate-spin rounded-full h-4 w-4 border-b-2 border-white"></span>
                        <span>Simpan Pengeluaran</span>
                    </button>
                    <p class="text-xs text-gray-500 text-center mt-2">Data akan langsung tercatat di Laporan Kas Harian.</p>
                </div>
            </div>
        </div>
    </div>
@endsection