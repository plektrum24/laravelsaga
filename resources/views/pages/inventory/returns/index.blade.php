@extends('layouts.app')

@section('title', 'Returns Management | SAGA TOKO APP')

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-gray-900 p-6" x-data="returnsManagement()">
    <!-- Header -->
    <div class="max-w-8xl mx-auto mb-8">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-14 h-14 bg-gradient-to-br from-red-500 to-rose-600 rounded-2xl flex items-center justify-center shadow-xl shadow-red-500/30">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-gray-800 dark:text-white">Returns Management</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Manage supplier & customer returns in one place</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <button @click="activeTab = 'supplier'" :class="activeTab === 'supplier' ? 'bg-red-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300'" class="px-6 py-3 rounded-xl font-semibold transition-all border-2 border-red-600">
                    Supplier Returns
                </button>
                <button @click="activeTab = 'customer'" :class="activeTab === 'customer' ? 'bg-red-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300'" class="px-6 py-3 rounded-xl font-semibold transition-all border-2 border-red-600">
                    Customer Returns
                </button>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="max-w-8xl mx-auto mb-8 grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-gradient-to-br from-red-500 to-rose-600 rounded-2xl p-6 text-white shadow-xl shadow-red-500/20">
            <div class="flex items-center justify-between mb-4">
                <div class="w-14 h-14 bg-white/20 backdrop-blur-sm rounded-2xl flex items-center justify-center">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                </div>
                <span class="text-3xl font-bold" x-text="stats.total"></span>
            </div>
            <p class="text-red-100 text-sm font-medium">Total Returns</p>
            <p class="text-red-200 text-xs mt-1">This month</p>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between mb-4">
                <div class="w-14 h-14 bg-gradient-to-br from-amber-100 to-orange-100 dark:from-amber-900/30 dark:to-orange-900/30 rounded-2xl flex items-center justify-center">
                    <svg class="w-7 h-7 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <span class="text-3xl font-bold text-gray-800 dark:text-white" x-text="stats.pending"></span>
            </div>
            <p class="text-gray-500 dark:text-gray-400 text-sm font-medium">Pending</p>
            <p class="text-amber-600 text-xs mt-1">Awaiting processing</p>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between mb-4">
                <div class="w-14 h-14 bg-gradient-to-br from-green-100 to-emerald-100 dark:from-green-900/30 dark:to-emerald-900/30 rounded-2xl flex items-center justify-center">
                    <svg class="w-7 h-7 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <span class="text-3xl font-bold text-gray-800 dark:text-white" x-text="stats.completed"></span>
            </div>
            <p class="text-gray-500 dark:text-gray-400 text-sm font-medium">Completed</p>
            <p class="text-green-600 text-xs mt-1">Successfully processed</p>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between mb-4">
                <div class="w-14 h-14 bg-gradient-to-br from-blue-100 to-cyan-100 dark:from-blue-900/30 dark:to-cyan-900/30 rounded-2xl flex items-center justify-center">
                    <svg class="w-7 h-7 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <span class="text-2xl font-bold text-gray-800 dark:text-white" x-text="formatCurrency(stats.value)"></span>
            </div>
            <p class="text-gray-500 dark:text-gray-400 text-sm font-medium">Total Return Value</p>
            <p class="text-blue-600 text-xs mt-1">This month</p>
        </div>
    </div>

    <!-- Returns Table -->
    <div class="max-w-8xl mx-auto bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-gray-800 dark:text-white" x-text="activeTab === 'supplier' ? 'Supplier Returns' : 'Customer Returns'"></h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Manage and track return requests</p>
            </div>
            <button @click="createNewReturn()" class="px-6 py-3 bg-gradient-to-r from-red-600 to-rose-600 text-white rounded-xl font-semibold hover:from-red-700 hover:to-rose-700 transition-all shadow-lg flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Create Return
            </button>
        </div>

        <div class="p-6 border-b border-gray-200 dark:border-gray-700 flex items-center gap-4">
            <input type="text" x-model="search" placeholder="🔍 Search returns..." class="px-4 py-2.5 border-2 border-gray-200 dark:border-gray-700 rounded-xl bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-red-500/20 focus:border-red-500">
            <select x-model="filterStatus" class="px-4 py-2.5 border-2 border-gray-200 dark:border-gray-700 rounded-xl bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-red-500/20 focus:border-red-500">
                <option value="">All Status</option>
                <option value="pending">Pending</option>
                <option value="approved">Approved</option>
                <option value="processing">Processing</option>
                <option value="completed">Completed</option>
                <option value="rejected">Rejected</option>
            </select>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-gray-900/50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Return #</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase" x-text="activeTab === 'supplier' ? 'Supplier' : 'Customer'"></th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Products</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Return Date</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Total Value</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Status</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Reason</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    <template x-for="ret in filteredReturns" :key="ret.id">
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/30 transition-colors">
                            <td class="px-6 py-4">
                                <span class="font-semibold text-red-600 dark:text-red-400" x-text="ret.return_number"></span>
                            </td>
                            <td class="px-6 py-4">
                                <div>
                                    <p class="font-semibold text-gray-800 dark:text-white" x-text="ret.party_name"></p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400" x-text="ret.party_type"></p>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div>
                                    <p class="font-semibold text-gray-800 dark:text-white" x-text="ret.products_count + ' items'"></p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400" x-text="ret.total_qty + ' units'"></p>
                                </div>
                            </td>
                            <td class="px-6 py-4"><span class="text-gray-700 dark:text-gray-300 text-sm" x-text="formatDate(ret.return_date)"></span></td>
                            <td class="px-6 py-4"><span class="font-bold text-gray-800 dark:text-white" x-text="formatCurrency(ret.total_value)"></span></td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold"
                                    :class="{
                                        'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400': ret.status === 'pending',
                                        'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400': ret.status === 'approved',
                                        'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400': ret.status === 'processing',
                                        'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400': ret.status === 'completed',
                                        'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400': ret.status === 'rejected'
                                    }"
                                    x-text="ret.status.charAt(0).toUpperCase() + ret.status.slice(1)">
                                </span>
                            </td>
                            <td class="px-6 py-4"><span class="text-gray-700 dark:text-gray-300 text-sm" x-text="ret.reason"></span></td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button @click="viewReturn(ret)" class="p-2 text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-colors" title="View Details">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </button>
                                    <button @click="processReturn(ret)" class="p-2 text-green-600 dark:text-green-400 hover:bg-green-50 dark:hover:bg-green-900/20 rounded-lg transition-colors" title="Process">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>

            <!-- Empty State -->
            <div x-show="filteredReturns.length === 0" class="p-12 text-center">
                <div class="w-24 h-24 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-2">No returns found</h3>
                <p class="text-gray-500 dark:text-gray-400 mb-6">Try adjusting your search or filters</p>
                <button @click="createNewReturn()" class="px-6 py-3 bg-gradient-to-r from-red-600 to-rose-600 text-white rounded-xl font-semibold hover:from-red-700 hover:to-rose-700 transition-all shadow-lg">
                    Create New Return
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function returnsManagement() {
    return {
        activeTab: 'supplier',
        search: '',
        filterStatus: '',
        stats: {
            total: 47,
            pending: 12,
            completed: 28,
            value: 15750000
        },
        supplierReturns: [
            { id: 1, return_number: 'SR-2026-001', party_name: 'PT. Supplier Utama', party_type: 'Supplier', products_count: 3, total_qty: 25, return_date: '2026-02-20', total_value: 2500000, status: 'pending', reason: 'Damaged goods' },
            { id: 2, return_number: 'SR-2026-002', party_name: 'CV. Berkah Jaya', party_type: 'Supplier', products_count: 2, total_qty: 15, return_date: '2026-02-18', total_value: 1800000, status: 'completed', reason: 'Wrong specification' },
            { id: 3, return_number: 'SR-2026-003', party_name: 'PT. Supplier Utama', party_type: 'Supplier', products_count: 5, total_qty: 50, return_date: '2026-02-15', total_value: 4200000, status: 'processing', reason: 'Expired products' }
        ],
        customerReturns: [
            { id: 1, return_number: 'CR-2026-001', party_name: 'John Doe', party_type: 'Customer', products_count: 1, total_qty: 2, return_date: '2026-02-21', total_value: 350000, status: 'pending', reason: 'Product defect' },
            { id: 2, return_number: 'CR-2026-002', party_name: 'Jane Smith', party_type: 'Customer', products_count: 2, total_qty: 3, return_date: '2026-02-19', total_value: 520000, status: 'approved', reason: 'Wrong size' },
            { id: 3, return_number: 'CR-2026-003', party_name: 'Bob Wilson', party_type: 'Customer', products_count: 1, total_qty: 1, return_date: '2026-02-17', total_value: 180000, status: 'completed', reason: 'Changed mind' }
        ],

        get returns() {
            return this.activeTab === 'supplier' ? this.supplierReturns : this.customerReturns;
        },

        get filteredReturns() {
            let result = this.returns;
            if (this.search) {
                const q = this.search.toLowerCase();
                result = result.filter(r =>
                    r.return_number.toLowerCase().includes(q) ||
                    r.party_name.toLowerCase().includes(q) ||
                    r.reason.toLowerCase().includes(q)
                );
            }
            if (this.filterStatus) {
                result = result.filter(r => r.status === this.filterStatus);
            }
            return result;
        },

        formatCurrency(amount) {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0
            }).format(amount);
        },

        formatDate(dateStr) {
            return new Date(dateStr).toLocaleDateString('id-ID', {
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            });
        },

        createNewReturn() {
            Swal.fire({
                title: 'Create New Return',
                html: `
                    <div class="space-y-3 text-left">
                        <input id="return-type" class="swal2-input" placeholder="Return Type (Supplier/Customer)">
                        <input id="party-name" class="swal2-input" placeholder="${this.activeTab === 'supplier' ? 'Supplier' : 'Customer'} Name">
                        <input id="products" class="swal2-input" placeholder="Products">
                        <input id="reason" class="swal2-input" placeholder="Return Reason">
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Create',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#dc2626'
            });
        },

        viewReturn(ret) {
            Swal.fire({
                title: `Return ${ret.return_number}`,
                html: `
                    <div class="text-left space-y-3 pt-4">
                        <p><strong>${this.activeTab === 'supplier' ? 'Supplier' : 'Customer'}:</strong> ${ret.party_name}</p>
                        <p><strong>Products:</strong> ${ret.products_count} items (${ret.total_qty} units)</p>
                        <p><strong>Return Date:</strong> ${this.formatDate(ret.return_date)}</p>
                        <p><strong>Total Value:</strong> ${this.formatCurrency(ret.total_value)}</p>
                        <p><strong>Status:</strong> ${ret.status}</p>
                        <p><strong>Reason:</strong> ${ret.reason}</p>
                    </div>
                `,
                confirmButtonText: 'Close',
                confirmButtonColor: '#dc2626'
            });
        },

        processReturn(ret) {
            Swal.fire({
                title: 'Process Return',
                text: `Mark return ${ret.return_number} as completed?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, Complete',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#16a34a'
            }).then((result) => {
                if (result.isConfirmed) {
                    ret.status = 'completed';
                    Swal.fire('Completed!', 'Return has been processed', 'success');
                }
            });
        }
    }
}
</script>
@endpush
@endsection
