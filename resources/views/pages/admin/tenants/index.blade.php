@extends('layouts.app')

@section('title', 'Manage Tenants')

@push('head')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.1/jspdf.plugin.autotable.min.js"></script>
@endpush

@section('content')
    <div x-data="tenantManager()" x-init="init()">
        <!-- Header -->
        <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Manage Tenants</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">Create and manage store branches</p>
            </div>
            <div class="flex gap-2">
                <button @click="exportToPdf()"
                    class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                        </path>
                    </svg>
                    Export PDF
                </button>
                <button @click="openAddModal()"
                    class="px-4 py-2 bg-brand-500 text-white rounded-lg hover:bg-brand-600 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Add Tenant
                </button>
            </div>
        </div>

        <!-- Tenants Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
            <template x-for="tenant in tenants" :key="tenant.id">
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <div
                                class="w-12 h-12 bg-brand-100 rounded-xl flex items-center justify-center dark:bg-brand-900/30">
                                <span class="text-xl font-bold text-brand-600" x-text="tenant.name.charAt(0)">T</span>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-800 dark:text-white" x-text="tenant.name">Tenant</h3>
                                <div class="flex items-center gap-2 mb-2">
                                    <p class="text-xs text-gray-500 font-mono" x-text="tenant.code"></p>
                                    <span
                                        class="px-1.5 py-0.5 rounded text-[10px] bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300 capitalize border border-gray-200 dark:border-gray-600"
                                        x-text="(tenant.business_type || 'Retail').replace('_', ' ')"></span>
                                </div>
                            </div>
                        </div>
                        <span class="px-2 py-1 rounded-full text-xs font-medium" :class="getStatusColor(tenant.status)"
                            x-text="tenant.status"></span>
                    </div>

                    <div class="space-y-2 text-sm mb-4">
                        <div class="flex items-center gap-2 text-gray-500 dark:text-gray-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z">
                                </path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            <span x-text="tenant.address || 'No address'"></span>
                        </div>
                        <div class="flex items-center gap-2 text-gray-500 dark:text-gray-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z">
                                </path>
                            </svg>
                            <span x-text="tenant.phone || 'No phone'"></span>
                        </div>
                        <div class="flex items-center gap-2 text-gray-500 dark:text-gray-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z">
                                </path>
                            </svg>
                            <span x-text="(tenant.users_count || 0) + ' users'"></span>
                        </div>
                        <div
                            class="flex items-center gap-2 text-gray-500 dark:text-gray-400 mt-2 pt-2 border-t border-dashed border-gray-200 dark:border-gray-700">
                            <svg class="w-4 h-4 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <div class="flex items-center gap-2 text-xs w-full">
                                <span class="capitalize font-semibold text-brand-600 dark:text-brand-400"
                                    x-text="tenant.subscription_plan || 'Basic'"></span>
                                <span class="text-gray-300 dark:text-gray-600">|</span>
                                <span
                                    x-text="tenant.valid_until ? 'Exp: ' + new Date(tenant.valid_until).toLocaleDateString('id-ID', {day: 'numeric', month: 'short', year: 'numeric'}) : 'No Expiry'"></span>
                            </div>
                        </div>
                    </div>

                    <div class="flex gap-2 pt-3 border-t border-gray-200 dark:border-gray-700">
                        <button @click="toggleStatus(tenant)" class="flex-1 py-2 text-sm font-medium rounded-lg"
                            :class="tenant.status === 'active' ? 'text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20' : 'text-green-500 hover:bg-green-50 dark:hover:bg-green-900/20'"
                            x-text="tenant.status === 'active' ? 'Suspend' : 'Activate'"></button>
                        <button @click="openResetModal(tenant.id)"
                            class="flex-1 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 rounded-lg dark:text-gray-400 dark:hover:bg-gray-800">Reset
                            Password</button>
                        <button @click="openSubModal(tenant)"
                            class="py-2 px-3 text-sm font-medium text-orange-500 hover:bg-orange-50 rounded-lg dark:hover:bg-orange-900/20"
                            title="Extend Subscription">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                                </path>
                            </svg>
                        </button>
                        <button @click="openEditModal(tenant)"
                            class="py-2 px-3 text-sm font-medium text-blue-500 hover:bg-blue-50 rounded-lg dark:hover:bg-blue-900/20"
                            title="Edit Tenant">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                </path>
                            </svg>
                        </button>
                        <button @click="deleteTenant(tenant)"
                            class="py-2 px-3 text-sm font-medium text-red-500 hover:bg-red-50 rounded-lg dark:hover:bg-red-900/20"
                            title="Delete Tenant">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                </path>
                            </svg>
                        </button>
                    </div>
                </div>
            </template>
        </div>

        <div x-show="isLoading" class="text-center py-12 text-gray-400">Loading tenants...</div>
        <div x-show="!isLoading && tenants.length === 0" class="text-center py-12 text-gray-400">No tenants found. Create
            your first tenant to get started.</div>

        <!-- Add Tenant Modal -->
        <div x-show="showModal"
            class="fixed inset-0 z-[9999] flex items-center justify-center bg-slate-900/80 backdrop-blur-sm" x-cloak>
            <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 w-full max-w-2xl mx-4 shadow-xl transform transition-all">
                <h2 class="text-xl font-bold text-gray-800 dark:text-white mb-6 border-b pb-3 dark:border-gray-700">Add New
                    Tenant</h2>
                <form @submit.prevent="createTenant()" class="space-y-4">
                    <div class="p-4 bg-gray-50 rounded-lg dark:bg-gray-900">
                        <h3 class="font-medium text-gray-800 dark:text-white mb-3">Branch Information</h3>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">Branch Name *</label>
                                <input type="text" x-model="currentTenant.name" required placeholder="e.g. Toko Jakarta"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg dark:bg-gray-800 dark:border-gray-600 dark:text-white">
                            </div>
                            <div>
                                <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">Code
                                    (Auto-generated)</label>
                                <input type="text" x-model="currentTenant.code" readonly disabled
                                    class="w-full px-3 py-2 border border-gray-300 bg-gray-100 text-gray-500 rounded-lg cursor-not-allowed dark:bg-gray-700 dark:border-gray-600 dark:text-gray-400 opacity-70">
                            </div>
                        </div>
                        <div class="mt-3">
                            <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">Address</label>
                            <input type="text" x-model="currentTenant.address" placeholder="Full address"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg dark:bg-gray-800 dark:border-gray-600 dark:text-white">
                        </div>
                        <div class="mt-3">
                            <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">Phone</label>
                            <input type="text" x-model="currentTenant.phone" placeholder="Phone number"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg dark:bg-gray-800 dark:border-gray-600 dark:text-white">
                        </div>
                    </div>

                    <div class="p-4 bg-blue-50 rounded-lg dark:bg-blue-900/20">
                        <h3 class="font-medium text-gray-800 dark:text-white mb-3">Owner Account</h3>
                        <div class="space-y-3">
                            <div>
                                <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">Owner Name *</label>
                                <input type="text" x-model="currentTenant.owner_name" required placeholder="Full name"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg dark:bg-gray-800 dark:border-gray-600 dark:text-white">
                            </div>
                            <div>
                                <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">Owner Email *</label>
                                <input type="email" x-model="currentTenant.owner_email" required
                                    placeholder="email@example.com"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg dark:bg-gray-800 dark:border-gray-600 dark:text-white">
                            </div>
                            <div>
                                <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">Password *</label>
                                <input type="password" x-model="currentTenant.owner_password" required minlength="6"
                                    placeholder="Min 6 characters"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg dark:bg-gray-800 dark:border-gray-600 dark:text-white">
                            </div>
                        </div>
                    </div>

                    <div class="flex gap-3 pt-2">
                        <button type="button" @click="showModal = false" :disabled="isCreating"
                            class="flex-1 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 dark:border-gray-600 dark:hover:bg-gray-700 disabled:opacity-50 dark:text-white">Cancel</button>
                        <button type="submit" :disabled="isCreating"
                            class="flex-1 py-2 bg-brand-500 text-white rounded-lg hover:bg-brand-600 disabled:opacity-50"
                            x-text="isCreating ? 'Creating...' : 'Create Tenant'">Create Tenant</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Edit Tenant Modal -->
        <div x-show="showEditModal"
            class="fixed inset-0 z-[9999] flex items-center justify-center bg-slate-900/80 backdrop-blur-sm" x-cloak>
            <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 w-full max-w-md mx-4">
                <h2 class="text-xl font-bold text-gray-800 dark:text-white mb-4">Edit Tenant</h2>
                <form @submit.prevent="updateTenant()" class="space-y-4">
                    <div>
                        <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">Code</label>
                        <input type="text" :value="editTenant.code" disabled
                            class="w-full px-3 py-2 border border-gray-200 rounded-lg bg-gray-100 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-400 cursor-not-allowed">
                        <p class="text-xs text-gray-400 mt-1">Code cannot be changed</p>
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">Name *</label>
                        <input type="text" x-model="editTenant.name" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg dark:bg-gray-900 dark:border-gray-600 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">Address</label>
                        <input type="text" x-model="editTenant.address"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg dark:bg-gray-900 dark:border-gray-600 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">Phone</label>
                        <input type="text" x-model="editTenant.phone"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg dark:bg-gray-900 dark:border-gray-600 dark:text-white">
                    </div>
                    <div class="flex gap-3 pt-2">
                        <button type="button" @click="showEditModal = false" :disabled="isUpdating"
                            class="flex-1 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 dark:border-gray-600 dark:hover:bg-gray-700 disabled:opacity-50 dark:text-white">Cancel</button>
                        <button type="submit" :disabled="isUpdating"
                            class="flex-1 py-2 bg-brand-500 text-white rounded-lg hover:bg-brand-600 disabled:opacity-50"
                            x-text="isUpdating ? 'Updating...' : 'Update Tenant'">Update Tenant</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Reset Password Modal -->
        <div x-show="showResetModal"
            class="fixed inset-0 z-[9999] flex items-center justify-center bg-slate-900/80 backdrop-blur-sm" x-cloak>
            <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 w-full max-w-sm mx-4">
                <h2 class="text-xl font-bold text-gray-800 dark:text-white mb-4">Reset Owner Password</h2>
                <form @submit.prevent="resetPassword()">
                    <div class="mb-4">
                        <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">New Password</label>
                        <input type="password" x-model="newPassword" required minlength="6" placeholder="Min 6 characters"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg dark:bg-gray-900 dark:border-gray-600 dark:text-white">
                    </div>
                    <div class="flex gap-3">
                        <button type="button" @click="showResetModal = false"
                            class="flex-1 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 dark:border-gray-600 dark:hover:bg-gray-700 dark:text-white">Cancel</button>
                        <button type="submit"
                            class="flex-1 py-2 bg-brand-500 text-white rounded-lg hover:bg-brand-600">Reset</button>
                    </div>
                </form>
            </div>
        </div>

    <!-- Subscription Modal -->
    <div x-show="showSubModal"
        class="fixed inset-0 z-[9999] flex items-center justify-center bg-slate-900/80 backdrop-blur-sm" x-cloak>
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 w-full max-w-md mx-4 shadow-xl">
            <h2 class="text-xl font-bold text-gray-800 dark:text-white mb-4">Extend Subscription</h2>
            <div class="mb-4 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                <p class="text-sm text-gray-600 dark:text-gray-300">Tenant: <span
                        class="font-bold text-gray-800 dark:text-white" x-text="subTenant.name"></span></p>
                <p class="text-sm text-gray-600 dark:text-gray-300">Current Expiry: <span
                        class="font-bold text-gray-800 dark:text-white"
                        x-text="subTenant.valid_until ? new Date(subTenant.valid_until).toLocaleDateString() : 'No Expiry'"></span>
                </p>
            </div>

            <form @submit.prevent="extendSubscription()" class="space-y-4">
                <div>
                    <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">Add Duration</label>
                    <select x-model="subForm.duration"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg dark:bg-gray-800 dark:border-gray-600 dark:text-white">
                        <option value="1_month">+ 1 Month</option>
                        <option value="6_months">+ 6 Months</option>
                        <option value="1_year">+ 1 Year</option>
                        <option value="custom">Set New Date</option>
                    </select>
                </div>
                <div x-show="subForm.duration === 'custom'">
                    <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">New Expiry Date</label>
                    <input type="date" x-model="subForm.custom_date"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg dark:bg-gray-800 dark:border-gray-600 dark:text-white">
                </div>

                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" @click="showSubModal = false"
                        class="px-4 py-2 text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-brand-500 text-white rounded-lg hover:bg-brand-600"
                        :disabled="isUpdating">
                        <span x-show="isUpdating">Saving...</span>
                        <span x-show="!isUpdating">Confirm Extension</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    </div>

    <script>
        function tenantManager() {
            return {
                tenants: [],
                isLoading: true,
                isCreating: false,
                isUpdating: false,
                showModal: false,
                showEditModal: false,
                showSubModal: false,
                showResetModal: false,
                currentTenant: {
                    name: '', code: '', address: '', phone: '',
                    owner_name: '', owner_email: '', owner_password: '',
                    business_type: 'retail', subscription_plan: 'basic',
                    subscription_duration: '1_month', custom_valid_until: ''
                },
                subTenant: { id: null, name: '', valid_until: '' },
                subForm: { duration: '1_year', custom_date: '' },
                editTenant: { id: null, name: '', code: '', address: '', phone: '' },
                resetTenantId: null,
                newPassword: '',

                async init() {
                    await this.fetchTenants();
                },

                generateTenantCode() {
                    // Example: SAGA-X7A9
                    const randomStr = Math.random().toString(36).substring(2, 6).toUpperCase();
                    return `SAGA-${randomStr}`;
                },

                async fetchTenants() {
                    this.isLoading = true;
                    try {
                        const token = localStorage.getItem('saga_token');
                        if (!token) return;

                        const response = await fetch('/api/admin/tenants', {
                            headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
                        });
                        const data = await response.json();
                        if (data.success) {
                            this.tenants = data.data;
                        }
                    } catch (error) { console.error('Error fetching tenants:', error); }
                    finally { this.isLoading = false; }
                },

                openAddModal() {
                    this.currentTenant = {
                        name: '',
                        code: this.generateTenantCode(), // Auto-generate code here
                        address: '',
                        phone: '',
                        owner_name: '',
                        owner_email: '',
                        owner_password: '',
                        business_type: 'retail',
                        subscription_plan: 'basic',
                        subscription_duration: '1_month',
                        custom_valid_until: ''
                    };
                    this.showModal = true;
                },

                openSubModal(tenant) {
                    this.subTenant = { id: tenant.id, name: tenant.name, valid_until: tenant.valid_until };
                    this.subForm = { duration: '1_year', custom_date: '' };
                    this.showSubModal = true;
                },

                async extendSubscription() {
                    this.isUpdating = true;
                    try {
                        const token = localStorage.getItem('saga_token');
                        const response = await fetch(`/api/admin/tenants/${this.subTenant.id}/subscription`, {
                            method: 'POST',
                            headers: { 'Authorization': 'Bearer ' + token, 'Content-Type': 'application/json', 'Accept': 'application/json' },
                            body: JSON.stringify({
                                subscription_duration: this.subForm.duration,
                                custom_valid_until: this.subForm.custom_date
                            })
                        });
                        const data = await response.json();
                        if (data.success) {
                            this.showSubModal = false;
                            await this.fetchTenants();
                            Swal.fire({ icon: 'success', title: 'Extended!', text: data.message, timer: 2000, showConfirmButton: false });
                        } else {
                            Swal.fire({ icon: 'error', title: 'Error', text: data.message });
                        }
                    } catch (error) {
                        console.error('Ext error:', error);
                    } finally {
                        this.isUpdating = false;
                    }
                },

                async createTenant() {
                    this.isCreating = true;
                    try {
                        const token = localStorage.getItem('saga_token');
                        const response = await fetch('/api/admin/tenants', {
                            method: 'POST',
                            headers: { 'Authorization': 'Bearer ' + token, 'Content-Type': 'application/json', 'Accept': 'application/json' },
                            body: JSON.stringify(this.currentTenant)
                        });
                        const data = await response.json();

                        if (data.success) {
                            this.showModal = false;
                            await this.fetchTenants();
                            Swal.fire({ icon: 'success', title: 'Success!', text: 'Tenant created successfully', timer: 1500, showConfirmButton: false });
                        } else {
                            Swal.fire({ icon: 'error', title: 'Failed', text: data.message || 'Validation error' });
                        }
                    } catch (error) {
                        console.error('Create tenant error:', error);
                        Swal.fire({ icon: 'error', title: 'Error', text: error.message || 'An unexpected error occurred', confirmButtonColor: '#ef4444' });
                    } finally {
                        this.isCreating = false;
                    }
                },

                openEditModal(tenant) {
                    this.editTenant = { id: tenant.id, name: tenant.name, code: tenant.code, address: tenant.address || '', phone: tenant.phone || '' };
                    this.showEditModal = true;
                },

                async updateTenant() {
                    this.isUpdating = true;
                    try {
                        const token = localStorage.getItem('saga_token');
                        const response = await fetch(`/api/admin/tenants/${this.editTenant.id}`, {
                            method: 'PUT',
                            headers: { 'Authorization': 'Bearer ' + token, 'Content-Type': 'application/json', 'Accept': 'application/json' },
                            body: JSON.stringify({ name: this.editTenant.name, address: this.editTenant.address, phone: this.editTenant.phone })
                        });
                        const data = await response.json();
                        if (data.success) {
                            this.showEditModal = false;
                            await this.fetchTenants();
                            Swal.fire({ icon: 'success', title: 'Updated!', text: 'Tenant updated successfully', timer: 1500, showConfirmButton: false });
                        } else {
                            Swal.fire({ icon: 'error', title: 'Error', text: data.message || 'Failed to update tenant' });
                        }
                    } catch (error) {
                        console.error('Update error:', error);
                        Swal.fire({ icon: 'error', title: 'Error', text: 'An unexpected error occurred' });
                    } finally {
                        this.isUpdating = false;
                    }
                },

                async toggleStatus(tenant) {
                    const newStatus = tenant.status === 'active' ? 'suspended' : 'active';
                    const result = await Swal.fire({
                        title: 'Are you sure?',
                        text: `Do you want to ${newStatus === 'active' ? 'activate' : 'suspend'} ${tenant.name}?`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#465fff',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Yes, update it!'
                    });

                    if (!result.isConfirmed) return;

                    try {
                        const token = localStorage.getItem('saga_token');
                        const response = await fetch(`/api/admin/tenants/${tenant.id}/status`, {
                            method: 'PATCH',
                            headers: { 'Authorization': 'Bearer ' + token, 'Content-Type': 'application/json', 'Accept': 'application/json' },
                            body: JSON.stringify({ status: newStatus })
                        });
                        const data = await response.json();
                        if (data.success) {
                            await this.fetchTenants();
                            Swal.fire('Updated!', data.message, 'success');
                        } else {
                            Swal.fire('Error!', data.message, 'error');
                        }
                    } catch (error) { console.error('Status update error:', error); }
                },

                openResetModal(tenantId) {
                    this.resetTenantId = tenantId;
                    this.newPassword = '';
                    this.showResetModal = true;
                },

                async resetPassword() {
                    if (this.newPassword.length < 6) {
                        Swal.fire('Warning', 'Password must be at least 6 characters', 'warning');
                        return;
                    }
                    Swal.fire('Info', 'Reset password feature will be implemented', 'info');
                    this.showResetModal = false;
                },

                async deleteTenant(tenant) {
                    const result = await Swal.fire({
                        title: 'Delete Tenant?',
                        text: 'Are you sure you want to delete ' + tenant.name + '? This will deactivate the tenant.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#ef4444',
                        cancelButtonColor: '#6b7280',
                        confirmButtonText: 'Yes, Delete',
                        cancelButtonText: 'Cancel'
                    });

                    if (!result.isConfirmed) return;

                    try {
                        const token = localStorage.getItem('saga_token');
                        const response = await fetch('/api/admin/tenants/' + tenant.id, {
                            method: 'DELETE',
                            headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
                        });
                        const data = await response.json();
                        if (data.success) {
                            await this.fetchTenants();
                            Swal.fire('Deleted!', data.message, 'success');
                        } else {
                            Swal.fire('Error!', data.message, 'error');
                        }
                    } catch (error) {
                        console.error('Delete error:', error);
                        Swal.fire('Error!', 'Failed to delete tenant', 'error');
                    }
                },

                getStatusColor(status) {
                    if (status === 'active') return 'bg-green-100 text-green-600 dark:bg-green-900/30 dark:text-green-400';
                    if (status === 'suspended') return 'bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400';
                    return 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400';
                },

                exportToPdf() {
                    const { jsPDF } = window.jspdf;
                    const doc = new jsPDF();

                    doc.setFontSize(18);
                    doc.text('Tenant Report', 14, 20);
                    doc.setFontSize(10);
                    doc.text(`Generated: ${new Date().toLocaleDateString('id-ID')}`, 14, 28);

                    const tableData = this.tenants.map(t => [t.name, t.code, t.address || '-', t.phone || '-', t.status, t.users_count || 0]);

                    doc.autoTable({
                        startY: 35,
                        head: [['Name', 'Code', 'Address', 'Phone', 'Status', 'Users']],
                        body: tableData,
                        styles: { fontSize: 9 },
                        headStyles: { fillColor: [70, 95, 255] }
                    });

                    doc.save(`Tenants_${new Date().toISOString().split('T')[0]}.pdf`);
                }
            }
        }
    </script>
@endsection