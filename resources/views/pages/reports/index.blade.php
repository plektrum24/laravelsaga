@extends('layouts.app')

@section('title', 'Reports Center | SAGA TOKO APP')

@section('content')
    <div x-data="{
                    activeTab: new URLSearchParams(window.location.search).get('tab') || 'sales',
                    dateFrom: new Date(new Date().setDate(new Date().getDate() - 30)).toISOString().split('T')[0],
                    dateTo: new Date().toISOString().split('T')[0],
                    isLoading: false,

                    // Mock Data for UI Visualization
                    stats: {
                        sales: { total: 15450000, count: 124, profit: 4500000 },
                        stock: { total_value: 85400000, low_stock: 5, out_stock: 2 },
                        purchase: { total: 8500000, count: 12 },
                        profit_details: [
                            { name: 'Kopi Kapal Api Mix', qty: 1240, revenue: 3100000, cost: 2480000, profit: 620000 },
                            { name: 'Indomie Goreng', qty: 950, revenue: 2850000, cost: 2375000, profit: 475000 },
                            { name: 'Aqua 600ml', qty: 500, revenue: 1500000, cost: 1000000, profit: 500000 },
                            { name: 'Roti Tawar Sari Roti', qty: 200, revenue: 3000000, cost: 2400000, profit: 600000 },
                            { name: 'Teh Pucuk Harum', qty: 350, revenue: 1225000, cost: 875000, profit: 350000 }
                        ]
                    },

                    formatCurrency(amount) {
                        return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(amount);
                    },

                    init() {
                        // Watch for tab changes to update URL without reload
                        this.$watch('activeTab', value => {
                            const url = new URL(window.location);
                            url.searchParams.set('tab', value);
                            window.history.pushState({}, '', url);
                        });
                    }
                }">

        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white flex items-center gap-2">
                <span class="p-2 bg-indigo-100 rounded-lg text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                        </path>
                    </svg>
                </span>
                Laporan & Analitik
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 ml-12">Pusat data penjualan, stok, dan keuangan.</p>
        </div>

        <!-- Navigation Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
            <button @click="activeTab = 'sales'"
                :class="activeTab === 'sales' ? 'border-brand-500 ring-1 ring-brand-500 bg-brand-50 dark:bg-brand-900/20' : 'border-gray-200 hover:border-brand-300 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800'"
                class="relative p-4 rounded-2xl border transition-all text-left group">
                <div class="flex items-center gap-3 mb-2">
                    <div
                        class="w-10 h-10 rounded-lg bg-brand-100 text-brand-600 flex items-center justify-center dark:bg-brand-900/30 dark:text-brand-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                            </path>
                        </svg>
                    </div>
                    <span class="font-bold text-gray-700 dark:text-gray-200">Penjualan</span>
                </div>
                <p class="text-xs text-gray-500">Omset & Transaksi</p>
            </button>

            <button @click="activeTab = 'stock'"
                :class="activeTab === 'stock' ? 'border-yellow-500 ring-1 ring-yellow-500 bg-yellow-50 dark:bg-yellow-900/20' : 'border-gray-200 hover:border-yellow-300 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800'"
                class="relative p-4 rounded-2xl border transition-all text-left group">
                <div class="flex items-center gap-3 mb-2">
                    <div
                        class="w-10 h-10 rounded-lg bg-yellow-100 text-yellow-600 flex items-center justify-center dark:bg-yellow-900/30 dark:text-yellow-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                    </div>
                    <span class="font-bold text-gray-700 dark:text-gray-200">Stok Barang</span>
                </div>
                <p class="text-xs text-gray-500">Aset & Mutasi</p>
            </button>

            <button @click="activeTab = 'purchase'"
                :class="activeTab === 'purchase' ? 'border-blue-500 ring-1 ring-blue-500 bg-blue-50 dark:bg-blue-900/20' : 'border-gray-200 hover:border-blue-300 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800'"
                class="relative p-4 rounded-2xl border transition-all text-left group">
                <div class="flex items-center gap-3 mb-2">
                    <div
                        class="w-10 h-10 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center dark:bg-blue-900/30 dark:text-blue-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z">
                            </path>
                        </svg>
                    </div>
                    <span class="font-bold text-gray-700 dark:text-gray-200">Pembelian</span>
                </div>
                <p class="text-xs text-gray-500">Belanja Supplier</p>
            </button>

            <button @click="activeTab = 'profit'"
                :class="activeTab === 'profit' ? 'border-green-500 ring-1 ring-green-500 bg-green-50 dark:bg-green-900/20' : 'border-gray-200 hover:border-green-300 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800'"
                class="relative p-4 rounded-2xl border transition-all text-left group">
                <div class="flex items-center gap-3 mb-2">
                    <div
                        class="w-10 h-10 rounded-lg bg-green-100 text-green-600 flex items-center justify-center dark:bg-green-900/30 dark:text-green-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                            </path>
                        </svg>
                    </div>
                    <span class="font-bold text-gray-700 dark:text-gray-200">Laba Rugi</span>
                </div>
                <p class="text-xs text-gray-500">Analisa Profit</p>
            </button>
        </div>

        <!-- Global Filters (Date Range) -->
        <div
            class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-4 mb-6 shadow-sm flex flex-col md:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                <span class="font-medium">Periode Laporan:</span>
            </div>
            <div class="flex items-center gap-3 w-full md:w-auto">
                <input type="date" x-model="dateFrom"
                    class="px-4 py-2 border border-gray-200 rounded-lg text-sm focus:ring-brand-500 focus:border-brand-500 dark:bg-gray-900 dark:border-gray-600 dark:text-white dark:[color-scheme:dark]">
                <span class="text-gray-400">-</span>
                <input type="date" x-model="dateTo"
                    class="px-4 py-2 border border-gray-200 rounded-lg text-sm focus:ring-brand-500 focus:border-brand-500 dark:bg-gray-900 dark:border-gray-600 dark:text-white dark:[color-scheme:dark]">
                <button
                    class="px-4 py-2 bg-brand-600 text-white rounded-lg text-sm font-medium hover:bg-brand-700 transition">Filter</button>
            </div>
        </div>

        <!-- Content Area -->
        <div class="min-h-[400px]">

            <!-- SALES TAB -->
            <div x-show="activeTab === 'sales'" x-cloak class="space-y-6"
                x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4"
                x-transition:enter-end="opacity-100 translate-y-0">
                <!-- Sales Stats -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div
                        class="bg-gradient-to-br from-brand-500 to-brand-600 rounded-2xl p-6 text-white shadow-lg shadow-brand-500/20">
                        <p class="text-brand-100 text-sm font-medium mb-1">Total Penjualan</p>
                        <h3 class="text-3xl font-bold" x-text="formatCurrency(stats.sales.total)">Rp 0</h3>
                        <div class="mt-4 flex items-center gap-2 text-xs bg-white/20 w-fit px-2 py-1 rounded-lg">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                            </svg>
                            <span>+12.5% dari bulan lalu</span>
                        </div>
                    </div>
                    <div
                        class="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-gray-100 dark:border-gray-700 shadow-sm">
                        <p class="text-gray-500 dark:text-gray-400 text-sm font-medium mb-1">Total Transaksi</p>
                        <h3 class="text-3xl font-bold text-gray-800 dark:text-white" x-text="stats.sales.count">0</h3>
                        <p class="text-xs text-green-500 mt-2 font-medium">+5 transaksi hari ini</p>
                    </div>
                    <div
                        class="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-gray-100 dark:border-gray-700 shadow-sm">
                        <p class="text-gray-500 dark:text-gray-400 text-sm font-medium mb-1">Rata-rata Keranjang</p>
                        <h3 class="text-3xl font-bold text-gray-800 dark:text-white"
                            x-text="formatCurrency(stats.sales.total / stats.sales.count)">Rp 0</h3>
                        <p class="text-xs text-gray-400 mt-2">Per transaksi</p>
                    </div>
                </div>

                <!-- Sales Chart Placeholder -->
                <div
                    class="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-gray-100 dark:border-gray-700 shadow-sm">
                    <h3 class="font-bold text-gray-800 dark:text-white mb-6">Grafik Penjualan</h3>
                    <div
                        class="h-64 flex items-center justify-center bg-gray-50 dark:bg-gray-700/30 rounded-xl border border-dashed border-gray-200 dark:border-gray-600">
                        <span class="text-gray-400 text-sm">Chart Visualization Area (ApexCharts)</span>
                    </div>
                </div>

                <!-- Recent Transactions Table -->
                <div
                    class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
                    <div class="p-6 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
                        <h3 class="font-bold text-gray-800 dark:text-white">Riwayat Transaksi</h3>
                        <button class="text-sm text-brand-600 hover:text-brand-700 font-medium">Lihat Semua</button>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="bg-gray-50 dark:bg-gray-700/50 text-gray-500 uppercase text-xs">
                                <tr>
                                    <th class="px-6 py-3">No. Invoice</th>
                                    <th class="px-6 py-3">Tanggal</th>
                                    <th class="px-6 py-3">Customer</th>
                                    <th class="px-6 py-3 text-right">Total</th>
                                    <th class="px-6 py-3 text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                    <td class="px-6 py-4 font-medium text-gray-800 dark:text-white">INV-2024001</td>
                                    <td class="px-6 py-4 text-gray-500">24 Jan 2024, 14:30</td>
                                    <td class="px-6 py-4 text-gray-600 dark:text-gray-300">Umum</td>
                                    <td class="px-6 py-4 text-right font-medium">Rp 150.000</td>
                                    <td class="px-6 py-4 text-center"><span
                                            class="px-2 py-1 bg-green-100 text-green-600 rounded-full text-xs font-bold dark:bg-green-900/30 dark:text-green-400">Paid</span>
                                    </td>
                                </tr>
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                    <td class="px-6 py-4 font-medium text-gray-800 dark:text-white">INV-2024002</td>
                                    <td class="px-6 py-4 text-gray-500">24 Jan 2024, 13:15</td>
                                    <td class="px-6 py-4 text-gray-600 dark:text-gray-300">Budi Santoso</td>
                                    <td class="px-6 py-4 text-right font-medium">Rp 450.000</td>
                                    <td class="px-6 py-4 text-center"><span
                                            class="px-2 py-1 bg-green-100 text-green-600 rounded-full text-xs font-bold dark:bg-green-900/30 dark:text-green-400">Paid</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- STOCK TAB -->
            <div x-show="activeTab === 'stock'" x-cloak class="space-y-6"
                x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4"
                x-transition:enter-end="opacity-100 translate-y-0">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div
                        class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm flex items-center gap-4">
                        <div
                            class="w-12 h-12 bg-green-100 text-green-600 rounded-xl flex items-center justify-center dark:bg-green-900/30 dark:text-green-400">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                </path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase font-semibold">Total Nilai Aset</p>
                            <p class="text-xl font-bold text-gray-800 dark:text-white"
                                x-text="formatCurrency(stats.stock.total_value)">Rp 0</p>
                        </div>
                    </div>
                    <div
                        class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm flex items-center gap-4">
                        <div
                            class="w-12 h-12 bg-red-100 text-red-600 rounded-xl flex items-center justify-center dark:bg-red-900/30 dark:text-red-400">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                                </path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase font-semibold">Stok Habis</p>
                            <p class="text-xl font-bold text-red-600" x-text="stats.stock.out_stock + ' Item'">0 Item</p>
                        </div>
                    </div>
                    <div
                        class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm flex items-center gap-4">
                        <div
                            class="w-12 h-12 bg-yellow-100 text-yellow-600 rounded-xl flex items-center justify-center dark:bg-yellow-900/30 dark:text-yellow-400">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase font-semibold">Stok Menipis</p>
                            <p class="text-xl font-bold text-yellow-600" x-text="stats.stock.low_stock + ' Item'">0 Item</p>
                        </div>
                    </div>
                </div>

                <div
                    class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm p-6">
                    <h3 class="font-bold text-gray-800 dark:text-white mb-4">Top 5 Pergerakan Stok (Fast Moving)</h3>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700/30 rounded-xl">
                            <div class="flex items-center gap-3">
                                <span
                                    class="w-8 h-8 flex items-center justify-center bg-brand-100 text-brand-600 rounded-lg font-bold text-sm">1</span>
                                <div>
                                    <p class="font-medium text-gray-800 dark:text-white">Kopi Kapal Api Mix</p>
                                    <p class="text-xs text-gray-500">Minuman</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="font-bold text-gray-800 dark:text-white">1,240 Pcs</p>
                                <p class="text-xs text-green-500">Terjual</p>
                            </div>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700/30 rounded-xl">
                            <div class="flex items-center gap-3">
                                <span
                                    class="w-8 h-8 flex items-center justify-center bg-brand-100 text-brand-600 rounded-lg font-bold text-sm">2</span>
                                <div>
                                    <p class="font-medium text-gray-800 dark:text-white">Indomie Goreng</p>
                                    <p class="text-xs text-gray-500">Makanan</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="font-bold text-gray-800 dark:text-white">950 Pcs</p>
                                <p class="text-xs text-green-500">Terjual</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- PURCHASE TAB -->
            <div x-show="activeTab === 'purchase'" x-cloak class="space-y-6"
                x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4"
                x-transition:enter-end="opacity-100 translate-y-0">
                <div
                    class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
                    <div class="p-6 border-b border-gray-100 dark:border-gray-700">
                        <h3 class="font-bold text-gray-800 dark:text-white">Riwayat Pembelian (Belanja Stok)</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="bg-gray-50 dark:bg-gray-700/50 text-gray-500 uppercase text-xs">
                                <tr>
                                    <th class="px-6 py-3">No. Faktur</th>
                                    <th class="px-6 py-3">Supplier</th>
                                    <th class="px-6 py-3">Tanggal</th>
                                    <th class="px-6 py-3 text-right">Total</th>
                                    <th class="px-6 py-3 text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                    <td class="px-6 py-4 font-medium text-gray-800 dark:text-white">PO-00123</td>
                                    <td class="px-6 py-4">PT. Sumber Makmur</td>
                                    <td class="px-6 py-4 text-gray-500">20 Jan 2024</td>
                                    <td class="px-6 py-4 text-right font-medium">Rp 5.200.000</td>
                                    <td class="px-6 py-4 text-center"><span
                                            class="px-2 py-1 bg-blue-100 text-blue-600 rounded-full text-xs font-bold dark:bg-blue-900/30 dark:text-blue-400">Received</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- PROFIT & LOSS TAB -->
            <div x-show="activeTab === 'profit'" x-cloak class="space-y-6"
                x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4"
                x-transition:enter-end="opacity-100 translate-y-0">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div
                        class="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-gray-100 dark:border-gray-700 shadow-sm">
                        <h3 class="font-bold text-gray-800 dark:text-white mb-6">Ringkasan Laba Rugi</h3>
                        <div class="space-y-4">
                            <div
                                class="flex justify-between items-center pb-4 border-b border-gray-100 dark:border-gray-700">
                                <span class="text-gray-600 dark:text-gray-400">Total Penjualan (Revenue)</span>
                                <span class="font-bold text-gray-800 dark:text-white"
                                    x-text="formatCurrency(stats.sales.total)">Rp 0</span>
                            </div>
                            <div
                                class="flex justify-between items-center pb-4 border-b border-gray-100 dark:border-gray-700">
                                <span class="text-gray-600 dark:text-gray-400">Harga Pokok Penjualan (HPP)</span>
                                <span class="font-bold text-red-500">- Rp 10.950.000</span>
                            </div>
                            <div
                                class="flex justify-between items-center pb-4 border-b border-gray-100 dark:border-gray-700">
                                <span class="text-gray-600 dark:text-gray-400">Biaya Operasional</span>
                                <span class="font-bold text-red-500">- Rp 500.000</span>
                            </div>
                            <div class="flex justify-between items-center pt-2">
                                <span class="text-lg font-bold text-gray-800 dark:text-white">Laba Bersih</span>
                                <span class="text-xl font-bold text-green-600"
                                    x-text="formatCurrency(stats.sales.profit)">Rp 0</span>
                            </div>
                        </div>
                    </div>

                    <div
                        class="bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl p-6 text-white flex flex-col justify-center items-center text-center">
                        <p class="text-indigo-100 mb-2">Margin Keuntungan</p>
                        <h3 class="text-5xl font-bold mb-4">29.1%</h3>
                        <p class="text-sm opacity-80">Margin keuntungan sehat. Pertahankan efisiensi HPP untuk meningkatkan
                            margin.</p>
                    </div>
                </div>

                <!-- Detailed Profit Table -->
                <div
                    class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
                    <div class="p-6 border-b border-gray-100 dark:border-gray-700">
                        <h3 class="font-bold text-gray-800 dark:text-white">Detail Keuntungan per Item</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="bg-gray-50 dark:bg-gray-700/50 text-gray-500 uppercase text-xs">
                                <tr>
                                    <th class="px-6 py-3">Nama Produk</th>
                                    <th class="px-6 py-3 text-right">Terjual</th>
                                    <th class="px-6 py-3 text-right">Omset (Revenue)</th>
                                    <th class="px-6 py-3 text-right">HPP (Cost)</th>
                                    <th class="px-6 py-3 text-right">Laba Bersih</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                <template x-for="item in stats.profit_details" :key="item.name">
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                        <td class="px-6 py-4 font-medium text-gray-800 dark:text-white" x-text="item.name">
                                        </td>
                                        <td class="px-6 py-4 text-right text-gray-600 dark:text-gray-300"
                                            x-text="item.qty + ' pcs'"></td>
                                        <td class="px-6 py-4 text-right text-gray-600 dark:text-gray-300"
                                            x-text="formatCurrency(item.revenue)"></td>
                                        <td class="px-6 py-4 text-right text-gray-600 dark:text-gray-300"
                                            x-text="formatCurrency(item.cost)"></td>
                                        <td class="px-6 py-4 text-right font-bold text-green-600"
                                            x-text="formatCurrency(item.profit)"></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection