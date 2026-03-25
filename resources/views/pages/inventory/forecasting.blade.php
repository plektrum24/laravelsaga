@extends('layouts.app')

@section('title', 'Product Forecasting | SAGA TOKO APP')

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-gray-900 p-6" x-data="productForecasting()">
    <!-- Header -->
    <div class="max-w-8xl mx-auto mb-8">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-14 h-14 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl flex items-center justify-center shadow-xl shadow-indigo-500/30">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path>
                    </svg>
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-gray-800 dark:text-white">Target Forecasting</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Set revenue targets & get AI-powered recommendations</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Target Input Form -->
    <div class="max-w-8xl mx-auto mb-8">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="p-6 bg-gradient-to-r from-indigo-500 to-purple-600">
                <h2 class="text-xl font-bold text-white flex items-center gap-2">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                    </svg>
                    Generate Forecast by Target Revenue
                </h2>
                <p class="text-indigo-100 text-sm mt-1">Enter your revenue target and get instant recommendations</p>
            </div>

            <div class="p-6">
                <!-- Target Revenue Input -->
                <div class="mb-6">
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">
                        🎯 Target Revenue (30 days)
                    </label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 dark:text-gray-400 font-semibold">Rp</span>
                        <input type="number" 
                            x-model.number="targetRevenue" 
                            @input.debounce.300ms="calculateForecast()"
                            placeholder="100000000"
                            class="w-full pl-12 pr-4 py-4 text-lg font-bold border-2 border-gray-200 dark:border-gray-700 rounded-xl bg-white dark:bg-gray-900 text-gray-800 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all">
                    </div>
                    
                    <!-- Slider -->
                    <div class="mt-4">
                        <input type="range" 
                            x-model.number="targetRevenue"
                            @input.debounce.300ms="calculateForecast()"
                            min="10000000" 
                            max="500000000" 
                            step="5000000"
                            class="w-full h-3 bg-gray-200 dark:bg-gray-700 rounded-lg appearance-none cursor-pointer accent-indigo-600">
                        <div class="flex justify-between mt-2 text-xs text-gray-500 dark:text-gray-400">
                            <span>Rp 10Jt</span>
                            <span>Rp 100Jt</span>
                            <span>Rp 250Jt</span>
                            <span>Rp 500Jt</span>
                        </div>
                    </div>
                </div>

                <!-- Duration Selector -->
                <div class="mb-6">
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">
                        📅 Forecast Duration
                    </label>
                    <div class="flex gap-3">
                        <button @click="durationDays = 7; calculateForecast()" 
                            :class="durationDays === 7 ? 'bg-indigo-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300'"
                            class="flex-1 py-3 rounded-xl font-semibold transition-all">
                            7 Days
                        </button>
                        <button @click="durationDays = 14; calculateForecast()" 
                            :class="durationDays === 14 ? 'bg-indigo-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300'"
                            class="flex-1 py-3 rounded-xl font-semibold transition-all">
                            14 Days
                        </button>
                        <button @click="durationDays = 30; calculateForecast()" 
                            :class="durationDays === 30 ? 'bg-indigo-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300'"
                            class="flex-1 py-3 rounded-xl font-semibold transition-all">
                            30 Days
                        </button>
                        <button @click="durationDays = 60; calculateForecast()" 
                            :class="durationDays === 60 ? 'bg-indigo-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300'"
                            class="flex-1 py-3 rounded-xl font-semibold transition-all">
                            60 Days
                        </button>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex gap-3">
                    <button @click="calculateForecast()" 
                        class="flex-1 py-4 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-xl font-bold text-lg hover:from-indigo-700 hover:to-purple-700 transition-all shadow-lg shadow-indigo-500/30 flex items-center justify-center gap-2">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 36v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                        Calculate Forecast
                    </button>
                    <button @click="saveTarget()" 
                        :disabled="!forecastCalculated"
                        class="px-8 py-4 bg-gradient-to-r from-green-600 to-emerald-600 text-white rounded-xl font-bold text-lg hover:from-green-700 hover:to-emerald-700 transition-all shadow-lg shadow-green-500/30 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path>
                        </svg>
                        Save
                    </button>
                    <button @click="exportForecast()" 
                        :disabled="!forecastCalculated"
                        class="px-6 py-4 bg-white dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-700 rounded-xl font-bold text-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-all disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
                        <svg class="w-6 h-6 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Export
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Forecast Summary Cards -->
    <div x-show="forecastCalculated" x-transition class="max-w-8xl mx-auto mb-8 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Target Revenue -->
        <div class="bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl p-6 text-white shadow-xl shadow-indigo-500/20">
            <div class="flex items-center justify-between mb-4">
                <div class="w-14 h-14 bg-white/20 backdrop-blur-sm rounded-2xl flex items-center justify-center">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                    </svg>
                </div>
                <span class="text-2xl font-bold" x-text="formatCurrency(forecastData.target_revenue)"></span>
            </div>
            <p class="text-indigo-100 text-sm font-medium">Target Revenue</p>
            <p class="text-indigo-200 text-xs mt-1" x-text="durationDays + ' days forecast'"></p>
        </div>

        <!-- Current Trajectory -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between mb-4">
                <div class="w-14 h-14 bg-gradient-to-br from-blue-100 to-cyan-100 dark:from-blue-900/30 dark:to-cyan-900/30 rounded-2xl flex items-center justify-center">
                    <svg class="w-7 h-7 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </div>
                <span class="text-2xl font-bold text-gray-800 dark:text-white" x-text="formatCurrency(forecastData.current_trajectory)"></span>
            </div>
            <p class="text-gray-600 dark:text-gray-400 text-sm font-medium">Current Trajectory</p>
            <p class="text-gray-400 text-xs mt-1">Based on historical data</p>
        </div>

        <!-- Revenue Gap -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between mb-4">
                <div class="w-14 h-14 bg-gradient-to-br from-amber-100 to-orange-100 dark:from-amber-900/30 dark:to-orange-900/30 rounded-2xl flex items-center justify-center">
                    <svg class="w-7 h-7 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
                <span class="text-2xl font-bold text-gray-800 dark:text-white" x-text="formatCurrency(forecastData.gap)"></span>
            </div>
            <p class="text-gray-600 dark:text-gray-400 text-sm font-medium">Revenue Gap</p>
            <p class="text-gray-400 text-xs mt-1">Additional revenue needed</p>
        </div>

        <!-- Daily Target -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between mb-4">
                <div class="w-14 h-14 bg-gradient-to-br from-green-100 to-emerald-100 dark:from-green-900/30 dark:to-emerald-900/30 rounded-2xl flex items-center justify-center">
                    <svg class="w-7 h-7 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <span class="text-2xl font-bold text-gray-800 dark:text-white" x-text="formatCurrency(forecastData.required_daily_sales)"></span>
            </div>
            <p class="text-gray-600 dark:text-gray-400 text-sm font-medium">Daily Sales Target</p>
            <p class="text-gray-400 text-xs mt-1">Required per day</p>
        </div>
    </div>

    <!-- Financial Summary -->
    <div x-show="forecastCalculated" x-transition class="max-w-8xl mx-auto mb-8">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="p-6 bg-gradient-to-r from-green-500 to-emerald-600">
                <h2 class="text-xl font-bold text-white flex items-center gap-2">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                    Financial Summary
                </h2>
            </div>

            <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Total Cost -->
                <div class="text-center p-6 bg-red-50 dark:bg-red-900/20 rounded-2xl">
                    <p class="text-sm text-gray-600 dark:text-gray-400 font-semibold mb-2">💰 Total Cost (COGS)</p>
                    <p class="text-3xl font-bold text-red-600 dark:text-red-400" x-text="formatCurrency(forecastData.total_cost)"></p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">Estimated product costs</p>
                </div>

                <!-- Expected Profit -->
                <div class="text-center p-6 bg-green-50 dark:bg-green-900/20 rounded-2xl">
                    <p class="text-sm text-gray-600 dark:text-gray-400 font-semibold mb-2">📈 Expected Profit</p>
                    <p class="text-3xl font-bold text-green-600 dark:text-green-400" x-text="formatCurrency(forecastData.expected_profit)"></p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">After all costs</p>
                </div>

                <!-- Profit Margin -->
                <div class="text-center p-6 bg-blue-50 dark:bg-blue-900/20 rounded-2xl">
                    <p class="text-sm text-gray-600 dark:text-gray-400 font-semibold mb-2">🎯 Profit Margin</p>
                    <p class="text-3xl font-bold text-blue-600 dark:text-blue-400" x-text="forecastData.profit_margin + '%'"></p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-2" x-text="'Break-even: ' + forecastData.break_even_date"></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Recommended Product Mix -->
    <div x-show="forecastCalculated && forecastData.product_mix && forecastData.product_mix.length > 0" x-transition class="max-w-8xl mx-auto">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-bold text-gray-800 dark:text-white">📦 Recommended Product Mix</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Products to stock for achieving your target</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="px-4 py-2 bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-400 rounded-full text-sm font-bold">
                        <span x-text="forecastData.product_mix?.length || 0"></span> Products
                    </span>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Priority</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Product</th>
                            <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Qty</th>
                            <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Unit Cost</th>
                            <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total Cost</th>
                            <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Revenue</th>
                            <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Profit</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        <template x-for="(item, index) in forecastData.product_mix" :key="index">
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold"
                                        :class="{
                                            'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400': item.priority <= 2,
                                            'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400': item.priority === 3,
                                            'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400': item.priority >= 4
                                        }"
                                        x-text="'#' + item.priority">
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3" x-data="{ product: getProductById(item.product_id) }">
                                        <div class="w-10 h-10 bg-gradient-to-br from-indigo-400 to-purple-500 rounded-lg flex items-center justify-center text-white font-bold" x-text="product?.name?.charAt(0) || 'P'"></div>
                                        <div>
                                            <p class="font-semibold text-gray-800 dark:text-white" x-text="product?.name || 'Product ' + item.product_id"></p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400" x-text="product?.sku || 'SKU-' + item.product_id"></p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <span class="font-bold text-gray-800 dark:text-white" x-text="item.recommended_qty"></span>
                                    <span class="text-xs text-gray-500 dark:text-gray-400 block">units</span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <span class="font-semibold text-gray-700 dark:text-gray-300" x-text="formatCurrency(item.unit_cost)"></span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <span class="font-bold text-red-600 dark:text-red-400" x-text="formatCurrency(item.total_cost)"></span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <span class="font-bold text-green-600 dark:text-green-400" x-text="formatCurrency(item.expected_revenue)"></span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <span class="font-bold text-indigo-600 dark:text-indigo-400" x-text="formatCurrency(item.expected_profit)"></span>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Loading State -->
    <div x-show="isLoading" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center">
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-8 shadow-2xl">
            <div class="animate-spin rounded-full h-16 w-16 border-4 border-indigo-200 border-t-indigo-600 mx-auto"></div>
            <p class="text-gray-600 dark:text-gray-400 mt-4 font-semibold">Calculating Forecast...</p>
        </div>
    </div>
