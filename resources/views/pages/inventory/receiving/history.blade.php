@extends('layouts.app')

@section('title', 'Receiving History | SAGA POS')

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-gray-900 p-6" x-data="receivingHistory()">
    <!-- Header -->
    <div class="max-w-8xl mx-auto mb-8">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-14 h-14 bg-gradient-to-br from-blue-500 to-cyan-600 rounded-2xl flex items-center justify-center shadow-xl shadow-blue-500/30">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                    </svg>
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-gray-800 dark:text-white">Receiving History</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400">View all goods in and receiving transactions</p>
                </div>
            </div>
            <a href="{{ route('inventory.receiving.create') }}" class="px-6 py-3 bg-gradient-to-r from-blue-600 to-cyan-600 text-white rounded-xl font-semibold hover:from-blue-700 hover:to-cyan-700 transition-all shadow-lg flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                New Goods In
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="max-w-8xl mx-auto mb-8 grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-gradient-to-br from-blue-500 to-cyan-600 rounded-2xl p-6 text-white shadow-xl shadow-blue-500/20">
            <div class="flex items-center justify-between mb-4">
                <div class="w-14 h-14 bg-white/20 backdrop-blur-sm rounded-2xl flex items-center justify-center">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4m0 0c0 1.657 1.343 3 3 3h10c1.657 0 3-1.343 3-3m0 0V6"></path>
                    </svg>
                </div>
                <span class="text-3xl font-bold" x-text="stats.total"></span>
            </div>
            <p class="text-blue-100 text-sm font-medium">Total Receiving</p>
            <p class="text-blue-200 text-xs mt-1">All time transactions</p>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between mb-4">
                <div class="w-14 h-14 bg-gradient-to-br from-green-100 to-emerald-100 dark:from-green-900/30 dark:to-emerald-900/30 rounded-2xl flex items-center justify-center">
                    <svg class="w-7 h-7 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <span class="text-3xl font-bold text-gray-800 dark:text-white" x-text="stats.today"></span>
            </div>
            <p class="text-gray-500 dark:text-gray-400 text-sm font-medium">Today</p>
            <p class="text-green-600 text-xs mt-1">Received today</p>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between mb-4">
                <div class="w-14 h-14 bg-gradient-to-br from-purple-100 to-indigo-100 dark:from-purple-900/30 dark:to-indigo-900/30 rounded-2xl flex items-center justify-center">
                    <svg class="w-7 h-7 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <span class="text-2xl font-bold text-gray-800 dark:text-white" x-text="formatCurrency(stats.totalValue)"></span>
            </div>
            <p class="text-gray-500 dark:text-gray-400 text-sm font-medium">Total Value</p>
            <p class="text-purple-600 text-xs mt-1">All receiving value</p>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between mb-4">
                <div class="w-14 h-14 bg-gradient-to-br from-amber-100 to-orange-100 dark:from-amber-900/30 dark:to-orange-900/30 rounded-2xl flex items-center justify-center">
                    <svg class="w-7 h-7 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <span class="text-3xl font-bold text-gray-800 dark:text-white" x-text="stats.pending"></span>
            </div>
            <p class="text-gray-500 dark:text-gray-400 text-sm font-medium">Pending Payment</p>
            <p class="text-amber-600 text-xs mt-1">Unpaid transactions</p>
        </div>
    </div>

    <!-- Filters and Table -->
    <div class="max-w-8xl mx-auto bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-gray-800 dark:text-white">All Receiving Transactions</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Manage and track all goods received</p>
            </div>
            <div class="flex gap-3">
                <div class="relative">
                    <input type="text" x-model="filters.search" placeholder="Search invoice, supplier..."
                        class="pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white text-sm">
                    <svg class="w-5 h-5 absolute left-3 top-2.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
                <select x-model="filters.paymentStatus" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white text-sm">
                    <option value="all">All Payment</option>
                    <option value="paid">Paid</option>
                    <option value="unpaid">Unpaid</option>
                    <option value="partial">Partial</option>
                </select>
                <button @click="exportToExcel()" class="px-4 py-2 bg-green-600 text-white rounded-xl hover:bg-green-700 transition flex items-center gap-2 text-sm font-medium">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Export
                </button>
            </div>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Invoice</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Supplier</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Items</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Payment</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    <template x-for="receiving in filteredReceivings" :key="receiving.id">
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                            <td class="px-6 py-4">
                                <span class="text-sm font-semibold text-gray-800 dark:text-white" x-text="receiving.invoice_number"></span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-sm text-gray-600 dark:text-gray-400" x-text="formatDate(receiving.date)"></span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 bg-gradient-to-br from-blue-100 to-blue-200 dark:from-blue-900/30 dark:to-blue-900/50 rounded-lg flex items-center justify-center">
                                        <span class="text-xs font-bold text-blue-600 dark:text-blue-400" x-text="receiving.supplier_name.charAt(0)"></span>
                                    </div>
                                    <span class="text-sm font-medium text-gray-800 dark:text-white" x-text="receiving.supplier_name"></span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-sm text-gray-600 dark:text-gray-400" x-text="receiving.items_count + ' items'"></span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <span class="text-sm font-bold text-gray-800 dark:text-white" x-text="formatCurrency(receiving.total_amount)"></span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span :class="getPaymentStatusBadge(receiving.payment_status).class" class="px-3 py-1 rounded-full text-xs font-medium" x-text="getPaymentStatusBadge(receiving.payment_status).label"></span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <button @click="viewDetails(receiving)" class="p-2 text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition" title="View Details">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </button>
                                    <button @click="printGRN(receiving)" class="p-2 text-purple-600 hover:bg-purple-50 dark:hover:bg-purple-900/20 rounded-lg transition" title="Print GRN">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <!-- Empty State -->
        <div x-show="filteredReceivings.length === 0" class="p-12 text-center">
            <svg class="w-16 h-16 mx-auto text-gray-300 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4m0 0c0 1.657 1.343 3 3 3h10c1.657 0 3-1.343 3-3m0 0V6"></path>
            </svg>
            <p class="text-gray-500 dark:text-gray-400 text-lg font-medium">No receiving transactions found</p>
            <p class="text-gray-400 dark:text-gray-500 text-sm mt-1">Start by creating a new goods in transaction</p>
            <a href="{{ route('inventory.receiving.create') }}" class="inline-flex items-center gap-2 mt-4 px-6 py-3 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition font-medium">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Create First Receiving
            </a>
        </div>
    </div>

    <!-- View Details Modal -->
    <div x-show="viewModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
        x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div @click.away="viewModal = false" class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-4xl max-h-[90vh] overflow-y-auto"
            x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100">
            
            <div class="p-6 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between sticky top-0 bg-white dark:bg-gray-800 z-10">
                <div>
                    <h3 class="text-xl font-bold text-gray-800 dark:text-white">Receiving Details</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400" x-text="selectedReceiving?.invoice_number"></p>
                </div>
                <button @click="viewModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <div class="p-6" x-show="selectedReceiving">
                <!-- Info Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Invoice Number</p>
                        <p class="text-lg font-bold text-gray-800 dark:text-white" x-text="selectedReceiving?.invoice_number"></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Date</p>
                        <p class="text-lg font-bold text-gray-800 dark:text-white" x-text="formatDate(selectedReceiving?.date)"></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Supplier</p>
                        <p class="text-lg font-bold text-gray-800 dark:text-white" x-text="selectedReceiving?.supplier_name"></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Payment Status</p>
                        <span :class="getPaymentStatusBadge(selectedReceiving?.payment_status).class" class="px-3 py-1 rounded-full text-sm font-medium" x-text="getPaymentStatusBadge(selectedReceiving?.payment_status).label"></span>
                    </div>
                </div>

                <!-- Items Table -->
                <div class="border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden mb-6">
                    <table class="w-full">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Product</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Qty</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Unit Price</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <template x-for="item in selectedReceiving?.items" :key="item.id">
                                <tr>
                                    <td class="px-4 py-3">
                                        <p class="font-medium text-gray-800 dark:text-white" x-text="item.name"></p>
                                        <p class="text-xs text-gray-500" x-text="item.sku"></p>
                                    </td>
                                    <td class="px-4 py-3 text-right text-gray-600 dark:text-gray-400" x-text="item.quantity"></td>
                                    <td class="px-4 py-3 text-right text-gray-600 dark:text-gray-400" x-text="formatCurrency(item.cost_price)"></td>
                                    <td class="px-4 py-3 text-right font-bold text-gray-800 dark:text-white" x-text="formatCurrency(item.quantity * item.cost_price)"></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                <!-- Total -->
                <div class="flex justify-end">
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-xl p-4 min-w-[200px]">
                        <div class="flex justify-between mb-2">
                            <span class="text-gray-500 dark:text-gray-400">Subtotal</span>
                            <span class="font-medium text-gray-800 dark:text-white" x-text="formatCurrency(selectedReceiving?.total_amount)"></span>
                        </div>
                        <div class="border-t border-gray-200 dark:border-gray-600 my-2"></div>
                        <div class="flex justify-between">
                            <span class="text-lg font-bold text-gray-500 dark:text-gray-400">Total</span>
                            <span class="text-2xl font-bold text-gray-800 dark:text-white" x-text="formatCurrency(selectedReceiving?.total_amount)"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function receivingHistory() {
    return {
        viewModal: false,
        selectedReceiving: null,
        stats: {
            total: 0,
            today: 0,
            totalValue: 0,
            pending: 0
        },
        filters: {
            search: '',
            paymentStatus: 'all'
        },
        receivings: [
            // Mock data - replace with API call
            { id: 1, invoice_number: 'GRN-20240120-001', date: '2024-01-20', supplier_name: 'PT. Sumber Makmur', items_count: 5, total_amount: 5200000, payment_status: 'paid', items: [] },
            { id: 2, invoice_number: 'GRN-20240121-002', date: '2024-01-21', supplier_name: 'CV. Maju Jaya', items_count: 3, total_amount: 1500000, payment_status: 'unpaid', items: [] },
            { id: 3, invoice_number: 'GRN-20240122-003', date: '2024-01-22', supplier_name: 'Toko Sejahtera', items_count: 2, total_amount: 750000, payment_status: 'paid', items: [] },
        ],

        async init() {
            await this.fetchReceivings();
            this.calculateStats();
        },

        async fetchReceivings() {
            try {
                const token = localStorage.getItem('saga_token');
                const response = await fetch('/api/purchases', {
                    headers: { 'Authorization': 'Bearer ' + token }
                });
                if (response.ok) {
                    const data = await response.json();
                    if (data.success) {
                        this.receivings = data.data;
                        this.calculateStats();
                    }
                }
            } catch (error) {
                console.error('Fetch error:', error);
            }
        },

        get filteredReceivings() {
            return this.receivings.filter(r => {
                const matchSearch = r.invoice_number.toLowerCase().includes(this.filters.search.toLowerCase()) ||
                                  r.supplier_name.toLowerCase().includes(this.filters.search.toLowerCase());
                const matchPayment = this.filters.paymentStatus === 'all' || r.payment_status === this.filters.paymentStatus;
                return matchSearch && matchPayment;
            });
        },

        calculateStats() {
            this.stats.total = this.receivings.length;
            this.stats.today = this.receivings.filter(r => r.date === new Date().toISOString().split('T')[0]).length;
            this.stats.totalValue = this.receivings.reduce((sum, r) => sum + r.total_amount, 0);
            this.stats.pending = this.receivings.filter(r => r.payment_status !== 'paid').length;
        },

        getPaymentStatusBadge(status) {
            switch(status) {
                case 'paid': return { class: 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400', label: 'Paid' };
                case 'unpaid': return { class: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400', label: 'Unpaid' };
                case 'partial': return { class: 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400', label: 'Partial' };
                default: return { class: 'bg-gray-100 text-gray-700', label: '-' };
            }
        },

        formatCurrency(amount) {
            return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(amount || 0);
        },

        formatDate(dateStr) {
            if (!dateStr) return '-';
            return new Date(dateStr).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
        },

        viewDetails(receiving) {
            this.selectedReceiving = receiving;
            this.viewModal = true;
        },

        printGRN(receiving) {
            // Print functionality
            window.open(`/api/purchases/${receiving.id}/receipt`, '_blank');
        },

        exportToExcel() {
            // Export functionality
            window.location.href = '/api/purchases/export/excel';
        }
    }
}
</script>
@endsection
