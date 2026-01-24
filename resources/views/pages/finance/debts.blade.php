@extends('layouts.app')

@section('title', 'Supplier Debts | SAGA TOKO APP')

@section('content')
    <div x-data="{
        page: 'supplierDebts',

        debts: [
            { id: 1, supplier: 'PT. Distribusi Maju', invoice: 'INV-2024-001', date: '2024-01-10', due_date: '2024-02-10', amount: 5000000, paid: 2500000, status: 'partial' },
            { id: 2, supplier: 'UD. Sumber Rejeki', invoice: 'INV-2024-005', date: '2024-01-15', due_date: '2024-02-15', amount: 12500000, paid: 0, status: 'unpaid' },
            { id: 3, supplier: 'CV. Berkah Abadi', invoice: 'INV-2023-156', date: '2023-12-20', due_date: '2024-01-20', amount: 3000000, paid: 3000000, status: 'paid' }
        ],

        filters: {
            status: 'all',
            search: ''
        },

        get filteredDebts() {
            return this.debts.filter(d => {
                const matchStatus = this.filters.status === 'all' || d.status === this.filters.status;
                const matchSearch = d.supplier.toLowerCase().includes(this.filters.search.toLowerCase()) || 
                                  d.invoice.toLowerCase().includes(this.filters.search.toLowerCase());
                return matchStatus && matchSearch;
            });
        },

        get totalDebt() {
            return this.debts.filter(d => d.status !== 'paid').reduce((acc, curr) => acc + (curr.amount - curr.paid), 0);
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

        payDebt(id) {
            Swal.fire({
                title: 'Bayar Hutang?',
                text: 'Fitur pembayaran akan segera hadir!',
                icon: 'info',
                confirmButtonText: 'Oke'
            });
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
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <div class="bg-gradient-to-br from-red-500 to-red-600 rounded-xl p-5 text-white shadow-lg shadow-red-500/30">
                <p class="text-white/80 text-sm font-medium mb-1">Total Hutang Belum Bayar</p>
                <h3 class="text-3xl font-bold" x-text="formatCurrency(totalDebt)"></h3>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl p-5 border border-gray-200 dark:border-gray-700 shadow-sm">
                <p class="text-gray-500 dark:text-gray-400 text-sm font-medium mb-1">Jatuh Tempo Bulan Ini</p>
                <h3 class="text-2xl font-bold text-gray-800 dark:text-white" x-text="formatCurrency(12500000)"></h3>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl p-5 border border-gray-200 dark:border-gray-700 shadow-sm">
                <p class="text-gray-500 dark:text-gray-400 text-sm font-medium mb-1">Total Lunas (Bulan Ini)</p>
                <h3 class="text-2xl font-bold text-green-600 dark:text-green-400" x-text="formatCurrency(3000000)"></h3>
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

    </div>
@endsection