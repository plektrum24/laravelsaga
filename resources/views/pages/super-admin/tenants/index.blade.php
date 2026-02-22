@extends('layouts.app')

@section('title', 'Tenant Management | Super Admin')

@section('content')
<div x-data="tenantManagement()" x-init="init()" class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Tenant Management</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Manage all tenants and subscriptions</p>
        </div>
        <div class="flex gap-2">
            <button @click="refreshData()" class="btn-secondary inline-flex items-center gap-2">
                <svg class="w-4 h-4" :class="{'animate-spin': loading}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                Refresh
            </button>
        </div>
    </div>

    <!-- Filters -->
    <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
            <!-- Search -->
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Search</label>
                <input type="text" x-model="filters.search" @input.debounce.300ms="fetchTenants"
                       placeholder="Search by name, email, or business..."
                       class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
            </div>

            <!-- Status Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
                <select x-model="filters.status" @change="fetchTenants"
                        class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                    <option value="">All Status</option>
                    <option value="trial">Trial</option>
                    <option value="active">Active</option>
                    <option value="suspended">Suspended</option>
                    <option value="cancelled">Cancelled</option>
                    <option value="expired">Expired</option>
                </select>
            </div>

            <!-- Sort -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Sort By</label>
                <select x-model="filters.sort_by" @change="fetchTenants"
                        class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                    <option value="created_at">Newest</option>
                    <option value="name">Name</option>
                    <option value="subscription_expires_at">Expiry Date</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Tenants Table -->
    <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="border-b border-gray-200 dark:border-gray-800">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tenant</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Plan</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Expiry</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Created</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                    <template x-for="tenant in tenants.data" :key="tenant.id">
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/50">
                            <td class="px-6 py-4">
                                <div>
                                    <p class="font-medium text-gray-800 dark:text-white" x-text="tenant.name"></p>
                                    <p class="text-sm text-gray-500" x-text="tenant.email"></p>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs rounded-full bg-brand-100 text-brand-700"
                                      x-text="tenant.subscription?.plan?.name || 'No Plan'">
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs rounded-full capitalize"
                                      :class="{
                                          'bg-green-100 text-green-700': tenant.subscription?.status === 'active',
                                          'bg-yellow-100 text-yellow-700': tenant.subscription?.status === 'trial',
                                          'bg-red-100 text-red-700': tenant.subscription?.status === 'suspended',
                                          'bg-gray-100 text-gray-700': tenant.subscription?.status === 'cancelled',
                                          'bg-orange-100 text-orange-700': tenant.subscription?.status === 'expired'
                                      }"
                                      x-text="tenant.subscription?.status || 'N/A'">
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
                                <span x-text="formatDate(tenant.subscription?.expires_at)"></span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
                                <span x-text="formatDate(tenant.created_at)"></span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button @click="viewTenant(tenant)" class="text-brand-500 hover:text-brand-600 text-sm">
                                        View
                                    </button>
                                    <button @click="showExtendModal(tenant)" class="text-blue-500 hover:text-blue-600 text-sm">
                                        Extend
                                    </button>
                                    <button @click="showStatusModal(tenant)" class="text-orange-500 hover:text-orange-600 text-sm">
                                        Status
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <!-- Empty State -->
        <div x-show="tenants.data.length === 0" class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
            </svg>
            <p class="mt-2 text-gray-500 dark:text-gray-400">No tenants found</p>
        </div>

        <!-- Pagination -->
        <div x-show="tenants.data.length > 0" class="border-t border-gray-200 dark:border-gray-800 px-6 py-4">
            <div class="flex items-center justify-between">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Showing <span x-text="tenants.from"></span> to <span x-text="tenants.to"></span> of <span x-text="tenants.total"></span> results
                </p>
                <div class="flex gap-2">
                    <button @click="fetchTenants(tenants.current_page - 1)" :disabled="tenants.current_page <= 1"
                            class="px-3 py-1 text-sm rounded-lg border border-gray-300 dark:border-gray-700 disabled:opacity-50">
                        Previous
                    </button>
                    <template x-for="page in tenants.last_page" :key="page">
                        <button @click="fetchTenants(page)"
                                :class="page === tenants.current_page ? 'bg-brand-500 text-white' : 'bg-white dark:bg-gray-900'"
                                class="px-3 py-1 text-sm rounded-lg border border-gray-300 dark:border-gray-700">
                            <span x-text="page"></span>
                        </button>
                    </template>
                    <button @click="fetchTenants(tenants.current_page + 1)" :disabled="tenants.current_page >= tenants.last_page"
                            class="px-3 py-1 text-sm rounded-lg border border-gray-300 dark:border-gray-700 disabled:opacity-50">
                        Next
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Extend Modal -->
<div x-show="showExtend" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="showExtend = false"></div>
        <div class="relative bg-white dark:bg-gray-900 rounded-2xl p-6 max-w-md w-full shadow-xl">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">Extend Subscription</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4" x-text="'Extending: ' + selectedTenant?.name"></p>
            <input type="number" x-model="extendDays" min="1" max="365" placeholder="Days to extend"
                   class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white mb-4">
            <div class="flex justify-end gap-2">
                <button @click="showExtend = false" class="btn-secondary">Cancel</button>
                <button @click="extendSubscription" class="btn-primary">Extend</button>
            </div>
        </div>
    </div>
