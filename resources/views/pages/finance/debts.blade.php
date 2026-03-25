@extends('layouts.app')

@section('title', 'Supplier Debts | SAGA TOKO APP')

@section('content')
    <div class="min-h-screen flex flex-col" x-data="{
        page: 'supplierDebts',
        debts: [],
        loading: false,
        showPaymentModal: false,
        selectedDebt: null,
        paymentForm: {
            amount: '',
            payment_date: new Date().toISOString().split('T')[0],
            payment_method: 'cash',
            notes: '',
            reference_number: ''
        },

        filters: {
            status: 'all',
            search: ''
        },

        statistics: {
            total_debt: 0,
            paid_this_month: 0,
            due_this_month: 0,
            overdue: 0
        },

        get filteredDebts() {
            return this.debts.filter(d => {
                const matchStatus = this.filters.status === 'all' || d.payment_status === this.filters.status;
                const matchSearch = d.supplier?.name?.toLowerCase().includes(this.filters.search.toLowerCase()) ||
                                  d.reference_number?.toLowerCase().includes(this.filters.search.toLowerCase());
                return matchStatus && matchSearch;
            });
        },

        get totalDebt() {
            return this.debts.reduce((acc, d) => acc + (d.total_amount - d.paid_amount), 0);
        },

        async init() {
            await this.fetchDebts();
            await this.fetchStatistics();
        },

        async fetchDebts() {
            this.loading = true;
            try {
                const token = localStorage.getItem('saga_token');
                const response = await fetch('/api/debts', {
                    headers: { 'Authorization': 'Bearer ' + token }
                });
                const data = await response.json();
                if (data.success) {
                    this.debts = data.data;
                }
            } catch (error) {
                console.error('Fetch debts error:', error);
                Swal.fire('Error', 'Gagal memuat data hutang', 'error');
            } finally {
                this.loading = false;
            }
        },

        async fetchStatistics() {
            try {
                const token = localStorage.getItem('saga_token');
                if (!token) {
                    console.warn('No token, using default statistics');
                    this.statistics = { total_debt: 0, paid_this_month: 0, due_this_month: 0, overdue: 0 };
                    return;
                }
                const response = await fetch('/api/debts/statistics', {
                    headers: { 'Authorization': 'Bearer ' + token }
                });
                
                // Check if response is JSON
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    console.error('Statistics endpoint returned non-JSON response');
                    this.statistics = { total_debt: 0, paid_this_month: 0, due_this_month: 0, overdue: 0 };
                    return;
                }
                
                const data = await response.json();
                if (data.success) {
                    this.statistics = data.data;
                } else {
                    console.warn('Statistics error:', data.message);
                    this.statistics = { total_debt: 0, paid_this_month: 0, due_this_month: 0, overdue: 0 };
                }
            } catch (error) {
                console.error('Fetch statistics error:', error);
                // Use default values on error
                this.statistics = { total_debt: 0, paid_this_month: 0, due_this_month: 0, overdue: 0 };
            }
        },

        openPaymentModal(debt) {
            this.selectedDebt = debt;
            this.paymentForm = {
                amount: '',
                payment_date: new Date().toISOString().split('T')[0],
                payment_method: 'cash',
                notes: '',
                reference_number: ''
            };
            this.showPaymentModal = true;
        },

        async submitPayment() {
            if (!this.paymentForm.amount || parseFloat(this.paymentForm.amount) <= 0) {
                Swal.fire('Error', 'Jumlah pembayaran harus lebih dari 0', 'error');
                return;
            }

            try {
                const token = localStorage.getItem('saga_token');
                const response = await fetch(`/api/debts/${this.selectedDebt.id}/pay`, {
                    method: 'POST',
                    headers: {
                        'Authorization': 'Bearer ' + token,
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(this.paymentForm)
                });
                const data = await response.json();
                
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: 'Pembayaran hutang berhasil disimpan'
                    });
                    this.showPaymentModal = false;
                    await this.fetchDebts();
                    await this.fetchStatistics();
                } else {
                    Swal.fire('Error', data.message || 'Gagal memproses pembayaran', 'error');
                }
            } catch (error) {
                console.error('Payment error:', error);
                Swal.fire('Error', 'Terjadi kesalahan saat memproses pembayaran', 'error');
            }
        },

        formatCurrency(amount) {
            return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(amount || 0);
        },

        formatDate(dateStr) {
            return new Date(dateStr).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
        },

        getStatusBadge(status) {
            switch(status) {
                case 'paid': return { class: 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400', label: 'Lunas' };
                case 'partial': return { class: 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400', label: 'Sebagian' };
                case 'unpaid': return { class: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400', label: 'Belum Lunas' };
                default: return { class: 'bg-gray-100 text-gray-700', label: '-' };
            }
        }
    }" x-init="">

        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white flex items-center gap-2">
                <span class="p-2 bg-red-50 rounded-lg text-red-600 dark:bg-red-900/30 dark:text-red-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                        </path>
                    </svg>
                </span>
                Hutang Supplier (Accounts Payable)
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 ml-12">Monitor dan kelola hutang pembelian ke supplier
            </p>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
            <div class="bg-gradient-to-br from-red-500 to-red-600 rounded-xl p-5 text-white shadow-lg shadow-red-500/30">
                <p class="text-white/80 text-sm font-medium mb-1">Total Hutang Belum Bayar</p>
                <h3 class="text-2xl font-bold" x-text="formatCurrency(statistics.total_debt)"></h3>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl p-5 border border-gray-200 dark:border-gray-700 shadow-sm">
                <p class="text-gray-500 dark:text-gray-400 text-sm font-medium mb-1">Dibayar Bulan Ini</p>
                <h3 class="text-2xl font-bold text-green-600 dark:text-green-400" x-text="formatCurrency(statistics.paid_this_month)"></h3>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl p-5 border border-gray-200 dark:border-gray-700 shadow-sm">
                <p class="text-gray-500 dark:text-gray-400 text-sm font-medium mb-1">Jatuh Tempo Bulan Ini</p>
                <h3 class="text-2xl font-bold text-blue-600 dark:text-blue-400" x-text="formatCurrency(statistics.due_this_month)"></h3>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl p-5 border border-gray-200 dark:border-gray-700 shadow-sm">
                <p class="text-gray-500 dark:text-gray-400 text-sm font-medium mb-1">Terlewat</p>
                <h3 class="text-2xl font-bold text-orange-600 dark:text-orange-400" x-text="formatCurrency(statistics.overdue)"></h3>
            </div>
        </div>

        <!-- Filters -->
        <div
            class="bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm mb-6 flex flex-col md:flex-row gap-4 justify-between items-center">
            <div class="flex gap-2 w-full md:w-auto">
                <button @click="filters.status = 'all'"
                    :class="filters.status === 'all' ? 'bg-gray-800 text-white dark:bg-white dark:text-gray-900' : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-400'"
                    class="px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                    Semua
                </button>
                <button @click="filters.status = 'unpaid'"
                    :class="filters.status === 'unpaid' ? 'bg-red-600 text-white shadow-lg shadow-red-500/30' : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-400'"
                    class="px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                    Belum Lunas
                </button>
                <button @click="filters.status = 'paid'"
                    :class="filters.status === 'paid' ? 'bg-green-600 text-white shadow-lg shadow-green-500/30' : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-400'"
                    class="px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                    Lunas
                </button>
            </div>
            <div class="relative w-full md:w-64">
                <input type="text" x-model="filters.search" placeholder="Cari Supplier / Invoice..."
                    class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg dark:bg-gray-900 dark:border-gray-600 dark:text-white focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 text-sm">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </div>
        </div>

        <!-- Table -->
        <div
            class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead
                        class="bg-gray-50 dark:bg-gray-700 text-gray-500 dark:text-gray-400 uppercase text-xs font-semibold">
                        <tr>
                            <th class="px-6 py-4">Invoice</th>
                            <th class="px-6 py-4">Supplier</th>
                            <th class="px-6 py-4 text-center">Tgl Jatuh Tempo</th>
                            <th class="px-6 py-4 text-right">Total Tagihan</th>
                            <th class="px-6 py-4 text-right">Sisa Hutang</th>
                            <th class="px-6 py-4 text-center">Status</th>
                            <th class="px-6 py-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        <template x-for="debt in filteredDebts" :key="debt.id">
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                <td class="px-6 py-4">
                                    <span class="font-mono font-medium text-gray-700 dark:text-gray-300"
                                        x-text="debt.invoice"></span>
                                    <div class="text-xs text-gray-400 mt-0.5" x-text="formatDate(debt.date)"></div>
                                </td>
                                <td class="px-6 py-4 font-medium text-gray-800 dark:text-white" x-text="debt.supplier"></td>
                                <td class="px-6 py-4 text-center">
                                    <span
                                        :class="new Date(debt.due_date) < new Date() && debt.status !== 'paid' ? 'text-red-500 font-bold' : 'text-gray-600 dark:text-gray-400'"
                                        x-text="formatDate(debt.due_date)"></span>
                                </td>
                                <td class="px-6 py-4 text-right text-gray-600 dark:text-gray-400"
                                    x-text="formatCurrency(debt.amount)"></td>
                                <td class="px-6 py-4 text-right font-bold text-gray-800 dark:text-white"
                                    x-text="formatCurrency(debt.amount - debt.paid)"></td>
                                <td class="px-6 py-4 text-center">
                                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold"
                                        :class="getStatusBadge(debt.status).class"
                                        x-text="getStatusBadge(debt.status).label"></span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <button x-show="debt.status !== 'paid'" @click="payDebt(debt.id)"
                                        class="text-blue-600 hover:text-blue-700 dark:text-blue-400 font-medium text-xs hover:underline">
                                        Bayar
                                    </button>
                                    <span x-show="debt.status === 'paid'" class="text-gray-400 text-xs">-</span>
                                </td>
                            </tr>
                        </template>
                        <tr x-show="filteredDebts.length === 0">
                            <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                                Tidak ada data hutang ditemukan.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Payment Modal -->
        <div x-show="showPaymentModal" 
             x-cloak
             class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4"
             @click="showPaymentModal = false">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-lg w-full overflow-hidden"
                 @click.stop>
                <div class="p-6 bg-gradient-to-r from-blue-600 to-cyan-600">
                    <h3 class="text-xl font-bold text-white">Bayar Hutang</h3>
                    <p class="text-blue-100 text-sm mt-1" x-text="selectedDebt?.supplier?.name"></p>
                </div>
                
                <div class="p-6 space-y-4">
                    <!-- Invoice Info -->
                    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
                        <div class="flex justify-between mb-2">
                            <span class="text-sm text-gray-500 dark:text-gray-400">Invoice</span>
                            <span class="text-sm font-semibold text-gray-800 dark:text-white" x-text="selectedDebt?.reference_number"></span>
                        </div>
                        <div class="flex justify-between mb-2">
                            <span class="text-sm text-gray-500 dark:text-gray-400">Total Tagihan</span>
                            <span class="text-sm font-semibold text-gray-800 dark:text-white" x-text="formatCurrency(selectedDebt?.total_amount)"></span>
                        </div>
                        <div class="flex justify-between mb-2">
                            <span class="text-sm text-gray-500 dark:text-gray-400">Sudah Dibayar</span>
                            <span class="text-sm font-semibold text-green-600 dark:text-green-400" x-text="formatCurrency(selectedDebt?.paid_amount)"></span>
                        </div>
                        <div class="border-t border-gray-200 dark:border-gray-600 pt-2 mt-2">
                            <div class="flex justify-between">
                                <span class="text-sm font-bold text-gray-700 dark:text-gray-300">Sisa Hutang</span>
                                <span class="text-lg font-bold text-red-600 dark:text-red-400" x-text="formatCurrency(selectedDebt?.total_amount - selectedDebt?.paid_amount)"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Form -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                            Jumlah Pembayaran *
                        </label>
                        <input type="number" 
                               x-model="paymentForm.amount"
                               placeholder="0"
                               class="w-full px-4 py-3 border-2 border-gray-200 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-900 text-gray-800 dark:text-white font-semibold focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                            Metode Pembayaran *
                        </label>
                        <select x-model="paymentForm.payment_method"
                                class="w-full px-4 py-3 border-2 border-gray-200 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-900 text-gray-800 dark:text-white font-semibold focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                            <option value="cash">Tunai</option>
                            <option value="transfer">Transfer Bank</option>
                            <option value="check">Cek / Giro</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                            Tanggal Pembayaran
                        </label>
                        <input type="date" 
                               x-model="paymentForm.payment_date"
                               class="w-full px-4 py-3 border-2 border-gray-200 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-900 text-gray-800 dark:text-white font-semibold focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                            Nomor Referensi (Opsional)
                        </label>
                        <input type="text" 
                               x-model="paymentForm.reference_number"
                               placeholder="No. Cek / No. Transfer"
                               class="w-full px-4 py-3 border-2 border-gray-200 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-900 text-gray-800 dark:text-white font-semibold focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                            Catatan
                        </label>
                        <textarea x-model="paymentForm.notes"
                                  rows="2"
                                  placeholder="Catatan tambahan (opsional)"
                                  class="w-full px-4 py-3 border-2 border-gray-200 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-900 text-gray-800 dark:text-white font-semibold focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"></textarea>
                    </div>
                </div>

                <div class="p-6 border-t border-gray-200 dark:border-gray-700 flex gap-3">
                    <button @click="showPaymentModal = false"
                            class="flex-1 px-6 py-3 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-xl font-semibold hover:bg-gray-200 dark:hover:bg-gray-600 transition-all">
                        Batal
                    </button>
                    <button @click="submitPayment()"
                            class="flex-1 px-6 py-3 bg-gradient-to-r from-blue-600 to-cyan-600 text-white rounded-xl font-semibold hover:from-blue-700 hover:to-cyan-700 transition-all shadow-lg">
                        Bayar Sekarang
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection