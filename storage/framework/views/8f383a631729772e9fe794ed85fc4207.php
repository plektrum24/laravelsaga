<?php $__env->startSection('title', 'Goods In - Item Receiving | SAGA POS'); ?>

<?php $__env->startSection('content'); ?>
<div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-800 p-6" x-data="goodsInSystem()">
    <!-- Header Section -->
    <div class="max-w-9xl mx-auto mb-8">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-2xl flex items-center justify-center shadow-xl shadow-emerald-500/30">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4m0 0c0 1.657 1.343 3 3 3h10c1.657 0 3-1.343 3-3m0 0V6"></path>
                    </svg>
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-gray-800 dark:text-white">Goods In</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Manage product receiving from suppliers</p>
                </div>
            </div>
            
            <div class="flex gap-3">
                <button @click="openCreateModal()" class="px-6 py-3 bg-gradient-to-r from-emerald-600 to-teal-600 text-white rounded-xl font-semibold hover:from-emerald-700 hover:to-teal-700 transition-all shadow-lg shadow-emerald-500/30 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    New Goods In
                </button>
                <button @click="exportToExcel()" class="px-6 py-3 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 rounded-xl font-semibold border-2 border-gray-200 dark:border-gray-700 hover:border-emerald-500 transition-all flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Export
                </button>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="max-w-9xl mx-auto mb-8 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Goods In -->
        <div class="bg-gradient-to-br from-emerald-500 to-teal-600 rounded-2xl p-6 text-white shadow-xl shadow-emerald-500/20">
            <div class="flex items-center justify-between mb-4">
                <div class="w-14 h-14 bg-white/20 backdrop-blur-sm rounded-2xl flex items-center justify-center">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <span class="text-3xl font-bold" x-text="stats.total"></span>
            </div>
            <p class="text-emerald-100 text-sm font-medium">Total Goods In</p>
            <p class="text-emerald-200 text-xs mt-1">This month</p>
        </div>

        <!-- Total Value -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-lg shadow-gray-200/50 dark:shadow-none border border-gray-100 dark:border-gray-700">
            <div class="flex items-center justify-between mb-4">
                <div class="w-14 h-14 bg-gradient-to-br from-blue-100 to-cyan-100 dark:from-blue-900/30 dark:to-cyan-900/30 rounded-2xl flex items-center justify-center">
                    <svg class="w-7 h-7 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <span class="text-2xl font-bold text-gray-800 dark:text-white" x-text="formatCurrency(stats.totalValue)"></span>
            </div>
            <p class="text-gray-500 dark:text-gray-400 text-sm font-medium">Total Value</p>
            <p class="text-blue-600 text-xs mt-1">All purchases</p>
        </div>

        <!-- Pending Payment -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-lg shadow-gray-200/50 dark:shadow-none border border-gray-100 dark:border-gray-700">
            <div class="flex items-center justify-between mb-4">
                <div class="w-14 h-14 bg-gradient-to-br from-amber-100 to-orange-100 dark:from-amber-900/30 dark:to-orange-900/30 rounded-2xl flex items-center justify-center">
                    <svg class="w-7 h-7 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <span class="text-3xl font-bold text-gray-800 dark:text-white" x-text="stats.pending"></span>
            </div>
            <p class="text-gray-500 dark:text-gray-400 text-sm font-medium">Pending Payment</p>
            <p class="text-amber-600 text-xs mt-1">Unpaid purchases</p>
        </div>

        <!-- Today's Receiving -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-lg shadow-gray-200/50 dark:shadow-none border border-gray-100 dark:border-gray-700">
            <div class="flex items-center justify-between mb-4">
                <div class="w-14 h-14 bg-gradient-to-br from-purple-100 to-indigo-100 dark:from-purple-900/30 dark:to-indigo-900/30 rounded-2xl flex items-center justify-center">
                    <svg class="w-7 h-7 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <span class="text-3xl font-bold text-gray-800 dark:text-white" x-text="stats.today"></span>
            </div>
            <p class="text-gray-500 dark:text-gray-400 text-sm font-medium">Today</p>
            <p class="text-purple-600 text-xs mt-1">Received today</p>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-9xl mx-auto bg-white dark:bg-gray-800 rounded-3xl shadow-2xl shadow-gray-200/50 dark:shadow-none border border-gray-100 dark:border-gray-700 overflow-hidden">
        <!-- Filters Header -->
        <div class="p-6 border-b border-gray-100 dark:border-gray-700 bg-gradient-to-r from-gray-50 to-white dark:from-gray-900 dark:to-gray-800">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div>
                    <h2 class="text-xl font-bold text-gray-800 dark:text-white">All Goods In Transactions</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Manage and track all incoming products</p>
                </div>
                
                <div class="flex flex-wrap gap-3">
                    <!-- Search -->
                    <div class="relative flex-1 lg:flex-none">
                        <input type="text" 
                            x-model="filters.search" 
                            @input.debounce.500ms="loadPurchases()"
                            placeholder="Search invoice, supplier..."
                            class="w-full lg:w-64 pl-10 pr-4 py-2.5 border-2 border-gray-200 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 dark:bg-gray-700 dark:text-white transition-all">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    
                    <!-- Status Filter -->
                    <select x-model="filters.paymentStatus" @change="loadPurchases()" class="px-4 py-2.5 border-2 border-gray-200 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 dark:bg-gray-700 dark:text-white transition-all">
                        <option value="all">All Payment Status</option>
                        <option value="paid">Paid</option>
                        <option value="unpaid">Unpaid</option>
                        <option value="partial">Partial</option>
                    </select>
                    
                    <!-- Date Range -->
                    <input type="date" x-model="filters.startDate" @change="loadPurchases()" class="px-4 py-2.5 border-2 border-gray-200 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 dark:bg-gray-700 dark:text-white transition-all">
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-800">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Invoice Number</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Supplier</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Items</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total Amount</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Payment Status</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    <!-- Loading State -->
                    <template x-if="loading">
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center">
                                <div class="inline-block animate-spin rounded-full h-10 w-10 border-4 border-emerald-200"></div>
                                <div class="animate-spin rounded-full h-10 w-10 border-t-4 border-emerald-600 absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2"></div>
                                <p class="mt-4 text-gray-500 dark:text-gray-400 font-medium">Loading transactions...</p>
                            </td>
                        </tr>
                    </template>

                    <!-- Empty State -->
                    <template x-if="!loading && purchases.length === 0">
                        <tr>
                            <td colspan="7" class="px-6 py-16 text-center">
                                <div class="w-20 h-20 mx-auto mb-4 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center">
                                    <svg class="w-10 h-10 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4m0 0c0 1.657 1.343 3 3 3h10c1.657 0 3-1.343 3-3m0 0V6"></path>
                                    </svg>
                                </div>
                                <p class="text-lg font-semibold text-gray-700 dark:text-gray-300 mb-2">No goods in transactions</p>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Start by creating your first receiving transaction</p>
                                <button @click="openCreateModal()" class="px-6 py-2.5 bg-emerald-600 text-white rounded-xl hover:bg-emerald-700 transition-all font-medium inline-flex items-center gap-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                    </svg>
                                    Create First Goods In
                                </button>
                            </td>
                        </tr>
                    </template>

                    <!-- Data Rows -->
                    <template x-for="purchase in purchases" :key="purchase.id">
                        <tr class="hover:bg-gradient-to-r hover:from-emerald-50/50 hover:to-teal-50/50 dark:hover:bg-gray-700/50 transition-all">
                            <td class="px-6 py-4">
                                <span class="text-sm font-bold text-emerald-600 dark:text-emerald-400" x-text="purchase.invoice_number"></span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-sm text-gray-600 dark:text-gray-400" x-text="formatDate(purchase.date)"></span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-gradient-to-br from-emerald-100 to-teal-100 dark:from-emerald-900/30 dark:to-teal-900/30 rounded-xl flex items-center justify-center">
                                        <span class="text-sm font-bold text-emerald-600 dark:text-emerald-400" x-text="purchase.supplier_name.charAt(0)"></span>
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-gray-800 dark:text-white" x-text="purchase.supplier_name"></p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400" x-text="purchase.supplier_code || ''"></p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-sm text-gray-600 dark:text-gray-400" x-text="purchase.items_count + ' items'"></span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <span class="text-sm font-bold text-gray-800 dark:text-white" x-text="formatCurrency(purchase.total_amount)"></span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span :class="getPaymentStatusBadge(purchase.payment_status).class" 
                                    class="px-3 py-1 rounded-full text-xs font-bold" 
                                    x-text="getPaymentStatusBadge(purchase.payment_status).label"></span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <button @click="viewDetails(purchase)" 
                                        class="p-2 text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition" 
                                        title="View Details">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </button>
                                    <button @click="printGRN(purchase)" 
                                        class="p-2 text-purple-600 hover:bg-purple-50 dark:hover:bg-purple-900/20 rounded-lg transition" 
                                        title="Print GRN">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                                        </svg>
                                    </button>
                                    <button @click="editPurchase(purchase)" 
                                        x-show="purchase.status === 'draft'"
                                        class="p-2 text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-900/20 rounded-lg transition" 
                                        title="Edit">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="p-6 border-t border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-900">
            <div class="flex items-center justify-between">
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Showing <span class="font-semibold" x-text="pagination.from"></span> to <span class="font-semibold" x-text="pagination.to"></span> of <span class="font-semibold" x-text="pagination.total"></span> results
                </p>
                <div class="flex gap-2">
                    <button @click="prevPage()" :disabled="pagination.currentPage === 1" 
                        class="px-4 py-2 border-2 border-gray-200 dark:border-gray-600 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed transition-all">
                        Previous
                    </button>
                    <template x-for="page in pagination.pages" :key="page">
                        <button @click="goToPage(page)" 
                            :class="page === pagination.currentPage ? 'bg-emerald-600 text-white border-emerald-600' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border-gray-200 dark:border-gray-600'"
                            class="px-4 py-2 border-2 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-700 transition-all font-medium"
                            x-text="page"></button>
                    </template>
                    <button @click="nextPage()" :disabled="pagination.currentPage === pagination.lastPage" 
                        class="px-4 py-2 border-2 border-gray-200 dark:border-gray-600 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed transition-all">
                        Next
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Create/Edit Modal -->
    <div x-show="showCreateModal" x-cloak 
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0">
        
        <div @click.away="closeCreateModal()" 
            class="bg-white dark:bg-gray-800 rounded-3xl shadow-2xl w-full max-w-6xl max-h-[90vh] overflow-y-auto"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100">
            
            <!-- Modal Header -->
            <div class="p-6 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between sticky top-0 bg-white dark:bg-gray-800 z-10 rounded-t-3xl">
                <div>
                    <h3 class="text-2xl font-bold text-gray-800 dark:text-white" x-text="isEditing ? 'Edit Goods In' : 'Create New Goods In'"></h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1" x-text="isEditing ? 'Update receiving transaction' : 'Record incoming products from supplier'"></p>
                </div>
                <button @click="closeCreateModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="p-6 space-y-6">
                <!-- Supplier & Date Info -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Supplier *</label>
                        <select x-model="form.supplierId" class="w-full px-4 py-3 border-2 border-gray-200 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 dark:bg-gray-700 dark:text-white">
                            <option value="">Select Supplier</option>
                            <template x-for="supplier in suppliers" :key="supplier.id">
                                <option :value="supplier.id" x-text="supplier.name"></option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Invoice Number</label>
                        <input type="text" x-model="form.invoiceNumber" readonly class="w-full px-4 py-3 bg-gray-100 dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-600 rounded-xl text-gray-500 cursor-not-allowed">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Date *</label>
                        <input type="date" x-model="form.date" class="w-full px-4 py-3 border-2 border-gray-200 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 dark:bg-gray-700 dark:text-white">
                    </div>
                </div>

                <!-- Add Products -->
                <div class="bg-gradient-to-br from-gray-50 to-white dark:from-gray-900 dark:to-gray-800 rounded-2xl border-2 border-dashed border-gray-300 dark:border-gray-600 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h4 class="text-lg font-bold text-gray-800 dark:text-white">Products</h4>
                        <button @click="openProductSelector()" class="px-4 py-2 bg-emerald-600 text-white rounded-xl hover:bg-emerald-700 transition-all font-medium inline-flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            Add Product
                        </button>
                    </div>

                    <!-- Products Table -->
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-100 dark:bg-gray-700">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Product</th>
                                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Quantity</th>
                                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Unit Price</th>
                                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Subtotal</th>
                                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                <template x-for="(item, index) in form.items" :key="index">
                                    <tr>
                                        <td class="px-4 py-3">
                                            <p class="font-semibold text-gray-800 dark:text-white" x-text="item.name"></p>
                                            <p class="text-xs text-gray-500" x-text="item.sku"></p>
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            <input type="number" x-model.number="item.qty" min="1" class="w-24 px-3 py-1.5 border-2 border-gray-200 dark:border-gray-600 rounded-lg text-right dark:bg-gray-700 dark:text-white">
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            <span class="text-sm text-gray-600 dark:text-gray-400" x-text="formatCurrency(item.buy_price)"></span>
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            <span class="text-sm font-bold text-gray-800 dark:text-white" x-text="formatCurrency(item.qty * item.buy_price)"></span>
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <button @click="removeItem(index)" class="p-2 text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                                <tr x-show="form.items.length === 0">
                                    <td colspan="5" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                        No products added. Click "Add Product" to start.
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <td colspan="3" class="px-4 py-3 text-right font-bold text-gray-700 dark:text-gray-300">Total:</td>
                                    <td class="px-4 py-3 text-right">
                                        <span class="text-lg font-bold text-emerald-600 dark:text-emerald-400" x-text="formatCurrency(calculateTotal())"></span>
                                    </td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <!-- Payment Info -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Payment Status</label>
                        <select x-model="form.paymentStatus" class="w-full px-4 py-3 border-2 border-gray-200 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 dark:bg-gray-700 dark:text-white">
                            <option value="paid">Paid</option>
                            <option value="unpaid">Unpaid</option>
                            <option value="partial">Partial</option>
                        </select>
                    </div>
                    <div x-show="form.paymentStatus !== 'paid'">
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Due Date</label>
                        <input type="date" x-model="form.dueDate" class="w-full px-4 py-3 border-2 border-gray-200 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 dark:bg-gray-700 dark:text-white">
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="p-6 border-t border-gray-100 dark:border-gray-700 flex justify-end gap-3 sticky bottom-0 bg-white dark:bg-gray-800 rounded-b-3xl">
                <button @click="closeCreateModal()" class="px-6 py-3 border-2 border-gray-200 dark:border-gray-600 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-700 transition-all font-medium">Cancel</button>
                <button @click="savePurchase()" :disabled="isSaving || form.items.length === 0" 
                    class="px-6 py-3 bg-gradient-to-r from-emerald-600 to-teal-600 text-white rounded-xl hover:from-emerald-700 hover:to-teal-700 disabled:opacity-50 disabled:cursor-not-allowed transition-all font-medium inline-flex items-center gap-2">
                    <svg x-show="isSaving" class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span x-text="isSaving ? 'Saving...' : (isEditing ? 'Update Goods In' : 'Save Goods In')"></span>
                </button>
            </div>
        </div>
    </div>

    <!-- Product Selector Modal -->
    <div x-show="showProductModal" x-cloak 
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
        @click.away="showProductModal = false">
        <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-2xl w-full max-w-4xl max-h-[80vh] overflow-hidden">
            <div class="p-6 border-b border-gray-100 dark:border-gray-700">
                <h3 class="text-xl font-bold text-gray-800 dark:text-white">Select Products</h3>
                <input type="text" x-model="productSearch" @input.debounce="searchProducts()" placeholder="Search products..." class="mt-3 w-full px-4 py-2.5 border-2 border-gray-200 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 dark:bg-gray-700 dark:text-white">
            </div>
            <div class="p-6 overflow-y-auto max-h-96">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <template x-for="product in filteredProducts" :key="product.id">
                        <div @click="addProduct(product)" class="p-4 border-2 border-gray-200 dark:border-gray-700 rounded-xl hover:border-emerald-500 cursor-pointer transition-all">
                            <p class="font-semibold text-gray-800 dark:text-white" x-text="product.name"></p>
                            <p class="text-xs text-gray-500" x-text="product.sku"></p>
                            <p class="text-sm font-bold text-emerald-600 mt-2" x-text="formatCurrency(product.buy_price)"></p>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>

    <!-- View Details Modal -->
    <div x-show="viewModal" x-cloak 
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <div @click.away="viewModal = false" class="bg-white dark:bg-gray-800 rounded-3xl shadow-2xl w-full max-w-4xl max-h-[90vh] overflow-y-auto">
            <div class="p-6 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center sticky top-0 bg-white dark:bg-gray-800 z-10 rounded-t-3xl">
                <div>
                    <h3 class="text-2xl font-bold text-gray-800 dark:text-white">Goods In Details</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400" x-text="selectedPurchase?.invoice_number"></p>
                </div>
                <button @click="viewModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Invoice Number</p>
                        <p class="text-lg font-bold text-gray-800 dark:text-white" x-text="selectedPurchase?.invoice_number"></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Date</p>
                        <p class="text-lg font-bold text-gray-800 dark:text-white" x-text="formatDate(selectedPurchase?.date)"></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Supplier</p>
                        <p class="text-lg font-bold text-gray-800 dark:text-white" x-text="selectedPurchase?.supplier_name"></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Payment Status</p>
                        <span :class="getPaymentStatusBadge(selectedPurchase?.payment_status).class" class="px-3 py-1 rounded-full text-sm font-bold" x-text="getPaymentStatusBadge(selectedPurchase?.payment_status).label"></span>
                    </div>
                </div>
                <!-- Items Table -->
                <div class="border border-gray-200 dark:border-gray-700 rounded-2xl overflow-hidden mb-6">
                    <table class="w-full">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Product</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Qty</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Unit Price</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <template x-for="item in selectedPurchase?.items" :key="item.id">
                                <tr>
                                    <td class="px-4 py-3">
                                        <p class="font-medium text-gray-800 dark:text-white" x-text="item.name"></p>
                                        <p class="text-xs text-gray-500" x-text="item.sku"></p>
                                    </td>
                                    <td class="px-4 py-3 text-right text-gray-600 dark:text-gray-400" x-text="item.quantity"></td>
                                    <td class="px-4 py-3 text-right text-gray-600 dark:text-gray-400" x-text="formatCurrency(item.cost_price)"></td>
                                    <td class="px-4 py-3 text-right font-bold text-gray-800 dark:text-white" x-text="formatCurrency(item.quantity * item.cost_price)"></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
                <div class="flex justify-end">
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-2xl p-4 min-w-[250px]">
                        <div class="flex justify-between mb-2">
                            <span class="text-gray-500 dark:text-gray-400">Subtotal</span>
                            <span class="font-medium text-gray-800 dark:text-white" x-text="formatCurrency(selectedPurchase?.total_amount)"></span>
                        </div>
                        <div class="border-t border-gray-200 dark:border-gray-600 my-2"></div>
                        <div class="flex justify-between">
                            <span class="text-lg font-bold text-gray-500 dark:text-gray-400">Total</span>
                            <span class="text-2xl font-bold text-emerald-600 dark:text-emerald-400" x-text="formatCurrency(selectedPurchase?.total_amount)"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function goodsInSystem() {
    return {
        loading: false,
        isSaving: false,
        isEditing: false,
        showCreateModal: false,
        showProductModal: false,
        viewModal: false,
        selectedPurchase: null,
        productSearch: '',
        
        stats: {
            total: 0,
            totalValue: 0,
            pending: 0,
            today: 0
        },
        
        filters: {
            search: '',
            paymentStatus: 'all',
            startDate: ''
        },
        
        purchases: [],
        suppliers: [],
        products: [],
        filteredProducts: [],
        
        pagination: {
            currentPage: 1,
            lastPage: 1,
            total: 0,
            from: 0,
            to: 0,
            pages: []
        },
        
        form: {
            supplierId: '',
            invoiceNumber: '',
            date: new Date().toISOString().split('T')[0],
            items: [],
            paymentStatus: 'paid',
            dueDate: ''
        },

        async init() {
            await Promise.all([
                this.loadPurchases(),
                this.loadSuppliers(),
                this.loadProducts(),
                this.loadStats()
            ]);
        },

        async loadPurchases() {
            this.loading = true;
            try {
                const token = localStorage.getItem('saga_token');
                const params = new URLSearchParams({
                    page: this.pagination.currentPage,
                    limit: 20,
                    ...this.filters
                });
                
                const response = await fetch(`/api/purchases?${params}`, {
                    headers: { 'Authorization': 'Bearer ' + token }
                });
                const data = await response.json();
                
                if (data.success) {
                    this.purchases = data.data;
                    this.pagination = {
                        currentPage: data.pagination.current_page,
                        lastPage: data.pagination.last_page,
                        total: data.pagination.total,
                        from: data.pagination.from,
                        to: data.pagination.to,
                        pages: this.getPaginationRange()
                    };
                }
            } catch (error) {
                console.error('Load purchases error:', error);
            } finally {
                this.loading = false;
            }
        },

        async loadSuppliers() {
            try {
                const token = localStorage.getItem('saga_token');
                const response = await fetch('/api/suppliers', {
                    headers: { 'Authorization': 'Bearer ' + token }
                });
                const data = await response.json();
                if (data.success) {
                    this.suppliers = data.data;
                }
            } catch (error) {
                console.error('Load suppliers error:', error);
            }
        },

        async loadProducts() {
            try {
                const token = localStorage.getItem('saga_token');
                const response = await fetch('/api/products', {
                    headers: { 'Authorization': 'Bearer ' + token }
                });
                const data = await response.json();
                if (data.success) {
                    this.products = data.data.products || [];
                    this.filteredProducts = this.products;
                }
            } catch (error) {
                console.error('Load products error:', error);
            }
        },

        async loadStats() {
            try {
                const token = localStorage.getItem('saga_token');
                const response = await fetch('/api/purchases/stats', {
                    headers: { 'Authorization': 'Bearer ' + token }
                });
                const data = await response.json();
                if (data.success) {
                    this.stats = data.data;
                }
            } catch (error) {
                console.error('Load stats error:', error);
            }
        },

        searchProducts() {
            if (!this.productSearch) {
                this.filteredProducts = this.products;
                return;
            }
            this.filteredProducts = this.products.filter(p => 
                p.name.toLowerCase().includes(this.productSearch.toLowerCase()) ||
                p.sku.toLowerCase().includes(this.productSearch.toLowerCase())
            );
        },

        openProductSelector() {
            this.showProductModal = true;
        },

        addProduct(product) {
            const existing = this.form.items.find(i => i.id === product.id);
            if (existing) {
                existing.qty++;
            } else {
                this.form.items.push({
                    id: product.id,
                    name: product.name,
                    sku: product.sku,
                    qty: 1,
                    buy_price: product.buy_price || 0
                });
            }
            this.showProductModal = false;
            this.productSearch = '';
        },

        removeItem(index) {
            this.form.items.splice(index, 1);
        },

        calculateTotal() {
            return this.form.items.reduce((sum, item) => sum + (item.qty * item.buy_price), 0);
        },

        openCreateModal() {
            this.isEditing = false;
            this.form = {
                supplierId: '',
                invoiceNumber: this.generateInvoiceNumber(),
                date: new Date().toISOString().split('T')[0],
                items: [],
                paymentStatus: 'paid',
                dueDate: ''
            };
            this.showCreateModal = true;
        },

        editPurchase(purchase) {
            this.isEditing = true;
            this.form = {
                supplierId: purchase.supplier_id,
                invoiceNumber: purchase.invoice_number,
                date: purchase.date,
                items: purchase.items || [],
                paymentStatus: purchase.payment_status,
                dueDate: purchase.due_date
            };
            this.showCreateModal = true;
        },

        closeCreateModal() {
            this.showCreateModal = false;
        },

        async savePurchase() {
            if (!this.form.supplierId || this.form.items.length === 0) {
                Swal.fire('Error', 'Please fill all required fields', 'error');
                return;
            }

            this.isSaving = true;
            try {
                const token = localStorage.getItem('saga_token');
                const url = this.isEditing ? `/api/purchases/${this.form.id}` : '/api/purchases';
                const method = this.isEditing ? 'PUT' : 'POST';

                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': 'Bearer ' + token
                    },
                    body: JSON.stringify(this.form)
                });

                const data = await response.json();
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: this.isEditing ? 'Goods In updated successfully' : 'Goods In created successfully'
                    });
                    this.closeCreateModal();
                    this.loadPurchases();
                    this.loadStats();
                } else {
                    Swal.fire('Error', data.message || 'Failed to save', 'error');
                }
            } catch (error) {
                console.error('Save error:', error);
                Swal.fire('Error', 'Failed to save goods in', 'error');
            } finally {
                this.isSaving = false;
            }
        },

        viewDetails(purchase) {
            this.selectedPurchase = purchase;
            this.viewModal = true;
        },

        printGRN(purchase) {
            window.open(`/api/purchases/${purchase.id}/receipt`, '_blank');
        },

        exportToExcel() {
            window.location.href = '/api/purchases/export/excel';
        },

        generateInvoiceNumber() {
            const date = new Date();
            const prefix = 'GRN-' + date.getFullYear() + 
                (date.getMonth() + 1).toString().padStart(2, '0') + 
                date.getDate().toString().padStart(2, '0');
            const random = Math.floor(Math.random() * 1000).toString().padStart(3, '0');
            return prefix + '-' + random;
        },

        getPaginationRange() {
            const pages = [];
            for (let i = 1; i <= this.pagination.lastPage; i++) {
                if (i === 1 || i === this.pagination.lastPage || (i >= this.pagination.currentPage - 1 && i <= this.pagination.currentPage + 1)) {
                    pages.push(i);
                }
            }
            return pages;
        },

        goToPage(page) {
            this.pagination.currentPage = page;
            this.loadPurchases();
        },

        prevPage() {
            if (this.pagination.currentPage > 1) {
                this.goToPage(this.pagination.currentPage - 1);
            }
        },

        nextPage() {
            if (this.pagination.currentPage < this.pagination.lastPage) {
                this.goToPage(this.pagination.currentPage + 1);
            }
        },

        getPaymentStatusBadge(status) {
            switch(status) {
                case 'paid': return { class: 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400', label: 'Paid' };
                case 'unpaid': return { class: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400', label: 'Unpaid' };
                case 'partial': return { class: 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400', label: 'Partial' };
                default: return { class: 'bg-gray-100 text-gray-700', label: '-' };
            }
        },

        formatCurrency(amount) {
            return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(amount || 0);
        },

        formatDate(dateStr) {
            if (!dateStr) return '-';
            return new Date(dateStr).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
        }
    }
}
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\Project App\laravelsaga\resources\views/pages/inventory/receiving/goods-in-standalone.blade.php ENDPATH**/ ?>