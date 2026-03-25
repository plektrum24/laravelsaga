@extends('layouts.app')

@section('title', 'Salesman Data | SAGA TOKO APP')

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-gray-900 p-6" x-data="salesmanPage()">
    <!-- Page Header -->
    <div class="max-w-7xl mx-auto mb-8">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <div class="w-12 h-12 bg-gradient-to-br from-brand-500 to-indigo-600 rounded-2xl flex items-center justify-center shadow-lg shadow-brand-500/30">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-800 dark:text-white">Salesman Data</h1>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Kelola tim sales force Anda dengan efektif</p>
                    </div>
                </div>
            </div>
            <button @click="openModal('add')" 
                class="px-6 py-3 bg-gradient-to-r from-brand-600 to-indigo-600 text-white rounded-xl font-semibold hover:from-brand-700 hover:to-indigo-700 transition-all shadow-lg shadow-brand-500/30 flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Add Salesman
            </button>
        </div>
    </div>

    <!-- Stats Overview -->
    <div class="max-w-7xl mx-auto mb-8 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Salesmen -->
        <div class="bg-gradient-to-br from-brand-500 to-indigo-600 rounded-2xl p-6 text-white shadow-xl shadow-brand-500/20">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-sm">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
                <span class="text-3xl font-bold" x-text="salesmen.length"></span>
            </div>
            <p class="text-brand-100 text-sm font-medium">Total Salesmen</p>
            <p class="text-brand-200 text-xs mt-1">Active team members</p>
        </div>

        <!-- Active This Month -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <span class="text-3xl font-bold text-gray-800 dark:text-white" x-text="activeCount"></span>
            </div>
            <p class="text-gray-500 dark:text-gray-400 text-sm font-medium">Active This Month</p>
            <p class="text-green-600 text-xs mt-1 font-medium">↑ 12% from last month</p>
        </div>

        <!-- Total Sales -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-brand-100 dark:bg-brand-900/30 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-brand-600 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <span class="text-2xl font-bold text-gray-800 dark:text-white" x-text="formatCurrency(totalSales)"></span>
            </div>
            <p class="text-gray-500 dark:text-gray-400 text-sm font-medium">Total Sales (MTD)</p>
            <p class="text-brand-600 text-xs mt-1 font-medium">This month revenue</p>
        </div>

        <!-- Avg Performance -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900/30 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                    </svg>
                </div>
                <span class="text-3xl font-bold text-gray-800 dark:text-white" x-text="avgPerformance + '%'"></span>
            </div>
            <p class="text-gray-500 dark:text-gray-400 text-sm font-medium">Avg Performance</p>
            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2 mt-2">
                <div class="bg-gradient-to-r from-purple-500 to-purple-600 h-2 rounded-full transition-all" :style="`width: ${avgPerformance}%`"></div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <!-- Table Header -->
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <h2 class="text-xl font-bold text-gray-800 dark:text-white flex items-center gap-2">
                        <svg class="w-5 h-5 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                        </svg>
                        Salesmen List
                    </h2>
                    <div class="flex items-center gap-3">
                        <div class="relative">
                            <input type="text" x-model="searchQuery" 
                                placeholder="🔍 Search salesman..." 
                                class="pl-10 pr-4 py-2.5 border-2 border-gray-200 dark:border-gray-700 rounded-xl bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all">
                            <svg class="w-5 h-5 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                        <select x-model="filterTerritory" 
                            class="px-4 py-2.5 border-2 border-gray-200 dark:border-gray-700 rounded-xl bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all">
                            <option value="">All Territories</option>
                            <option value="Jakarta Selatan">Jakarta Selatan</option>
                            <option value="Jakarta Barat">Jakarta Barat</option>
                            <option value="Jakarta Utara">Jakarta Utara</option>
                            <option value="Jakarta Timur">Jakarta Timur</option>
                            <option value="Jakarta Pusat">Jakarta Pusat</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Loading State -->
            <div x-show="isLoading" class="p-12 text-center">
                <div class="inline-block animate-spin rounded-full h-12 w-12 border-4 border-brand-200 border-t-brand-600 mb-4"></div>
                <p class="text-gray-500 dark:text-gray-400">Loading salesmen data...</p>
            </div>

            <!-- Table -->
            <div x-show="!isLoading" class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 dark:bg-gray-900/50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Salesman</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Contact Info</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Territory</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Join Date</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Performance</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-4 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        <template x-for="salesman in filteredSalesmen" :key="salesman.id">
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/30 transition-colors group">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-12 h-12 bg-gradient-to-br from-brand-400 to-indigo-500 rounded-full flex items-center justify-center text-white font-bold shadow-md"
                                            x-text="getInitials(salesman.name)">
                                        </div>
                                        <div>
                                            <p class="font-semibold text-gray-800 dark:text-white" x-text="salesman.name"></p>
                                            <p class="text-sm text-gray-500 dark:text-gray-400" x-text="salesman.code"></p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="space-y-1">
                                        <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                            </svg>
                                            <span x-text="salesman.phone"></span>
                                        </div>
                                        <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                            </svg>
                                            <span x-text="salesman.email"></span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        </svg>
                                        <span class="text-gray-700 dark:text-gray-300 text-sm" x-text="salesman.territory"></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-gray-700 dark:text-gray-300 text-sm" x-text="formatDate(salesman.join_date)"></span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="flex-1 max-w-[120px]">
                                            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5">
                                                <div class="h-2.5 rounded-full transition-all duration-500"
                                                    :class="getPerformanceColor(salesman.performance)"
                                                    :style="`width: ${salesman.performance}%`"></div>
                                            </div>
                                        </div>
                                        <span class="text-sm font-semibold text-gray-700 dark:text-gray-300 min-w-[45px]" x-text="salesman.performance + '%'"></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold"
                                        :class="{
                                            'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400': salesman.status === 'active',
                                            'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400': salesman.status === 'probation',
                                            'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400': salesman.status === 'inactive'
                                        }"
                                        x-text="formatStatus(salesman.status)">
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <button @click="viewDetails(salesman)" 
                                            class="p-2 text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-colors" title="View Details">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                            </svg>
                                        </button>
                                        <button @click="editSalesman(salesman)" 
                                            class="p-2 text-brand-600 dark:text-brand-400 hover:bg-brand-50 dark:hover:bg-brand-900/20 rounded-lg transition-colors" title="Edit">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                        </button>
                                        <button @click="deleteSalesman(salesman)" 
                                            class="p-2 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors" title="Delete">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>

                <!-- Empty State -->
                <div x-show="filteredSalesmen.length === 0" class="p-12 text-center">
                    <div class="w-24 h-24 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-2">No salesmen found</h3>
                    <p class="text-gray-500 dark:text-gray-400 mb-6">Try adjusting your search or filters</p>
                    <button @click="searchQuery = ''; filterTerritory = ''" 
                        class="px-6 py-2.5 bg-brand-600 text-white rounded-xl font-semibold hover:bg-brand-700 transition-all">
                        Clear Filters
                    </button>
                </div>
            </div>

            <!-- Pagination -->
            <div x-show="!isLoading && filteredSalesmen.length > 0" class="p-6 border-t border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Showing <span class="font-semibold text-gray-800 dark:text-white" x-text="filteredSalesmen.length"></span> of <span class="font-semibold text-gray-800 dark:text-white" x-text="salesmen.length"></span> salesmen
                    </p>
                    <div class="flex items-center gap-2">
                        <button class="px-4 py-2 border-2 border-gray-200 dark:border-gray-700 rounded-xl text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-900 transition-colors disabled:opacity-50" disabled>
                            Previous
                        </button>
                        <button class="px-4 py-2 bg-brand-600 text-white rounded-xl font-semibold">1</button>
                        <button class="px-4 py-2 border-2 border-gray-200 dark:border-gray-700 rounded-xl text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-900 transition-colors disabled:opacity-50" disabled>
                            Next
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add/Edit Modal -->
    <div x-show="showModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" x-cloak>
        <div class="flex items-center justify-center min-h-screen px-4">
            <div x-show="showModal" 
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-black/50 backdrop-blur-sm"
                @click="showModal = false"></div>

            <div x-show="showModal"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-2xl w-full p-6 z-10">
                
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-2xl font-bold text-gray-800 dark:text-white" x-text="modalMode === 'add' ? 'Add New Salesman' : 'Edit Salesman'"></h3>
                    <button @click="showModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <form @submit.prevent="submitForm()" class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Full Name *</label>
                            <input type="text" x-model="formData.name" required
                                class="w-full px-4 py-3 border-2 border-gray-200 dark:border-gray-700 rounded-xl bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Salesman Code *</label>
                            <input type="text" x-model="formData.code" required
                                class="w-full px-4 py-3 border-2 border-gray-200 dark:border-gray-700 rounded-xl bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Phone Number *</label>
                            <input type="tel" x-model="formData.phone" required
                                class="w-full px-4 py-3 border-2 border-gray-200 dark:border-gray-700 rounded-xl bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Email *</label>
                            <input type="email" x-model="formData.email" required
                                class="w-full px-4 py-3 border-2 border-gray-200 dark:border-gray-700 rounded-xl bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Territory *</label>
                            <select x-model="formData.territory" required
                                class="w-full px-4 py-3 border-2 border-gray-200 dark:border-gray-700 rounded-xl bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all">
                                <option value="">Select Territory</option>
                                <option value="Jakarta Selatan">Jakarta Selatan</option>
                                <option value="Jakarta Barat">Jakarta Barat</option>
                                <option value="Jakarta Utara">Jakarta Utara</option>
                                <option value="Jakarta Timur">Jakarta Timur</option>
                                <option value="Jakarta Pusat">Jakarta Pusat</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Join Date *</label>
                            <input type="date" x-model="formData.join_date" required
                                class="w-full px-4 py-3 border-2 border-gray-200 dark:border-gray-700 rounded-xl bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status *</label>
                        <select x-model="formData.status" required
                            class="w-full px-4 py-3 border-2 border-gray-200 dark:border-gray-700 rounded-xl bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all">
                            <option value="active">Active</option>
                            <option value="probation">Probation</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>

                    <div class="flex gap-3 pt-6 border-t border-gray-200 dark:border-gray-700">
                        <button type="button" @click="showModal = false"
                            class="flex-1 px-6 py-3 border-2 border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 rounded-xl font-semibold hover:bg-gray-50 dark:hover:bg-gray-900 transition-colors">
                            Cancel
                        </button>
                        <button type="submit" :disabled="isSubmitting"
                            class="flex-1 px-6 py-3 bg-gradient-to-r from-brand-600 to-indigo-600 text-white rounded-xl font-semibold hover:from-brand-700 hover:to-indigo-700 transition-all shadow-lg shadow-brand-500/30 disabled:opacity-50 disabled:cursor-not-allowed">
                            <span x-show="!isSubmitting" x-text="modalMode === 'add' ? 'Add Salesman' : 'Update Salesman'"></span>
                            <span x-show="isSubmitting">Processing...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function salesmanPage() {
    return {
        isLoading: false,
        showModal: false,
        isSubmitting: false,
        modalMode: 'add',
        searchQuery: '',
        filterTerritory: '',
        salesmen: [
            { id: 1, name: 'Ahmad Rizky', code: 'SLM-001', phone: '0812-3456-7890', email: 'ahmad.rizky@saga.com', territory: 'Jakarta Selatan', join_date: '2025-06-15', performance: 85, status: 'active' },
            { id: 2, name: 'Budi Santoso', code: 'SLM-002', phone: '0813-4567-8901', email: 'budi.santoso@saga.com', territory: 'Jakarta Barat', join_date: '2025-07-20', performance: 72, status: 'active' },
            { id: 3, name: 'Citra Dewi', code: 'SLM-003', phone: '0814-5678-9012', email: 'citra.dewi@saga.com', territory: 'Jakarta Utara', join_date: '2025-08-10', performance: 91, status: 'active' },
            { id: 4, name: 'Doni Pratama', code: 'SLM-004', phone: '0815-6789-0123', email: 'doni.pratama@saga.com', territory: 'Jakarta Timur', join_date: '2025-09-05', performance: 68, status: 'probation' },
            { id: 5, name: 'Eka Sari', code: 'SLM-005', phone: '0816-7890-1234', email: 'eka.sari@saga.com', territory: 'Jakarta Pusat', join_date: '2025-05-12', performance: 94, status: 'active' },
        ],
        formData: {
            id: null,
            name: '',
            code: '',
            phone: '',
            email: '',
            territory: '',
            join_date: '',
            status: 'active'
        },

        get filteredSalesmen() {
            let result = this.salesmen;
            
            if (this.searchQuery) {
                const q = this.searchQuery.toLowerCase();
                result = result.filter(s =>
                    s.name.toLowerCase().includes(q) ||
                    s.code.toLowerCase().includes(q) ||
                    s.email.toLowerCase().includes(q) ||
                    s.territory.toLowerCase().includes(q)
                );
            }
            
            if (this.filterTerritory) {
                result = result.filter(s => s.territory === this.filterTerritory);
            }
            
            return result;
        },

        get activeCount() {
            return this.salesmen.filter(s => s.status === 'active').length;
        },

        get totalSales() {
            return this.salesmen.reduce((sum, s) => sum + (s.performance * 150000), 0);
        },

        get avgPerformance() {
            if (this.salesmen.length === 0) return 0;
            const total = this.salesmen.reduce((sum, s) => sum + s.performance, 0);
            return Math.round(total / this.salesmen.length);
        },

        getInitials(name) {
            return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);
        },

        formatDate(dateStr) {
            return new Date(dateStr).toLocaleDateString('id-ID', {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
        },

        formatCurrency(amount) {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            }).format(amount);
        },

        formatStatus(status) {
            const statuses = {
                'active': 'Active',
                'probation': 'Probation',
                'inactive': 'Inactive'
            };
            return statuses[status] || status;
        },

        getPerformanceColor(performance) {
            if (performance >= 80) return 'bg-green-500';
            if (performance >= 60) return 'bg-yellow-500';
            return 'bg-red-500';
        },

        openModal(mode) {
            this.modalMode = mode;
            this.formData = {
                id: null,
                name: '',
                code: '',
                phone: '',
                email: '',
                territory: '',
                join_date: '',
                status: 'active'
            };
            this.showModal = true;
        },

        editSalesman(salesman) {
            this.modalMode = 'edit';
            this.formData = { ...salesman };
            this.showModal = true;
        },

        viewDetails(salesman) {
            Swal.fire({
                title: salesman.name,
                html: `
                    <div class="text-left space-y-3 pt-4">
                        <div class="flex items-center gap-3">
                            <div class="w-16 h-16 bg-gradient-to-br from-brand-400 to-indigo-500 rounded-full flex items-center justify-center text-white font-bold text-xl">
                                ${this.getInitials(salesman.name)}
                            </div>
                            <div>
                                <p class="font-bold text-lg">${salesman.name}</p>
                                <p class="text-gray-500">${salesman.code}</p>
                            </div>
                        </div>
                        <div class="border-t pt-3 space-y-2">
                            <p><strong>Phone:</strong> ${salesman.phone}</p>
                            <p><strong>Email:</strong> ${salesman.email}</p>
                            <p><strong>Territory:</strong> ${salesman.territory}</p>
                            <p><strong>Join Date:</strong> ${this.formatDate(salesman.join_date)}</p>
                            <p><strong>Performance:</strong> ${salesman.performance}%</p>
                            <p><strong>Status:</strong> ${this.formatStatus(salesman.status)}</p>
                        </div>
                    </div>
                `,
                confirmButtonText: 'Close',
                confirmButtonColor: '#4f46e5'
            });
        },

        async submitForm() {
            this.isSubmitting = true;
            
            // Simulate API call
            await new Promise(resolve => setTimeout(resolve, 1000));
            
            if (this.modalMode === 'add') {
                const newSalesman = {
                    id: Date.now(),
                    ...this.formData,
                    performance: 0
                };
                this.salesmen.push(newSalesman);
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: 'Salesman added successfully',
                    timer: 2000,
                    showConfirmButton: false
                });
            } else {
                const index = this.salesmen.findIndex(s => s.id === this.formData.id);
                if (index !== -1) {
                    this.salesmen[index] = { ...this.salesmen[index], ...this.formData };
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: 'Salesman updated successfully',
                        timer: 2000,
                        showConfirmButton: false
                    });
                }
            }
            
            this.showModal = false;
            this.isSubmitting = false;
        },

        deleteSalesman(salesman) {
            Swal.fire({
                title: 'Delete Salesman?',
                text: `Are you sure you want to delete ${salesman.name}? This action cannot be undone.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, Delete',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#6b7280'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.salesmen = this.salesmen.filter(s => s.id !== salesman.id);
                    Swal.fire({
                        title: 'Deleted',
                        text: 'Salesman has been deleted',
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false
                    });
                }
            });
        }
    }
}
</script>
@endpush
@endsection
