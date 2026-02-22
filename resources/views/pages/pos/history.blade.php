@extends('layouts.app')

@section('title', 'Riwayat Transaksi | SAGA POS')

@section('content')
<div class="px-6 py-4" x-data="posHistory()">
    <!-- Page Header -->
    <div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-800 dark:text-white flex items-center gap-3">
                <svg class="w-8 h-8 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Riwayat Transaksi
            </h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Lihat dan kelola history penjualan kasir</p>
        </div>
        <div class="flex gap-3">
            <button @click="exportReport()" class="bg-gradient-to-r from-brand-600 to-indigo-600 text-white px-5 py-2.5 rounded-xl hover:from-brand-700 hover:to-indigo-700 font-semibold text-sm flex items-center gap-2 shadow-lg shadow-brand-500/30 transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Export Laporan
            </button>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-gradient-to-br from-brand-500 to-indigo-600 rounded-2xl p-5 text-white shadow-xl shadow-brand-500/30">
            <div class="flex items-center justify-between mb-3">
                <span class="text-sm font-medium opacity-90">Total Transaksi</span>
                <svg class="w-6 h-6 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
            </div>
            <p class="text-3xl font-bold" x-text="stats.total"></p>
            <p class="text-xs opacity-75 mt-1">Hari ini</p>
        </div>

        <div class="bg-gradient-to-br from-green-500 to-emerald-600 rounded-2xl p-5 text-white shadow-xl shadow-green-500/30">
            <div class="flex items-center justify-between mb-3">
                <span class="text-sm font-medium opacity-90">Total Pendapatan</span>
                <svg class="w-6 h-6 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <p class="text-2xl font-bold" x-text="formatCurrency(stats.revenue)"></p>
            <p class="text-xs opacity-75 mt-1">Hari ini</p>
        </div>

        <div class="bg-gradient-to-br from-orange-500 to-amber-600 rounded-2xl p-5 text-white shadow-xl shadow-orange-500/30">
            <div class="flex items-center justify-between mb-3">
                <span class="text-sm font-medium opacity-90">Transaksi Tunai</span>
                <svg class="w-6 h-6 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
            </div>
            <p class="text-3xl font-bold" x-text="stats.cash"></p>
            <p class="text-xs opacity-75 mt-1">Transaksi</p>
        </div>

        <div class="bg-gradient-to-br from-purple-500 to-violet-600 rounded-2xl p-5 text-white shadow-xl shadow-purple-500/30">
            <div class="flex items-center justify-between mb-3">
                <span class="text-sm font-medium opacity-90">Rata-rata</span>
                <svg class="w-6 h-6 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                </svg>
            </div>
            <p class="text-2xl font-bold" x-text="formatCurrency(stats.average)"></p>
            <p class="text-xs opacity-75 mt-1">Per transaksi</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-5 mb-6 border border-gray-200 dark:border-gray-700">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div>
                <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-2 uppercase tracking-wide">
                    📅 Tanggal Mulai
                </label>
                <input type="date" x-model="startDate"
                    class="w-full bg-gray-50 dark:bg-gray-700 border-2 border-gray-200 dark:border-gray-600 rounded-xl px-4 py-3 text-sm font-medium focus:outline-none focus:border-brand-500 dark:text-gray-200 transition-all">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-2 uppercase tracking-wide">
                    📅 Tanggal Akhir
                </label>
                <input type="date" x-model="endDate"
                    class="w-full bg-gray-50 dark:bg-gray-700 border-2 border-gray-200 dark:border-gray-600 rounded-xl px-4 py-3 text-sm font-medium focus:outline-none focus:border-brand-500 dark:text-gray-200 transition-all">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-2 uppercase tracking-wide">
                    👤 Kasir
                </label>
                <select x-model="selectedCashier"
                    class="w-full bg-gray-50 dark:bg-gray-700 border-2 border-gray-200 dark:border-gray-600 rounded-xl px-4 py-3 text-sm font-medium focus:outline-none focus:border-brand-500 dark:text-gray-200 transition-all">
                    <option value="">Semua Kasir</option>
                    <template x-for="cashier in cashiers" :key="cashier.id">
                        <option :value="cashier.id" x-text="cashier.name"></option>
                    </template>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-2 uppercase tracking-wide">
                    💳 Metode
                </label>
                <select x-model="selectedMethod"
                    class="w-full bg-gray-50 dark:bg-gray-700 border-2 border-gray-200 dark:border-gray-600 rounded-xl px-4 py-3 text-sm font-medium focus:outline-none focus:border-brand-500 dark:text-gray-200 transition-all">
                    <option value="">Semua Metode</option>
                    <option value="Cash">Tunai</option>
                    <option value="Transfer">Transfer</option>
                    <option value="Card">Kartu</option>
                    <option value="E-Wallet">E-Wallet</option>
                </select>
            </div>
            <div class="flex items-end">
                <button @click="applyFilters()"
                    class="w-full bg-gradient-to-r from-brand-600 to-indigo-600 text-white px-4 py-3 rounded-xl hover:from-brand-700 hover:to-indigo-700 font-semibold text-sm shadow-lg shadow-brand-500/30 transition-all flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    Filter
                </button>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm overflow-hidden border border-gray-200 dark:border-gray-700">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-700 dark:to-gray-600 text-xs uppercase text-gray-500 dark:text-gray-300 font-bold border-b-2 border-gray-200 dark:border-gray-700">
                        <th class="px-6 py-4">No. Transaksi</th>
                        <th class="px-6 py-4">Waktu</th>
                        <th class="px-6 py-4">Pelanggan</th>
                        <th class="px-6 py-4">Total</th>
                        <th class="px-6 py-4">Metode</th>
                        <th class="px-6 py-4">Kasir</th>
                        <th class="px-6 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700 text-sm">
                    <!-- Loading State -->
                    <tr x-show="isLoading">
                        <td colspan="7" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <div class="animate-spin rounded-full h-10 w-10 border-4 border-brand-200 mb-3"></div>
                                <div class="animate-spin rounded-full h-10 w-10 border-t-4 border-brand-600 absolute"></div>
                                <p class="text-brand-600 font-semibold">Memuat data...</p>
                            </div>
                        </td>
                    </tr>

                    <!-- Empty State -->
                    <tr x-show="!isLoading && transactions.length === 0">
                        <td colspan="7" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <div class="w-20 h-20 mb-4 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center">
                                    <svg class="w-10 h-10 text-gray-300 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                </div>
                                <p class="text-lg font-semibold text-gray-600 dark:text-gray-300 mb-1">Belum ada transaksi</p>
                                <p class="text-sm text-gray-400">Transaksi yang telah dibuat akan muncul di sini</p>
                            </div>
                        </td>
                    </tr>

                    <!-- Transaction Rows -->
                    <template x-for="trx in transactions" :key="trx.id">
                        <tr class="hover:bg-gradient-to-r hover:from-brand-50 hover:to-indigo-50 dark:hover:from-gray-700 dark:hover:to-gray-600 transition-all group">
                            <td class="px-6 py-4">
                                <span class="font-bold text-brand-600 dark:text-brand-400 bg-brand-50 dark:bg-brand-900/30 px-3 py-1.5 rounded-lg text-xs" x-text="trx.invoice_number"></span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    <span class="text-gray-800 dark:text-gray-200 font-medium" x-text="formatDate(trx.created_at)"></span>
                                    <span class="text-xs text-gray-400" x-text="formatTime(trx.created_at)"></span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="font-semibold text-gray-800 dark:text-gray-200" x-text="trx.customer?.name || '👤 Umum'"></span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-lg font-bold text-gray-800 dark:text-white" x-text="formatCurrency(trx.grand_total)"></span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-3 py-1.5 rounded-full text-xs font-bold"
                                    :class="{
                                        'bg-green-100 text-green-700': trx.payment_method === 'Cash',
                                        'bg-blue-100 text-blue-700': trx.payment_method === 'Transfer',
                                        'bg-purple-100 text-purple-700': trx.payment_method === 'Card',
                                        'bg-orange-100 text-orange-700': trx.payment_method === 'E-Wallet'
                                    }"
                                    x-text="trx.payment_method">
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-gray-600 dark:text-gray-400 font-medium" x-text="trx.user?.name || '-'"></span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <button @click="printReceipt(trx.id)" 
                                        class="p-2 rounded-lg bg-brand-100 dark:bg-brand-900/30 text-brand-600 dark:text-brand-400 hover:bg-brand-200 dark:hover:bg-brand-900/50 transition-all" 
                                        title="Cetak Ulang">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                                        </svg>
                                    </button>
                                    <button @click="viewDetail(trx)" 
                                        class="p-2 rounded-lg bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 hover:bg-blue-200 dark:hover:bg-blue-900/50 transition-all" 
                                        title="Detail">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 flex justify-between items-center">
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Menampilkan <span class="font-semibold" x-text="transactions.length"></span> dari <span class="font-semibold" x-text="totalTransactions"></span> transaksi
            </p>
            <div class="flex gap-2">
                <button @click="prevPage()" :disabled="currentPage === 1"
                    class="px-4 py-2 rounded-lg border-2 border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-300 font-semibold hover:bg-gray-50 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed transition-all">
                    ← Sebelumnya
                </button>
                <button @click="nextPage()" :disabled="currentPage === lastPage"
                    class="px-4 py-2 rounded-lg border-2 border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-300 font-semibold hover:bg-gray-50 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed transition-all">
                    Berikutnya →
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function posHistory() {
    return {
        transactions: [],
        isLoading: false,
        startDate: '',
        endDate: '',
        selectedCashier: '',
        selectedMethod: '',
        cashiers: [],
        currentPage: 1,
        lastPage: 1,
        totalTransactions: 0,
        stats: {
            total: 0,
            revenue: 0,
            cash: 0,
            average: 0
        },

        async init() {
            await this.fetchCashiers();
            await this.fetchTransactions();
        },

        async fetchCashiers() {
            try {
                const token = localStorage.getItem('saga_token');
                const response = await fetch('/api/users?role=cashier', {
                    headers: { 'Authorization': 'Bearer ' + token }
                });
                const result = await response.json();
                if(result.success) {
                    this.cashiers = result.data.data || result.data;
                }
            } catch(e) {
                console.error('Error fetching cashiers:', e);
            }
        },

        async fetchTransactions() {
            this.isLoading = true;
            try {
                const token = localStorage.getItem('saga_token');
                let url = `/api/transactions?page=${this.currentPage}`;
                
                if(this.startDate) url += '&start_date=' + this.startDate;
                if(this.endDate) url += '&end_date=' + this.endDate;
                if(this.selectedCashier) url += '&cashier_id=' + this.selectedCashier;
                if(this.selectedMethod) url += '&payment_method=' + this.selectedMethod;

                const response = await fetch(url, { headers: { 'Authorization': 'Bearer ' + token } });
                const result = await response.json();
                if(result.success) {
                    this.transactions = result.data.data || result.data;
                    this.currentPage = result.data.current_page || 1;
                    this.lastPage = result.data.last_page || 1;
                    this.totalTransactions = result.data.total || 0;
                    this.calculateStats();
                }
            } catch(e) {
                console.error('Error fetching transactions:', e);
                Swal.fire('Error', 'Gagal memuat data transaksi', 'error');
            } finally {
                this.isLoading = false;
            }
        },

        applyFilters() {
            this.currentPage = 1;
            this.fetchTransactions();
        },

        nextPage() {
            if(this.currentPage < this.lastPage) {
                this.currentPage++;
                this.fetchTransactions();
            }
        },

        prevPage() {
            if(this.currentPage > 1) {
                this.currentPage--;
                this.fetchTransactions();
            }
        },

        calculateStats() {
            const today = new Date().toISOString().split('T')[0];
            const todayTransactions = this.transactions.filter(t => t.created_at.startsWith(today));
            
            this.stats.total = todayTransactions.length;
            this.stats.revenue = todayTransactions.reduce((sum, t) => sum + parseFloat(t.grand_total || 0), 0);
            this.stats.cash = todayTransactions.filter(t => t.payment_method === 'Cash').length;
            this.stats.average = this.stats.total > 0 ? this.stats.revenue / this.stats.total : 0;
        },

        printReceipt(id) {
            window.open(`/api/transactions/${id}/receipt`, '_blank');
        },

        viewDetail(trx) {
            Swal.fire({
                title: '📄 Detail Transaksi',
                html: `
                    <div class="text-left">
                        <p class="mb-2"><strong>No. Transaksi:</strong> <span class="text-brand-600">${trx.invoice_number}</span></p>
                        <p class="mb-2"><strong>Waktu:</strong> ${this.formatDate(trx.created_at)} ${this.formatTime(trx.created_at)}</p>
                        <p class="mb-2"><strong>Pelanggan:</strong> ${trx.customer?.name || 'Umum'}</p>
                        <p class="mb-2"><strong>Total:</strong> <strong class="text-brand-600">${this.formatCurrency(trx.grand_total)}</strong></p>
                        <p class="mb-2"><strong>Metode:</strong> ${trx.payment_method}</p>
                        <p><strong>Kasir:</strong> ${trx.user?.name || '-'}</p>
                    </div>
                `,
                icon: 'info',
                showCancelButton: true,
                confirmButtonText: '🖨️ Cetak Ulang',
                cancelButtonText: '❌ Tutup',
                confirmButtonColor: '#4f46e5',
                cancelButtonColor: '#6b7280'
            }).then((result) => {
                if(result.isConfirmed) {
                    this.printReceipt(trx.id);
                }
            });
        },

        exportReport() {
            Swal.fire({
                title: '📊 Export Laporan',
                text: 'Pilih format export:',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: '📄 PDF',
                cancelButtonText: '📊 Excel',
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#10b981'
            }).then((result) => {
                if(result.isConfirmed) {
                    // Export PDF
                    window.open(`/api/transactions/export/pdf?start=${this.startDate}&end=${this.endDate}`, '_blank');
                } else {
                    // Export Excel
                    window.open(`/api/transactions/export/excel?start=${this.startDate}&end=${this.endDate}`, '_blank');
                }
            });
        },

        formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
        },

        formatTime(dateString) {
            const date = new Date(dateString);
            return date.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
        },

        formatCurrency(amount) {
            return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(amount);
        }
    }
}
</script>
@endpush
@endsection