</div>

<!-- Status Modal -->
<div x-show="showStatus" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="showStatus = false"></div>
        <div class="relative bg-white dark:bg-gray-900 rounded-2xl p-6 max-w-md w-full shadow-xl">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">Update Tenant Status</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4" x-text="'Updating: ' + selectedTenant?.name"></p>
            <select x-model="newStatus" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white mb-4">
                <option value="trial">Trial</option>
                <option value="active">Active</option>
                <option value="suspended">Suspended</option>
                <option value="cancelled">Cancelled</option>
            </select>
            <textarea x-model="statusReason" placeholder="Reason (optional)" rows="3"
                      class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white mb-4"></textarea>
            <div class="flex justify-end gap-2">
                <button @click="showStatus = false" class="btn-secondary">Cancel</button>
                <button @click="updateStatus" class="btn-primary">Update</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function tenantManagement() {
    return {
        loading: false,
        tenants: { data: [], current_page: 1, last_page: 1, from: 0, to: 0, total: 0 },
        filters: { search: '', status: '', sort_by: 'created_at' },
        showExtend: false,
        showStatus: false,
        selectedTenant: null,
        extendDays: 30,
        newStatus: 'active',
        statusReason: '',

        async init() {
            await this.fetchTenants();
        },

        async refreshData() {
            await this.fetchTenants();
        },

        async fetchTenants(page = 1) {
            this.loading = true;
            try {
                const token = localStorage.getItem('saga_token');
                const params = new URLSearchParams({
                    page,
                    search: this.filters.search,
                    status: this.filters.status,
                    sort_by: this.filters.sort_by
                });

                const response = await fetch(`/api/admin/tenants?${params}`, {
                    headers: { 'Authorization': 'Bearer ' + token }
                });
                const result = await response.json();

                if (result.success) {
                    this.tenants = result.data;
                }
            } catch (error) {
                console.error('Error fetching tenants:', error);
            } finally {
                this.loading = false;
            }
        },

        viewTenant(tenant) {
            window.location.href = `/super-admin/tenants/${tenant.id}`;
        },

        showExtendModal(tenant) {
            this.selectedTenant = tenant;
            this.extendDays = 30;
            this.showExtend = true;
        },

        async extendSubscription() {
            try {
                const token = localStorage.getItem('saga_token');
                const response = await fetch(`/api/admin/tenants/${this.selectedTenant.id}/extend`, {
                    method: 'POST',
                    headers: {
                        'Authorization': 'Bearer ' + token,
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ days: this.extendDays })
                });

                const result = await response.json();
                if (result.success) {
                    alert('Subscription extended successfully');
                    this.showExtend = false;
                    await this.fetchTenants();
                }
            } catch (error) {
                console.error('Error extending subscription:', error);
            }
        },

        showStatusModal(tenant) {
            this.selectedTenant = tenant;
            this.newStatus = tenant.subscription?.status || 'active';
            this.statusReason = '';
            this.showStatus = true;
        },

        async updateStatus() {
            try {
                const token = localStorage.getItem('saga_token');
                const response = await fetch(`/api/admin/tenants/${this.selectedTenant.id}/status`, {
                    method: 'PATCH',
                    headers: {
                        'Authorization': 'Bearer ' + token,
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        status: this.newStatus,
                        reason: this.statusReason
                    })
                });

                const result = await response.json();
                if (result.success) {
                    alert('Status updated successfully');
                    this.showStatus = false;
                    await this.fetchTenants();
                }
            } catch (error) {
                console.error('Error updating status:', error);
            }
        },

        formatDate(dateString) {
            if (!dateString) return 'N/A';
            return new Date(dateString).toLocaleDateString('id-ID', {
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
