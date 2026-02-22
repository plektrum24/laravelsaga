@extends('layouts.app')

@section('title', 'Stock Transfer')

@section('content')
<div x-data="stockTransfer()" x-init="init()">
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Stock Transfer</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Transfer stock between branches</p>
        </div>
        <button @click="openCreateModal()" class="px-4 py-2 bg-brand-600 text-white rounded-lg hover:bg-brand-700 flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            New Transfer
        </button>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Search</label>
                <input type="text" x-model="filters.search" @input.debounce.500ms="loadTransfers()" placeholder="Transfer number..." class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">From Branch</label>
                <select x-model="filters.from_branch_id" @change="loadTransfers()" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                    <option value="">All Branches</option>
                    <template x-for="branch in branches" :key="branch.id">
                        <option :value="branch.id" x-text="branch.name"></option>
                    </template>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">To Branch</label>
                <select x-model="filters.to_branch_id" @change="loadTransfers()" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                    <option value="">All Branches</option>
                    <template x-for="branch in branches" :key="branch.id">
                        <option :value="branch.id" x-text="branch.name"></option>
                    </template>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
                <select x-model="filters.status" @change="loadTransfers()" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                    <option value="">All Status</option>
                    <option value="draft">Draft</option>
                    <option value="pending_approval">Pending Approval</option>
                    <option value="approved">Approved</option>
                    <option value="in_transit">In Transit</option>
                    <option value="received">Received</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Transfer List -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Transfer #</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">From</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">To</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Items</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    <template x-if="loading">
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center">
                                <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-brand-600"></div>
                                <p class="mt-2 text-gray-500">Loading transfers...</p>
                            </td>
                        </tr>
                    </template>
                    <template x-if="!loading && transfers.length === 0">
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                No transfers found. Create your first transfer!
                            </td>
                        </tr>
                    </template>
                    <template x-for="transfer in transfers" :key="transfer.id">
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <td class="px-6 py-4">
                                <span class="font-semibold text-brand-600" x-text="transfer.transfer_number"></span>
                            </td>
                            <td class="px-6 py-4 text-sm" x-text="transfer.from_branch?.name || '-'"></td>
                            <td class="px-6 py-4 text-sm" x-text="transfer.to_branch?.name || '-'"></td>
                            <td class="px-6 py-4 text-sm" x-text="transfer.total_items + ' items'"></td>
                            <td class="px-6 py-4 text-sm text-gray-500" x-text="formatDate(transfer.created_at)"></td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs rounded-full font-semibold"
                                    :class="{
                                        'bg-gray-100 text-gray-700': transfer.status === 'draft',
                                        'bg-yellow-100 text-yellow-700': transfer.status === 'pending_approval',
                                        'bg-blue-100 text-blue-700': transfer.status === 'approved',
                                        'bg-purple-100 text-purple-700': transfer.status === 'in_transit',
                                        'bg-green-100 text-green-700': transfer.status === 'received',
                                        'bg-red-100 text-red-700': transfer.status === 'cancelled'
                                    }"
                                    x-text="formatStatus(transfer.status)">
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end gap-2">
                                    <button @click="viewTransfer(transfer.id)" class="text-blue-600 hover:text-blue-800" title="View">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </button>
                                    <button @click="printTransfer(transfer.id)" class="text-green-600 hover:text-green-800" title="Print">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                                        </svg>
                                    </button>
                                    <template x-if="transfer.status === 'draft'">
                                        <button @click="editTransfer(transfer.id)" class="text-yellow-600 hover:text-yellow-800" title="Edit">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                        </button>
                                    </template>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 flex justify-between items-center">
            <span class="text-sm text-gray-500" x-text="'Showing ' + transfers.length + ' transfers'"></span>
            <div class="flex gap-2">
                <button @click="loadTransfers(currentPage - 1)" :disabled="currentPage === 1" class="px-3 py-1 rounded border disabled:opacity-50">Prev</button>
                <span class="px-3 py-1" x-text="currentPage + ' / ' + totalPages"></span>
                <button @click="loadTransfers(currentPage + 1)" :disabled="currentPage === totalPages" class="px-3 py-1 rounded border disabled:opacity-50">Next</button>
            </div>
        </div>
    </div>

    <!-- Create/Edit Modal -->
    <div x-show="showModal" class="fixed inset-0 z-[99999] flex items-center justify-center bg-gray-900/70 backdrop-blur-sm p-4" x-cloak>
        <div class="bg-white dark:bg-gray-800 rounded-2xl w-full max-w-4xl shadow-2xl overflow-hidden max-h-[90vh] overflow-y-auto">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                <h3 class="text-xl font-bold text-gray-800 dark:text-white" x-text="editMode ? 'Edit Transfer' : 'New Stock Transfer'"></h3>
                <button @click="showModal = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <div class="p-6 space-y-4">
                <!-- Branch Selection -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">From Branch <span class="text-red-500">*</span></label>
                        <select x-model="transferData.from_branch_id" :disabled="editMode" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                            <option value="">Select Source Branch</option>
                            <template x-for="branch in branches" :key="branch.id">
                                <option :value="branch.id" x-text="branch.name"></option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">To Branch <span class="text-red-500">*</span></label>
                        <select x-model="transferData.to_branch_id" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                            <option value="">Select Destination Branch</option>
                            <template x-for="branch in branches" :key="branch.id">
                                <option :value="branch.id" x-text="branch.name"></option>
                            </template>
                        </select>
                    </div>
                </div>

                <!-- Notes -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Notes</label>
                    <textarea x-model="transferData.notes" rows="2" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white" placeholder="Transfer notes..."></textarea>
                </div>

                <!-- Products -->
                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                    <div class="flex justify-between items-center mb-4">
                        <h4 class="font-bold text-gray-800 dark:text-white">Products</h4>
                        <button @click="addProduct()" class="text-sm text-brand-600 hover:text-brand-800">+ Add Product</button>
                    </div>

                    <template x-for="(item, index) in transferData.items" :key="index">
                        <div class="flex gap-2 mb-2 items-end">
                            <div class="flex-1">
                                <label class="text-xs text-gray-500">Product</label>
                                <select x-model="item.product_id" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white text-sm">
                                    <option value="">Select Product</option>
                                    <template x-for="product in products" :key="product.id">
                                        <option :value="product.id" x-text="product.name"></option>
                                    </template>
                                </select>
                            </div>
                            <div class="w-32">
                                <label class="text-xs text-gray-500">Quantity</label>
                                <input type="number" x-model="item.qty_requested" min="0.01" step="0.01" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white text-sm">
                            </div>
                            <button @click="removeProduct(index)" class="p-2 text-red-600 hover:text-red-800">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        </div>
                    </template>
                </div>
            </div>

            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 flex justify-end gap-3">
                <button @click="showModal = false" class="px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">Cancel</button>
                <button @click="saveTransfer()" class="px-6 py-2 bg-brand-600 text-white rounded-lg hover:bg-brand-700">Save Transfer</button>
            </div>
        </div>
    </div>

    <!-- View Modal -->
    <div x-show="showViewModal" class="fixed inset-0 z-[99999] flex items-center justify-center bg-gray-900/70 backdrop-blur-sm p-4" x-cloak>
        <div class="bg-white dark:bg-gray-800 rounded-2xl w-full max-w-4xl shadow-2xl overflow-hidden max-h-[90vh] overflow-y-auto">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                <h3 class="text-xl font-bold text-gray-800 dark:text-white">Transfer Details</h3>
                <button @click="showViewModal = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <div class="p-6" x-show="selectedTransfer">
                <!-- Header Info -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                    <div>
                        <p class="text-xs text-gray-500">Transfer #</p>
                        <p class="font-bold text-brand-600" x-text="selectedTransfer?.transfer_number"></p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">From</p>
                        <p class="font-semibold" x-text="selectedTransfer?.from_branch?.name || '-'"></p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">To</p>
                        <p class="font-semibold" x-text="selectedTransfer?.to_branch?.name || '-'"></p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Status</p>
                        <span class="px-2 py-1 text-xs rounded-full font-semibold"
                            :class="{
                                'bg-gray-100 text-gray-700': selectedTransfer?.status === 'draft',
                                'bg-yellow-100 text-yellow-700': selectedTransfer?.status === 'pending_approval',
                                'bg-blue-100 text-blue-700': selectedTransfer?.status === 'approved',
                                'bg-purple-100 text-purple-700': selectedTransfer?.status === 'in_transit',
                                'bg-green-100 text-green-700': selectedTransfer?.status === 'received',
                                'bg-red-100 text-red-700': selectedTransfer?.status === 'cancelled'
                            }"
                            x-text="formatStatus(selectedTransfer?.status)">
                        </span>
                    </div>
                </div>

                <!-- Items -->
                <div class="mb-6">
                    <h4 class="font-bold mb-2">Transfer Items</h4>
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-4 py-2 text-left">Product</th>
                                <th class="px-4 py-2 text-right">Requested</th>
                                <th class="px-4 py-2 text-right">Approved</th>
                                <th class="px-4 py-2 text-right">Shipped</th>
                                <th class="px-4 py-2 text-right">Received</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="item in selectedTransfer?.items" :key="item.id">
                                <tr class="border-b dark:border-gray-700">
                                    <td class="px-4 py-2" x-text="item.product?.name"></td>
                                    <td class="px-4 py-2 text-right" x-text="formatNumber(item.qty_requested)"></td>
                                    <td class="px-4 py-2 text-right" x-text="formatNumber(item.qty_approved || '-')"></td>
                                    <td class="px-4 py-2 text-right" x-text="formatNumber(item.qty_shipped || '-')"></td>
                                    <td class="px-4 py-2 text-right font-semibold" x-text="formatNumber(item.qty_received || '-')"></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                <!-- Actions -->
                <div class="flex gap-3 pt-4 border-t dark:border-gray-700">
                    <template x-if="selectedTransfer?.status === 'draft'">
                        <button @click="submitTransfer(selectedTransfer.id)" class="px-4 py-2 bg-brand-600 text-white rounded-lg hover:bg-brand-700">Submit for Approval</button>
                    </template>
                    <template x-if="selectedTransfer?.status === 'pending_approval'">
                        <>
                            <button @click="approveTransfer(selectedTransfer.id)" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">Approve</button>
                            <button @click="rejectTransfer(selectedTransfer.id)" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">Reject</button>
                        </>
                    </template>
                    <template x-if="selectedTransfer?.status === 'approved'">
                        <button @click="shipTransfer(selectedTransfer.id)" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Ship</button>
                    </template>
                    <template x-if="selectedTransfer?.status === 'in_transit'">
                        <button @click="receiveTransfer(selectedTransfer.id)" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">Receive</button>
                    </template>
                    <template x-if="selectedTransfer?.status === 'draft' || selectedTransfer?.status === 'pending_approval'">
                        <button @click="cancelTransfer(selectedTransfer.id)" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">Cancel</button>
                    </template>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function stockTransfer() {
    return {
        transfers: [],
        branches: [],
        products: [],
        loading: false,
        showModal: false,
        showViewModal: false,
        editMode: false,
        currentPage: 1,
        totalPages: 1,
        filters: {
            search: '',
            from_branch_id: '',
            to_branch_id: '',
            status: ''
        },
        transferData: {
            from_branch_id: '',
            to_branch_id: '',
            notes: '',
            items: []
        },
        selectedTransfer: null,

        async init() {
            await Promise.all([
                this.loadTransfers(),
                this.loadBranches(),
                this.loadProducts()
            ]);
        },

        async loadTransfers() {
            this.loading = true;
            const token = localStorage.getItem('saga_token');
            try {
                let url = `/api/stock-transfers?page=${this.currentPage}&limit=20`;
                if (this.filters.search) url += `&search=${this.filters.search}`;
                if (this.filters.from_branch_id) url += `&from_branch_id=${this.filters.from_branch_id}`;
                if (this.filters.to_branch_id) url += `&to_branch_id=${this.filters.to_branch_id}`;
                if (this.filters.status) url += `&status=${this.filters.status}`;

                const res = await fetch(url, { headers: { 'Authorization': 'Bearer ' + token } });
                const data = await res.json();
                if (data.success) {
                    this.transfers = data.data.data;
                    this.totalPages = data.data.last_page;
                }
            } catch (e) {
                console.error('Load transfers error:', e);
            } finally {
                this.loading = false;
            }
        },

        async loadBranches() {
            const token = localStorage.getItem('saga_token');
            try {
                const res = await fetch('/api/branches', { headers: { 'Authorization': 'Bearer ' + token } });
                const data = await res.json();
                if (data.success) {
                    this.branches = data.data;
                }
            } catch (e) {
                console.error('Load branches error:', e);
            }
        },

        async loadProducts() {
            const token = localStorage.getItem('saga_token');
            try {
                const res = await fetch('/api/products?limit=1000', { headers: { 'Authorization': 'Bearer ' + token } });
                const data = await res.json();
                if (data.success) {
                    this.products = data.data.products;
                }
            } catch (e) {
                console.error('Load products error:', e);
            }
        },

        openCreateModal() {
            this.editMode = false;
            this.transferData = {
                from_branch_id: '',
                to_branch_id: '',
                notes: '',
                items: [{ product_id: '', qty_requested: 1 }]
            };
            this.showModal = true;
        },

        addProduct() {
            this.transferData.items.push({ product_id: '', qty_requested: 1 });
        },

        removeProduct(index) {
            if (this.transferData.items.length > 1) {
                this.transferData.items.splice(index, 1);
            }
        },

        async saveTransfer() {
            const token = localStorage.getItem('saga_token');
            const url = this.editMode ? `/api/stock-transfers/${this.transferData.id}` : '/api/stock-transfers';
            const method = this.editMode ? 'PUT' : 'POST';

            try {
                const res = await fetch(url, {
                    method,
                    headers: {
                        'Authorization': 'Bearer ' + token,
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(this.transferData)
                });
                const data = await res.json();
                if (data.success) {
                    this.showModal = false;
                    await this.loadTransfers();
                    Swal.fire({ icon: 'success', title: 'Saved', text: 'Transfer saved successfully', toast: true, position: 'top-end', timer: 2000, showConfirmButton: false });
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: data.message });
                }
            } catch (e) {
                console.error('Save error:', e);
                Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to save transfer' });
            }
        },

        async viewTransfer(id) {
            const token = localStorage.getItem('saga_token');
            try {
                const res = await fetch(`/api/stock-transfers/${id}`, { headers: { 'Authorization': 'Bearer ' + token } });
                const data = await res.json();
                if (data.success) {
                    this.selectedTransfer = data.data;
                    this.showViewModal = true;
                }
            } catch (e) {
                console.error('Load transfer error:', e);
            }
        },

        printTransfer(id) {
            const token = localStorage.getItem('saga_token');
            window.open(`/api/stock-transfers/${id}/print?token=${token}`, '_blank');
        },

        async submitTransfer(id) {
            if (!await this.confirmAction('Submit this transfer for approval?')) return;
            await this.performAction(id, 'submit');
        },

        async approveTransfer(id) {
            if (!await this.confirmAction('Approve this transfer?')) return;
            await this.performAction(id, 'approve');
        },

        async rejectTransfer(id) {
            if (!await this.confirmAction('Reject this transfer?')) return;
            await this.performAction(id, 'reject');
        },

        async shipTransfer(id) {
            if (!await this.confirmAction('Mark this transfer as shipped?')) return;
            await this.performAction(id, 'ship');
        },

        async receiveTransfer(id) {
            if (!await this.confirmAction('Mark this transfer as received?')) return;
            await this.performAction(id, 'receive');
        },

        async cancelTransfer(id) {
            if (!await this.confirmAction('Cancel this transfer?')) return;
            await this.performAction(id, 'destroy');
        },

        async performAction(id, action) {
            const token = localStorage.getItem('saga_token');
            try {
                const res = await fetch(`/api/stock-transfers/${id}/${action === 'destroy' ? '' : ''}${action === 'approve' || action === 'reject' || action === 'ship' || action === 'receive' || action === 'submit' ? '/' + action : ''}`, {
                    method: action === 'destroy' ? 'DELETE' : 'POST',
                    headers: { 'Authorization': 'Bearer ' + token }
                });
                const data = await res.json();
                if (data.success) {
                    this.showViewModal = false;
                    await this.loadTransfers();
                    Swal.fire({ icon: 'success', title: 'Success', text: data.message, toast: true, position: 'top-end', timer: 2000, showConfirmButton: false });
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: data.message });
                }
            } catch (e) {
                console.error('Action error:', e);
                Swal.fire({ icon: 'error', title: 'Error', text: 'Action failed' });
            }
        },

        async confirmAction(message) {
            const result = await Swal.fire({
                title: 'Are you sure?',
                text: message,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#4F46E5',
                cancelButtonColor: '#6B7280',
                confirmButtonText: 'Yes'
            });
            return result.isConfirmed;
        },

        formatDate(date) {
            if (!date) return '-';
            return new Date(date).toLocaleDateString('id-ID', { year: 'numeric', month: 'short', day: 'numeric' });
        },

        formatStatus(status) {
            return status.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase());
        },

        formatNumber(num) {
            if (num === '-' || num === null || num === undefined) return '-';
            return new Intl.NumberFormat('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 }).format(num);
        }
    }
}
</script>
@endsection
