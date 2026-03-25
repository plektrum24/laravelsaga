@extends('layouts.app')

@section('title', 'Stock Analytics | SAGA TOKO APP')

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-gray-900 p-6" x-data="stockAnalytics()">
    <!-- Header -->
    <div class="max-w-8xl mx-auto mb-8">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-14 h-14 bg-gradient-to-br from-purple-500 to-pink-600 rounded-2xl flex items-center justify-center shadow-xl shadow-purple-500/30">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-gray-800 dark:text-white">Stock Product Analytics</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Analisis performa dan pergerakan stok produk</p>
                </div>
            </div>
            <button @click="exportReport" class="px-6 py-3 bg-gradient-to-r from-purple-600 to-pink-600 text-white rounded-xl font-semibold hover:from-purple-700 hover:to-pink-700 transition-all shadow-lg shadow-purple-500/30 flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Export Report
            </button>
        </div>
    </div>

    <!-- KPI Cards -->
    <div class="max-w-8xl mx-auto mb-8 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Products -->
        <div class="bg-gradient-to-br from-purple-500 to-pink-600 rounded-2xl p-6 text-white shadow-xl shadow-purple-500/20">
            <div class="flex items-center justify-between mb-4">
                <div class="w-14 h-14 bg-white/20 backdrop-blur-sm rounded-2xl flex items-center justify-center">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                </div>
                <div class="flex items-center gap-1 bg-white/20 backdrop-blur-sm px-3 py-1.5 rounded-full">
                    <span class="text-sm font-bold">1,247</span>
                    <span class="text-xs">SKUs</span>
                </div>
            </div>
            <p class="text-purple-100 text-sm font-medium mb-1">Total Products</p>
            <h3 class="text-4xl font-bold">Active in inventory</h3>
        </div>

        <!-- Fast Moving -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between mb-4">
                <div class="w-14 h-14 bg-gradient-to-br from-green-100 to-emerald-100 dark:from-green-900/30 dark:to-emerald-900/30 rounded-2xl flex items-center justify-center">
                    <svg class="w-7 h-7 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                    </svg>
                </div>
                <div class="flex items-center gap-1 bg-green-100 dark:bg-green-900/30 px-3 py-1.5 rounded-full">
                    <span class="text-sm font-bold text-green-600">+12%</span>
                </div>
            </div>
            <p class="text-gray-500 dark:text-gray-400 text-sm font-medium mb-1">Fast Moving</p>
            <h3 class="text-4xl font-bold text-gray-800 dark:text-white">312 <span class="text-lg text-gray-500 font-normal">products</span></h3>
            <p class="text-green-600 text-xs mt-2">High turnover rate</p>
        </div>

        <!-- Slow Moving -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between mb-4">
                <div class="w-14 h-14 bg-gradient-to-br from-amber-100 to-orange-100 dark:from-amber-900/30 dark:to-orange-900/30 rounded-2xl flex items-center justify-center">
                    <svg class="w-7 h-7 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="flex items-center gap-1 bg-amber-100 dark:bg-amber-900/30 px-3 py-1.5 rounded-full">
                    <span class="text-sm font-bold text-amber-600">-5%</span>
                </div>
            </div>
            <p class="text-gray-500 dark:text-gray-400 text-sm font-medium mb-1">Slow Moving</p>
            <h3 class="text-4xl font-bold text-gray-800 dark:text-white">186 <span class="text-lg text-gray-500 font-normal">products</span></h3>
            <p class="text-amber-600 text-xs mt-2">> 60 days no movement</p>
        </div>

        <!-- Dead Stock -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between mb-4">
                <div class="w-14 h-14 bg-gradient-to-br from-red-100 to-rose-100 dark:from-red-900/30 dark:to-rose-900/30 rounded-2xl flex items-center justify-center">
                    <svg class="w-7 h-7 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                </div>
                <div class="flex items-center gap-1 bg-red-100 dark:bg-red-900/30 px-3 py-1.5 rounded-full">
                    <span class="text-sm font-bold text-red-600">Alert</span>
                </div>
            </div>
            <p class="text-gray-500 dark:text-gray-400 text-sm font-medium mb-1">Dead Stock</p>
            <h3 class="text-4xl font-bold text-gray-800 dark:text-white">43 <span class="text-lg text-gray-500 font-normal">products</span></h3>
            <p class="text-red-600 text-xs mt-2">> 90 days no movement</p>
        </div>
    </div>

    <!-- Analytics Charts -->
    <div class="max-w-8xl mx-auto grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Stock Movement Trend -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-xl font-bold text-gray-800 dark:text-white flex items-center gap-2">
                    <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-cyan-500 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                        </svg>
                    </div>
                    Stock Movement Trend
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Inbound vs Outbound stock movement</p>
            </div>
            <div class="p-6">
                <div class="h-80 bg-gradient-to-br from-blue-50 to-cyan-50 dark:from-gray-900 dark:to-gray-900 rounded-xl flex items-end justify-between gap-2 px-4 pb-4">
                    <template x-for="(month, index) in movementData" :key="index">
                        <div class="flex-1 flex flex-col items-center gap-2">
                            <div class="flex gap-1 w-full justify-center">
                                <div class="w-3 bg-blue-500 rounded-t" :style="`height: ${month.inbound}px`" :title="`Inbound: ${month.inbound}`"></div>
                                <div class="w-3 bg-cyan-400 rounded-t" :style="`height: ${month.outbound}px`" :title="`Outbound: ${month.outbound}`"></div>
                            </div>
                            <span class="text-xs text-gray-500 dark:text-gray-400 font-medium" x-text="month.label"></span>
                        </div>
                    </template>
                </div>
                <div class="flex items-center justify-center gap-6 mt-4">
                    <div class="flex items-center gap-2">
                        <div class="w-3 h-3 bg-blue-500 rounded"></div>
                        <span class="text-sm text-gray-600 dark:text-gray-400">Inbound</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-3 h-3 bg-cyan-400 rounded"></div>
                        <span class="text-sm text-gray-600 dark:text-gray-400">Outbound</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Category Performance -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-xl font-bold text-gray-800 dark:text-white flex items-center gap-2">
                    <div class="w-8 h-8 bg-gradient-to-br from-purple-500 to-pink-500 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"></path>
                        </svg>
                    </div>
                    Stock by Category
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Distribution of stock value per category</p>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    <template x-for="cat in categoryData" :key="cat.name">
                        <div class="group">
                            <div class="flex items-center justify-between mb-2">
                                <div class="flex items-center gap-3">
                                    <div class="w-3 h-3 rounded-full" :class="cat.color"></div>
                                    <span class="text-sm font-semibold text-gray-700 dark:text-gray-300" x-text="cat.name"></span>
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="text-sm font-bold text-gray-800 dark:text-white" x-text="cat.percentage + '%'"></span>
                                    <span class="text-xs text-gray-500 dark:text-gray-400 w-24 text-right" x-text="formatCurrency(cat.value)"></span>
                                </div>
                            </div>
                            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3 overflow-hidden">
                                <div class="h-full rounded-full transition-all duration-700 group-hover:opacity-80" :class="cat.color" :style="`width: ${cat.percentage}%`"></div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>

    <!-- Product Performance Table -->
    <div class="max-w-8xl mx-auto bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden mb-8">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-gray-800 dark:text-white">Product Performance Analysis</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Detailed stock movement and turnover analysis</p>
            </div>
            <div class="flex items-center gap-3">
                <select x-model="filterCategory" class="px-4 py-2.5 border-2 border-gray-200 dark:border-gray-700 rounded-xl bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500">
                    <option value="">All Categories</option>
                    <option value="food">Food & Beverage</option>
                    <option value="electronics">Electronics</option>
                    <option value="fashion">Fashion</option>
                </select>
                <input type="text" x-model="search" placeholder="🔍 Search products..." class="px-4 py-2.5 border-2 border-gray-200 dark:border-gray-700 rounded-xl bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500">
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-gray-900/50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Product</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Category</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Current Stock</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Stock Value</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Turnover Rate</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Movement Status</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Last Movement</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    <template x-for="product in filteredProducts" :key="product.id">
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/30 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-gradient-to-br from-brand-400 to-indigo-500 rounded-lg flex items-center justify-center text-white font-bold" x-text="product.name.charAt(0)"></div>
                                    <div>
                                        <p class="font-semibold text-gray-800 dark:text-white" x-text="product.name"></p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400" x-text="product.sku"></p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4"><span class="px-3 py-1 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-full text-xs font-medium" x-text="product.category"></span></td>
                            <td class="px-6 py-4">
                                <div>
                                    <p class="font-semibold text-gray-800 dark:text-white" x-text="product.stock"></p>
                                    <p class="text-xs" :class="product.stock < product.min_stock ? 'text-red-600' : 'text-gray-500'" x-text="product.stock < product.min_stock ? 'Below min' : 'OK'"></p>
                                </div>
                            </td>
                            <td class="px-6 py-4"><span class="font-bold text-gray-800 dark:text-white" x-text="formatCurrency(product.stock_value)"></span></td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <div class="flex-1 max-w-[100px] bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                        <div class="h-2 rounded-full" :class="getTurnoverColor(product.turnover_rate)" :style="`width: ${product.turnover_rate}%`"></div>
                                    </div>
                                    <span class="text-sm font-semibold text-gray-700 dark:text-gray-300" x-text="product.turnover_rate + '%'"></span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold"
                                    :class="{
                                        'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400': product.status === 'fast',
                                        'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400': product.status === 'slow',
                                        'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400': product.status === 'dead',
                                        'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400': product.status === 'normal'
                                    }"
                                    x-text="formatStatus(product.status)">
                                </span>
                            </td>
                            <td class="px-6 py-4"><span class="text-gray-700 dark:text-gray-300 text-sm" x-text="product.last_movement"></span></td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script>
