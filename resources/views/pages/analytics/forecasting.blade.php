@extends('layouts.app')

@section('title', 'Sales Forecasting & Trends')

@section('content')
<div x-data="forecastingDashboard()" x-init="init()" class="space-y-6">
    <!-- Header -->
    <div class="bg-gradient-to-r from-purple-600 to-purple-700 rounded-2xl p-6 shadow-lg">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-white flex items-center gap-3">
                    <span class="p-2 bg-white/20 rounded-lg">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path>
                        </svg>
                    </span>
                    Sales Forecasting
                </h1>
                <p class="text-purple-100 text-sm mt-2">Predict future sales based on historical data</p>
            </div>
            <div class="flex gap-2">
                <select x-model="forecastDays" @change="fetchForecast()" class="px-4 py-2 bg-white/20 border-0 rounded-lg text-white focus:ring-2 focus:ring-white/50">
                    <option value="7">7 Days</option>
                    <option value="14">14 Days</option>
                    <option value="30">30 Days</option>
                </select>
                <button @click="fetchForecast()" class="px-4 py-2 bg-white/20 hover:bg-white/30 rounded-lg transition-colors flex items-center gap-2">
                    <svg class="w-4 h-4" :class="{'animate-spin': loading}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    Refresh
                </button>
            </div>
        </div>
    </div>

    <!-- Forecast Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Total Forecasted Revenue -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-gray-100 dark:border-gray-700">
            <div class="flex items-center justify-between mb-4">
                <div class="p-2 bg-green-100 dark:bg-green-900/30 rounded-xl">
                    <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <span class="text-xs font-medium px-2 py-1 rounded-full bg-purple-100 text-purple-700" x-text="forecast.summary.forecast_period"></span>
            </div>
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Total Forecasted Revenue</p>
                <h3 class="text-2xl font-bold text-gray-800 dark:text-white mt-1" x-text="formatCurrency(forecast.summary.total_forecasted_revenue)"></h3>
                <p class="text-xs text-gray-500 mt-2" x-text="'Avg daily: ' + formatCurrency(forecast.summary.average_daily_revenue)"></p>
            </div>
        </div>

        <!-- Confidence Level -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-gray-100 dark:border-gray-700">
            <div class="flex items-center justify-between mb-4">
                <div class="p-2 bg-blue-100 dark:bg-blue-900/30 rounded-xl">
                    <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <span class="text-xs font-medium px-2 py-1 rounded-full" :class="{
                    'bg-green-100 text-green-700': forecast.summary.confidence_level === 'High',
                    'bg-yellow-100 text-yellow-700': forecast.summary.confidence_level === 'Medium',
                    'bg-red-100 text-red-700': forecast.summary.confidence_level === 'Low'
                }" x-text="forecast.summary.confidence_level + ' Confidence'"></span>
            </div>
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Forecast Confidence</p>
                <h3 class="text-2xl font-bold text-gray-800 dark:text-white mt-1" x-text="forecast.summary.confidence_level"></h3>
                <p class="text-xs text-gray-500 mt-2">Based on data variability</p>
            </div>
        </div>

        <!-- Historical Average -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-gray-100 dark:border-gray-700">
            <div class="flex items-center justify-between mb-4">
                <div class="p-2 bg-orange-100 dark:bg-orange-900/30 rounded-xl">
                    <svg class="w-6 h-6 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path>
                    </svg>
                </div>
                <span class="text-xs text-gray-500 dark:text-gray-400">Last 30 days</span>
            </div>
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Historical Daily Average</p>
                <h3 class="text-2xl font-bold text-gray-800 dark:text-white mt-1" x-text="formatCurrency(forecast.historical_average.daily_revenue)"></h3>
                <p class="text-xs text-gray-500 mt-2" x-text="Math.round(forecast.historical_average.daily_transactions) + ' transactions/day'"></p>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Sales Forecast Chart -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="p-6 border-b border-gray-100 dark:border-gray-700">
                <h3 class="text-lg font-bold text-gray-800 dark:text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path>
                    </svg>
                    Sales Forecast
                </h3>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    <template x-for="(item, index) in forecast.forecast" :key="index">
                        <div class="flex items-center gap-4">
                            <div class="w-24 text-xs text-gray-500 dark:text-gray-400" x-text="item.date_formatted"></div>
                            <div class="flex-1 bg-gray-100 dark:bg-gray-700 rounded-full h-8 overflow-hidden">
                                <div class="bg-gradient-to-r from-purple-500 to-purple-600 h-full rounded-full transition-all duration-500"
                                     :style="'width: ' + getBarWidth(item.forecasted_revenue) + '%'"></div>
                            </div>
                            <div class="w-24 text-right text-sm font-semibold text-gray-800 dark:text-white" x-text="formatCurrency(item.forecasted_revenue)"></div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- Sales Trend -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="p-6 border-b border-gray-100 dark:border-gray-700">
                <h3 class="text-lg font-bold text-gray-800 dark:text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                    </svg>
                    Sales Trend (30 Days)
                </h3>
            </div>
            <div class="p-6">
                <div class="flex items-center gap-4 mb-6">
                    <div class="flex-1">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Trend Direction</p>
                        <div class="flex items-center gap-2 mt-1">
                            <svg class="w-6 h-6" :class="{
                                'text-green-600': trend.trend_direction === 'up',
                                'text-red-600': trend.trend_direction === 'down',
                                'text-gray-600': trend.trend_direction === 'stable'
                            }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path x-show="trend.trend_direction === 'up'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                <path x-show="trend.trend_direction === 'down'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"></path>
                                <path x-show="trend.trend_direction === 'stable'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14"></path>
                            </svg>
                            <span class="text-lg font-bold capitalize" x-text="trend.trend_direction"></span>
                            <span class="text-sm px-2 py-1 rounded-full" :class="{
                                'bg-green-100 text-green-700': trend.trend_percentage > 0,
                                'bg-red-100 text-red-700': trend.trend_percentage < 0,
                                'bg-gray-100 text-gray-700': trend.trend_percentage === 0
                            }" x-text="(trend.trend_percentage > 0 ? '+' : '') + trend.trend_percentage + '%'"></span>
                        </div>
                    </div>
                </div>
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-500 dark:text-gray-400">First Half Average</span>
                        <span class="text-sm font-semibold text-gray-800 dark:text-white" x-text="formatCurrency(trend.summary.first_half_avg)"></span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Second Half Average</span>
                        <span class="text-sm font-semibold text-gray-800 dark:text-white" x-text="formatCurrency(trend.summary.second_half_avg)"></span>
                    </div>
                    <div class="flex justify-between items-center pt-3 border-t border-gray-100 dark:border-gray-700">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Change</span>
                        <span class="text-sm font-bold" :class="trend.summary.change >= 0 ? 'text-green-600' : 'text-red-600'" x-text="(trend.summary.change >= 0 ? '+' : '') + formatCurrency(trend.summary.change)"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Inventory Forecast -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="p-6 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
            <h3 class="text-lg font-bold text-gray-800 dark:text-white flex items-center gap-2">
                <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                </svg>
                Inventory Restock Forecast
            </h3>
            <button @click="fetchInventoryForecast()" class="px-3 py-1.5 text-xs bg-orange-100 dark:bg-orange-900/30 text-orange-600 dark:text-orange-400 rounded-lg hover:bg-orange-200 dark:hover:bg-orange-900/50 transition-colors">
                Refresh
            </button>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Product</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Current Stock</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Avg Daily Sale</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Days Until Stockout</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Restock Date</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Priority</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    <template x-for="item in inventoryForecast.forecast" :key="item.id">
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <td class="px-4 py-3">
                                <div>
                                    <p class="font-medium text-gray-800 dark:text-white" x-text="item.name"></p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400" x-text="item.sku"></p>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-300" x-text="item.current_stock"></td>
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-300" x-text="item.avg_daily_sale"></td>
                            <td class="px-4 py-3">
                                <span class="font-medium" x-text="item.days_until_stockout + ' days'"></span>
                            </td>
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-300" x-text="item.restock_date"></td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 text-xs rounded-full font-medium" :class="{
                                    'bg-red-100 text-red-700': item.priority === 'High',
                                    'bg-yellow-100 text-yellow-700': item.priority === 'Medium',
                                    'bg-green-100 text-green-700': item.priority === 'Low'
                                }" x-text="item.priority"></span>
                            </td>
                        </tr>
                    </template>
                    <tr x-show="inventoryForecast.forecast.length === 0">
                        <td colspan="6" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                            No inventory data available
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function forecastingDashboard() {
    return {
        loading: false,
        forecastDays: 7,
        forecast: {
            forecast: [],
            summary: {
                total_forecasted_revenue: 0,
                average_daily_revenue: 0,
                forecast_period: '7 days',
                confidence_level: 'Medium'
            },
            historical_average: {
                daily_revenue: 0,
                daily_transactions: 0
            }
        },
        trend: {
            trend_data: [],
            trend_direction: 'stable',
            trend_percentage: 0,
            summary: {
                first_half_avg: 0,
                second_half_avg: 0,
                change: 0
            }
        },
        inventoryForecast: {
            forecast: [],
            high_priority_count: 0,
            medium_priority_count: 0
        },

        async init() {
            await this.fetchForecast();
            await this.fetchTrend();
            await this.fetchInventoryForecast();
        },

        async fetchForecast() {
            this.loading = true;
            const token = localStorage.getItem('saga_token');
            
            try {
                const response = await fetch(`/api/forecasting/sales?days=${this.forecastDays}`, {
                    headers: { 'Authorization': 'Bearer ' + token }
                });
                const data = await response.json();
                
                if (data.success) {
                    this.forecast = data.data;
                }
            } catch (error) {
                console.error('Forecast error:', error);
            } finally {
                this.loading = false;
            }
        },

        async fetchTrend() {
            const token = localStorage.getItem('saga_token');
            
            try {
                const response = await fetch('/api/forecasting/trend', {
                    headers: { 'Authorization': 'Bearer ' + token }
                });
                const data = await response.json();
                
                if (data.success) {
                    this.trend = data.data;
                }
            } catch (error) {
                console.error('Trend error:', error);
            }
        },

        async fetchInventoryForecast() {
            const token = localStorage.getItem('saga_token');
            
            try {
                const response = await fetch('/api/forecasting/inventory', {
                    headers: { 'Authorization': 'Bearer ' + token }
                });
                const data = await response.json();
                
                if (data.success) {
                    this.inventoryForecast = data.data;
                }
            } catch (error) {
                console.error('Inventory forecast error:', error);
            }
        },

        formatCurrency(amount) {
            return 'Rp ' + Number(amount).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        },

        getBarWidth(amount) {
            const maxAmount = Math.max(...this.forecast.forecast.map(f => f.forecasted_revenue));
            return maxAmount > 0 ? (amount / maxAmount) * 100 : 0;
        }
    }
}
</script>
@endsection
