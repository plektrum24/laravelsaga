@extends('layouts.app')

@section('title', 'Sales Order History | SAGA TOKO APP')

@section('content')
<div class="max-w-7xl mx-auto p-6" x-data="salesHistory()">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-800 dark:text-white">📋 Sales Order History</h1>
                <p class="text-gray-500 dark:text-gray-400 mt-1">Riwayat pesanan penjualan dari sales force</p>
            </div>
            <div class="flex items-center gap-3">
                <button @click="exportData()" 
                    class="px-6 py-2.5 bg-gradient-to-r from-brand-600 to-indigo-600 text-white rounded-xl font-semibold hover:from-brand-700 hover:to-indigo-700 transition-all shadow-lg shadow-brand-500/30 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Export
                </button>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">Total Orders</p>
                    <h3 class="text-3xl font-bold text-gray-800 dark:text-white mt-1" x-text="stats.total"></h3>
                </div>
                <div class="w-14 h-14 bg-brand-100 dark:bg-brand-900/30 rounded-2xl flex items-center justify-center">
                    <svg class="w-7 h-7 text-brand-600 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">Pending</p>
                    <h3 class="text-3xl font-bold text-yellow-600 dark:text-yellow-400 mt-1" x-text="stats.pending"></h3>
                </div>
                <div class="w-14 h-14 bg-yellow-100 dark:bg-yellow-900/30 rounded-2xl flex items-center justify-center">
                    <svg class="w-7 h-7 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">Completed</p>
                    <h3 class="text-3xl font-bold text-green-600 dark:text-green-400 mt-1" x-text="stats.completed"></h3>
                </div>
                <div class="w-14 h-14 bg-green-100 dark:bg-green-900/30 rounded-2xl flex items-center justify-center">
                    <svg class="w-7 h-7 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">Total Revenue</p>
                    <h3 class="text-2xl font-bold text-gray-800 dark:text-white mt-1" x-text="formatCurrency(stats.revenue)"></h3>
                </div>
                <div class="w-14 h-14 bg-green-100 dark:bg-green-900/30 rounded-2xl flex items-center justify-center">
                    <svg class="w-7 h-7 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-gray-200 dark:border-gray-700 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Salesman</label>
                <select x-model="filters.salesman_id" @change="fetchOrders()"
                    class="w-full px-4 py-2.5 border-2 border-gray-200 dark:border-gray-700 rounded-xl bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                    <option value="">All Salesmen</option>
                    <template x-for="salesman in salesmen" :key="salesman.id">
                        <option :value="salesman.id" x-text="salesman.name"></option>
                    </template>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status</label>
                <select x-model="filters.status" @change="fetchOrders()"
                    class="w-full px-4 py-2.5 border-2 border-gray-200 dark:border-gray-700 rounded-xl bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                    <option value="">All Status</option>
                    <option value="pending">Pending</option>
                    <option value="confirmed">Confirmed</option>
                    <option value="processing">Processing</option>
                    <option value="completed">Completed</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">From Date</label>
                <input type="date" x-model="filters.date_from" @change="fetchOrders()"
                    class="w-full px-4 py-2.5 border-2 border-gray-200 dark:border-gray-700 rounded-xl bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">To Date</label>
                <input type="date" x-model="filters.date_to" @change="fetchOrders()"
                    class="w-full px-4 py-2.5 border-2 border-gray-200 dark:border-gray-700 rounded-xl bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
            <h2 class="text-xl font-bold text-gray-800 dark:text-white">Daftar Sales Order</h2>
            <input type="text" x-model="searchQuery" @input.debounce="fetchOrders()" placeholder="🔍 Search orders..."
                class="px-4 py-2.5 border-2 border-gray-200 dark:border-gray-700 rounded-xl bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
        </div>

        <!-- Loading -->
        <div x-show="isLoading" class="p-12 text-center">
            <div class="inline-block animate-spin rounded-full h-12 w-12 border-4 border-brand-200 border-t-brand-600"></div>
            <p class="text-gray-500 dark:text-gray-400 mt-4">Loading orders...</p>
        </div>

        <!-- Table Content -->
        <div x-show="!isLoading" class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-gray-900/50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Order #</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Customer</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Salesman</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Date</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Total</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Status</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    <template x-for="order in orders" :key="order.id">
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/30 transition-colors">
                            <td class="px-6 py-4">
                                <span class="font-semibold text-brand-600 dark:text-brand-400" x-text="order.order_number"></span>
                            </td>
                            <td class="px-6 py-4">
                                <div>
                                    <p class="font-medium text-gray-800 dark:text-white" x-text="order.customer_name"></p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400" x-text="order.customer_phone"></p>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-gray-700 dark:text-gray-300" x-text="order.salesman_name"></span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-gray-700 dark:text-gray-300" x-text="formatDate(order.order_date)"></span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="font-bold text-gray-800 dark:text-white" x-text="formatCurrency(order.total_amount)"></span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold"
                                    :class="{
                                        'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400': order.status === 'pending',
                                        'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400': order.status === 'confirmed',
                                        'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400': order.status === 'processing',
                                        'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400': order.status === 'completed',
                                        'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400': order.status === 'cancelled'
                                    }"
                                    x-text="formatStatus(order.status)">
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <button @click="viewOrder(order)" 
                                    class="px-4 py-2 text-sm font-medium text-brand-600 dark:text-brand-400 hover:bg-brand-50 dark:hover:bg-brand-900/20 rounded-lg transition-colors">
                                    View Details
                                </button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>

            <!-- Empty State -->
            <div x-show="orders.length === 0" class="p-12 text-center">
                <div class="w-20 h-20 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <p class="text-gray-500 dark:text-gray-400 text-lg font-medium">No orders found</p>
                <p class="text-gray-400 dark:text-gray-500 text-sm mt-1">Try adjusting your filters</p>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function salesHistory() {
    return {
        isLoading: false,
        searchQuery: '',
        orders: [],
        salesmen: [],
        stats: {
            total: 0,
            pending: 0,
            completed: 0,
            revenue: 0
        },
        filters: {
            salesman_id: '',
            status: '',
            date_from: '',
            date_to: ''
        },

        async init() {
            await this.fetchSalesmen();
            await this.fetchOrders();
        },

        async fetchSalesmen() {
            try {
                const token = localStorage.getItem('saga_token');
                const response = await fetch('/api/users?role=salesman', {
                    headers: { 'Authorization': 'Bearer ' + token }
                });
                const result = await response.json();
                if (result.success) {
                    this.salesmen = result.data.data || [];
                }
            } catch (e) {
                console.error('Error fetching salesmen:', e);
            }
        },

        async fetchOrders() {
            this.isLoading = true;
            try {
                const token = localStorage.getItem('saga_token');
                const params = new URLSearchParams({
                    search: this.searchQuery,
                    salesman_id: this.filters.salesman_id,
                    status: this.filters.status,
                    date_from: this.filters.date_from,
                    date_to: this.filters.date_to
                });
                
                const response = await fetch(`/api/sales-orders?${params}`, {
                    headers: { 'Authorization': 'Bearer ' + token }
                });
                const result = await response.json();
                if (result.success) {
                    this.orders = result.data.data || [];
                    this.calculateStats();
                }
            } catch (e) {
                console.error('Error fetching orders:', e);
            } finally {
                this.isLoading = false;
            }
        },

        calculateStats() {
            this.stats.total = this.orders.length;
            this.stats.pending = this.orders.filter(o => o.status === 'pending').length;
            this.stats.completed = this.orders.filter(o => o.status === 'completed').length;
            this.stats.revenue = this.orders
                .filter(o => o.status === 'completed')
                .reduce((sum, o) => sum + parseFloat(o.total_amount), 0);
        },

        formatStatus(status) {
            const statuses = {
                'pending': 'Pending',
                'confirmed': 'Confirmed',
                'processing': 'Processing',
                'completed': 'Completed',
                'cancelled': 'Cancelled'
            };
            return statuses[status] || status;
        },

        formatDate(dateStr) {
            return new Date(dateStr).toLocaleDateString('id-ID', {
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            });
        },

        formatCurrency(amount) {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0
            }).format(amount);
        },

        viewOrder(order) {
            Swal.fire({
                title: `Order ${order.order_number}`,
                html: `
                    <div class="text-left space-y-3">
                        <p><strong>Customer:</strong> ${order.customer_name}</p>
                        <p><strong>Phone:</strong> ${order.customer_phone}</p>
                        <p><strong>Salesman:</strong> ${order.salesman_name}</p>
                        <p><strong>Date:</strong> ${this.formatDate(order.order_date)}</p>
                        <p><strong>Total:</strong> ${this.formatCurrency(order.total_amount)}</p>
                        <p><strong>Status:</strong> ${this.formatStatus(order.status)}</p>
                        <p><strong>Notes:</strong> ${order.notes || '-'}</p>
                    </div>
                `,
                confirmButtonText: 'Close',
                confirmButtonColor: '#4f46e5'
            });
        },

        exportData() {
            Swal.fire({
                title: 'Export Data',
                text: 'Export sales order history to Excel?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, Export',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#4f46e5'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.open('/api/sales-orders/export', '_blank');
                }
            });
        }
    }
}
</script>
@endpush
@endsection
