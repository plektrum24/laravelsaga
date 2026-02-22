@extends('layouts.app')

@section('title', 'Tenant Portal | SAGA POS')

@section('content')
<div x-data="tenantPortal()" x-init="init()" class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Tenant Portal</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Manage your subscription and billing</p>
        </div>
        <div class="flex gap-2">
            <a href="/tenant-portal/subscription" class="btn-primary inline-flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                Manage Subscription
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
        <!-- Subscription Status -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Subscription</p>
                    <h3 class="text-lg font-bold text-gray-800 dark:text-white mt-1" x-text="subscription?.plan?.name || 'N/A'">Loading...</h3>
                    <span class="inline-block mt-2 px-2 py-1 text-xs rounded-full capitalize"
                          :class="{
                              'bg-green-100 text-green-700': subscription?.status === 'active',
                              'bg-yellow-100 text-yellow-700': subscription?.status === 'trial',
                              'bg-red-100 text-red-700': subscription?.status === 'suspended'
                          }"
                          x-text="subscription?.status || 'N/A'">
                    </span>
                </div>
                <div class="flex items-center justify-center w-12 h-12 bg-brand-50 rounded-xl dark:bg-brand-900/20">
                    <svg class="w-6 h-6 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Expiry Date -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Expires On</p>
                    <h3 class="text-lg font-bold text-gray-800 dark:text-white mt-1" x-text="formatDate(subscription?.expires_at)">-</h3>
                    <p class="text-xs text-gray-500 mt-2" x-show="daysUntilExpiry !== null">
                        <span x-text="daysUntilExpiry"></span> days remaining
                    </p>
                </div>
                <div class="flex items-center justify-center w-12 h-12 bg-purple-50 rounded-xl dark:bg-purple-900/20">
                    <svg class="w-6 h-6 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Unpaid Invoices -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Unpaid Invoices</p>
                    <h3 class="text-lg font-bold text-gray-800 dark:text-white mt-1" x-text="invoiceSummary?.pending_total || 0">0</h3>
                    <p class="text-xs text-red-500 mt-2" x-show="invoiceSummary?.overdue_total > 0">
                        Rp <span x-text="formatNumber(invoiceSummary?.overdue_total)"></span> overdue
                    </p>
                </div>
                <div class="flex items-center justify-center w-12 h-12 bg-red-50 rounded-xl dark:bg-red-900/20">
                    <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Open Tickets -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Support Tickets</p>
                    <h3 class="text-lg font-bold text-gray-800 dark:text-white mt-1" x-text="ticketCount">0</h3>
                    <p class="text-xs text-gray-500 mt-2">Open tickets</p>
                </div>
                <div class="flex items-center justify-center w-12 h-12 bg-blue-50 rounded-xl dark:bg-blue-900/20">
                    <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <!-- Usage Stats -->
        <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">Current Usage</h3>
            <div class="space-y-4">
                <template x-for="item in usage" :key="item.metric">
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300" x-text="formatMetric(item.metric)"></span>
                            <span class="text-sm text-gray-500" x-text="item.current_value + ' / ' + (item.limit_value > 0 ? item.limit_value : '∞')"></span>
                        </div>
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                            <div class="h-2 rounded-full transition-all duration-300"
                                 :class="item.usage_percent > 90 ? 'bg-red-500' : item.usage_percent > 70 ? 'bg-yellow-500' : 'bg-green-500'"
                                 :style="'width: ' + Math.min(item.usage_percent, 100) + '%'"></div>
                        </div>
                    </div>
                </template>
                <p x-show="usage.length === 0" class="text-center text-gray-500 py-4">No usage data available</p>
            </div>
        </div>

        <!-- Recent Invoices -->
        <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Recent Invoices</h3>
                <a href="/tenant-portal/invoices" class="text-sm text-brand-500 hover:text-brand-600">View All</a>
            </div>
            <div class="space-y-3">
                <template x-for="invoice in recentInvoices" :key="invoice.id">
                    <div class="flex items-center justify-between p-3 rounded-lg bg-gray-50 dark:bg-gray-900/50">
                        <div>
                            <p class="font-medium text-gray-800 dark:text-white" x-text="invoice.invoice_number"></p>
                            <p class="text-xs text-gray-500" x-text="formatDate(invoice.created_at)"></p>
                        </div>
                        <div class="text-right">
                            <p class="font-medium text-gray-800 dark:text-white">Rp <span x-text="formatNumber(invoice.total)"></span></p>
                            <span class="inline-block px-2 py-1 text-xs rounded-full capitalize"
                                  :class="{
                                      'bg-green-100 text-green-700': invoice.status === 'paid',
                                      'bg-blue-100 text-blue-700': invoice.status === 'sent',
                                      'bg-red-100 text-red-700': invoice.status === 'overdue'
                                  }"
                                  x-text="invoice.status">
                            </span>
                        </div>
                    </div>
                </template>
                <p x-show="recentInvoices.length === 0" class="text-center text-gray-500 py-4">No invoices yet</p>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
        <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">Quick Actions</h3>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <a href="/tenant-portal/subscription" class="flex flex-col items-center p-4 rounded-xl border border-gray-200 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-900/50 transition-colors">
                <svg class="w-8 h-8 text-brand-500 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Upgrade Plan</span>
            </a>
            <a href="/tenant-portal/invoices" class="flex flex-col items-center p-4 rounded-xl border border-gray-200 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-900/50 transition-colors">
                <svg class="w-8 h-8 text-blue-500 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Pay Invoice</span>
            </a>
            <a href="/tenant-portal/tickets" class="flex flex-col items-center p-4 rounded-xl border border-gray-200 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-900/50 transition-colors">
                <svg class="w-8 h-8 text-green-500 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"></path>
                </svg>
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Support</span>
            </a>
            <a href="/tenant-portal/usage" class="flex flex-col items-center p-4 rounded-xl border border-gray-200 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-900/50 transition-colors">
                <svg class="w-8 h-8 text-purple-500 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">View Usage</span>
            </a>
        </div>
    </div>
</div>

@push('scripts')
<script>
function tenantPortal() {
    return {
        subscription: null,
        usage: [],
        invoiceSummary: null,
        recentInvoices: [],
        ticketCount: 0,
        daysUntilExpiry: null,

        async init() {
            await Promise.all([
                this.fetchSubscription(),
                this.fetchUsage(),
                this.fetchInvoiceSummary(),
                this.fetchRecentInvoices(),
                this.fetchTicketCount()
            ]);
        },

        async fetchSubscription() {
            try {
                const token = localStorage.getItem('saga_token');
                const response = await fetch('/api/tenant/subscription', {
                    headers: { 'Authorization': 'Bearer ' + token }
                });
                const result = await response.json();
                if (result.success) {
                    this.subscription = result.data.subscription;
                    if (this.subscription?.expires_at) {
                        const expiry = new Date(this.subscription.expires_at);
                        const now = new Date();
                        this.daysUntilExpiry = Math.ceil((expiry - now) / (1000 * 60 * 60 * 24));
                    }
                }
            } catch (error) {
                console.error('Error fetching subscription:', error);
            }
        },

        async fetchUsage() {
            try {
                const token = localStorage.getItem('saga_token');
                const response = await fetch('/api/tenant/usage/current', {
                    headers: { 'Authorization': 'Bearer ' + token }
                });
                const result = await response.json();
                if (result.success) {
                    this.usage = result.data.map(item => ({
                        ...item,
                        usage_percent: item.limit_value > 0 ? (item.current_value / item.limit_value) * 100 : 0
                    }));
                }
            } catch (error) {
                console.error('Error fetching usage:', error);
            }
        },

        async fetchInvoiceSummary() {
            try {
                const token = localStorage.getItem('saga_token');
                const response = await fetch('/api/tenant/invoices/summary', {
                    headers: { 'Authorization': 'Bearer ' + token }
                });
                const result = await response.json();
                if (result.success) {
                    this.invoiceSummary = result.data.summary;
                }
            } catch (error) {
                console.error('Error fetching invoice summary:', error);
            }
        },

        async fetchRecentInvoices() {
            try {
                const token = localStorage.getItem('saga_token');
                const response = await fetch('/api/tenant/invoices?per_page=5', {
                    headers: { 'Authorization': 'Bearer ' + token }
                });
                const result = await response.json();
                if (result.success) {
                    this.recentInvoices = result.data.data || [];
                }
            } catch (error) {
                console.error('Error fetching invoices:', error);
            }
        },

        async fetchTicketCount() {
            try {
                const token = localStorage.getItem('saga_token');
                const response = await fetch('/api/tenant/tickets?per_page=1', {
                    headers: { 'Authorization': 'Bearer ' + token }
                });
                const result = await response.json();
                if (result.success) {
                    this.ticketCount = result.data.total || 0;
                }
            } catch (error) {
                console.error('Error fetching tickets:', error);
            }
        },

        formatDate(dateString) {
            if (!dateString) return 'N/A';
            return new Date(dateString).toLocaleDateString('id-ID', {
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            });
        },

        formatNumber(num) {
            if (!num && num !== 0) return '0';
            return new Intl.NumberFormat('id-ID').format(num);
        },

        formatMetric(metric) {
            const labels = {
                'users': 'Users',
                'products': 'Products',
                'branches': 'Branches',
                'orders': 'Orders',
                'storage_mb': 'Storage (MB)'
            };
            return labels[metric] || metric;
        }
    }
}
</script>
@endpush
@endsection