function stockAnalytics() {
    return {
        filterCategory: '',
        search: '',
        movementData: [
            { label: 'Jan', inbound: 65, outbound: 58 },
            { label: 'Feb', inbound: 78, outbound: 72 },
            { label: 'Mar', inbound: 52, outbound: 68 },
            { label: 'Apr', inbound: 81, outbound: 75 },
            { label: 'May', inbound: 95, outbound: 88 },
            { label: 'Jun', inbound: 87, outbound: 92 }
        ],
        categoryData: [
            { name: 'Food & Beverage', percentage: 45, value: 125000000, color: 'bg-gradient-to-r from-blue-500 to-cyan-500' },
            { name: 'Electronics', percentage: 28, value: 78000000, color: 'bg-gradient-to-r from-purple-500 to-pink-500' },
            { name: 'Fashion', percentage: 18, value: 52000000, color: 'bg-gradient-to-r from-amber-500 to-orange-500' },
            { name: 'Home & Living', percentage: 9, value: 25000000, color: 'bg-gradient-to-r from-green-500 to-emerald-500' }
        ],
        products: [
            { id: 1, name: 'Premium Coffee Beans', sku: 'PRD-001', category: 'Food', stock: 245, min_stock: 100, stock_value: 25750000, turnover_rate: 85, status: 'fast', last_movement: '2 days ago' },
            { id: 2, name: 'Organic Tea Set', sku: 'PRD-002', category: 'Beverage', stock: 182, min_stock: 80, stock_value: 18500000, turnover_rate: 72, status: 'normal', last_movement: '1 week ago' },
            { id: 3, name: 'Artisan Snacks', sku: 'PRD-003', category: 'Food', stock: 450, min_stock: 150, stock_value: 15200000, turnover_rate: 35, status: 'slow', last_movement: '45 days ago' },
            { id: 4, name: 'Gourmet Cookies', sku: 'PRD-004', category: 'Food', stock: 890, min_stock: 200, stock_value: 9500000, turnover_rate: 12, status: 'dead', last_movement: '95 days ago' },
            { id: 5, name: 'Fresh Juice Pack', sku: 'PRD-005', category: 'Beverage', stock: 156, min_stock: 100, stock_value: 12800000, turnover_rate: 78, status: 'normal', last_movement: '3 days ago' }
        ],

        get filteredProducts() {
            let result = this.products;
            if (this.filterCategory) {
                result = result.filter(p => p.category.toLowerCase().includes(this.filterCategory.toLowerCase()));
            }
            if (this.search) {
                const q = this.search.toLowerCase();
                result = result.filter(p => p.name.toLowerCase().includes(q) || p.sku.toLowerCase().includes(q));
            }
            return result;
        },

        formatCurrency(amount) {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0
            }).format(amount);
        },

        formatStatus(status) {
            const statuses = {
                'fast': 'Fast Moving',
                'normal': 'Normal',
                'slow': 'Slow Moving',
                'dead': 'Dead Stock'
            };
            return statuses[status] || status;
        },

        getTurnoverColor(rate) {
            if (rate >= 70) return 'bg-green-500';
            if (rate >= 40) return 'bg-blue-500';
            if (rate >= 20) return 'bg-amber-500';
            return 'bg-red-500';
        },

        exportReport() {
            Swal.fire({
                icon: 'success',
                title: 'Exporting Report',
                text: 'Your stock analytics report is being generated...',
                timer: 2000,
                showConfirmButton: false
            });
        }
    }
}
</script>
@endpush
@endsection
