@extends('layouts.app')

@section('title', 'Branch Management | SAGA POS')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-800 p-6" x-data="branchesPage()">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="flex flex-col md:flex-row items-start md:items-center justify-between mb-8 gap-4">
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 bg-gradient-to-br from-cyan-500 to-blue-600 rounded-2xl flex items-center justify-center shadow-xl shadow-cyan-500/30">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-gray-800 dark:text-white">Branch Management</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Kelola cabang toko Anda dengan mudah</p>
                </div>
            </div>
            <button @click="openAddModal()" 
                    class="px-6 py-3 bg-gradient-to-r from-cyan-600 to-blue-600 text-white rounded-xl font-semibold hover:from-cyan-700 hover:to-blue-700 transition-all shadow-lg shadow-cyan-500/30 flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Add Branch
            </button>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-gradient-to-br from-cyan-500 to-blue-600 rounded-2xl p-6 text-white shadow-xl shadow-cyan-500/30">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-cyan-100 text-sm font-medium">Total Branches</p>
                        <h3 class="text-4xl font-bold mt-2" x-text="stats.total_branches || 0"></h3>
                    </div>
                    <div class="w-16 h-16 bg-white/20 rounded-2xl flex items-center justify-center">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border-2 border-green-200 dark:border-green-900/30">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 dark:text-gray-400 text-sm font-medium">Active Branches</p>
                        <h3 class="text-4xl font-bold text-green-600 dark:text-green-400 mt-2" x-text="stats.active_branches || 0"></h3>
                    </div>
                    <div class="w-16 h-16 bg-green-100 dark:bg-green-900/30 rounded-2xl flex items-center justify-center">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border-2 border-red-200 dark:border-red-900/30">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 dark:text-gray-400 text-sm font-medium">Inactive Branches</p>
                        <h3 class="text-4xl font-bold text-red-600 dark:text-red-400 mt-2" x-text="stats.inactive_branches || 0"></h3>
                    </div>
                    <div class="w-16 h-16 bg-red-100 dark:bg-red-900/30 rounded-2xl flex items-center justify-center">
                        <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border-2 border-purple-200 dark:border-purple-900/30">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 dark:text-gray-400 text-sm font-medium">Total Employees</p>
                        <h3 class="text-4xl font-bold text-purple-600 dark:text-purple-400 mt-2" x-text="stats.total_employees || 0"></h3>
                    </div>
                    <div class="w-16 h-16 bg-purple-100 dark:bg-purple-900/30 rounded-2xl flex items-center justify-center">
                        <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Branches Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <template x-for="branch in branches" :key="branch.id">
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden hover:shadow-xl transition-all duration-300 group">
                    <!-- Card Header Image -->
                    <div class="h-40 bg-gradient-to-br from-cyan-500 via-blue-500 to-purple-600 relative overflow-hidden">
                        <div class="absolute inset-0 flex items-center justify-center">
                            <svg class="w-20 h-20 text-white/20 group-hover:scale-110 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                        </div>
                        <div class="absolute top-4 right-4">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wide"
                                  :class="{
                                      'bg-green-500 text-white': branch.status === 'active',
                                      'bg-red-500 text-white': branch.status === 'inactive'
                                  }"
                                  x-text="branch.status || 'Active'">
                            </span>
                        </div>
                        <div class="absolute bottom-4 left-4">
                            <h3 class="text-xl font-bold text-white" x-text="branch.name"></h3>
                            <p class="text-sm text-white/80" x-text="branch.code"></p>
                        </div>
                    </div>

                    <!-- Card Body -->
                    <div class="p-5">
                        <!-- Address -->
                        <div class="flex items-start gap-2 mb-3" x-show="branch.address">
                            <svg class="w-5 h-5 text-gray-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            <p class="text-sm text-gray-600 dark:text-gray-400" x-text="branch.address"></p>
                        </div>

                        <!-- Phone & Email -->
                        <div class="space-y-2 mb-3">
                            <div class="flex items-center gap-2 text-sm" x-show="branch.phone">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                </svg>
                                <span class="text-gray-600 dark:text-gray-400" x-text="branch.phone"></span>
                            </div>
                            <div class="flex items-center gap-2 text-sm" x-show="branch.email">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                </svg>
                                <span class="text-gray-600 dark:text-gray-400" x-text="branch.email"></span>
                            </div>
                        </div>

                        <!-- Manager Info -->
                        <div class="border-t border-gray-200 dark:border-gray-700 pt-3 mb-3" x-show="branch.manager_name">
                            <div class="flex items-center gap-2 text-sm">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                <span class="text-gray-600 dark:text-gray-400">Manager: <span class="font-medium text-gray-800 dark:text-white" x-text="branch.manager_name"></span></span>
                            </div>
                        </div>

                        <!-- Employees Count -->
                        <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                            </svg>
                            <span x-text="(branch.employees_count || 0) + ' employees'"></span>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex gap-2 mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                            <button @click="editBranch(branch)" 
                                    class="flex-1 px-4 py-2 bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 rounded-lg font-medium hover:bg-blue-100 dark:hover:bg-blue-900/30 transition-colors text-sm">
                                Edit
                            </button>
                            <button @click="deleteBranch(branch)" 
                                    class="flex-1 px-4 py-2 bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 rounded-lg font-medium hover:bg-red-100 dark:hover:bg-red-900/30 transition-colors text-sm">
                                Delete
                            </button>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <!-- Empty State -->
        <div x-show="branches.length === 0 && !loading" class="text-center py-16">
            <div class="w-24 h-24 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                </svg>
            </div>
            <h3 class="text-xl font-bold text-gray-800 dark:text-white mb-2">No Branches Yet</h3>
            <p class="text-gray-500 dark:text-gray-400 mb-6">Start by adding your first branch</p>
            <button @click="openAddModal()" class="px-6 py-3 bg-gradient-to-r from-cyan-600 to-blue-600 text-white rounded-xl font-semibold hover:from-cyan-700 hover:to-blue-700 transition-all shadow-lg">
                Add First Branch
            </button>
        </div>
    </div>

    <!-- Add/Edit Branch Modal -->
    <div x-show="showModal" 
         x-cloak
         class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4"
         @click="showModal = false">
        <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-y-auto"
             @click.stop
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95">
            
            <!-- Modal Header -->
            <div class="sticky top-0 z-10 bg-gradient-to-r from-cyan-600 to-blue-600 px-6 py-4 rounded-t-3xl">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-2xl font-bold text-white" x-text="editMode ? 'Edit Branch' : 'Add New Branch'"></h2>
                        <p class="text-cyan-100 text-sm mt-1" x-text="editMode ? 'Update branch information' : 'Create a new branch location'"></p>
                    </div>
                    <button @click="showModal = false" class="w-10 h-10 bg-white/20 hover:bg-white/30 rounded-full flex items-center justify-center transition-colors">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Modal Body (Form) -->
            <div class="p-6">
                <form @submit.prevent="saveBranch()" class="space-y-6">
                    <!-- Basic Information -->
                    <div>
                        <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Basic Information
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                    Branch Name <span class="text-red-500">*</span>
                                </label>
                                <input type="text" 
                                       x-model="form.name"
                                       required
                                       placeholder="e.g., Jakarta Pusat"
                                       class="w-full px-4 py-3 border-2 border-gray-200 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-900 text-gray-800 dark:text-white font-medium focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 transition-all">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                    Branch Code
                                </label>
                                <input type="text" 
                                       x-model="form.code"
                                       placeholder="Auto-generated if empty"
                                       class="w-full px-4 py-3 border-2 border-gray-200 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-900 text-gray-800 dark:text-white font-medium focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 transition-all">
                            </div>
                        </div>
                    </div>

                    <!-- Address Information -->
                    <div>
                        <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                            </svg>
                            Address Information
                        </h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                    Street Address
                                </label>
                                <textarea x-model="form.address"
                                          rows="3"
                                          placeholder="Complete street address"
                                          class="w-full px-4 py-3 border-2 border-gray-200 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-900 text-gray-800 dark:text-white font-medium focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 transition-all"></textarea>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                        City
                                    </label>
                                    <input type="text" 
                                           x-model="form.city"
                                           placeholder="Jakarta"
                                           class="w-full px-4 py-3 border-2 border-gray-200 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-900 text-gray-800 dark:text-white font-medium focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 transition-all">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                        Province
                                    </label>
                                    <input type="text" 
                                           x-model="form.province"
                                           placeholder="DKI Jakarta"
                                           class="w-full px-4 py-3 border-2 border-gray-200 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-900 text-gray-800 dark:text-white font-medium focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 transition-all">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                        Postal Code
                                    </label>
                                    <input type="text" 
                                           x-model="form.postal_code"
                                           placeholder="12345"
                                           class="w-full px-4 py-3 border-2 border-gray-200 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-900 text-gray-800 dark:text-white font-medium focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 transition-all">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Contact Information -->
                    <div>
                        <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                            </svg>
                            Contact Information
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                    Phone Number
                                </label>
                                <input type="text" 
                                       x-model="form.phone"
                                       placeholder="021-1234567"
                                       class="w-full px-4 py-3 border-2 border-gray-200 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-900 text-gray-800 dark:text-white font-medium focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 transition-all">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                    Email Address
                                </label>
                                <input type="email" 
                                       x-model="form.email"
                                       placeholder="branch@example.com"
                                       class="w-full px-4 py-3 border-2 border-gray-200 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-900 text-gray-800 dark:text-white font-medium focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 transition-all">
                            </div>
                        </div>
                    </div>

                    <!-- Manager Information -->
                    <div>
                        <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            Manager Information
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                    Manager Name
                                </label>
                                <input type="text" 
                                       x-model="form.manager_name"
                                       placeholder="Manager full name"
                                       class="w-full px-4 py-3 border-2 border-gray-200 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-900 text-gray-800 dark:text-white font-medium focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 transition-all">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                    Manager Phone
                                </label>
                                <input type="text" 
                                       x-model="form.manager_phone"
                                       placeholder="0812-3456-7890"
                                       class="w-full px-4 py-3 border-2 border-gray-200 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-900 text-gray-800 dark:text-white font-medium focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 transition-all">
                            </div>
                        </div>
                    </div>

                    <!-- Status -->
                    <div>
                        <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Branch Status
                        </h3>
                        <div class="flex gap-4">
                            <label class="flex-1 cursor-pointer">
                                <input type="radio" 
                                       x-model="form.status"
                                       value="active"
                                       class="peer sr-only">
                                <div class="p-4 border-2 border-gray-200 dark:border-gray-600 rounded-xl text-center peer-checked:border-green-500 peer-checked:bg-green-50 dark:peer-checked:bg-green-900/20 transition-all">
                                    <div class="text-2xl mb-1">✅</div>
                                    <div class="font-bold text-gray-800 dark:text-white">Active</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">Operational</div>
                                </div>
                            </label>
                            <label class="flex-1 cursor-pointer">
                                <input type="radio" 
                                       x-model="form.status"
                                       value="inactive"
                                       class="peer sr-only">
                                <div class="p-4 border-2 border-gray-200 dark:border-gray-600 rounded-xl text-center peer-checked:border-red-500 peer-checked:bg-red-50 dark:peer-checked:bg-red-900/20 transition-all">
                                    <div class="text-2xl mb-1">❌</div>
                                    <div class="font-bold text-gray-800 dark:text-white">Inactive</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">Not operational</div>
                                </div>
                            </label>
                        </div>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Branch Status
                        </h3>
                        <div class="flex gap-4">
                            <label class="flex-1 cursor-pointer">
                                <input type="radio" 
                                       x-model="form.is_active"
                                       :value="true"
                                       class="peer sr-only">
                                <div class="p-4 border-2 border-gray-200 dark:border-gray-600 rounded-xl text-center peer-checked:border-green-500 peer-checked:bg-green-50 dark:peer-checked:bg-green-900/20 transition-all">
                                    <div class="text-2xl mb-1">✅</div>
                                    <div class="font-bold text-gray-800 dark:text-white">Active</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">Operational</div>
                                </div>
                            </label>
                            <label class="flex-1 cursor-pointer">
                                <input type="radio" 
                                       x-model="form.is_active"
                                       :value="false"
                                       class="peer sr-only">
                                <div class="p-4 border-2 border-gray-200 dark:border-gray-600 rounded-xl text-center peer-checked:border-red-500 peer-checked:bg-red-50 dark:peer-checked:bg-red-900/20 transition-all">
                                    <div class="text-2xl mb-1">❌</div>
                                    <div class="font-bold text-gray-800 dark:text-white">Inactive</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">Not operational</div>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex gap-4 pt-6 border-t border-gray-200 dark:border-gray-700">
                        <button type="button"
                                @click="showModal = false"
                                class="flex-1 px-6 py-3 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-xl font-semibold hover:bg-gray-200 dark:hover:bg-gray-600 transition-all">
                            Cancel
                        </button>
                        <button type="submit"
                                :disabled="saving"
                                class="flex-1 px-6 py-3 bg-gradient-to-r from-cyan-600 to-blue-600 text-white rounded-xl font-semibold hover:from-cyan-700 hover:to-blue-700 transition-all shadow-lg disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2">
                            <svg x-show="saving" class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span x-text="saving ? 'Saving...' : (editMode ? 'Update Branch' : 'Create Branch')"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function branchesPage() {
    return {
        branches: [],
        stats: {},
        loading: false,
        saving: false,
        showModal: false,
        editMode: false,
        form: {
            id: null,
            name: '',
            code: '',
            address: '',
            city: '',
            province: '',
            postal_code: '',
            phone: '',
            email: '',
            status: 'active',
            manager_name: '',
            manager_phone: ''
        },

        async init() {
            await this.fetchBranches();
            await this.fetchStatistics();
        },

        async fetchBranches() {
            this.loading = true;
            try {
                const token = localStorage.getItem('saga_token');
                const response = await fetch('/api/branches', {
                    headers: { 'Authorization': 'Bearer ' + token }
                });
                const data = await response.json();
                if (data.success) {
                    this.branches = data.data;
                }
            } catch (error) {
                console.error('Fetch branches error:', error);
                Swal.fire('Error', 'Failed to load branches', 'error');
            } finally {
                this.loading = false;
            }
        },

        async fetchStatistics() {
            try {
                const token = localStorage.getItem('saga_token');
                const response = await fetch('/api/branches/statistics', {
                    headers: { 'Authorization': 'Bearer ' + token }
                });
                const data = await response.json();
                if (data.success) {
                    this.stats = data.data;
                }
            } catch (error) {
                console.error('Fetch statistics error:', error);
            }
        },

        openAddModal() {
            this.editMode = false;
            this.form = {
                id: null,
                name: '',
                code: '',
                address: '',
                city: '',
                province: '',
                postal_code: '',
                phone: '',
                email: '',
                status: 'active',
                manager_name: '',
                manager_phone: ''
            };
            this.showModal = true;
        },

        editBranch(branch) {
            this.editMode = true;
            this.form = {
                id: branch.id,
                name: branch.name,
                code: branch.code,
                address: branch.address || '',
                city: branch.city || '',
                province: branch.province || '',
                postal_code: branch.postal_code || '',
                phone: branch.phone || '',
                email: branch.email || '',
                status: branch.status || 'active',
                manager_name: branch.manager_name || '',
                manager_phone: branch.manager_phone || ''
            };
            this.showModal = true;
        },

        async saveBranch() {
            this.saving = true;
            try {
                const token = localStorage.getItem('saga_token');
                const url = this.editMode ? `/api/branches/${this.form.id}` : '/api/branches';
                const method = this.editMode ? 'PUT' : 'POST';

                console.log('Saving branch:', this.form);

                const response = await fetch(url, {
                    method,
                    headers: {
                        'Authorization': 'Bearer ' + token,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(this.form)
                });

                console.log('Response status:', response.status);

                // Check if response is HTML (error page)
                const contentType = response.headers.get('content-type');
                if (contentType && contentType.includes('text/html')) {
                    throw new Error('Server error. Please check your connection or try again later.');
                }

                const data = await response.json();
                console.log('Response data:', data);

                if (response.ok && data.success) {
                    // Success notification
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil! ✅',
                        html: `<div class="text-left"><p class="font-semibold">${this.editMode ? 'Branch Berhasil Diupdate' : 'Branch Berhasil Dibuat'}</p>
                               <p class="text-sm text-gray-600 mt-2">${this.form.name}</p>
                               ${this.form.code ? `<p class="text-xs text-gray-500">Code: ${this.form.code}</p>` : ''}</div>`,
                        timer: 3000,
                        showConfirmButton: false,
                        toast: false,
                        position: 'center'
                    });
                    
                    this.showModal = false;
                    await this.fetchBranches();
                    await this.fetchStatistics();
                } else {
                    // API returned error
                    let errorMessage = data.message || 'Failed to save branch';
                    
                    // Show validation errors if any
                    if (data.errors) {
                        errorMessage = '<div class="text-left"><ul class="list-disc list-inside text-sm">';
                        for (const [field, errors] of Object.entries(data.errors)) {
                            errors.forEach(error => {
                                errorMessage += `<li>${error}</li>`;
                            });
                        }
                        errorMessage += '</ul></div>';
                    }
                    
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal! ❌',
                        html: errorMessage,
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#ef4444'
                    });
                }
            } catch (error) {
                console.error('Save branch error:', error);
                
                let errorMessage = 'Terjadi kesalahan saat menyimpan branch';
                
                if (error.message.includes('HTML')) {
                    errorMessage = 'Session expired atau server error. Silakan refresh halaman dan coba lagi.';
                } else if (error.message.includes('Failed to fetch')) {
                    errorMessage = 'Tidak dapat terhubung ke server. Periksa koneksi internet Anda.';
                } else if (error.message) {
                    errorMessage = error.message;
                }
                
                Swal.fire({
                    icon: 'error',
                    title: 'Error! ❌',
                    text: errorMessage,
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#ef4444'
                });
            } finally {
                this.saving = false;
            }
        },

        deleteBranch(branch) {
            Swal.fire({
                title: 'Delete Branch?',
                html: `Are you sure you want to delete <strong>${branch.name}</strong>?<br>This action cannot be undone.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, Delete',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#6b7280'
            }).then(async (result) => {
                if (result.isConfirmed) {
                    try {
                        const token = localStorage.getItem('saga_token');
                        const response = await fetch(`/api/branches/${branch.id}`, {
                            method: 'DELETE',
                            headers: { 'Authorization': 'Bearer ' + token }
                        });
                        const data = await response.json();

                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted',
                                text: 'Branch deleted successfully',
                                timer: 2000,
                                showConfirmButton: false
                            });
                            await this.fetchBranches();
                            await this.fetchStatistics();
                        } else {
                            Swal.fire('Error', data.message || 'Failed to delete branch', 'error');
                        }
                    } catch (error) {
                        console.error('Delete branch error:', error);
                        Swal.fire('Error', 'Failed to delete branch', 'error');
                    }
                }
            });
        }
    }
}
</script>
@endpush
@endsection
