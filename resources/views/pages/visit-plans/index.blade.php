@extends('layouts.app')

@section('title', 'Visit Plans | SAGA TOKO APP')

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-gray-900 p-6" x-data="visitPlansPage()">
    <!-- Page Header -->
    <div class="max-w-7xl mx-auto mb-8">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-emerald-600 rounded-2xl flex items-center justify-center shadow-lg shadow-green-500/30">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-800 dark:text-white">Visit Plans</h1>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Rencanakan dan kelola kunjungan sales ke customer</p>
                    </div>
                </div>
            </div>
            <button @click="openModal('add')" 
                class="px-6 py-3 bg-gradient-to-r from-green-600 to-emerald-600 text-white rounded-xl font-semibold hover:from-green-700 hover:to-emerald-700 transition-all shadow-lg shadow-green-500/30 flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Create Visit Plan
            </button>
        </div>
    </div>

    <!-- Stats Overview -->
    <div class="max-w-7xl mx-auto mb-8 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Plans -->
        <div class="bg-gradient-to-br from-blue-500 to-cyan-600 rounded-2xl p-6 text-white shadow-xl shadow-blue-500/20">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-sm">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <span class="text-3xl font-bold" x-text="plans.length"></span>
            </div>
            <p class="text-blue-100 text-sm font-medium">Total Visit Plans</p>
            <p class="text-blue-200 text-xs mt-1">All time visits</p>
        </div>

        <!-- Scheduled -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <span class="text-3xl font-bold text-gray-800 dark:text-white" x-text="scheduledCount"></span>
            </div>
            <p class="text-gray-500 dark:text-gray-400 text-sm font-medium">Scheduled</p>
            <p class="text-blue-600 text-xs mt-1 font-medium">Upcoming visits</p>
        </div>

        <!-- Completed -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <span class="text-3xl font-bold text-gray-800 dark:text-white" x-text="completedCount"></span>
            </div>
            <p class="text-gray-500 dark:text-gray-400 text-sm font-medium">Completed</p>
            <p class="text-green-600 text-xs mt-1 font-medium">Successful visits</p>
        </div>

        <!-- Completion Rate -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900/30 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                    </svg>
                </div>
                <span class="text-3xl font-bold text-gray-800 dark:text-white" x-text="completionRate + '%'"></span>
            </div>
            <p class="text-gray-500 dark:text-gray-400 text-sm font-medium">Completion Rate</p>
            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2 mt-2">
                <div class="bg-gradient-to-r from-purple-500 to-purple-600 h-2 rounded-full transition-all" :style="`width: ${completionRate}%`"></div>
            </div>
        </div>
    </div>

    <!-- Calendar & List Layout -->
    <div class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Weekly Calendar -->
        <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                <h2 class="text-xl font-bold text-gray-800 dark:text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    Weekly Visit Calendar
                </h2>
                <div class="flex items-center gap-2">
                    <button @click="previousWeek()" class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                    </button>
                    <span class="font-semibold text-gray-700 dark:text-gray-300" x-text="currentWeekLabel"></span>
                    <button @click="nextWeek()" class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </button>
                </div>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-7 gap-2">
                    <template x-for="day in currentWeekDays" :key="day.date">
                        <div class="border border-gray-200 dark:border-gray-700 rounded-xl p-3 min-h-[120px] transition-all hover:shadow-md"
                            :class="day.isToday ? 'bg-gradient-to-br from-brand-50 to-indigo-50 dark:from-brand-900/20 dark:to-indigo-900/20 border-brand-300 dark:border-brand-700' : 'hover:border-brand-300 dark:hover:border-brand-600'">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase" x-text="day.dayName"></span>
                                <span class="text-sm font-bold" 
                                    :class="day.isToday ? 'text-brand-600 dark:text-brand-400' : 'text-gray-700 dark:text-gray-300'"
                                    x-text="day.dayNumber"></span>
                            </div>
                            <div class="space-y-1">
                                <template x-for="visit in getVisitsForDay(day.date)" :key="visit.id">
                                    <div class="text-xs px-2 py-1.5 rounded-lg cursor-pointer transition-all hover:scale-105"
                                        :class="visit.status === 'completed' ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400' : 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400'"
                                        @click="viewVisit(visit)"
                                        x-text="visit.customer_name">
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- Today's Visits -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-xl font-bold text-gray-800 dark:text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Today's Visits
                </h2>
            </div>
            <div class="p-6 space-y-4 max-h-[500px] overflow-y-auto">
                <template x-for="visit in todayVisits" :key="visit.id">
                    <div class="p-4 bg-gradient-to-br from-gray-50 to-white dark:from-gray-900 dark:to-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 hover:shadow-md transition-all cursor-pointer"
                        @click="viewVisit(visit)">
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex-1">
                                <h3 class="font-bold text-gray-800 dark:text-white" x-text="visit.customer_name"></h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1" x-text="visit.address"></p>
                            </div>
                            <span class="inline-flex items-center px-2 py-1 rounded-lg text-xs font-semibold"
                                :class="{
                                    'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400': visit.status === 'scheduled',
                                    'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400': visit.status === 'completed',
                                    'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400': visit.status === 'cancelled'
                                }"
                                x-text="formatStatus(visit.status)">
                            </span>
                        </div>
                        <div class="flex items-center gap-4 text-xs text-gray-500 dark:text-gray-400">
                            <div class="flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span x-text="formatTime(visit.visit_time)"></span>
                            </div>
                            <div class="flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                <span x-text="visit.salesman_name"></span>
                            </div>
                        </div>
                    </div>
                </template>
                <div x-show="todayVisits.length === 0" class="text-center py-8">
                    <div class="w-16 h-16 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-3">
                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">No visits scheduled for today</p>
                </div>
            </div>
        </div>
    </div>

    <!-- All Plans List -->
    <div class="max-w-7xl mx-auto">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <h2 class="text-xl font-bold text-gray-800 dark:text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                    All Visit Plans
                </h2>
                <div class="flex items-center gap-3">
                    <select x-model="filterStatus" 
                        class="px-4 py-2.5 border-2 border-gray-200 dark:border-gray-700 rounded-xl bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all">
                        <option value="">All Status</option>
                        <option value="scheduled">Scheduled</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                    <div class="relative">
                        <input type="text" x-model="searchQuery" 
                            placeholder="🔍 Search visits..." 
                            class="pl-10 pr-4 py-2.5 border-2 border-gray-200 dark:border-gray-700 rounded-xl bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all">
                        <svg class="w-5 h-5 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Loading State -->
            <div x-show="isLoading" class="p-12 text-center">
                <div class="inline-block animate-spin rounded-full h-12 w-12 border-4 border-brand-200 border-t-brand-600 mb-4"></div>
                <p class="text-gray-500 dark:text-gray-400">Loading visit plans...</p>
            </div>

            <!-- Table -->
            <div x-show="!isLoading" class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 dark:bg-gray-900/50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Customer</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Visit Date & Time</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Salesman</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Purpose</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-4 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        <template x-for="plan in filteredPlans" :key="plan.id">
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/30 transition-colors group">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 bg-gradient-to-br from-green-400 to-emerald-500 rounded-full flex items-center justify-center text-white font-bold"
                                            x-text="getInitials(plan.customer_name)">
                                        </div>
                                        <div>
                                            <p class="font-semibold text-gray-800 dark:text-white" x-text="plan.customer_name"></p>
                                            <p class="text-sm text-gray-500 dark:text-gray-400" x-text="plan.address"></p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="space-y-1">
                                        <div class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                            </svg>
                                            <span x-text="formatDate(plan.visit_date)"></span>
                                        </div>
                                        <div class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            <span x-text="formatTime(plan.visit_time)"></span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-gray-700 dark:text-gray-300 text-sm" x-text="plan.salesman_name"></span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-gray-700 dark:text-gray-300 text-sm" x-text="plan.purpose || '-'"></span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold"
                                        :class="{
                                            'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400': plan.status === 'scheduled',
                                            'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400': plan.status === 'completed',
                                            'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400': plan.status === 'cancelled'
                                        }"
                                        x-text="formatStatus(plan.status)">
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <button @click="viewVisit(plan)" 
                                            class="p-2 text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-colors" title="View Details">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                            </svg>
                                        </button>
                                        <button @click="editPlan(plan)" 
                                            class="p-2 text-brand-600 dark:text-brand-400 hover:bg-brand-50 dark:hover:bg-brand-900/20 rounded-lg transition-colors" title="Edit">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                        </button>
                                        <button @click="deletePlan(plan)" 
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
                <div x-show="filteredPlans.length === 0" class="p-12 text-center">
                    <div class="w-24 h-24 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-2">No visit plans found</h3>
                    <p class="text-gray-500 dark:text-gray-400 mb-6">Try adjusting your search or filters</p>
                    <button @click="searchQuery = ''; filterStatus = ''" 
                        class="px-6 py-2.5 bg-brand-600 text-white rounded-xl font-semibold hover:bg-brand-700 transition-all">
                        Clear Filters
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function visitPlansPage() {
    return {
        isLoading: false,
        showModal: false,
        modalMode: 'add',
        searchQuery: '',
        filterStatus: '',
        currentDate: new Date(),
        plans: [
            { id: 1, customer_name: 'Toko Maju Jaya', address: 'Jl. Sudirman No. 123', visit_date: '2026-02-24', visit_time: '09:00', salesman_name: 'Ahmad Rizky', status: 'scheduled', purpose: 'Follow up order' },
            { id: 2, customer_name: 'Sumber Rejeki', address: 'Jl. Gatot Subroto No. 45', visit_date: '2026-02-25', visit_time: '10:30', salesman_name: 'Budi Santoso', status: 'scheduled', purpose: 'Product introduction' },
            { id: 3, customer_name: 'Berkah Store', address: 'Jl. Thamrin No. 78', visit_date: '2026-02-23', visit_time: '14:00', salesman_name: 'Citra Dewi', status: 'completed', purpose: 'Regular visit' },
            { id: 4, customer_name: 'Makmur Jaya', address: 'Jl. Rasuna Said No. 12', visit_date: '2026-02-23', visit_time: '11:00', salesman_name: 'Ahmad Rizky', status: 'scheduled', purpose: 'New product demo' },
            { id: 5, customer_name: 'Sentosa Retail', address: 'Jl. Kuningan No. 88', visit_date: '2026-02-22', visit_time: '15:30', salesman_name: 'Eka Sari', status: 'completed', purpose: 'Contract renewal' },
        ],

        get filteredPlans() {
            let result = this.plans;
            
            if (this.searchQuery) {
                const q = this.searchQuery.toLowerCase();
                result = result.filter(p =>
                    p.customer_name.toLowerCase().includes(q) ||
                    p.salesman_name.toLowerCase().includes(q) ||
                    p.address.toLowerCase().includes(q)
                );
            }
            
            if (this.filterStatus) {
                result = result.filter(p => p.status === this.filterStatus);
            }
            
            return result;
        },

        get scheduledCount() {
            return this.plans.filter(p => p.status === 'scheduled').length;
        },

        get completedCount() {
            return this.plans.filter(p => p.status === 'completed').length;
        },

        get completionRate() {
            if (this.plans.length === 0) return 0;
            return Math.round((this.completedCount / this.plans.length) * 100);
        },

        get currentWeekDays() {
            const days = [];
            const today = new Date();
            const dayOfWeek = today.getDay();
            const startOfWeek = new Date(today);
            startOfWeek.setDate(today.getDate() - dayOfWeek);

            for (let i = 0; i < 7; i++) {
                const date = new Date(startOfWeek);
                date.setDate(startOfWeek.getDate() + i);
                days.push({
                    date: date.toISOString().split('T')[0],
                    dayName: date.toLocaleDateString('id-ID', { weekday: 'short' }),
                    dayNumber: date.getDate(),
                    isToday: date.toDateString() === today.toDateString()
                });
            }
            return days;
        },

        get currentWeekLabel() {
            const start = this.currentWeekDays[0].date;
            const end = this.currentWeekDays[6].date;
            return `${this.formatDateShort(start)} - ${this.formatDateShort(end)}`;
        },

        get todayVisits() {
            const today = new Date().toISOString().split('T')[0];
            return this.plans.filter(p => p.visit_date === today);
        },

        getVisitsForDay(date) {
            return this.plans.filter(p => p.visit_date === date);
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

        formatDateShort(dateStr) {
            return new Date(dateStr).toLocaleDateString('id-ID', {
                month: 'short',
                day: 'numeric'
            });
        },

        formatTime(timeStr) {
            if (!timeStr) return '';
            const [hours, minutes] = timeStr.split(':');
            const h = parseInt(hours);
            const ampm = h >= 12 ? 'PM' : 'AM';
            const h12 = h % 12 || 12;
            return `${h12}:${minutes} ${ampm}`;
        },

        formatStatus(status) {
            const statuses = {
                'scheduled': 'Scheduled',
                'completed': 'Completed',
                'cancelled': 'Cancelled'
            };
            return statuses[status] || status;
        },

        previousWeek() {
            this.currentDate.setDate(this.currentDate.getDate() - 7);
        },

        nextWeek() {
            this.currentDate.setDate(this.currentDate.getDate() + 7);
        },

        openModal(mode) {
            this.modalMode = mode;
            this.showModal = true;
        },

        viewVisit(visit) {
            Swal.fire({
                title: visit.customer_name,
                html: `
                    <div class="text-left space-y-3 pt-4">
                        <div class="flex items-center gap-3">
                            <div class="w-16 h-16 bg-gradient-to-br from-green-400 to-emerald-500 rounded-full flex items-center justify-center text-white font-bold text-xl">
                                ${this.getInitials(visit.customer_name)}
                            </div>
                            <div>
                                <p class="font-bold text-lg">${visit.customer_name}</p>
                                <p class="text-gray-500 text-sm">${visit.address}</p>
                            </div>
                        </div>
                        <div class="border-t pt-3 space-y-2">
                            <div class="flex items-center gap-2">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                <span>${this.formatDate(visit.visit_date)}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span>${this.formatTime(visit.visit_time)}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                <span>${visit.salesman_name}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                </svg>
                                <span>${visit.purpose || '-'}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span class="font-semibold">${this.formatStatus(visit.status)}</span>
                            </div>
                        </div>
                    </div>
                `,
                confirmButtonText: 'Close',
                confirmButtonColor: '#10b981'
            });
        },

        editPlan(plan) {
            Swal.fire({
                title: 'Edit Visit Plan',
                html: `
                    <div class="space-y-3 text-left">
                        <input id="edit-customer" class="swal2-input" value="${plan.customer_name}">
                        <input id="edit-address" class="swal2-input" value="${plan.address}">
                        <input id="edit-date" type="date" class="swal2-input" value="${plan.visit_date}">
                        <input id="edit-time" type="time" class="swal2-input" value="${plan.visit_time || ''}">
                        <select id="edit-salesman" class="swal2-input">
                            <option value="Ahmad Rizky" ${plan.salesman_name === 'Ahmad Rizky' ? 'selected' : ''}>Ahmad Rizky</option>
                            <option value="Budi Santoso" ${plan.salesman_name === 'Budi Santoso' ? 'selected' : ''}>Budi Santoso</option>
                            <option value="Citra Dewi" ${plan.salesman_name === 'Citra Dewi' ? 'selected' : ''}>Citra Dewi</option>
                        </select>
                        <input id="edit-purpose" class="swal2-input" value="${plan.purpose || ''}" placeholder="Purpose of visit">
                        <select id="edit-status" class="swal2-input">
                            <option value="scheduled" ${plan.status === 'scheduled' ? 'selected' : ''}>Scheduled</option>
                            <option value="completed" ${plan.status === 'completed' ? 'selected' : ''}>Completed</option>
                            <option value="cancelled" ${plan.status === 'cancelled' ? 'selected' : ''}>Cancelled</option>
                        </select>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Update',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#10b981',
                preConfirm: () => {
                    return {
                        customer_name: document.getElementById('edit-customer').value,
                        address: document.getElementById('edit-address').value,
                        visit_date: document.getElementById('edit-date').value,
                        visit_time: document.getElementById('edit-time').value,
                        salesman_name: document.getElementById('edit-salesman').value,
                        purpose: document.getElementById('edit-purpose').value,
                        status: document.getElementById('edit-status').value
                    };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    Object.assign(plan, result.value);
                    Swal.fire('Updated', 'Visit plan updated successfully', 'success');
                }
            });
        },

        deletePlan(plan) {
            Swal.fire({
                title: 'Delete Visit Plan?',
                text: `Are you sure you want to delete this visit plan to ${plan.customer_name}?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, Delete',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#6b7280'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.plans = this.plans.filter(p => p.id !== plan.id);
                    Swal.fire('Deleted', 'Visit plan has been deleted', 'success');
                }
            });
        }
    }
}
</script>
@endpush
@endsection
