@extends('layouts.app')

@section('title', 'Customer Analytics Dashboard')

@section('content')
<div x-data="customerAnalytics()" x-init="init()" class="space-y-6">
    <!-- Header -->
    <div class="bg-gradient-to-r from-pink-600 to-rose-700 rounded-2xl p-6 shadow-lg">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-white flex items-center gap-3">
                    <span class="p-2 bg-white/20 rounded-lg">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </span>
                    Customer Analytics
                </h1>
                <p class="text-pink-100 text-sm mt-2">Segmentation, CLV, and churn prediction</p>
            </div>
            <div class="flex gap-2">
                <button @click="activeTab = 'rfm'" :class="activeTab === 'rfm' ? 'bg-white text-pink-600' : 'bg-white/20 text-white hover:bg-white/30'" class="px-4 py-2 rounded-lg transition-colors font-medium">RFM Analysis</button>
                <button @click="activeTab = 'clv'" :class="activeTab === 'clv' ? 'bg-white text-pink-600' : 'bg-white/20 text-white hover:bg-white/30'" class="px-4 py-2 rounded-lg transition-colors font-medium">CLV</button>
                <button @click="activeTab = 'churn'" :class="activeTab === 'churn' ? 'bg-white text-pink-600' : 'bg-white/20 text-white hover:bg-white/30'" class="px-4 py-2 rounded-lg transition-colors font-medium">Churn Risk</button>
            </div>
        </div>
    </div>

    <!-- RFM Analysis Tab -->
    <div x-show="activeTab === 'rfm'" class="space-y-6">
        <!-- Segment Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4">
            <template x-for="segment in rfmSegments" :key="segment.segment">
                <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border border-gray-100 dark:border-gray-700">
                    <div class="flex items-center justify-between mb-2">
                        <h4 class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase" x-text="segment.segment"></h4>
                        <span class="w-2 h-2 rounded-full" :class="{
                            'bg-yellow-500': segment.segment === 'Champions',
                            'bg-blue-500': segment.segment === 'Loyal Customers',
                            'bg-green-500': segment.segment === 'New Customers',
                            'bg-orange-500': segment.segment === 'At Risk',
                            'bg-red-500': segment.segment === 'Lost',
                            'bg-gray-500': segment.segment === 'Regular'
                        }"></span>
                    </div>
                    <p class="text-2xl font-bold text-gray-800 dark:text-white" x-text="segment.count"></p>
                    <p class="text-xs text-gray-500 mt-1" x-text="formatCurrency(segment.total_revenue)"></p>
                </div>
            </template>
        </div>

        <!-- RFM Customers Table -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="p-6 border-b border-gray-100 dark:border-gray-700">
                <h3 class="text-lg font-bold text-gray-800 dark:text-white">Customer Segments</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Customer</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Recency</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Frequency</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Monetary</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">R Score</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">F Score</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">M Score</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Segment</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        <template x-for="customer in rfmData.customers.slice(0, 50)" :key="customer.id">
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="px-4 py-3">
                                    <div>
                                        <p class="font-medium text-gray-800 dark:text-white" x-text="customer.name"></p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400" x-text="customer.phone || '-'"></p>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-gray-600 dark:text-gray-300" x-text="customer.recency + ' days'"></td>
                                <td class="px-4 py-3 text-gray-600 dark:text-gray-300" x-text="customer.frequency"></td>
                                <td class="px-4 py-3 text-gray-600 dark:text-gray-300" x-text="formatCurrency(customer.monetary)"></td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-1 text-xs rounded-full font-medium" :class="{
                                        'bg-green-100 text-green-700': customer.r_score >= 3,
                                        'bg-yellow-100 text-yellow-700': customer.r_score === 2,
                                        'bg-red-100 text-red-700': customer.r_score === 1
                                    }" x-text="customer.r_score"></span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-1 text-xs rounded-full font-medium" :class="{
                                        'bg-green-100 text-green-700': customer.f_score >= 3,
                                        'bg-yellow-100 text-yellow-700': customer.f_score === 2,
                                        'bg-red-100 text-red-700': customer.f_score === 1
                                    }" x-text="customer.f_score"></span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-1 text-xs rounded-full font-medium" :class="{
                                        'bg-green-100 text-green-700': customer.m_score >= 3,
                                        'bg-yellow-100 text-yellow-700': customer.m_score === 2,
                                        'bg-red-100 text-red-700': customer.m_score === 1
                                    }" x-text="customer.m_score"></span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-1 text-xs rounded-full font-medium" :class="{
                                        'bg-yellow-100 text-yellow-700': customer.segment === 'Champions',
                                        'bg-blue-100 text-blue-700': customer.segment === 'Loyal Customers',
                                        'bg-green-100 text-green-700': customer.segment === 'New Customers',
                                        'bg-orange-100 text-orange-700': customer.segment === 'At Risk',
                                        'bg-red-100 text-red-700': customer.segment === 'Lost',
                                        'bg-gray-100 text-gray-700': customer.segment === 'Regular'
                                    }" x-text="customer.segment"></span>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- CLV Tab -->
    <div x-show="activeTab === 'clv'" class="space-y-6">
        <!-- CLV Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-gray-100 dark:border-gray-700">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-2 bg-green-100 dark:bg-green-900/30 rounded-xl">
                        <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Total CLV</p>
                    <h3 class="text-2xl font-bold text-gray-800 dark:text-white mt-1" x-text="formatCurrency(clvData.summary.total_clv)"></h3>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-gray-100 dark:border-gray-700">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-2 bg-blue-100 dark:bg-blue-900/30 rounded-xl">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                </div>
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Average CLV</p>
                    <h3 class="text-2xl font-bold text-gray-800 dark:text-white mt-1" x-text="formatCurrency(clvData.summary.average_clv)"></h3>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-gray-100 dark:border-gray-700">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-2 bg-purple-100 dark:bg-purple-900/30 rounded-xl">
                        <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path>
                        </svg>
                    </div>
                    <span class="text-xs font-medium px-2 py-1 rounded-full bg-purple-100 text-purple-700" x-text="clvData.summary.high_value_customers + ' customers'"></span>
                </div>
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">High Value</p>
                    <h3 class="text-2xl font-bold text-gray-800 dark:text-white mt-1" x-text="clvData.summary.high_value_customers"></h3>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-gray-100 dark:border-gray-700">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-2 bg-orange-100 dark:bg-orange-900/30 rounded-xl">
                        <svg class="w-6 h-6 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                    <span class="text-xs font-medium px-2 py-1 rounded-full bg-orange-100 text-orange-700" x-text="clvData.summary.medium_value_customers + ' customers'"></span>
                </div>
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Medium Value</p>
                    <h3 class="text-2xl font-bold text-gray-800 dark:text-white mt-1" x-text="clvData.summary.medium_value_customers"></h3>
                </div>
            </div>
        </div>

        <!-- CLV Customers Table -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="p-6 border-b border-gray-100 dark:border-gray-700">
                <h3 class="text-lg font-bold text-gray-800 dark:text-white">Customer Lifetime Value</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Customer</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Transactions</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Avg Order</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Frequency</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">CLV</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Tier</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        <template x-for="customer in clvData.customers.slice(0, 50)" :key="customer.id">
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="px-4 py-3">
                                    <div>
                                        <p class="font-medium text-gray-800 dark:text-white" x-text="customer.name"></p>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-gray-600 dark:text-gray-300" x-text="customer.total_transactions"></td>
                                <td class="px-4 py-3 text-gray-600 dark:text-gray-300" x-text="formatCurrency(customer.avg_order_value)"></td>
                                <td class="px-4 py-3 text-gray-600 dark:text-gray-300" x-text="customer.purchase_frequency + '/month'"></td>
                                <td class="px-4 py-3">
                                    <span class="font-bold text-green-600 dark:text-green-400" x-text="formatCurrency(customer.clv)"></span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-1 text-xs rounded-full font-medium" :class="{
                                        'bg-purple-100 text-purple-700': customer.clv_tier === 'High',
                                        'bg-blue-100 text-blue-700': customer.clv_tier === 'Medium',
                                        'bg-gray-100 text-gray-700': customer.clv_tier === 'Low'
                                    }" x-text="customer.clv_tier"></span>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Churn Risk Tab -->
    <div x-show="activeTab === 'churn'" class="space-y-6">
        <!-- Churn Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-gray-100 dark:border-gray-700">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-2 bg-blue-100 dark:bg-blue-900/30 rounded-xl">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                </div>
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Total Customers</p>
                    <h3 class="text-2xl font-bold text-gray-800 dark:text-white mt-1" x-text="churnData.summary.total_customers"></h3>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-gray-100 dark:border-gray-700">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-2 bg-red-100 dark:bg-red-900/30 rounded-xl">
                        <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                    </div>
                    <span class="text-xs font-medium px-2 py-1 rounded-full bg-red-100 text-red-700" x-text="churnData.summary.high_risk + ' at risk'"></span>
                </div>
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">High Risk</p>
                    <h3 class="text-2xl font-bold text-red-600 dark:text-red-400 mt-1" x-text="churnData.summary.high_risk"></h3>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-gray-100 dark:border-gray-700">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-2 bg-yellow-100 dark:bg-yellow-900/30 rounded-xl">
                        <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <span class="text-xs font-medium px-2 py-1 rounded-full bg-yellow-100 text-yellow-700" x-text="churnData.summary.medium_risk + ' medium'"></span>
                </div>
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Medium Risk</p>
                    <h3 class="text-2xl font-bold text-yellow-600 dark:text-yellow-400 mt-1" x-text="churnData.summary.medium_risk"></h3>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-gray-100 dark:border-gray-700">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-2 bg-green-100 dark:bg-green-900/30 rounded-xl">
                        <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <span class="text-xs font-medium px-2 py-1 rounded-full bg-green-100 text-green-700" x-text="churnData.summary.churn_rate + '%'"></span>
                </div>
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Churn Rate</p>
                    <h3 class="text-2xl font-bold text-green-600 dark:text-green-400 mt-1" x-text="churnData.summary.churn_rate + '%'"></h3>
                </div>
            </div>
        </div>

        <!-- At-Risk Customers Table -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="p-6 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                <h3 class="text-lg font-bold text-gray-800 dark:text-white">Churn Risk Analysis</h3>
                <span class="text-xs text-gray-500 dark:text-gray-400">Sorted by risk level</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Customer</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Last Purchase</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Days Inactive</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Total Purchases</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Total Spent</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Churn Probability</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Risk Level</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        <template x-for="customer in churnData.customers.filter(c => c.churn_risk === 'High' || c.churn_risk === 'Medium').slice(0, 50)" :key="customer.id">
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="px-4 py-3">
                                    <div>
                                        <p class="font-medium text-gray-800 dark:text-white" x-text="customer.name"></p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400" x-text="customer.phone || '-'"></p>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-gray-600 dark:text-gray-300" x-text="formatDate(customer.last_purchase)"></td>
                                <td class="px-4 py-3">
                                    <span class="font-medium" :class="customer.days_since_purchase > 90 ? 'text-red-600' : 'text-gray-600'" x-text="customer.days_since_purchase + ' days'"></span>
                                </td>
                                <td class="px-4 py-3 text-gray-600 dark:text-gray-300" x-text="customer.total_purchases"></td>
                                <td class="px-4 py-3 text-gray-600 dark:text-gray-300" x-text="formatCurrency(customer.total_spent)"></td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <div class="flex-1 bg-gray-200 dark:bg-gray-700 rounded-full h-2 w-24">
                                            <div class="bg-red-500 h-2 rounded-full" :style="'width: ' + customer.churn_probability + '%'"></div>
                                        </div>
                                        <span class="text-xs font-medium text-gray-600 dark:text-gray-400" x-text="customer.churn_probability + '%'"></span>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-1 text-xs rounded-full font-medium" :class="{
                                        'bg-red-100 text-red-700': customer.churn_risk === 'High',
                                        'bg-yellow-100 text-yellow-700': customer.churn_risk === 'Medium',
                                        'bg-green-100 text-green-700': customer.churn_risk === 'Low'
                                    }" x-text="customer.churn_risk"></span>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function customerAnalytics() {
    return {
        loading: false,
        activeTab: 'rfm',
        rfmData: { customers: [], segments: {}, summary: {} },
        rfmSegments: [],
        clvData: { customers: [], summary: {} },
        churnData: { customers: [], summary: {} },

        async init() {
            await this.fetchRFM();
            await this.fetchCLV();
            await this.fetchChurn();
        },

        async fetchRFM() {
            this.loading = true;
            const token = localStorage.getItem('saga_token');
            
            try {
                const response = await fetch('/api/customers/segmentation', {
                    headers: { 'Authorization': 'Bearer ' + token }
                });
                const data = await response.json();
                
                if (data.success) {
                    this.rfmData = data.data;
                    this.rfmSegments = Object.values(data.data.segments);
                }
            } catch (error) {
                console.error('RFM error:', error);
            } finally {
                this.loading = false;
            }
        },

        async fetchCLV() {
            const token = localStorage.getItem('saga_token');
            
            try {
                const response = await fetch('/api/customers/lifetime-value', {
                    headers: { 'Authorization': 'Bearer ' + token }
                });
                const data = await response.json();
                
                if (data.success) {
                    this.clvData = data.data;
                }
            } catch (error) {
                console.error('CLV error:', error);
            }
        },

        async fetchChurn() {
            const token = localStorage.getItem('saga_token');
            
            try {
                const response = await fetch('/api/customers/churn-risk', {
                    headers: { 'Authorization': 'Bearer ' + token }
                });
                const data = await response.json();
                
                if (data.success) {
                    this.churnData = data.data;
                }
            } catch (error) {
                console.error('Churn error:', error);
            }
        },

        formatCurrency(amount) {
            return 'Rp ' + Number(amount).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        },

        formatDate(dateStr) {
            if (!dateStr) return '-';
            const date = new Date(dateStr);
            return date.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
        }
    }
}
</script>
@endsection
