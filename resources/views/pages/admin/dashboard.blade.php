@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
    <div x-data="adminDashboard()" x-init="init()">
        <!-- Page Title -->
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Global Dashboard</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Overview of all tenants and revenue</p>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4 mb-6">
            <!-- Total Tenants -->
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Tenants</p>
                        <h3 class="text-2xl font-bold text-gray-800 dark:text-white mt-1" x-text="overview.tenants.total">0
                        </h3>
                    </div>
                    <div class="flex items-center justify-center w-12 h-12 bg-blue-50 rounded-xl dark:bg-blue-900/20">
                        <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                            </path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Active Tenants -->
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Active Tenants</p>
                        <h3 class="text-2xl font-bold text-green-500 mt-1" x-text="overview.tenants.active">0</h3>
                    </div>
                    <div class="flex items-center justify-center w-12 h-12 bg-green-50 rounded-xl dark:bg-green-900/20">
                        <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Total Users -->
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Users</p>
                        <h3 class="text-2xl font-bold text-gray-800 dark:text-white mt-1" x-text="overview.users.total">0
                        </h3>
                    </div>
                    <div class="flex items-center justify-center w-12 h-12 bg-purple-50 rounded-xl dark:bg-purple-900/20">
                        <svg class="w-6 h-6 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z">
                            </path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Total Revenue -->
            <div class="rounded-2xl border border-gray-200 bg-gradient-to-br from-brand-500 to-brand-600 p-5 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-white/80">Total Revenue (Month)</p>
                        <h3 class="text-2xl font-bold mt-1" x-text="formatCurrency(revenue.totalRevenue)">Rp 0</h3>
                    </div>
                    <div class="flex items-center justify-center w-12 h-12 bg-white/20 rounded-xl">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                            </path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Revenue by Tenant & Quick Actions -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- Revenue Table -->
            <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Revenue by Tenant</h3>
                    <a href="{{ route('admin.reports.index') }}" class="text-sm text-brand-500 hover:underline">View All
                        →</a>
                </div>
                <div class="space-y-3">
                    <template x-for="(tenant, index) in revenue.revenueByTenant.slice(0, 5)" :key="index">
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg dark:bg-gray-800">
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-10 h-10 bg-brand-100 rounded-lg flex items-center justify-center dark:bg-brand-900/30">
                                    <span class="text-brand-600 font-semibold"
                                        x-text="tenant.tenant_name.charAt(0)">T</span>
                                </div>
                                <span class="font-medium text-gray-800 dark:text-white"
                                    x-text="tenant.tenant_name">Tenant</span>
                            </div>
                            <span class="font-semibold text-gray-800 dark:text-white"
                                x-text="formatCurrency(tenant.revenue)">Rp 0</span>
                        </div>
                    </template>
                    <div x-show="revenue.revenueByTenant.length === 0" class="text-center py-8 text-gray-400">
                        No revenue data available
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">Quick Actions</h3>
                <div class="space-y-3">
                    <a href="{{ route('admin.tenants.index') }}"
                        class="flex items-center gap-4 p-4 bg-gray-50 rounded-xl hover:bg-gray-100 transition dark:bg-gray-800 dark:hover:bg-gray-700">
                        <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center dark:bg-blue-900/30">
                            <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="font-medium text-gray-800 dark:text-white">Add New Tenant</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Create a new branch/store</p>
                        </div>
                    </a>
                    <a href="{{ route('admin.tenants.index') }}"
                        class="flex items-center gap-4 p-4 bg-gray-50 rounded-xl hover:bg-gray-100 transition dark:bg-gray-800 dark:hover:bg-gray-700">
                        <div
                            class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center dark:bg-orange-900/30">
                            <svg class="w-6 h-6 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z">
                                </path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="font-medium text-gray-800 dark:text-white">Manage Tenants</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">View and edit all tenants</p>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        function adminDashboard() {
            return {
                overview: { tenants: { total: 0, active: 0 }, users: { total: 0 } },
                revenue: { totalRevenue: 0, revenueByTenant: [], dailyRevenue: [] },
                isLoading: true,

                async init() {
                    await this.fetchOverview();
                },

                async fetchOverview() {
                    try {
                        const token = localStorage.getItem('saga_token');
                        const headers = {
                            'Authorization': 'Bearer ' + token,
                            'Accept': 'application/json'
                        };

                        const [overviewRes, revenueRes] = await Promise.all([
                            fetch('/api/admin/analytics/overview', { headers }),
                            fetch('/api/admin/analytics/revenue?period=month', { headers })
                        ]);

                        const overviewData = await overviewRes.json();
                        const revenueData = await revenueRes.json();

                        if (overviewData.success) this.overview = overviewData.data;
                        if (revenueData.success) this.revenue = revenueData.data;
                    } catch (error) {
                        console.error('Dashboard fetch error:', error);
                    } finally {
                        this.isLoading = false;
                    }
                },

                formatCurrency(amount) {
                    return new Intl.NumberFormat('id-ID', {
                        style: 'currency',
                        currency: 'IDR',
                        minimumFractionDigits: 0
                    }).format(amount || 0);
                }
            }
        }
    </script>
@endsection