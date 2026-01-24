@extends('layouts.app')

@section('title', 'Receivables | SAGA TOKO APP')

@section('content')
    <div x-data="{
        page: 'receivables',

        receivables: [
            { id: 1, customer: 'Budi Santoso', invoice: 'INV-S-001', date: '2024-01-12', due_date: '2024-02-12', amount: 750000, paid: 0, status: 'unpaid' },
            { id: 2, customer: 'Siti Aminah', invoice: 'INV-S-008', date: '2024-01-18', due_date: '2024-02-18', amount: 1500000, paid: 500000, status: 'partial' },
            { id: 3, customer: 'Warung Pojok', invoice: 'INV-S-015', date: '2024-01-20', due_date: '2024-02-20', amount: 250000, paid: 250000, status: 'paid' }
        ],

        filters: {
            status: 'all',
            search: ''
        },

        get filteredReceivables() {
            return this.receivables.filter(r => {
                const matchStatus = this.filters.status === 'all' || r.status === this.filters.status;
                const matchSearch = r.customer.toLowerCase().includes(this.filters.search.toLowerCase()) || 
                                  r.invoice.toLowerCase().includes(this.filters.search.toLowerCase());
                return matchStatus && matchSearch;
            });
        },

        get totalReceivable() {
            return this.receivables.filter(r => r.status !== 'paid').reduce((acc, curr) => acc + (curr.amount - curr.paid), 0);
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
        },

        receivePayment(id) {
            Swal.fire({
                title: 'Terima Pembayaran?',
                text: 'Fitur penerimaan pembayaran akan segera hadir!',
                icon: 'info',
                confirmButtonText: 'Oke'
            });
        }
    }" x-init="">

        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white flex items-center gap-2">
                <span class="p-2 bg-green-50 rounded-lg text-green-600 dark:bg-green-900/30 dark:text-green-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z">
                        </path>
                    </svg>
                </span>
                Piutang Pelanggan (Receivables)
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 ml-12">Monitor piutang penjualan (Bon) pelanggan</p>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <div
                class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl p-5 text-white shadow-lg shadow-green-500/30">
                <p class="text-white/80 text-sm font-medium mb-1">Total Piutang Belum Tertagih</p>
                <h3 class="text-3xl font-bold" x-text="formatCurrency(totalReceivable)"></h3>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl p-5 border border-gray-200 dark:border-gray-700 shadow-sm">
                <p class="text-gray-500 dark:text-gray-400 text-sm font-medium mb-1">Jatuh Tempo Bulan Ini</p>
                <h3 class="text-2xl font-bold text-gray-800 dark:text-white" x-text="formatCurrency(750000)"></h3>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl p-5 border border-gray-200 dark:border-gray-700 shadow-sm">
                <p class="text-gray-500 dark:text-gray-400 text-sm font-medium mb-1">Total Lunas (Bulan Ini)</p>
                <h3 class="text-2xl font-bold text-blue-600 dark:text-blue-400" x-text="formatCurrency(250000)"></h3>
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
                <input type="text" x-model="filters.search" placeholder="Cari Pelanggan..."
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
                            <th class="px-6 py-4">Pelanggan</th>
                            <th class="px-6 py-4 text-center">Tgl Jatuh Tempo</th>
                            <th class="px-6 py-4 text-right">Total Tagihan</th>
                            <th class="px-6 py-4 text-right">Sisa Piutang</th>
                            <th class="px-6 py-4 text-center">Status</th>
                            <th class="px-6 py-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        <template x-for="bill in filteredReceivables" :key="bill.id">
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                <td class="px-6 py-4">
                                    <span class="font-mono font-medium text-gray-700 dark:text-gray-300"
                                        x-text="bill.invoice"></span>
                                    <div class="text-xs text-gray-400 mt-0.5" x-text="formatDate(bill.date)"></div>
                                </td>
                                <td class="px-6 py-4 font-medium text-gray-800 dark:text-white" x-text="bill.customer"></td>
                                <td class="px-6 py-4 text-center">
                                    <span
                                        :class="new Date(bill.due_date) < new Date() && bill.status !== 'paid' ? 'text-red-500 font-bold' : 'text-gray-600 dark:text-gray-400'"
                                        x-text="formatDate(bill.due_date)"></span>
                                </td>
                                <td class="px-6 py-4 text-right text-gray-600 dark:text-gray-400"
                                    x-text="formatCurrency(bill.amount)"></td>
                                <td class="px-6 py-4 text-right font-bold text-gray-800 dark:text-white"
                                    x-text="formatCurrency(bill.amount - bill.paid)"></td>
                                <td class="px-6 py-4 text-center">
                                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold"
                                        :class="getStatusBadge(bill.status).class"
                                        x-text="getStatusBadge(bill.status).label"></span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <button x-show="bill.status !== 'paid'" @click="receivePayment(bill.id)"
                                        class="text-green-600 hover:text-green-700 dark:text-green-400 font-medium text-xs hover:underline">
                                        Terima
                                    </button>
                                    <span x-show="bill.status === 'paid'" class="text-gray-400 text-xs">-</span>
                                </td>
                            </tr>
                        </template>
                        <tr x-show="filteredReceivables.length === 0">
                            <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                                Tidak ada data piutang ditemukan.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
@endsection