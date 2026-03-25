@extends('layouts.app')

@section('title', 'Sales Analytics | SAGA TOKO APP')

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-gray-900 p-6" x-data="salesAnalytics()">
    <!-- Header -->
    <div class="max-w-7xl mx-auto mb-8">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-12 h-12 bg-gradient-to-br from-orange-500 to-red-600 rounded-2xl flex items-center justify-center shadow-lg shadow-orange-500/30">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
            </div>
            <div>
                <h1 class="text-3xl font-bold text-gray-800 dark:text-white">Sales Analytics</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">Analisis performa penjualan dan laporan</p>
            </div>
        </div>
    </div>

    <!-- Period Selector -->
    <div class="max-w-7xl mx-auto mb-8 bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="flex flex-wrap items-center gap-4">
            <button @click="setPeriod('today')" :class="period === 'today' ? 'bg-brand-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300'" class="px-4 py-2 rounded-xl font-medium transition-all">Today</button>
            <button @click="setPeriod('week')" :class="period === 'week' ? 'bg-brand-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300'" class="px-4 py-2 rounded-xl font-medium transition-all">This Week</button>
            <button @click="setPeriod('month')" :class="period === 'month' ? 'bg-brand-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300'" class="px-4 py-2 rounded-xl font-medium transition-all">This Month</button>
            <button @click="setPeriod('year')" :class="period === 'year' ? 'bg-brand-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300'" class="px-4 py-2 rounded-xl font-medium transition-all">This Year</button>
            <div class="flex-1"></div>
            <button @click="exportReport" class="px-6 py-2.5 bg-green-600 text-white rounded-xl font-semibold hover:bg-green-700 transition-all flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Export
            </button>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="max-w-7xl mx-auto mb-8 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-gradient-to-br from-brand-500 to-indigo-600 rounded-2xl p-6 text-white shadow-xl">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg></div>
                <span class="text-2xl font-bold" x-text="formatCurrency(stats.revenue)"></span>
            </div>
            <p class="text-brand-100 text-sm">Total Revenue</p>
            <p class="text-brand-200 text-xs mt-1">↑ 12.5% from last period</p>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-xl flex items-center justify-center"><svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg></div>
                <span class="text-3xl font-bold text-gray-800 dark:text-white" x-text="stats.orders"></span>
            </div>
            <p class="text-gray-500 dark:text-gray-400 text-sm">Total Orders</p>
            <p class="text-green-600 text-xs mt-1">↑ 8.2% from last period</p>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-xl flex items-center justify-center"><svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg></div>
                <span class="text-3xl font-bold text-gray-800 dark:text-white" x-text="stats.customers"></span>
            </div>
            <p class="text-gray-500 dark:text-gray-400 text-sm">Active Customers</p>
            <p class="text-blue-600 text-xs mt-1">↑ 5.3% from last period</p>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900/30 rounded-xl flex items-center justify-center"><svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg></div>
                <span class="text-3xl font-bold text-gray-800 dark:text-white" x-text="stats.avgOrder + '%'"></span>
            </div>
            <p class="text-gray-500 dark:text-gray-400 text-sm">Avg Order Value</p>
            <p class="text-purple-600 text-xs mt-1">↑ 3.1% from last period</p>
        </div>
    </div>

    <!-- Charts -->
    <div class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-4">Sales Trend</h3>
            <div class="h-64 bg-gradient-to-br from-brand-50 to-indigo-50 dark:from-gray-900 dark:to-gray-900 rounded-xl flex items-center justify-center">
                <div class="text-center">
                    <div class="flex items-end justify-center gap-2 h-40">
                        <div class="w-8 bg-brand-500 rounded-t-lg" style="height: 60%"></div>
                        <div class="w-8 bg-brand-400 rounded-t-lg" style="height: 80%"></div>
                        <div class="w-8 bg-brand-600 rounded-t-lg" style="height: 45%"></div>
                        <div class="w-8 bg-brand-500 rounded-t-lg" style="height: 90%"></div>
                        <div class="w-8 bg-brand-400 rounded-t-lg" style="height: 70%"></div>
                        <div class="w-8 bg-brand-600 rounded-t-lg" style="height: 85%"></div>
                        <div class="w-8 bg-brand-500 rounded-t-lg" style="height: 65%"></div>
                    </div>
                    <p class="text-gray-500 dark:text-gray-400 text-sm mt-4">📈 Sales Trend Chart (Last 7 Days)</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-4">Category Performance</h3>
            <div class="h-64 bg-gradient-to-br from-green-50 to-emerald-50 dark:from-gray-900 dark:to-gray-900 rounded-xl flex items-center justify-center">
                <div class="w-full px-8 space-y-3">
                    <div class="flex items-center gap-3">
                        <span class="text-sm text-gray-600 dark:text-gray-400 w-24">Food</span>
                        <div class="flex-1 bg-gray-200 dark:bg-gray-700 rounded-full h-3"><div class="bg-green-500 h-3 rounded-full" style="width: 75%"></div></div>
                        <span class="text-sm font-semibold w-12 text-right">75%</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="text-sm text-gray-600 dark:text-gray-400 w-24">Beverage</span>
                        <div class="flex-1 bg-gray-200 dark:bg-gray-700 rounded-full h-3"><div class="bg-blue-500 h-3 rounded-full" style="width: 60%"></div></div>
                        <span class="text-sm font-semibold w-12 text-right">60%</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="text-sm text-gray-600 dark:text-gray-400 w-24">Snacks</span>
                        <div class="flex-1 bg-gray-200 dark:bg-gray-700 rounded-full h-3"><div class="bg-purple-500 h-3 rounded-full" style="width: 45%"></div></div>
                        <span class="text-sm font-semibold w-12 text-right">45%</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="text-sm text-gray-600 dark:text-gray-400 w-24">Others</span>
                        <div class="flex-1 bg-gray-200 dark:bg-gray-700 rounded-full h-3"><div class="bg-orange-500 h-3 rounded-full" style="width: 30%"></div></div>
                        <span class="text-sm font-semibold w-12 text-right">30%</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Products Table -->
    <div class="max-w-7xl mx-auto bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-xl font-bold text-gray-800 dark:text-white">Top Selling Products</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-gray-900/50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Product</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Category</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Units Sold</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Revenue</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Stock Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    <template x-for="product in topProducts" :key="product.id">
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/30">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-gradient-to-br from-brand-400 to-indigo-500 rounded-lg flex items-center justify-center text-white font-bold" x-text="product.name.charAt(0)"></div>
                                    <span class="font-semibold text-gray-800 dark:text-white" x-text="product.name"></span>
                                </div>
                            </td>
                            <td class="px-6 py-4"><span class="text-gray-700 dark:text-gray-300" x-text="product.category"></span></td>
                            <td class="px-6 py-4"><span class="font-semibold text-gray-800 dark:text-white" x-text="product.sold"></span></td>
                            <td class="px-6 py-4"><span class="font-bold text-green-600" x-text="formatCurrency(product.revenue)"></span></td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold"
                                    :class="{'bg-green-100 text-green-700': product.stock > 50, 'bg-yellow-100 text-yellow-700': product.stock <= 50 && product.stock > 10, 'bg-red-100 text-red-700': product.stock <= 10}"
                                    x-text="product.stock > 50 ? 'In Stock' : product.stock > 10 ? 'Low Stock' : 'Out of Stock'">
                                </span>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script>
function salesAnalytics() {
    return {
        period: 'month',
        stats: { revenue: 125750000, orders: 1247, customers: 856, avgOrder: 100800 },
        topProducts: [
            { id: 1, name: 'Product A', category: 'Food', sold: 523, revenue: 25750000, stock: 150 },
            { id: 2, name: 'Product B', category: 'Beverage', sold: 412, revenue: 18500000, stock: 85 },
            { id: 3, name: 'Product C', category: 'Snacks', sold: 387, revenue: 15200000, stock: 42 },
            { id: 4, name: 'Product D', category: 'Food', sold: 298, revenue: 12800000, stock: 120 },
            { id: 5, name: 'Product E', category: 'Beverage', sold: 245, revenue: 9500000, stock: 8 }
        ],
        setPeriod(p) { this.period = p; },
        formatCurrency(amount) {
            return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(amount);
        },
        exportReport() { Swal.fire({title: 'Export Report', text: 'Generating Excel report...', icon: 'success', timer: 1500, showConfirmButton: false}); }
    }
}
</script>
@endpush
@endsection
