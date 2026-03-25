@extends('layouts.app')

@section('title', 'Real-time Analytics Dashboard')

@section('content')
<div x-data="realtimeDashboard()" x-init="init()" class="space-y-6">
    <!-- Header -->
    <div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-2xl p-6 shadow-lg">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-white flex items-center gap-3">
                    <span class="p-2 bg-white/20 rounded-lg">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </span>
                    Real-time Analytics
                </h1>
                <p class="text-blue-100 text-sm mt-2">Live dashboard - Auto refresh every 10 seconds</p>
            </div>
            <div class="flex items-center gap-2 text-white">
                <span class="flex items-center gap-2">
                    <span class="relative flex h-3 w-3">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
                    </span>
                    <span class="text-sm font-medium">Live</span>
                </span>
                <button @click="fetchData()" class="ml-4 px-4 py-2 bg-white/20 hover:bg-white/30 rounded-lg transition-colors flex items-center gap-2">
                    <svg class="w-4 h-4" :class="{'animate-spin': loading}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    Refresh
                </button>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Revenue Today -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-gray-100 dark:border-gray-700">
            <div class="flex items-center justify-between mb-4">
                <div class="p-2 bg-green-100 dark:bg-green-900/30 rounded-xl">
                    <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <span class="text-xs font-medium px-2 py-1 rounded-full" :class="revenue.growth >= 0 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'" x-text="revenue.growth_formatted"></span>
            </div>
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Revenue Today</p>
                <h3 class="text-2xl font-bold text-gray-800 dark:text-white mt-1" x-text="revenue.formatted"></h3>
                <p class="text-xs text-gray-500 mt-2" x-text="revenue.transactions + ' transactions'"></p>
            </div>
        </div>

        <!-- Active Users -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-gray-100 dark:border-gray-700">
            <div class="flex items-center justify-between mb-4">
                <div class="p-2 bg-blue-100 dark:bg-blue-900/30 rounded-xl">
                    <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                </div>
                <span class="text-xs text-gray-500 dark:text-gray-400" x-text="'Last: ' + users.last_updated"></span>
            </div>
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Active Users</p>
                <h3 class="text-2xl font-bold text-gray-800 dark:text-white mt-1" x-text="users.total"></h3>
                <p class="text-xs text-gray-500 mt-2" x-text="users.cashiers + ' cashiers'"></p>
            </div>
        </div>

        <!-- Current Hour -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-gray-100 dark:border-gray-700">
            <div class="flex items-center justify-between mb-4">
                <div class="p-2 bg-purple-100 dark:bg-purple-900/30 rounded-xl">
                    <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <span class="text-xs text-gray-500 dark:text-gray-400" x-text="'Hour ' + hourly.current_hour.hour"></span>
            </div>
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Current Hour</p>
                <h3 class="text-2xl font-bold text-gray-800 dark:text-white mt-1" x-text="hourly.current_hour.formatted"></h3>
                <p class="text-xs text-gray-500 mt-2" x-text="hourly.current_hour.transactions + ' transactions'"></p>
            </div>
        </div>

        <!-- Live Sales Count -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-gray-100 dark:border-gray-700">
            <div class="flex items-center justify-between mb-4">
                <div class="p-2 bg-orange-100 dark:bg-orange-900/30 rounded-xl">
                    <svg class="w-6 h-6 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                    </svg>
                </div>
                <span class="text-xs text-gray-500 dark:text-gray-400">Last 50</span>
            </div>
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Recent Sales</p>
                <h3 class="text-2xl font-bold text-gray-800 dark:text-white mt-1" x-text="sales.length"></h3>
                <p class="text-xs text-gray-500 mt-2">Latest transactions</p>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Live Sales Feed -->
        <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="p-6 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                <h3 class="text-lg font-bold text-gray-800 dark:text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                    Live Sales Feed
                </h3>
                <span class="text-xs text-gray-500 dark:text-gray-400">Real-time</span>
            </div>
            <div class="overflow-y-auto" style="max-height: 500px;">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-700 sticky top-0">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Time</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Reference</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Customer</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Amount</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        <template x-for="sale in sales" :key="sale.id">
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                <td class="px-4 py-3 text-xs text-gray-500 dark:text-gray-400" x-text="sale.created_at"></td>
                                <td class="px-4 py-3 text-xs font-medium text-gray-800 dark:text-white" x-text="sale.reference_number"></td>
                                <td class="px-4 py-3 text-xs text-gray-600 dark:text-gray-300" x-text="sale.customer_name"></td>
                                <td class="px-4 py-3 text-xs font-semibold text-green-600 dark:text-green-400" x-text="'Rp ' + formatNumber(sale.total_amount)"></td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-1 text-xs rounded-full" :class="{
                                        'bg-green-100 text-green-700': sale.status === 'completed',
                                        'bg-yellow-100 text-yellow-700': sale.status === 'pending',
                                        'bg-red-100 text-red-700': sale.status === 'cancelled'
                                    }" x-text="sale.status"></span>
                                </td>
                            </tr>
                        </template>
                        <tr x-show="sales.length === 0">
                            <td colspan="5" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                No recent sales
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Top Products -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="p-6 border-b border-gray-100 dark:border-gray-700">
                <h3 class="text-lg font-bold text-gray-800 dark:text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path>
                    </svg>
                    Top Products (Last Hour)
                </h3>
            </div>
            <div class="overflow-y-auto" style="max-height: 500px;">
                <div class="divide-y divide-gray-100 dark:divide-gray-700">
                    <template x-for="(product, index) in topProducts" :key="product.id">
                        <div class="p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors flex items-center gap-3">
                            <div class="flex-shrink-0 w-8 h-8 rounded-full bg-gradient-to-br from-orange-400 to-orange-600 flex items-center justify-center text-white font-bold text-sm" x-text="index + 1"></div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-gray-800 dark:text-white truncate" x-text="product.name"></p>
                                <p class="text-xs text-gray-500 dark:text-gray-400" x-text="product.sku"></p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-bold text-orange-600 dark:text-orange-400" x-text="product.total_sold + ' sold'"></p>
                                <p class="text-xs text-gray-500 dark:text-gray-400" x-text="'Rp ' + formatNumber(product.total_revenue)"></p>
                            </div>
                        </div>
                    </template>
                    <div x-show="topProducts.length === 0" class="p-8 text-center text-gray-500 dark:text-gray-400">
                        No products sold in the last hour
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function realtimeDashboard() {
    return {
        loading: false,
        revenue: {
            amount: 0,
            formatted: 'Rp 0',
            transactions: 0,
            growth: 0,
            growth_formatted: '0%'
        },
        users: {
            total: 0,
            cashiers: 0,
            last_updated: '00:00:00'
        },
        hourly: {
            current_hour: {
                hour: 0,
                amount: 0,
                formatted: 'Rp 0',
                transactions: 0
            }
        },
        sales: [],
        topProducts: [],
        refreshInterval: null,

        async init() {
            await this.fetchData();
            // Auto refresh every 10 seconds
            this.refreshInterval = setInterval(() => this.fetchData(), 10000);
        },

        async fetchData() {
            if (this.loading) return;
            
            this.loading = true;
            const token = localStorage.getItem('saga_token');
            
            try {
                const response = await fetch('/api/analytics/realtime', {
                    headers: { 'Authorization': 'Bearer ' + token }
                });
                const data = await response.json();
                
                if (data.success) {
                    this.revenue = data.data.revenue_today;
                    this.users = data.data.active_users;
                    this.hourly = data.data.hourly_stats;
                    this.sales = data.data.live_sales;
                    this.topProducts = data.data.top_products;
                }
            } catch (error) {
                console.error('Fetch error:', error);
            } finally {
                this.loading = false;
            }
        },

        formatNumber(num) {
            return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        }
    }
}
</script>
@endsection
