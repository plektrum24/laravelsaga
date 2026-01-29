@extends('layouts.app')

@section('title', 'Admin Reports')

@push('head')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endpush

@section('content')
    <div x-data="adminReports()" x-init="init()">
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Global Reports</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Analytics and reports across all tenants</p>
        </div>

        <!-- Filter -->
        <div class="flex flex-wrap items-center gap-4 mb-6">
            <select x-model="period" @change="fetchData()"
                class="px-4 py-2.5 border border-gray-200 rounded-xl dark:bg-gray-800 dark:border-gray-700 dark:text-white">
                <option value="week">Last 7 Days</option>
                <option value="month">This Month</option>
                <option value="year">This Year</option>
            </select>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <p class="text-sm text-gray-500 dark:text-gray-400">Total Revenue</p>
                <h3 class="text-2xl font-bold text-gray-800 dark:text-white mt-1"
                    x-text="formatCurrency(stats.totalRevenue)">Rp 0</h3>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <p class="text-sm text-gray-500 dark:text-gray-400">Total Transactions</p>
                <h3 class="text-2xl font-bold text-blue-600 mt-1" x-text="stats.totalTransactions">0</h3>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <p class="text-sm text-gray-500 dark:text-gray-400">Active Tenants</p>
                <h3 class="text-2xl font-bold text-green-600 mt-1" x-text="stats.activeTenants">0</h3>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <p class="text-sm text-gray-500 dark:text-gray-400">Total Users</p>
                <h3 class="text-2xl font-bold text-purple-600 mt-1" x-text="stats.totalUsers">0</h3>
            </div>
        </div>

        <!-- Charts -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- Revenue Chart -->
            <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">Revenue Trend</h3>
                <div class="h-64">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>

            <!-- Tenant Performance -->
            <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">Revenue by Tenant</h3>
                <div class="h-64">
                    <canvas id="tenantChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Tenant Revenue Table -->
        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] overflow-hidden">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Revenue by Tenant</h3>
            </div>
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-gray-800/50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tenant</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Revenue</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Transactions</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Avg. Transaction</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    <template x-for="tenant in tenantRevenue" :key="tenant.tenant_id">
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/30">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-10 h-10 bg-brand-100 rounded-lg flex items-center justify-center dark:bg-brand-900/30">
                                        <span class="text-brand-600 font-semibold"
                                            x-text="tenant.tenant_name.charAt(0)">T</span>
                                    </div>
                                    <span class="font-medium text-gray-800 dark:text-white"
                                        x-text="tenant.tenant_name"></span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-right font-semibold text-gray-800 dark:text-white"
                                x-text="formatCurrency(tenant.revenue)"></td>
                            <td class="px-6 py-4 text-right text-gray-600 dark:text-gray-300" x-text="tenant.transactions">
                            </td>
                            <td class="px-6 py-4 text-right text-gray-600 dark:text-gray-300"
                                x-text="formatCurrency(tenant.transactions > 0 ? tenant.revenue / tenant.transactions : 0)">
                            </td>
                        </tr>
                    </template>
                    <tr x-show="tenantRevenue.length === 0">
                        <td colspan="4" class="px-6 py-8 text-center text-gray-400">No revenue data available</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        function adminReports() {
            return {
                period: 'month',
                stats: { totalRevenue: 0, totalTransactions: 0, activeTenants: 0, totalUsers: 0 },
                tenantRevenue: [],
                revenueChart: null,
                tenantChart: null,

                async init() {
                    await this.fetchData();
                },

                async fetchData() {
                    const token = localStorage.getItem('saga_token');
                    const headers = { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' };

                    try {
                        const [overviewRes, revenueRes] = await Promise.all([
                            fetch('/api/admin/analytics/overview', { headers }),
                            fetch(`/api/admin/analytics/revenue?period=${this.period}`, { headers })
                        ]);

                        const overviewData = await overviewRes.json();
                        const revenueData = await revenueRes.json();

                        if (overviewData.success) {
                            this.stats.activeTenants = overviewData.data.tenants.active;
                            this.stats.totalUsers = overviewData.data.users.total;
                        }

                        if (revenueData.success) {
                            this.stats.totalRevenue = revenueData.data.totalRevenue;
                            this.stats.totalTransactions = revenueData.data.totalTransactions;
                            this.tenantRevenue = revenueData.data.revenueByTenant || [];
                            this.updateCharts(revenueData.data);
                        }
                    } catch (error) {
                        console.error('Fetch error:', error);
                    }
                },

                updateCharts(data) {
                    // Revenue Chart
                    const revenueCtx = document.getElementById('revenueChart');
                    if (this.revenueChart) this.revenueChart.destroy();

                    const dailyRevenue = data.dailyRevenue || [];
                    this.revenueChart = new Chart(revenueCtx, {
                        type: 'line',
                        data: {
                            labels: dailyRevenue.map(d => d.date),
                            datasets: [{
                                label: 'Revenue',
                                data: dailyRevenue.map(d => d.total),
                                borderColor: '#465fff',
                                backgroundColor: 'rgba(70, 95, 255, 0.1)',
                                fill: true,
                                tension: 0.4
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: { legend: { display: false } }
                        }
                    });

                    // Tenant Chart
                    const tenantCtx = document.getElementById('tenantChart');
                    if (this.tenantChart) this.tenantChart.destroy();

                    const tenantData = this.tenantRevenue.slice(0, 5);
                    this.tenantChart = new Chart(tenantCtx, {
                        type: 'doughnut',
                        data: {
                            labels: tenantData.map(t => t.tenant_name),
                            datasets: [{
                                data: tenantData.map(t => t.revenue),
                                backgroundColor: ['#465fff', '#22c55e', '#f59e0b', '#ef4444', '#8b5cf6']
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false
                        }
                    });
                },

                formatCurrency(amount) {
                    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(amount || 0);
                }
            }
        }
    </script>
@endsection