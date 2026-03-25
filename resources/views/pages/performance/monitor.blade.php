@extends('layouts.app')

@section('title', 'Performance Monitoring')

@section('content')
<div x-data="performanceMonitor()" x-init="init()" class="space-y-6">
    <!-- Header -->
    <div class="bg-gradient-to-r from-emerald-600 to-teal-700 rounded-2xl p-6 shadow-lg">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-white flex items-center gap-3">
                    <span class="p-2 bg-white/20 rounded-lg">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </span>
                    Performance Monitoring
                </h1>
                <p class="text-emerald-100 text-sm mt-2">Database optimization and cache management</p>
            </div>
            <div class="flex gap-2">
                <button @click="activeTab = 'database'" :class="activeTab === 'database' ? 'bg-white text-emerald-600' : 'bg-white/20 text-white hover:bg-white/30'" class="px-4 py-2 rounded-lg transition-colors font-medium">Database</button>
                <button @click="activeTab = 'cache'" :class="activeTab === 'cache' ? 'bg-white text-emerald-600' : 'bg-white/20 text-white hover:bg-white/30'" class="px-4 py-2 rounded-lg transition-colors font-medium">Cache</button>
            </div>
        </div>
    </div>

    <!-- Database Tab -->
    <div x-show="activeTab === 'database'" class="space-y-6">
        <!-- Database Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-gray-100 dark:border-gray-700">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-2 bg-blue-100 dark:bg-blue-900/30 rounded-xl">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"></path>
                        </svg>
                    </div>
                </div>
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Total Tables</p>
                    <h3 class="text-2xl font-bold text-gray-800 dark:text-white mt-1" x-text="dbStats.total_tables"></h3>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-gray-100 dark:border-gray-700">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-2 bg-purple-100 dark:bg-purple-900/30 rounded-xl">
                        <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"></path>
                        </svg>
                    </div>
                </div>
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Database Size</p>
                    <h3 class="text-2xl font-bold text-gray-800 dark:text-white mt-1" x-text="dbStats.total_size"></h3>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-gray-100 dark:border-gray-700">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-2 bg-green-100 dark:bg-green-900/30 rounded-xl">
                        <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <span class="text-xs font-medium px-2 py-1 rounded-full bg-green-100 text-green-700">Optimized</span>
                </div>
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Status</p>
                    <h3 class="text-lg font-bold text-green-600 dark:text-green-400 mt-1">Healthy</h3>
                </div>
            </div>
        </div>

        <!-- Database Actions -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="p-6 border-b border-gray-100 dark:border-gray-700">
                <h3 class="text-lg font-bold text-gray-800 dark:text-white">Database Optimization</h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <button @click="analyzeSlowQueries()" :disabled="loading" class="p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl hover:bg-blue-100 dark:hover:bg-blue-900/30 transition-colors text-left">
                        <div class="flex items-center gap-3">
                            <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                            <div>
                                <h4 class="font-semibold text-gray-800 dark:text-white">Analyze Slow Queries</h4>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Identify queries taking > 100ms</p>
                            </div>
                        </div>
                    </button>

                    <button @click="checkIndexes()" :disabled="loading" class="p-4 bg-purple-50 dark:bg-purple-900/20 border border-purple-200 dark:border-purple-800 rounded-xl hover:bg-purple-100 dark:hover:bg-purple-900/30 transition-colors text-left">
                        <div class="flex items-center gap-3">
                            <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                            </svg>
                            <div>
                                <h4 class="font-semibold text-gray-800 dark:text-white">Check Missing Indexes</h4>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Find tables needing indexes</p>
                            </div>
                        </div>
                    </button>

                    <button @click="optimizeTables()" :disabled="loading" class="p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl hover:bg-green-100 dark:hover:bg-green-900/30 transition-colors text-left">
                        <div class="flex items-center gap-3">
                            <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.384-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path>
                            </svg>
                            <div>
                                <h4 class="font-semibold text-gray-800 dark:text-white">Optimize Tables</h4>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Defragment and optimize</p>
                            </div>
                        </div>
                    </button>

                    <button @click="getTableStats()" :disabled="loading" class="p-4 bg-orange-50 dark:bg-orange-900/20 border border-orange-200 dark:border-orange-800 rounded-xl hover:bg-orange-100 dark:hover:bg-orange-900/30 transition-colors text-left">
                        <div class="flex items-center gap-3">
                            <svg class="w-6 h-6 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                            <div>
                                <h4 class="font-semibold text-gray-800 dark:text-white">Table Statistics</h4>
                                <p class="text-sm text-gray-500 dark:text-gray-400">View table sizes and rows</p>
                            </div>
                        </div>
                    </button>
                </div>
            </div>
        </div>

        <!-- Results Display -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden" x-show="results.length > 0">
            <div class="p-6 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                <h3 class="text-lg font-bold text-gray-800 dark:text-white" x-text="resultsTitle"></h3>
                <button @click="results = []" class="text-gray-500 hover:text-gray-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div class="p-6 overflow-x-auto">
                <template x-if="slowQueries.length > 0">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Query</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Time (ms)</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            <template x-for="query in slowQueries" :key="query.sql">
                                <tr>
                                    <td class="px-4 py-3 font-mono text-xs text-gray-600 dark:text-gray-300" x-text="query.sql"></td>
                                    <td class="px-4 py-3" :class="query.time_ms > 100 ? 'text-red-600' : 'text-gray-600'">
                                        <span class="font-semibold" x-text="query.time_ms.toFixed(2) + ' ms'"></span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="px-2 py-1 text-xs rounded-full font-medium" :class="query.is_slow ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700'" x-text="query.is_slow ? 'Slow' : 'Fast'"></span>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </template>

                <template x-if="missingIndexes.length > 0">
                    <div class="space-y-3">
                        <template x-for="item in missingIndexes" :key="item.sql">
                            <div class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl border border-gray-200 dark:border-gray-600">
                                <div class="flex items-center justify-between mb-2">
                                    <h4 class="font-semibold text-gray-800 dark:text-white" x-text="item.recommendation"></h4>
                                    <span class="px-2 py-1 text-xs rounded-full font-medium" :class="{
                                        'bg-red-100 text-red-700': item.priority === 'High',
                                        'bg-yellow-100 text-yellow-700': item.priority === 'Medium'
                                    }" x-text="item.priority + ' Priority'"></span>
                                </div>
                                <code class="text-xs font-mono text-gray-600 dark:text-gray-300 block bg-white dark:bg-gray-800 p-2 rounded" x-text="item.sql"></code>
                            </div>
                        </template>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <!-- Cache Tab -->
    <div x-show="activeTab === 'cache'" class="space-y-6">
        <!-- Cache Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-gray-100 dark:border-gray-700">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-2 bg-blue-100 dark:bg-blue-900/30 rounded-xl">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                    </div>
                </div>
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Cache Driver</p>
                    <h3 class="text-lg font-bold text-gray-800 dark:text-white mt-1" x-text="cacheStats.driver || 'file'"></h3>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-gray-100 dark:border-gray-700">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-2 bg-purple-100 dark:bg-purple-900/30 rounded-xl">
                        <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                        </svg>
                    </div>
                </div>
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Cache Stores</p>
                    <h3 class="text-lg font-bold text-gray-800 dark:text-white mt-1" x-text="cacheStats.stores?.join(', ') || 'file'"></h3>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-gray-100 dark:border-gray-700">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-2 bg-green-100 dark:bg-green-900/30 rounded-xl">
                        <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Default TTL</p>
                    <h3 class="text-lg font-bold text-gray-800 dark:text-white mt-1" x-text="(cacheStats.default_ttl || 3600) + 's'"></h3>
                </div>
            </div>
        </div>

        <!-- Cache Actions -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="p-6 border-b border-gray-100 dark:border-gray-700">
                <h3 class="text-lg font-bold text-gray-800 dark:text-white">Cache Management</h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <button @click="warmupCache()" :disabled="loading" class="p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl hover:bg-green-100 dark:hover:bg-green-900/30 transition-colors text-left">
                        <div class="flex items-center gap-3">
                            <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"></path>
                            </svg>
                            <div>
                                <h4 class="font-semibold text-gray-800 dark:text-white">Warmup Cache</h4>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Pre-cache common data</p>
                            </div>
                        </div>
                    </button>

                    <button @click="clearCache()" :disabled="loading" class="p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl hover:bg-red-100 dark:hover:bg-red-900/30 transition-colors text-left">
                        <div class="flex items-center gap-3">
                            <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                            <div>
                                <h4 class="font-semibold text-gray-800 dark:text-white">Clear All Cache</h4>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Remove all cached data</p>
                            </div>
                        </div>
                    </button>

                    <button @click="cacheDashboard()" :disabled="loading" class="p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl hover:bg-blue-100 dark:hover:bg-blue-900/30 transition-colors text-left">
                        <div class="flex items-center gap-3">
                            <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"></path>
                            </svg>
                            <div>
                                <h4 class="font-semibold text-gray-800 dark:text-white">Cache Dashboard</h4>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Cache dashboard queries</p>
                            </div>
                        </div>
                    </button>
                </div>
            </div>
        </div>

        <!-- Cache Result -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden" x-show="cacheResult">
            <div class="p-6 border-b border-gray-100 dark:border-gray-700">
                <h3 class="text-lg font-bold text-gray-800 dark:text-white">Cache Operation Result</h3>
            </div>
            <div class="p-6">
                <pre class="bg-gray-50 dark:bg-gray-700 p-4 rounded-xl overflow-x-auto text-sm"><code class="text-gray-800 dark:text-gray-200" x-text="JSON.stringify(cacheResult, null, 2)"></code></pre>
            </div>
        </div>
    </div>
