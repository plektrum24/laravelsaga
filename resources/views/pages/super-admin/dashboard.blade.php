@extends('layouts.app')

@section('title', 'Super Admin Dashboard | SaaS Management')

@section('content')
<div x-data="saasDashboard()" x-init="init()" class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">SaaS Management Dashboard</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Monitor tenants, revenue, and subscription health</p>
        </div>
        <div class="flex gap-2">
            <button @click="refreshData()" class="btn-primary inline-flex items-center gap-2">
                <svg class="w-4 h-4" :class="{'animate-spin': loading}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                Refresh
            </button>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
        <!-- Total Tenants -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Tenants</p>
                    <h3 class="text-2xl font-bold text-gray-800 dark:text-white mt-1" x-text="stats.total_tenants">0</h3>
                    <p class="text-xs text-green-500 mt-1" x-show="stats.active_tenants">
                        <span x-text="stats.active_tenants"></span> active
                    </p>
                </div>
                <div class="flex items-center justify-center w-12 h-12 bg-blue-50 rounded-xl dark:bg-blue-900/20">
                    <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Monthly Revenue -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Monthly Revenue</p>
                    <h3 class="text-2xl font-bold text-gray-800 dark:text-white mt-1" x-text="formatCurrency(stats.monthly_revenue)">Rp 0</h3>
                    <p class="text-xs text-gray-500 mt-1">This month</p>
                </div>
                <div class="flex items-center justify-center w-12 h-12 bg-green-50 rounded-xl dark:bg-green-900/20">
                    <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Total Revenue -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Revenue</p>
                    <h3 class="text-2xl font-bold text-gray-800 dark:text-white mt-1" x-text="formatCurrency(stats.total_revenue)">Rp 0</h3>
                    <p class="text-xs text-gray-500 mt-1">All time</p>
                </div>
                <div class="flex items-center justify-center w-12 h-12 bg-purple-50 rounded-xl dark:bg-purple-900/20">
                    <svg class="w-6 h-6 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Open Tickets -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Open Tickets</p>
                    <h3 class="text-2xl font-bold text-gray-800 dark:text-white mt-1" x-text="stats.open_tickets">0</h3>
                    <p class="text-xs text-red-500 mt-1" x-show="stats.urgent_tickets > 0">
                        <span x-text="stats.urgent_tickets"></span> urgent
                    </p>
                </div>
                <div class="flex items-center justify-center w-12 h-12 bg-orange-50 rounded-xl dark:bg-orange-900/20">
                    <svg class="w-6 h-6 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Stats -->
    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
        <!-- Unpaid Invoices -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Unpaid Invoices</p>
                    <h3 class="text-2xl font-bold text-gray-800 dark:text-white mt-1" x-text="stats.unpaid_invoices">0</h3>
                    <p class="text-xs text-red-500 mt-1" x-show="stats.overdue_invoices > 0">
                        <span x-text="stats.overdue_invoices"></span> overdue
                    </p>
                </div>
                <div class="flex items-center justify-center w-12 h-12 bg-red-50 rounded-xl dark:bg-red-900/20">
                    <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Suspended Tenants -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Suspended Tenants</p>
                    <h3 class="text-2xl font-bold text-gray-800 dark:text-white mt-1" x-text="stats.suspended_tenants">0</h3>
                    <p class="text-xs text-gray-500 mt-1">Require attention</p>
                </div>
                <div class="flex items-center justify-center w-12 h-12 bg-gray-50 rounded-xl dark:bg-gray-900/20">
                    <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Total Invoices -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Invoices</p>
                    <h3 class="text-2xl font-bold text-gray-800 dark:text-white mt-1" x-text="stats.total_invoices">0</h3>
                    <p class="text-xs text-gray-500 mt-1">All time</p>
                </div>
                <div class="flex items-center justify-center w-12 h-12 bg-indigo-50 rounded-xl dark:bg-indigo-900/20">
                    <svg class="w-6 h-6 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts and Tables -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <!-- Revenue Trend Chart -->
        <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">Revenue Trend (6 Months)</h3>
            <canvas id="revenueChart" height="200"></canvas>
        </div>

        <!-- Plan Distribution -->
        <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">Plan Distribution</h3>
            <canvas id="planChart" height="200"></canvas>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <!-- Recent Tenants -->
        <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Recent Tenants</h3>
                <a href="/super-admin/tenants" class="text-sm text-brand-500 hover:text-brand-600">View All</a>
            </div>
            <div class="space-y-3">
                <template x-for="tenant in recentTenants" :key="tenant.id">
                    <div class="flex items-center justify-between p-3 rounded-lg bg-gray-50 dark:bg-gray-900/50">
                        <div>
                            <p class="font-medium text-gray-800 dark:text-white" x-text="tenant.name"></p>
                            <p class="text-xs text-gray-500" x-text="tenant.email"></p>
                        </div>
                        <span class="px-2 py-1 text-xs rounded-full"
                              :class="{
                                  'bg-green-100 text-green-700': tenant.subscription?.status === 'active',
                                  'bg-yellow-100 text-yellow-700': tenant.subscription?.status === 'trial',
                                  'bg-red-100 text-red-700': tenant.subscription?.status === 'suspended'
                              }"
                              x-text="tenant.subscription?.status || 'N/A'">
                        </span>
                    </div>
                </template>
                <p x-show="recentTenants.length === 0" class="text-center text-gray-500 py-4">No tenants yet</p>
            </div>
        </div>

        <!-- Recent Tickets -->
        <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Recent Tickets</h3>
                <a href="/super-admin/tickets" class="text-sm text-brand-500 hover:text-brand-600">View All</a>
            </div>
            <div class="space-y-3">
                <template x-for="ticket in recentTickets" :key="ticket.id">
                    <div class="flex items-center justify-between p-3 rounded-lg bg-gray-50 dark:bg-gray-900/50">
                        <div class="flex-1">
                            <p class="font-medium text-gray-800 dark:text-white" x-text="ticket.subject"></p>
                            <p class="text-xs text-gray-500" x-text="ticket.tenant?.name || 'Unknown'"></p>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="px-2 py-1 text-xs rounded-full capitalize"
                                  :class="{
                                      'bg-blue-100 text-blue-700': ticket.status === 'open',
                                      'bg-purple-100 text-purple-700': ticket.status === 'in_progress',
                                      'bg-orange-100 text-orange-700': ticket.status === 'waiting_customer',
                                      'bg-green-100 text-green-700': ticket.status === 'resolved'
                                  }"
                                  x-text="ticket.status">
                            </span>
                            <span class="px-2 py-1 text-xs rounded-full capitalize"
                                  :class="{
                                      'bg-gray-100 text-gray-700': ticket.priority === 'low',
                                      'bg-blue-100 text-blue-700': ticket.priority === 'medium',
                                      'bg-orange-100 text-orange-700': ticket.priority === 'high',
                                      'bg-red-100 text-red-700': ticket.priority === 'urgent'
                                  }"
                                  x-text="ticket.priority">
                            </span>
                        </div>
                    </div>
                </template>
                <p x-show="recentTickets.length === 0" class="text-center text-gray-500 py-4">No tickets</p>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
