@extends('layouts.app')

@section('title', 'Subscription Management | Tenant Portal')

@section('content')
<div x-data="subscriptionManager()" x-init="init()" class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Subscription Management</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Manage your plan and billing</p>
        </div>
        <a href="/tenant-portal" class="btn-secondary">← Back to Portal</a>
    </div>

    <!-- Current Subscription -->
    <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
        <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">Current Subscription</h3>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Plan</p>
                <p class="text-lg font-bold text-gray-800 dark:text-white" x-text="currentSubscription?.plan?.name || 'N/A'">Loading...</p>
            </div>
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Status</p>
                <span class="inline-block mt-1 px-3 py-1 text-sm rounded-full capitalize"
                      :class="{
                          'bg-green-100 text-green-700': currentSubscription?.status === 'active',
                          'bg-yellow-100 text-yellow-700': currentSubscription?.status === 'trial',
                          'bg-red-100 text-red-700': currentSubscription?.status === 'suspended'
                      }"
                      x-text="currentSubscription?.status || 'N/A'">
                </span>
            </div>
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Billing Cycle</p>
                <p class="text-lg font-bold text-gray-800 dark:text-white capitalize" x-text="currentSubscription?.billing_cycle || 'N/A'">-</p>
            </div>
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Expires On</p>
                <p class="text-lg font-bold text-gray-800 dark:text-white" x-text="formatDate(currentSubscription?.expires_at)">-</p>
            </div>
        </div>
    </div>

    <!-- Available Plans -->
    <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
        <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">Available Plans</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Free Plan -->
            <template x-for="plan in availablePlans" :key="plan.id">
                <div class="rounded-xl border-2 p-6 transition-all"
                     :class="plan.is_current ? 'border-brand-500 bg-brand-50 dark:bg-brand-900/20' : 'border-gray-200 dark:border-gray-800 hover:border-brand-300'">
                    <div class="text-center mb-4">
                        <h4 class="text-xl font-bold text-gray-800 dark:text-white" x-text="plan.name"></h4>
                        <div class="mt-2">
                            <span class="text-3xl font-bold text-brand-500" x-text="plan.price_monthly == 0 ? 'Free' : 'Rp ' + formatNumber(plan.price_monthly)"></span>
                            <span class="text-sm text-gray-500">/month</span>
                        </div>
                        <p class="text-sm text-gray-500 mt-1" x-show="plan.price_yearly > 0">
                            Rp <span x-text="formatNumber(plan.price_yearly)"></span> /year (Save <span x-text="plan.yearly_savings_percent"></span>%)
                        </p>
                    </div>

                    <!-- Features -->
                    <ul class="space-y-2 mb-6">
                        <template x-for="(limit, metric) in plan.limits" :key="metric">
                            <li class="flex items-center gap-2 text-sm" x-show="limit > 0 || metric === 'users'">
                                <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span class="text-gray-600 dark:text-gray-400">
                                    <span x-text="formatMetric(metric)"></span>: 
                                    <span class="font-medium" x-text="limit === 0 ? 'Unlimited' : limit"></span>
                                </span>
                            </li>
                        </template>
                    </ul>

                    <!-- Action Button -->
                    <button @click="showChangePlanModal(plan)" 
                            :disabled="plan.is_current"
                            class="w-full py-2 px-4 rounded-lg font-medium transition-colors"
                            :class="plan.is_current ? 'bg-gray-200 text-gray-500 cursor-not-allowed' : 'bg-brand-500 text-white hover:bg-brand-600'">
                        <span x-text="plan.is_current ? 'Current Plan' : 'Upgrade'"></span>
                    </button>
                </div>
            </template>
        </div>
    </div>

    <!-- Billing Cycle Selection -->
    <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
        <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">Billing Preference</h3>
        <div class="flex items-center gap-4">
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="radio" name="billing_cycle" value="monthly" x-model="selectedBillingCycle" class="w-4 h-4 text-brand-500">
                <span class="text-gray-700 dark:text-gray-300">Monthly</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="radio" name="billing_cycle" value="yearly" x-model="selectedBillingCycle" class="w-4 h-4 text-brand-500">
                <span class="text-gray-700 dark:text-gray-300">Yearly (Save up to 17%)</span>
            </label>
        </div>
    </div>