</div>

<script>
function performanceMonitor() {
    return {
        loading: false,
        activeTab: 'database',
        dbStats: { total_tables: 0, total_size: '0 B' },
        cacheStats: { driver: 'file', stores: [], default_ttl: 3600 },
        results: [],
        resultsTitle: '',
        slowQueries: [],
        missingIndexes: [],
        cacheResult: null,

        async init() {
            await this.getTableStats();
            await this.getCacheStats();
        },

        async getTableStats() {
            this.loading = true;
            const token = localStorage.getItem('saga_token');
            
            try {
                const response = await fetch('/api/performance/database/stats', {
                    headers: { 'Authorization': 'Bearer ' + token }
                });
                const data = await response.json();
                
                if (data.success) {
                    this.dbStats = {
                        total_tables: data.data.total_tables,
                        total_size: data.data.total_size
                    };
                }
            } catch (error) {
                console.error('Stats error:', error);
            } finally {
                this.loading = false;
            }
        },

        async getCacheStats() {
            const token = localStorage.getItem('saga_token');
            
            try {
                const response = await fetch('/api/performance/cache/stats', {
                    headers: { 'Authorization': 'Bearer ' + token }
                });
                const data = await response.json();
                
                if (data.success) {
                    this.cacheStats = data.data;
                }
            } catch (error) {
                console.error('Cache stats error:', error);
            }
        },

        async analyzeSlowQueries() {
            this.loading = true;
            const token = localStorage.getItem('saga_token');
            
            try {
                const response = await fetch('/api/performance/database/slow-queries', {
                    headers: { 'Authorization': 'Bearer ' + token }
                });
                const data = await response.json();
                
                if (data.success) {
                    this.slowQueries = data.data.queries;
                    this.resultsTitle = 'Slow Query Analysis';
                }
            } catch (error) {
                console.error('Slow queries error:', error);
            } finally {
                this.loading = false;
            }
        },

        async checkIndexes() {
            this.loading = true;
            const token = localStorage.getItem('saga_token');
            
            try {
                const response = await fetch('/api/performance/database/indexes', {
                    headers: { 'Authorization': 'Bearer ' + token }
                });
                const data = await response.json();
                
                if (data.success) {
                    this.missingIndexes = data.data.recommendations;
                    this.resultsTitle = 'Missing Index Recommendations';
                }
            } catch (error) {
                console.error('Index check error:', error);
            } finally {
                this.loading = false;
            }
        },

        async optimizeTables() {
            this.loading = true;
            const token = localStorage.getItem('saga_token');
            
            try {
                const response = await fetch('/api/performance/database/optimize', {
                    method: 'POST',
                    headers: { 'Authorization': 'Bearer ' + token }
                });
                const data = await response.json();
                
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Tables Optimized',
                        text: data.data.tables.length + ' tables optimized successfully'
                    });
                }
            } catch (error) {
                console.error('Optimize error:', error);
            } finally {
                this.loading = false;
            }
        },

        async warmupCache() {
            this.loading = true;
            const token = localStorage.getItem('saga_token');
            
            try {
                const response = await fetch('/api/performance/cache/warmup', {
                    method: 'POST',
                    headers: { 'Authorization': 'Bearer ' + token }
                });
                const data = await response.json();
                
                if (data.success) {
                    this.cacheResult = data.data;
                    Swal.fire({
                        icon: 'success',
                        title: 'Cache Warmed Up',
                        text: 'Dashboard, products, and customers cached'
                    });
                }
            } catch (error) {
                console.error('Warmup error:', error);
            } finally {
                this.loading = false;
            }
        },

        async clearCache() {
            const result = await Swal.fire({
                title: 'Clear All Cache?',
                text: 'This will remove all cached data. Performance may be temporarily affected.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, Clear',
                cancelButtonText: 'Cancel'
            });

            if (result.isConfirmed) {
                this.loading = true;
                const token = localStorage.getItem('saga_token');
                
                try {
                    const response = await fetch('/api/performance/cache/clear', {
                        method: 'POST',
                        headers: { 'Authorization': 'Bearer ' + token }
                    });
                    const data = await response.json();
                    
                    if (data.success) {
                        this.cacheResult = data.data;
                        Swal.fire({
                            icon: 'success',
                            title: 'Cache Cleared',
                            text: 'All cache has been cleared'
                        });
                    }
                } catch (error) {
                    console.error('Clear error:', error);
                } finally {
                    this.loading = false;
                }
            }
        },

        async cacheDashboard() {
            this.loading = true;
            const token = localStorage.getItem('saga_token');
            
            try {
                const response = await fetch('/api/performance/cache/warmup', {
                    method: 'POST',
                    headers: { 'Authorization': 'Bearer ' + token }
                });
                const data = await response.json();
                
                if (data.success) {
                    this.cacheResult = data.data;
                    Swal.fire({
                        icon: 'success',
                        title: 'Dashboard Cached',
                        text: 'Dashboard queries cached for 5 minutes'
                    });
                }
            } catch (error) {
                console.error('Cache dashboard error:', error);
            } finally {
                this.loading = false;
            }
        }
    }
}
</script>
@endsection
