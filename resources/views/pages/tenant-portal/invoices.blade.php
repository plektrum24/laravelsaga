@extends('layouts.app')

@section('title', 'Invoices | Tenant Portal')

@section('content')
<div x-data="invoicePortal()" x-init="init()" class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Invoices</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">View and pay your invoices</p>
        </div>
        <div class="flex gap-2">
            <a href="/tenant-portal" class="btn-secondary">← Back to Portal</a>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Paid</p>
            <h3 class="text-2xl font-bold text-green-500 mt-1">Rp <span x-text="formatNumber(summary?.paid_total)"></span></h3>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Pending</p>
            <h3 class="text-2xl font-bold text-blue-500 mt-1">Rp <span x-text="formatNumber(summary?.pending_total)"></span></h3>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Overdue</p>
            <h3 class="text-2xl font-bold text-red-500 mt-1">Rp <span x-text="formatNumber(summary?.overdue_total)"></span></h3>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Invoices</p>
            <h3 class="text-2xl font-bold text-gray-800 dark:text-white mt-1" x-text="invoices.total || 0">0</h3>
        </div>
    </div>

    <!-- Filters -->
    <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="flex gap-4">
            <select x-model="filters.status" @change="fetchInvoices" class="rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                <option value="">All Status</option>
                <option value="paid">Paid</option>
                <option value="sent">Sent</option>
                <option value="overdue">Overdue</option>
                <option value="draft">Draft</option>
            </select>
        </div>
    </div>

    <!-- Invoices Table -->
    <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="border-b border-gray-200 dark:border-gray-800">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Invoice #</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Due Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                    <template x-for="invoice in invoices.data" :key="invoice.id">
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/50">
                            <td class="px-6 py-4 font-medium text-gray-800 dark:text-white" x-text="invoice.invoice_number"></td>
                            <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400" x-text="formatDate(invoice.created_at)"></td>
                            <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400" x-text="formatDate(invoice.due_date)"></td>
                            <td class="px-6 py-4 font-medium text-gray-800 dark:text-white">Rp <span x-text="formatNumber(invoice.total)"></span></td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs rounded-full capitalize"
                                      :class="{
                                          'bg-green-100 text-green-700': invoice.status === 'paid',
                                          'bg-blue-100 text-blue-700': invoice.status === 'sent',
                                          'bg-red-100 text-red-700': invoice.status === 'overdue',
                                          'bg-gray-100 text-gray-700': invoice.status === 'draft'
                                      }"
                                      x-text="invoice.status">
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a :href="`/api/tenant/invoices/${invoice.id}/pdf`" target="_blank" class="text-brand-500 hover:text-brand-600 text-sm">Download PDF</a>
                                    <button x-show="invoice.status !== 'paid'" @click="payInvoice(invoice)" class="text-green-500 hover:text-green-600 text-sm">Pay Now</button>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
        <div x-show="invoices.data.length === 0" class="text-center py-12">
            <p class="text-gray-500 dark:text-gray-400">No invoices found</p>
        </div>
    </div>
</div>

<!-- Payment Modal -->
<div x-show="showPaymentModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75" @click="showPaymentModal = false"></div>
        <div class="relative bg-white dark:bg-gray-900 rounded-2xl p-6 max-w-lg w-full shadow-xl">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">Pay Invoice</h3>
            <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4 mb-4">
                <div class="flex justify-between mb-2">
                    <span class="text-sm text-gray-500">Invoice:</span>
                    <span class="font-medium" x-text="selectedInvoice?.invoice_number"></span>
                </div>
                <div class="flex justify-between mb-2">
                    <span class="text-sm text-gray-500">Amount:</span>
                    <span class="font-bold text-brand-500">Rp <span x-text="formatNumber(selectedInvoice?.total)"></span></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-sm text-gray-500">Due Date:</span>
                    <span class="font-medium" x-text="formatDate(selectedInvoice?.due_date)"></span>
                </div>
            </div>
            
            <!-- Payment Method Selection -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Payment Method</label>
                <select x-model="selectedPaymentMethod" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    <option value="credit_card">Credit/Debit Card</option>
                    <option value="bank_transfer">Bank Transfer</option>
                    <option value="gopay">GoPay</option>
                    <option value="shopeepay">ShopeePay</option>
                </select>
            </div>

            <!-- Midtrans Snap Container -->
            <div id="snap-container" class="mb-4"></div>

            <div class="flex justify-end gap-2">
                <button @click="showPaymentModal = false" class="btn-secondary">Cancel</button>
                <button @click="initiatePayment" class="btn-primary">Pay Now</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<!-- Midtrans Snap -->
<script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('services.midtrans.client_key') }}"></script>
<script>
function invoicePortal() {
    return {
        invoices: { data: [], total: 0 },
        summary: null,
        filters: { status: '' },
        selectedInvoice: null,
        selectedPaymentMethod: 'credit_card',
        showPaymentModal: false,

        async init() {
            await Promise.all([this.fetchInvoices(), this.fetchSummary()]);
        },

        async fetchInvoices() {
            try {
                const token = localStorage.getItem('saga_token');
                const params = new URLSearchParams({ status: this.filters.status });
                const response = await fetch(`/api/tenant/invoices?${params}`, {
                    headers: { 'Authorization': 'Bearer ' + token }
                });
                const result = await response.json();
                if (result.success) {
                    this.invoices = result.data;
                }
            } catch (error) {
                console.error('Error fetching invoices:', error);
            }
        },

        async fetchSummary() {
            try {
                const token = localStorage.getItem('saga_token');
                const response = await fetch('/api/tenant/invoices/summary', {
                    headers: { 'Authorization': 'Bearer ' + token }
                });
                const result = await response.json();
                if (result.success) {
                    this.summary = result.data.summary;
                }
            } catch (error) {
                console.error('Error fetching summary:', error);
            }
        },

        payInvoice(invoice) {
            this.selectedInvoice = invoice;
            this.showPaymentModal = true;
        },

        async initiatePayment() {
            try {
                const token = localStorage.getItem('saga_token');
                const response = await fetch(`/api/tenant/invoices/${this.selectedInvoice.id}/pay`, {
                    method: 'POST',
                    headers: {
                        'Authorization': 'Bearer ' + token,
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ payment_method: this.selectedPaymentMethod })
                });
                const result = await response.json();
                if (result.success && result.data.snap_token) {
                    // Open Midtrans Snap popup
                    snap.pay(result.data.snap_token, {
                        onSuccess: (result) => {
                            alert('Payment successful!');
                            this.showPaymentModal = false;
                            this.fetchInvoices();
                        },
                        onPending: (result) => {
                            alert('Payment pending. Please complete the payment.');
                        },
                        onError: (result) => {
                            alert('Payment failed. Please try again.');
                        },
                        onClose: () => {
                            // User closed the popup
                        }
                    });
                } else {
                    alert(result.message || 'Failed to initiate payment');
                }
            } catch (error) {
                console.error('Error initiating payment:', error);
                alert('An error occurred. Please try again.');
            }
        },

        formatDate(dateString) {
            if (!dateString) return 'N/A';
            return new Date(dateString).toLocaleDateString('id-ID', {
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            });
        },

        formatNumber(num) {
            if (!num && num !== 0) return '0';
            return new Intl.NumberFormat('id-ID').format(num);
        }
    }
}
</script>
@endpush
@endsection