</div>

<!-- Change Plan Modal -->
<div x-show="showModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="showModal = false"></div>
        <div class="relative bg-white dark:bg-gray-900 rounded-2xl p-6 max-w-md w-full shadow-xl">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">Change Subscription Plan</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                You are about to change to <span class="font-bold text-brand-500" x-text="selectedPlan?.name"></span>
            </p>
            <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4 mb-4">
                <div class="flex justify-between mb-2">
                    <span class="text-sm text-gray-500">New Plan:</span>
                    <span class="font-medium" x-text="selectedPlan?.name"></span>
                </div>
                <div class="flex justify-between mb-2">
                    <span class="text-sm text-gray-500">Billing Cycle:</span>
                    <span class="font-medium capitalize" x-text="selectedBillingCycle"></span>
                </div>
                <div class="flex justify-between mb-2">
                    <span class="text-sm text-gray-500">Price:</span>
                    <span class="font-medium">
                        Rp <span x-text="formatNumber(selectedBillingCycle === 'yearly' ? selectedPlan?.price_yearly : selectedPlan?.price_monthly)"></span>
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-sm text-gray-500">Next Billing Date:</span>
                    <span class="font-medium" x-text="getNextBillingDate()"></span>
                </div>
            </div>
            <div class="flex justify-end gap-2">
                <button @click="showModal = false" type="button" class="btn-secondary">Cancel</button>
                <button @click="changePlan" type="button" class="btn-primary">Confirm Change</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function subscriptionManager() {
    return {
        currentSubscription: null,
        availablePlans: [],
        selectedPlan: null,
        selectedBillingCycle: 'monthly',
        showModal: false,

        async init() {
            await this.fetchSubscription();
        },

        async fetchSubscription() {
            try {
                const token = localStorage.getItem('saga_token');
                const response = await fetch('/api/tenant/subscription', {
                    headers: { 'Authorization': 'Bearer ' + token }
                });
                const result = await response.json();
                if (result.success) {
                    this.currentSubscription = result.data.subscription;
                    this.availablePlans = result.data.available_plans;
                    this.selectedBillingCycle = this.currentSubscription?.billing_cycle || 'monthly';
                }
            } catch (error) {
                console.error('Error fetching subscription:', error);
            }
        },

        showChangePlanModal(plan) {
            this.selectedPlan = plan;
            this.showModal = true;
        },

        async changePlan() {
            try {
                const token = localStorage.getItem('saga_token');
                const response = await fetch('/api/tenant/subscription/change', {
                    method: 'POST',
                    headers: {
                        'Authorization': 'Bearer ' + token,
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        plan_id: this.selectedPlan.id,
                        billing_cycle: this.selectedBillingCycle
                    })
                });
                const result = await response.json();
                if (result.success) {
                    alert('Subscription plan changed successfully!');
                    this.showModal = false;
                    await this.fetchSubscription();
                } else {
                    alert(result.message || 'Failed to change plan');
                }
            } catch (error) {
                console.error('Error changing plan:', error);
                alert('An error occurred. Please try again.');
            }
        },

        formatDate(dateString) {
            if (!dateString) return 'N/A';
            return new Date(dateString).toLocaleDateString('id-ID', {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
        },

        formatNumber(num) {
            if (!num && num !== 0) return '0';
            return new Intl.NumberFormat('id-ID').format(num);
        },

        formatMetric(metric) {
            const labels = {
                'users': 'Users',
                'products': 'Products',
                'branches': 'Branches',
                'transactions_per_month': 'Transactions/Month',
                'storage_mb': 'Storage (MB)'
            };
            return labels[metric] || metric;
        },

        getNextBillingDate() {
            const date = new Date();
            if (this.selectedBillingCycle === 'yearly') {
                date.setFullYear(date.getFullYear() + 1);
            } else {
                date.setMonth(date.getMonth() + 1);
            }
            return date.toLocaleDateString('id-ID', {
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            });
        }
    }
}
</script>
@endpush
@endsection