function saasDashboard() {
    return {
        loading: false,
        stats: {
            total_tenants: 0,
            active_tenants: 0,
            suspended_tenants: 0,
            total_revenue: 0,
            monthly_revenue: 0,
            total_invoices: 0,
            unpaid_invoices: 0,
            overdue_invoices: 0,
            open_tickets: 0,
            urgent_tickets: 0
        },
        revenueTrend: [],
        tenantGrowth: [],
        planDistribution: [],
        recentTenants: [],
        recentTickets: [],
        revenueChart: null,
        planChart: null,

        async init() {
            await this.refreshData();
        },

        async refreshData() {
            this.loading = true;
            try {
                const token = localStorage.getItem('saga_token');
                const headers = { 'Authorization': 'Bearer ' + token };

                // Fetch dashboard stats
                const response = await fetch('/api/admin/dashboard/stats', { headers });
                const result = await response.json();

                if (result.success) {
                    this.stats = result.data.stats;
                    this.revenueTrend = result.data.revenue_trend;
                    this.tenantGrowth = result.data.tenant_growth;
                    this.planDistribution = result.data.plan_distribution;
                    this.recentTenants = result.data.recent_tenants;
                    this.recentTickets = result.data.recent_tickets;

                    this.$nextTick(() => {
                        this.renderCharts();
                    });
                }
            } catch (error) {
                console.error('Error fetching dashboard data:', error);
            } finally {
                this.loading = false;
            }
        },

        formatCurrency(amount) {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0
            }).format(amount);
        },

        renderCharts() {
            // Revenue Trend Chart
            const revenueCtx = document.getElementById('revenueChart').getContext('2d');
            if (this.revenueChart) {
                this.revenueChart.destroy();
            }
            this.revenueChart = new Chart(revenueCtx, {
                type: 'line',
                data: {
                    labels: this.revenueTrend.map(item => item.month),
                    datasets: [{
                        label: 'Revenue',
                        data: this.revenueTrend.map(item => item.revenue),
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: (value) => 'Rp ' + (value / 1000000).toFixed(0) + 'M'
                            }
                        }
                    }
                }
            });

            // Plan Distribution Chart
            const planCtx = document.getElementById('planChart').getContext('2d');
            if (this.planChart) {
                this.planChart.destroy();
            }
            this.planChart = new Chart(planCtx, {
                type: 'doughnut',
                data: {
                    labels: this.planDistribution.map(item => item.name),
                    datasets: [{
                        data: this.planDistribution.map(item => item.count),
                        backgroundColor: [
                            '#6b7280', // Free - Gray
                            '#3b82f6', // Starter - Blue
                            '#8b5cf6', // Pro - Purple
                            '#f59e0b'  // Enterprise - Amber
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        }
    }
}
</script>
@endpush
@endsection
