@extends('layouts.app')

@section('title', 'Loyalty Settings')

@section('content')
<div x-data="loyaltySettings()" x-init="init()">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Loyalty Program Settings</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400">Configure points earning and redemption rules</p>
    </div>
    
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Enable/Disable -->
            <div class="md:col-span-2">
                <label class="flex items-center gap-3">
                    <input type="checkbox" x-model="settings.enabled"
                        class="w-5 h-5 text-brand-600 rounded focus:ring-brand-500">
                    <span class="font-semibold text-gray-700 dark:text-gray-300">Enable Loyalty Program</span>
                </label>
                <p class="text-xs text-gray-500 mt-1 ml-8">When disabled, customers won't earn or redeem points</p>
            </div>
            
            <!-- Earn Rate -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">
                    Earn Rate (Amount per Point)
                </label>
                <div class="relative">
                    <span class="absolute left-3 top-2.5 text-gray-500">Rp</span>
                    <input type="number" x-model="settings.earn_rate"
                        class="w-full pl-10 pr-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white focus:ring-2 focus:ring-brand-500">
                </div>
                <p class="text-xs text-gray-500 mt-1">Customer earns 1 point per Rp <span x-text="formatNumber(settings.earn_rate)" class="font-semibold"></span></p>
            </div>
            
            <!-- Point Value -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">
                    Point Value (Redemption)
                </label>
                <div class="relative">
                    <span class="absolute left-3 top-2.5 text-gray-500">Rp</span>
                    <input type="number" x-model="settings.point_value" step="0.01"
                        class="w-full pl-10 pr-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white focus:ring-2 focus:ring-brand-500">
                </div>
                <p class="text-xs text-gray-500 mt-1">1 point = Rp <span x-text="formatNumber(settings.point_value)" class="font-semibold"></span> discount</p>
            </div>
            
            <!-- Min Redemption -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">
                    Minimum Points for Redemption
                </label>
                <input type="number" x-model="settings.min_redemption_points"
                    class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white focus:ring-2 focus:ring-brand-500">
                <p class="text-xs text-gray-500 mt-1">Customer must have at least this many points to redeem</p>
            </div>
            
            <!-- Max Redemption % -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">
                    Maximum Redemption (%)
                </label>
                <input type="number" x-model="settings.max_redemption_percent" step="0.01"
                    class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white focus:ring-2 focus:ring-brand-500">
                <p class="text-xs text-gray-500 mt-1">Max percentage of bill that can be paid with points</p>
            </div>
            
            <!-- Points Expiry -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">
                    Points Expiry (Months)
                </label>
                <input type="number" x-model="settings.points_expiry_months"
                    class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white focus:ring-2 focus:ring-brand-500">
                <p class="text-xs text-gray-500 mt-1">Points expire after this many months from earn date</p>
            </div>
            
            <!-- Example Calculation -->
            <div class="md:col-span-2 bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg border border-blue-100 dark:border-blue-800">
                <h4 class="font-semibold text-blue-800 dark:text-blue-300 mb-2">Example Calculation</h4>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm">
                    <div>
                        <span class="text-gray-500 dark:text-gray-400">Purchase Amount:</span>
                        <p class="font-bold text-gray-800 dark:text-white">Rp 100,000</p>
                    </div>
                    <div>
                        <span class="text-gray-500 dark:text-gray-400">Points Earned:</span>
                        <p class="font-bold text-brand-600" x-text="formatNumber(Math.floor(100000 / settings.earn_rate)) + ' points'"></p>
                    </div>
                    <div>
                        <span class="text-gray-500 dark:text-gray-400">Discount Value:</span>
                        <p class="font-bold text-green-600" x-text="formatCurrency(Math.floor(100000 / settings.earn_rate) * settings.point_value)"></p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Save Button -->
        <div class="mt-6 flex justify-end gap-3">
            <button @click="loadSettings()"
                class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-500 transition-colors">
                Reset
            </button>
            <button @click="saveSettings()"
                class="px-6 py-2 bg-brand-600 text-white rounded-lg hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-500 shadow-sm transition-colors font-medium">
                Save Settings
            </button>
        </div>
    </div>
    
    <!-- Quick Stats -->
    <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-brand-100 dark:bg-brand-900/30 flex items-center justify-center">
                    <svg class="w-6 h-6 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Point Value</p>
                    <p class="font-bold text-gray-800 dark:text-white" x-text="formatCurrency(settings.point_value)"></p>
                </div>
            </div>
        </div>
        
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Min Redemption</p>
                    <p class="font-bold text-gray-800 dark:text-white" x-text="formatNumber(settings.min_redemption_points) + ' points'"></p>
                </div>
            </div>
        </div>
        
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Points Expire</p>
                    <p class="font-bold text-gray-800 dark:text-white" x-text="settings.points_expiry_months + ' months'"></p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function loyaltySettings() {
    return {
        settings: {
            enabled: true,
            earn_rate: 10000,
            point_value: 100,
            min_redemption_points: 100,
            max_redemption_percent: 50.00,
            points_expiry_months: 12,
        },
        
        async init() {
            await this.loadSettings();
        },
        
        async loadSettings() {
            const token = localStorage.getItem('saga_token');
            try {
                const res = await fetch('/api/loyalty/settings', {
                    headers: { 'Authorization': 'Bearer ' + token }
                });
                const data = await res.json();
                if (data.success) {
                    this.settings = data.data;
                }
            } catch (e) {
                console.error('Load settings error:', e);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to load loyalty settings'
                });
            }
        },
        
        async saveSettings() {
            const token = localStorage.getItem('saga_token');
            try {
                const res = await fetch('/api/loyalty/settings', {
                    method: 'POST',
                    headers: {
                        'Authorization': 'Bearer ' + token,
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(this.settings)
                });
                const data = await res.json();
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Saved',
                        text: 'Loyalty settings saved successfully',
                        toast: true,
                        position: 'top-end',
                        timer: 2000,
                        showConfirmButton: false
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message || 'Failed to save settings'
                    });
                }
            } catch (e) {
                console.error('Save error:', e);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to save settings'
                });
            }
        },
        
        formatNumber(num) {
            return new Intl.NumberFormat('id-ID').format(num);
        },
        
        formatCurrency(amount) {
            return new Intl.NumberFormat('id-ID', { 
                style: 'currency', 
                currency: 'IDR', 
                minimumFractionDigits: 0 
            }).format(amount);
        }
    }
}
</script>
@endsection
