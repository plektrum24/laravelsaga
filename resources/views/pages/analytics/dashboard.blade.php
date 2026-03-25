@extends('layouts.app')

@section('title', 'Sales Analytics Dashboard | SAGA TOKO APP')

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-gray-900 p-6" x-data="salesAnalytics()">
    <!-- Header -->
    <div class="max-w-8xl mx-auto mb-8">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-14 h-14 bg-gradient-to-br from-orange-500 to-red-600 rounded-2xl flex items-center justify-center shadow-xl shadow-orange-500/30">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-gray-800 dark:text-white">Sales Analytics Dashboard</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Real-time insights and performance metrics</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <div class="flex items-center bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-1">
                    <button @click="setPeriod('today')" :class="period === 'today' ? 'bg-orange-500 text-white shadow-lg' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700'" class="px-4 py-2 rounded-lg text-sm font-semibold transition-all">Today</button>
                    <button @click="setPeriod('week')" :class="period === 'week' ? 'bg-orange-500 text-white shadow-lg' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700'" class="px-4 py-2 rounded-lg text-sm font-semibold transition-all">Week</button>
                    <button @click="setPeriod('month')" :class="period === 'month' ? 'bg-orange-500 text-white shadow-lg' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700'" class="px-4 py-2 rounded-lg text-sm font-semibold transition-all">Month</button>
                    <button @click="setPeriod('year')" :class="period === 'year' ? 'bg-orange-500 text-white shadow-lg' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700'" class="px-4 py-2 rounded-lg text-sm font-semibold transition-all">Year</button>
                </div>
                <button @click="exportReport" class="px-6 py-3 bg-gradient-to-r from-orange-600 to-red-600 text-white rounded-xl font-semibold hover:from-orange-700 hover:to-red-700 transition-all shadow-lg shadow-orange-500/30 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Export
                </button>
            </div>
        </div>
    </div>

    <!-- KPI Cards -->
    <div class="max-w-8xl mx-auto mb-8 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Revenue -->
        <div class="bg-gradient-to-br from-orange-500 via-red-500 to-pink-500 rounded-2xl p-6 text-white shadow-xl shadow-orange-500/20 relative overflow-hidden group">
            <div class="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full -mr-16 -mt-16 group-hover:scale-110 transition-transform"></div>
            <div class="relative">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-14 h-14 bg-white/20 backdrop-blur-sm rounded-2xl flex items-center justify-center">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="flex items-center gap-1 bg-white/20 backdrop-blur-sm px-3 py-1.5 rounded-full">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                        </svg>
                        <span class="text-sm font-bold">+12.5%</span>
                    </div>
                </div>
                <p class="text-orange-100 text-sm font-medium mb-1">Total Revenue</p>
                <h3 class="text-4xl font-bold mb-1" x-text="formatCurrency(kpi.revenue)"></h3>
                <p class="text-orange-200 text-xs">vs previous period</p>
            </div>
        </div>

        <!-- Total Orders -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-gray-200 dark:border-gray-700 group hover:shadow-lg transition-all">
            <div class="flex items-center justify-between mb-4">
                <div class="w-14 h-14 bg-gradient-to-br from-blue-100 to-cyan-100 dark:from-blue-900/30 dark:to-cyan-900/30 rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-7 h-7 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                    </svg>
                </div>
                <div class="flex items-center gap-1 bg-green-100 dark:bg-green-900/30 px-3 py-1.5 rounded-full">
                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                    </svg>
                    <span class="text-sm font-bold text-green-600">+8.2%</span>
                </div>
            </div>
            <p class="text-gray-500 dark:text-gray-400 text-sm font-medium mb-1">Total Orders</p>
            <h3 class="text-4xl font-bold text-gray-800 dark:text-white mb-1" x-text="kpi.orders"></h3>
            <p class="text-gray-400 text-xs">Completed transactions</p>
        </div>

        <!-- Average Order Value -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-gray-200 dark:border-gray-700 group hover:shadow-lg transition-all">
            <div class="flex items-center justify-between mb-4">
                <div class="w-14 h-14 bg-gradient-to-br from-purple-100 to-pink-100 dark:from-purple-900/30 dark:to-pink-900/30 rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-7 h-7 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 3.666V19.343a2 2 0 01-.586 1.414l-2.414 2.414a2 2 0 01-2.828 0l-2.414-2.414a2 2 0 01-.586-1.414V10.666M12 3a2 2 0 012 2v2H10V5a2 2 0 012-2z"></path>
                    </svg>
                </div>
                <div class="flex items-center gap-1 bg-blue-100 dark:bg-blue-900/30 px-3 py-1.5 rounded-full">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                    </svg>
                    <span class="text-sm font-bold text-blue-600">+3.1%</span>
                </div>
            </div>
            <p class="text-gray-500 dark:text-gray-400 text-sm font-medium mb-1">Avg Order Value</p>
            <h3 class="text-4xl font-bold text-gray-800 dark:text-white mb-1" x-text="formatCurrency(kpi.avgOrderValue)"></h3>
            <p class="text-gray-400 text-xs">Per transaction average</p>
        </div>

        <!-- Active Customers -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-gray-200 dark:border-gray-700 group hover:shadow-lg transition-all">
            <div class="flex items-center justify-between mb-4">
                <div class="w-14 h-14 bg-gradient-to-br from-green-100 to-emerald-100 dark:from-green-900/30 dark:to-emerald-900/30 rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-7 h-7 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
                <div class="flex items-center gap-1 bg-purple-100 dark:bg-purple-900/30 px-3 py-1.5 rounded-full">
                    <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                    </svg>
                    <span class="text-sm font-bold text-purple-600">+5.3%</span>
                </div>
            </div>
            <p class="text-gray-500 dark:text-gray-400 text-sm font-medium mb-1">Active Customers</p>
            <h3 class="text-4xl font-bold text-gray-800 dark:text-white mb-1" x-text="kpi.customers"></h3>
            <p class="text-gray-400 text-xs">Unique buyers this period</p>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="max-w-8xl mx-auto grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Sales Trend Chart -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-bold text-gray-800 dark:text-white flex items-center gap-2">
                        <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-cyan-500 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                            </svg>
                        </div>
                        Sales Trend
                    </h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Daily sales performance</p>
                </div>
                <select x-model="chartType" class="px-3 py-2 border-2 border-gray-200 dark:border-gray-700 rounded-xl bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-200 text-sm focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500">
                    <option value="line">Line Chart</option>
                    <option value="bar">Bar Chart</option>
                    <option value="area">Area Chart</option>
                </select>
            </div>
            <div class="p-6">
                <div class="h-80 bg-gradient-to-br from-blue-50 to-cyan-50 dark:from-gray-900 dark:to-gray-900 rounded-xl flex items-center justify-center relative overflow-hidden">
                    <!-- Simulated Chart -->
                    <div class="absolute inset-4 flex items-end justify-between gap-2 px-4">
                        <template x-for="(value, index) in chartData" :key="index">
                            <div class="flex-1 flex flex-col items-center gap-2 group">
                                <div class="w-full relative">
                                    <div class="absolute -top-8 left-1/2 -translate-x-1/2 bg-gray-800 dark:bg-gray-700 text-white text-xs font-bold px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap" x-text="formatCurrency(value)"></div>
                                    <div class="w-full bg-gradient-to-t from-blue-500 to-cyan-400 rounded-t-lg transition-all duration-500 hover:from-blue-600 hover:to-cyan-500" :style="`height: ${value / 2}px`"></div>
                                </div>
                                <span class="text-xs text-gray-500 dark:text-gray-400 font-medium" x-text="chartLabels[index]"></span>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <!-- Category Performance -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-bold text-gray-800 dark:text-white flex items-center gap-2">
                        <div class="w-8 h-8 bg-gradient-to-br from-purple-500 to-pink-500 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"></path>
                            </svg>
                        </div>
                        Category Performance
                    </h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Sales by product category</p>
                </div>
            </div>
            <div class="p-6">
                <div class="h-80 bg-gradient-to-br from-purple-50 to-pink-50 dark:from-gray-900 dark:to-gray-900 rounded-xl flex items-center p-6">
                    <div class="w-full space-y-4">
                        <template x-for="cat in categoryData" :key="cat.name">
                            <div class="group">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex items-center gap-3">
                                        <div class="w-3 h-3 rounded-full" :class="cat.color"></div>
                                        <span class="text-sm font-semibold text-gray-700 dark:text-gray-300" x-text="cat.name"></span>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <span class="text-sm font-bold text-gray-800 dark:text-white" x-text="cat.percentage + '%'"></span>
                                        <span class="text-xs text-gray-500 dark:text-gray-400 w-20 text-right" x-text="formatCurrency(cat.value)"></span>
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
    </div>

    <!-- Top Products & Insights -->
    <div class="max-w-8xl mx-auto grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Top Products -->
        <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-bold text-gray-800 dark:text-white flex items-center gap-2">
                        <div class="w-8 h-8 bg-gradient-to-br from-amber-500 to-orange-500 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path>
                            </svg>
                        </div>
                        Top Selling Products
                    </h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Best performers this period</p>
                </div>
                <button class="px-4 py-2 text-orange-600 dark:text-orange-400 hover:bg-orange-50 dark:hover:bg-orange-900/20 rounded-xl text-sm font-semibold transition-all">View All</button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 dark:bg-gray-900/50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Rank</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Product</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Category</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Units Sold</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Revenue</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Growth</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        <template x-for="(product, index) in topProducts" :key="product.id">
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/30 transition-colors group">
                                <td class="px-6 py-4">
                                    <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-sm"
                                        :class="{
                                            'bg-gradient-to-br from-amber-400 to-orange-500 text-white': index === 0,
                                            'bg-gradient-to-br from-gray-300 to-gray-400 text-white': index === 1,
                                            'bg-gradient-to-br from-orange-600 to-orange-700 text-white': index === 2,
                                            'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400': index > 2
                                        }"
                                        x-text="#" + (index + 1)">
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-12 h-12 bg-gradient-to-br from-brand-400 to-indigo-500 rounded-xl flex items-center justify-center text-white font-bold shadow-md" x-text="product.name.charAt(0)"></div>
                                        <div>
                                            <p class="font-bold text-gray-800 dark:text-white" x-text="product.name"></p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400" x-text="product.sku"></p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4"><span class="px-3 py-1 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-full text-xs font-medium" x-text="product.category"></span></td>
                                <td class="px-6 py-4"><span class="font-semibold text-gray-800 dark:text-white" x-text="product.sold"></span></td>
                                <td class="px-6 py-4"><span class="font-bold text-green-600 dark:text-green-400" x-text="formatCurrency(product.revenue)"></span></td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-1">
                                        <svg class="w-4 h-4" :class="product.growth >= 0 ? 'text-green-600' : 'text-red-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="product.growth >= 0 ? 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6' : 'M13 17h8m0 0V9m0 8l-8-8-4 4-6-6'"></path>
                                        </svg>
                                        <span class="text-sm font-bold" :class="product.growth >= 0 ? 'text-green-600' : 'text-red-600'" x-text="product.growth + '%'"></span>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Insights & Tips -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-xl font-bold text-gray-800 dark:text-white flex items-center gap-2">
                    <div class="w-8 h-8 bg-gradient-to-br from-green-500 to-emerald-500 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                        </svg>
                    </div>
                    Insights & Tips
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">AI-powered recommendations</p>
            </div>
            <div class="p-6 space-y-4">
                <div class="p-4 bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-xl border border-green-200 dark:border-green-800">
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 bg-green-500 rounded-lg flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-green-800 dark:text-green-400">Revenue Increased</p>
                            <p class="text-xs text-green-600 dark:text-green-500 mt-1">Your revenue is up 12.5% compared to last period. Great job!</p>
                        </div>
                    </div>
                </div>

                <div class="p-4 bg-gradient-to-br from-blue-50 to-cyan-50 dark:from-blue-900/20 dark:to-cyan-900/20 rounded-xl border border-blue-200 dark:border-blue-800">
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 bg-blue-500 rounded-lg flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-blue-800 dark:text-blue-400">High-Value Products</p>
                            <p class="text-xs text-blue-600 dark:text-blue-500 mt-1">Product A contributes 35% of total revenue. Consider bundling strategies.</p>
                        </div>
                    </div>
                </div>

                <div class="p-4 bg-gradient-to-br from-amber-50 to-orange-50 dark:from-amber-900/20 dark:to-orange-900/20 rounded-xl border border-amber-200 dark:border-amber-800">
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 bg-amber-500 rounded-lg flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-amber-800 dark:text-amber-400">Low Stock Alert</p>
                            <p class="text-xs text-amber-600 dark:text-amber-500 mt-1">3 products are running low on stock. Reorder soon to avoid stockouts.</p>
                        </div>
                    </div>
                </div>

                <div class="p-4 bg-gradient-to-br from-purple-50 to-pink-50 dark:from-purple-900/20 dark:to-pink-900/20 rounded-xl border border-purple-200 dark:border-purple-800">
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 bg-purple-500 rounded-lg flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-purple-800 dark:text-purple-400">Customer Growth</p>
                            <p class="text-xs text-purple-600 dark:text-purple-500 mt-1">New customer acquisition is up 5.3%. Consider loyalty programs.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function salesAnalytics() {
    return {
        period: 'month',
        chartType: 'bar',
        kpi: {
            revenue: 125750000,
            orders: 1247,
            avgOrderValue: 100800,
            customers: 856
        },
        chartData: [65, 78, 52, 81, 95, 87, 92, 68, 75, 88, 94, 102],
        chartLabels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        categoryData: [
            { name: 'Food & Beverage', percentage: 75, value: 45000000, color: 'bg-gradient-to-r from-blue-500 to-cyan-500' },
            { name: 'Electronics', percentage: 60, value: 32000000, color: 'bg-gradient-to-r from-purple-500 to-pink-500' },
            { name: 'Fashion', percentage: 45, value: 28000000, color: 'bg-gradient-to-r from-amber-500 to-orange-500' },
            { name: 'Home & Living', percentage: 30, value: 15000000, color: 'bg-gradient-to-r from-green-500 to-emerald-500' }
        ],
        topProducts: [
            { id: 1, name: 'Premium Coffee Beans', sku: 'PRD-001', category: 'Food', sold: 523, revenue: 25750000, growth: 15.2 },
            { id: 2, name: 'Organic Tea Set', sku: 'PRD-002', category: 'Beverage', sold: 412, revenue: 18500000, growth: 8.7 },
            { id: 3, name: 'Artisan Snacks', sku: 'PRD-003', category: 'Food', sold: 387, revenue: 15200000, growth: -2.3 },
            { id: 4, name: 'Fresh Juice Pack', sku: 'PRD-004', category: 'Beverage', sold: 298, revenue: 12800000, growth: 22.1 },
            { id: 5, name: 'Gourmet Cookies', sku: 'PRD-005', category: 'Food', sold: 245, revenue: 9500000, growth: 5.4 }
        ],

        setPeriod(p) {
            this.period = p;
            // Simulate data change
            this.kpi.revenue = Math.floor(Math.random() * 100000000) + 80000000;
            this.kpi.orders = Math.floor(Math.random() * 500) + 800;
            this.kpi.avgOrderValue = Math.floor(Math.random() * 50000) + 80000;
            this.kpi.customers = Math.floor(Math.random() * 300) + 600;
        },

        formatCurrency(amount) {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            }).format(amount);
        },

        exportReport() {
            Swal.fire({
                icon: 'success',
                title: 'Exporting Report',
                text: 'Your sales analytics report is being generated...',
                timer: 2000,
                showConfirmButton: false
            });
        }
    }
}
</script>
@endpush
@endsection
