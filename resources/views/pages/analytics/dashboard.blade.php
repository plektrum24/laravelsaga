@extends('layouts.app')

@section('title', 'Analytics Dashboard')

@section('content')
<div x-data="analyticsDashboard()" x-init="init()">
    <!-- Header -->
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Analytics Dashboard</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Real-time business metrics and insights</p>
        </div>
        <div class="flex gap-2">
            <input type="date" x-model="dateRange.from" @change="loadDashboard()" 
                class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 text-sm">
            <span class="self-center">-</span>
            <input type="date" x-model="dateRange.to" @change="loadDashboard()" 
                class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 text-sm">
            <button @click="loadDashboard()" class="px-4 py-2 bg-brand-600 text-white rounded-lg hover:bg-brand-700">
                Refresh
            </button>
        </div>
    </div>

    <!-- Loading State -->
    <div x-show="loading" class="text-center py-12">
        <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-brand-600"></div>
        <p class="mt-4 text-gray-500">Loading dashboard...</p>
    </div>

    <!-- Dashboard Content -->
    <div x-show="!loading" class="space-y-6">
        <!-- KPI Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <!-- Revenue Card -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 border-l-4 border-green-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Total Revenue</p>
                        <p class="text-2xl font-bold text-gray-800 dark:text-white" x-text="formatCurrency(kpis.revenue)"></p>
                        <p class="text-xs mt-2" :class="kpis.growth_percent >= 0 ? 'text-green-600' : 'text-red-600'">
                            <span x-text="kpis.growth_percent >= 0 ? '↑' : '↓'"></span>
                            <span x-text="Math.abs(kpis.growth_percent) + '% vs previous period'"></span>
                        </p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Orders Card -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 border-l-4 border-blue-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Total Orders</p>
                        <p class="text-2xl font-bold text-gray-800 dark:text-white" x-text="kpis.orders"></p>
                        <p class="text-xs mt-2 text-gray-500">
                            <span x-text="kpis.today_orders + ' today'"></span>
                        </p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Customers Card -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 border-l-4 border-purple-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Total Customers</p>
                        <p class="text-2xl font-bold text-gray-800 dark:text-white" x-text="kpis.customers"></p>
                        <p class="text-xs mt-2 text-gray-500">
                            <span x-text="'Avg: ' + formatCurrency(kpis.avg_order_value) + '/order'"></span>
                        </p>
                    </div>
                    <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Today's Performance Card -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 border-l-4 border-orange-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Today's Revenue</p>
                        <p class="text-2xl font-bold text-gray-800 dark:text-white" x-text="formatCurrency(kpis.today_revenue)"></p>
                        <p class="text-xs mt-2 text-gray-500">
                            <span x-text="kpis.today_orders + ' orders'"></span>
                        </p>
                    </div>
                    <div class="w-12 h-12 bg-orange-100 dark:bg-orange-900/30 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sales Trend Chart -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-4">Sales Trend</h3>
            <div id="salesChart" class="h-80"></div>
        </div>

        <!-- Top Products & Category Performance -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Top Products -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-4">Top Products</h3>
                <div class="space-y-3">
                    <template x-for="(product, index) in topProducts" :key="product.id">
                        <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                            <div class="flex items-center gap-3">
                                <span class="w-6 h-6 bg-brand-100 dark:bg-brand-900/30 rounded-full flex items-center justify-center text-xs font-bold text-brand-600" x-text="index + 1"></span>
                                <div>
                                    <p class="font-semibold text-gray-800 dark:text-white text-sm" x-text="product.name"></p>
                                    <p class="text-xs text-gray-500" x-text="product.sku"></p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="font-bold text-gray-800 dark:text-white text-sm" x-text="formatCurrency(product.total_revenue)"></p>
                                <p class="text-xs text-gray-500" x-text="product.total_qty + ' sold'"></p>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Category Performance -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-4">Category Performance</h3>
                <div id="categoryChart" class="h-64"></div>
            </div>
        </div>

        <!-- Customer Segments -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-4">Customer Segments</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <template x-for="segment in customerSegments" :key="segment.segment_value">
                    <div class="text-center p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                        <p class="text-2xl font-bold text-gray-800 dark:text-white" x-text="segment.count"></p>
                        <p class="text-xs text-gray-500 mt-1" x-text="segment.segment_value"></p>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