</div>

@push('scripts')
<script>
function productForecasting() {
    return {
        targetRevenue: 100000000,
        durationDays: 30,
        forecastCalculated: false,
        isLoading: false,
        forecastData: {
            target_revenue: 0,
            current_trajectory: 0,
            gap: 0,
            required_daily_sales: 0,
            product_mix: [],
            total_cost: 0,
            expected_profit: 0,
            profit_margin: 0,
            break_even_date: '',
        },
        products: [],

        async init() {
            await this.fetchProducts();
        },

        async fetchProducts() {
            const token = localStorage.getItem('saga_token');
            try {
                const res = await fetch('/api/products', {
                    headers: { 'Authorization': 'Bearer ' + token }
                });
                const data = await res.json();
                if (data.success) {
                    this.products = data.data.products || [];
                }
            } catch (error) {
                console.error('Error fetching products:', error);
            }
        },

        getProductById(id) {
            return this.products.find(p => p.id === id);
        },

        async calculateForecast() {
            if (!this.targetRevenue || this.targetRevenue <= 0) {
                return;
            }

            this.isLoading = true;
            const token = localStorage.getItem('saga_token');

            try {
                const res = await fetch('/api/forecast/calculate-target', {
                    method: 'POST',
                    headers: {
                        'Authorization': 'Bearer ' + token,
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        target_revenue: this.targetRevenue,
                        duration_days: this.durationDays,
                    }),
                });

                const data = await res.json();
                if (data.success) {
                    this.forecastData = data.data;
                    this.forecastCalculated = true;
                    
                    this.$nextTick(() => {
                        Swal.fire({
                            icon: 'success',
                            title: 'Forecast Calculated',
                            text: `Target: ${this.formatCurrency(this.targetRevenue)} | Gap: ${this.formatCurrency(this.forecastData.gap)}`,
                            timer: 2000,
                            showConfirmButton: false,
                            toast: true,
                            position: 'top-end',
                        });
                    });
                }
            } catch (error) {
                console.error('Error calculating forecast:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to calculate forecast. Please try again.',
                });
            } finally {
                this.isLoading = false;
            }
        },

        async saveTarget() {
            if (!this.forecastCalculated) {
                return;
            }

            const token = localStorage.getItem('saga_token');

            try {
                const res = await fetch('/api/forecast/save-target', {
                    method: 'POST',
                    headers: {
                        'Authorization': 'Bearer ' + token,
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        target_revenue: this.targetRevenue,
                        duration_days: this.durationDays,
                        current_trajectory: this.forecastData.current_trajectory,
                        gap: this.forecastData.gap,
                        product_mix: this.forecastData.product_mix,
                    }),
                });

                const data = await res.json();
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Target Saved',
                        text: 'Your forecast target has been saved successfully!',
                        timer: 2000,
                        showConfirmButton: false,
                    });
                }
            } catch (error) {
                console.error('Error saving target:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to save target. Please try again.',
                });
            }
        },

        exportForecast() {
            const csv = this.generateCSV();
            const blob = new Blob([csv], { type: 'text/csv' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `forecast_${new Date().toISOString().split('T')[0]}.csv`;
            a.click();
            window.URL.revokeObjectURL(url);

            Swal.fire({
                icon: 'success',
                title: 'Exported',
                text: 'Forecast exported to CSV',
                timer: 2000,
                showConfirmButton: false,
            });
        },

        generateCSV() {
            let csv = 'Product,SKU,Quantity,Unit Cost,Total Cost,Expected Revenue,Expected Profit,Priority\n';
            this.forecastData.product_mix.forEach(item => {
                const product = this.getProductById(item.product_id);
                csv += `${product?.name || 'Product'},${product?.sku || ''},${item.recommended_qty},${item.unit_cost},${item.total_cost},${item.expected_revenue},${item.expected_profit},${item.priority}\n`;
            });
            return csv;
        },

        formatCurrency(amount) {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0,
            }).format(amount);
        },
    };
}
</script>
@endpush
@endsection
