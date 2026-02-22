@extends('layouts.app')

@section('title', 'Sales | SAGA POS')

@section('content')
    <div class="mb-6" x-data="salesIndex()">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Sales Orders</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">Manage and track all sales transactions</p>
            </div>
            <a href="{{ route('sales.create') }}"
                class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-white hover:bg-brand-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                New Sale
            </a>
        </div>

        <!-- Filters -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Search</label>
                    <input type="text" x-model="search" @input.debounce.500ms="fetchSales" 
                        placeholder="Order # or Customer"
                        class="w-full bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-brand-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Status</label>
                    <select x-model="status" @change="fetchSales"
                        class="w-full bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-brand-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200">
                        <option value="">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="confirmed">Confirmed</option>
                        <option value="processing">Processing</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Date From</label>
                    <input type="date" x-model="dateFrom" @change="fetchSales"
                        class="w-full bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-brand-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Date To</label>
                    <input type="date" x-model="dateTo" @change="fetchSales"
                        class="w-full bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-brand-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200">
                </div>
            </div>
        </div>

        <!-- Table -->
        <x-card.card>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="border-b border-gray-200 dark:border-gray-700">
                        <tr class="text-gray-600 dark:text-gray-400">
                            <th class="text-left py-3 px-4">Order #</th>
                            <th class="text-left py-3 px-4">Customer</th>
                            <th class="text-right py-3 px-4">Amount</th>
                            <th class="text-left py-3 px-4">Date</th>
                            <th class="text-center py-3 px-4">Status</th>
                            <th class="text-center py-3 px-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-if="isLoading">
                            <tr>
                                <td colspan="6" class="py-8 text-center text-gray-500">
                                    <div class="flex items-center justify-center gap-2">
                                        <div class="w-5 h-5 border-2 border-brand-500 border-t-transparent rounded-full animate-spin"></div>
                                        Loading...
                                    </div>
                                </td>
                            </tr>
                        </template>
                        <template x-if="!isLoading && sales.length === 0">
                            <tr>
                                <td colspan="6" class="py-8 text-center text-gray-500">No sales orders found</td>
                            </tr>
                        </template>
                        <template x-for="order in sales" :key="order.id">
                            <tr class="border-b border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-800">
                                <td class="py-3 px-4 font-medium text-gray-900 dark:text-white" x-text="order.order_number"></td>
                                <td class="py-3 px-4 text-gray-700 dark:text-gray-300" x-text="order.customer?.name || 'General'"></td>
                                <td class="py-3 px-4 text-right text-gray-900 dark:text-white font-semibold" x-text="formatCurrency(order.grand_total)"></td>
                                <td class="py-3 px-4 text-gray-700 dark:text-gray-300" x-text="formatDate(order.order_date)"></td>
                                <td class="py-3 px-4 text-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                        :class="{
                                            'bg-yellow-100 text-yellow-800': order.status === 'pending',
                                            'bg-blue-100 text-blue-800': order.status === 'confirmed',
                                            'bg-purple-100 text-purple-800': order.status === 'processing',
                                            'bg-green-100 text-green-800': order.status === 'completed',
                                            'bg-red-100 text-red-800': order.status === 'cancelled'
                                        }"
                                        x-text="capitalize(order.status)">
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <button @click="viewOrder(order.id)" class="text-brand-500 hover:text-brand-600 text-sm">View</button>
                                        <button @click="deleteOrder(order.id)" class="text-red-500 hover:text-red-600 text-sm">Delete</button>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div x-if="pagination.last_page > 1" class="border-t border-gray-200 dark:border-gray-700 px-4 py-3">
                <div class="flex items-center justify-between">
                    <p class="text-sm text-gray-700 dark:text-gray-300">
                        Showing <span x-text="pagination.from"></span> to <span x-text="pagination.to"></span> of <span x-text="pagination.total"></span> results
                    </p>
                    <div class="flex gap-2">
                        <button @click="changePage(pagination.current_page - 1)" 
                            :disabled="pagination.current_page <= 1"
                            class="px-3 py-1 text-sm border rounded disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-100 dark:border-gray-600 dark:hover:bg-gray-700">
                            Previous
                        </button>
                        <button @click="changePage(pagination.current_page + 1)" 
                            :disabled="pagination.current_page >= pagination.last_page"
                            class="px-3 py-1 text-sm border rounded disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-100 dark:border-gray-600 dark:hover:bg-gray-700">
                            Next
                        </button>
                    </div>
                </div>
            </div>
        </x-card.card>
    </div>
@endsection

@push('scripts')
<script>
function salesIndex() {
    return {
        sales: [],
        isLoading: false,
        search: '',
        status: '',
        dateFrom: '',
        dateTo: '',
        pagination: {
            current_page: 1,
            last_page: 1,
            from: 0,
            to: 0,
            total: 0
        },

        async init() {
            await this.fetchSales();
        },

        async fetchSales() {
            this.isLoading = true;
            try {
                const token = localStorage.getItem('saga_token');
                const params = new URLSearchParams({
                    page: this.pagination.current_page,
                    per_page: 15,
                    search: this.search,
                    status: this.status,
                    date_from: this.dateFrom,
                    date_to: this.dateTo
                });

                const response = await fetch(`/api/sales-orders?${params}`, {
                    headers: {
                        'Authorization': 'Bearer ' + token,
                        'Accept': 'application/json'
                    }
                });
                const result = await response.json();
                
                if (result.success) {
                    this.sales = result.data.data;
                    this.pagination = {
                        current_page: result.data.current_page,
                        last_page: result.data.last_page,
                        from: result.data.from,
                        to: result.data.to,
                        total: result.data.total
                    };
                }
            } catch (e) {
                console.error('Error fetching sales:', e);
            } finally {
                this.isLoading = false;
            }
        },

        changePage(page) {
            if (page >= 1 && page <= this.pagination.last_page) {
                this.pagination.current_page = page;
                this.fetchSales();
            }
        },

        viewOrder(id) {
            alert('View order ' + id + ' - Feature coming soon');
        },

        async deleteOrder(id) {
            if (!confirm('Are you sure you want to delete this sales order?')) return;

            try {
                const token = localStorage.getItem('saga_token');
                const response = await fetch(`/api/sales-orders/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'Authorization': 'Bearer ' + token,
                        'Accept': 'application/json'
                    }
                });
                const result = await response.json();

                if (result.success) {
                    await this.fetchSales();
                    alert('Sales order deleted successfully');
                } else {
                    alert('Failed to delete sales order');
                }
            } catch (e) {
                console.error('Error deleting order:', e);
                alert('Error deleting sales order');
            }
        },

        formatCurrency(amount) {
            return new Intl.NumberFormat('id-ID', { 
                style: 'currency', 
                currency: 'IDR', 
                minimumFractionDigits: 0 
            }).format(amount);
        },

        formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('id-ID', { 
                year: 'numeric', 
                month: 'short', 
                day: 'numeric' 
            });
        },

        capitalize(str) {
            return str.charAt(0).toUpperCase() + str.slice(1);
        }
    }
}
</script>
@endpush