function analyticsDashboard() {
    return {
        loading: false,
        dateRange: {
            from: new Date().toISOString().split('T')[0],
            to: new Date().toISOString().split('T')[0]
        },
        kpis: {
            revenue: 0,
            orders: 0,
            customers: 0,
            avg_order_value: 0,
            today_revenue: 0,
            today_orders: 0,
            growth_percent: 0
        },
        salesTrend: [],
        topProducts: [],
        categoryPerformance: [],
        customerSegments: [],

        async init() {
            await this.loadDashboard();
        },

        async loadDashboard() {
            this.loading = true;
            const token = localStorage.getItem('saga_token');
            
            try {
                await Promise.all([
                    this.loadKPIs(token),
                    this.loadSalesTrend(token),
                    this.loadTopProducts(token),
                    this.loadCategoryPerformance(token),
                    this.loadCustomerSegments(token)
                ]);
            } catch (error) {
                console.error('Dashboard load error:', error);
            } finally {
                this.loading = false;
            }
        },

        async loadKPIs(token) {
            const url = `/api/analytics/kpis?from=${this.dateRange.from}&to=${this.dateRange.to}`;
            const res = await fetch(url, { headers: { 'Authorization': 'Bearer ' + token } });
            const data = await res.json();
            if (data.success) {
                this.kpis = data.data;
            }
        },

        async loadSalesTrend(token) {
            const res = await fetch('/api/analytics/sales-trend?period=30', { 
                headers: { 'Authorization': 'Bearer ' + token } 
            });
            const data = await res.json();
            if (data.success) {
                this.salesTrend = data.data;
                this.renderSalesChart();
            }
        },

        async loadTopProducts(token) {
            const res = await fetch('/api/analytics/top-products?limit=5', { 
                headers: { 'Authorization': 'Bearer ' + token } 
            });
            const data = await res.json();
            if (data.success) {
                this.topProducts = data.data;
            }
        },

        async loadCategoryPerformance(token) {
            const res = await fetch('/api/analytics/category-performance', { 
                headers: { 'Authorization': 'Bearer ' + token } 
            });
            const data = await res.json();
            if (data.success) {
                this.categoryPerformance = data.data;
                this.renderCategoryChart();
            }
        },

        async loadCustomerSegments(token) {
            const res = await fetch('/api/analytics/customer-segments', { 
                headers: { 'Authorization': 'Bearer ' + token } 
            });
            const data = await res.json();
            if (data.success) {
                this.customerSegments = data.data;
            }
        },

        renderSalesChart() {
            const options = {
                series: [{
                    name: 'Revenue',
                    data: this.salesTrend.map(item => ({
                        x: item.date,
                        y: parseFloat(item.revenue)
                    }))
                }],
                chart: {
                    type: 'area',
                    height: 320,
                    toolbar: { show: false }
                },
                colors: ['#4F46E5'],
                dataLabels: { enabled: false },
                stroke: { curve: 'smooth', width: 2 },
                fill: {
                    type: 'gradient',
                    gradient: {
                        shadeIntensity: 1,
                        opacityFrom: 0.7,
                        opacityTo: 0.2,
                        stops: [0, 90, 100]
                    }
                },
                xaxis: {
                    type: 'datetime',
                    labels: {
                        format: 'dd MMM'
                    }
                },
                yaxis: {
                    labels: {
                        formatter: (value) => 'Rp ' + (value / 1000000).toFixed(1) + 'M'
                    }
                },
                tooltip: {
                    x: { format: 'dd MMM yyyy' },
                    y: {
                        formatter: (value) => 'Rp ' + value.toLocaleString('id-ID')
                    }
                }
            };

            const chart = new ApexCharts(document.querySelector("#salesChart"), options);
            chart.render();
        },

        renderCategoryChart() {
            const options = {
                series: this.categoryPerformance.map(cat => cat.total_revenue),
                labels: this.categoryPerformance.map(cat => cat.name),
                chart: {
                    type: 'donut',
                    height: 256
                },
                colors: ['#4F46E5', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6'],
                plotOptions: {
                    pie: {
                        donut: {
                            size: '65%',
                            labels: {
                                show: true,
                                name: { show: true },
                                value: {
                                    show: true,
                                    formatter: (value) => 'Rp ' + (parseFloat(value) / 1000000).toFixed(1) + 'M'
                                }
                            }
                        }
                    }
                },
                dataLabels: { enabled: false },
                legend: { position: 'bottom' },
                tooltip: {
                    y: {
                        formatter: (value) => 'Rp ' + value.toLocaleString('id-ID')
                    }
                }
            };

            const chart = new ApexCharts(document.querySelector("#categoryChart"), options);
            chart.render();
        },

        formatCurrency(value) {
            return new Intl.NumberFormat('id-ID', { 
                style: 'currency', 
                currency: 'IDR',
                minimumFractionDigits: 0
            }).format(value);
        }
    }
}
</script>
@endsection
