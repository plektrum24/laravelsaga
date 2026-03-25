<?php $__env->startSection('title', 'Reports Center | SAGA TOKO APP'); ?>

<?php $__env->startSection('content'); ?>
    <div x-data="{
                            activeTab: new URLSearchParams(window.location.search).get('tab') || 'sales',
                            dateFrom: new Date(new Date().setDate(new Date().getDate() - 30)).toISOString().split('T')[0],
                            dateTo: new Date().toISOString().split('T')[0],
                            isLoading: false,

                            // Real Data from API
                            salesData: { chartData: [], totalRevenue: 0, totalProfit: 0, totalTransactions: 0 },
                            topProducts: [],
                            categories: [],
                            salesChart: null,

                            // Sales Force Data
                            salesForceData: {
                                summary: { total_salesmen: 0, total_orders: 0, total_revenue: 0, avg_order_value: 0, avg_conversion_rate: 0 },
                                salesmen: [],
                                top_performer: null
                            },

                            formatCurrency(amount) {
                                return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(amount);
                            },

                            async init() {
                                await this.fetchAllData();
                                
                                this.$watch('activeTab', async (value) => {
                                    const url = new URL(window.location);
                                    url.searchParams.set('tab', value);
                                    window.history.pushState({}, '', url);
                                    
                                    if(value === 'sales') {
                                        this.$nextTick(() => this.renderSalesChart());
                                    }
                                });
                            },

                            async fetchAllData() {
                                this.isLoading = true;
                                try {
                                    const token = localStorage.getItem('saga_token');
                                    const headers = { 'Authorization': 'Bearer ' + token };

                                    // Fetch Sales Overview
                                    const resSales = await fetch(`/api/reports/sales-overview?days=30`, { headers });
                                    const dataSales = await resSales.json();
                                    if(dataSales.success) this.salesData = dataSales.data;

                                    // Fetch Top Products
                                    const resTop = await fetch(`/api/reports/top-products?limit=5`, { headers });
                                    const dataTop = await resTop.json();
                                    if(dataTop.success) this.topProducts = dataTop.data;

                                    // Fetch Category Performance
                                    const resCat = await fetch(`/api/reports/category-performance`, { headers });
                                    const dataCat = await resCat.json();
                                    if(dataCat.success) this.categories = dataCat.data;

                                    // Fetch Sales Force Performance
                                    const resSalesForce = await fetch(`/api/reports/sales-force/performance?days=30`, { headers });
                                    const dataSalesForce = await resSalesForce.json();
                                    if(dataSalesForce.success) this.salesForceData = dataSalesForce.data;

                                    this.$nextTick(() => {
                                        if(this.activeTab === 'sales') this.renderSalesChart();
                                    });
                                } catch(e) {
                                    console.error('Error fetching report data:', e);
                                } finally {
                                    this.isLoading = false;
                                }
                            },

                            renderSalesChart() {
                                const chartEl = document.querySelector('#sales-chart-container');
                                if(!chartEl || this.salesData.chartData.length === 0) return;

                                if(this.salesChart) this.salesChart.destroy();

                                const options = {
                                    series: [{
                                        name: 'Revenue',
                                        data: this.salesData.chartData.map(d => parseFloat(d.total_revenue))
                                    }],
                                    chart: {
                                        type: 'area',
                                        height: 300,
                                        toolbar: { show: false },
                                        zoom: { enabled: false }
                                    },
                                    colors: ['#4F46E5'],
                                    dataLabels: { enabled: false },
                                    stroke: { curve: 'smooth', width: 3 },
                                    xaxis: {
                                        categories: this.salesData.chartData.map(d => d.day),
                                        labels: { rotate: -45, style: { fontSize: '10px' } }
                                    },
                                    yaxis: {
                                        labels: {
                                            formatter: (val) => 'Rp ' + (val/1000000).toFixed(1) + 'M'
                                        }
                                    },
                                    fill: {
                                        type: 'gradient',
                                        gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.1 }
                                    }
                                };

                                this.salesChart = new ApexCharts(chartEl, options);
                                this.salesChart.render();
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

            <!-- Cash Register Report -->
            <button @click="window.location.href = '<?php echo e(route('reports.cash-register')); ?>'"
                class="relative p-4 rounded-2xl border border-gray-200 hover:border-teal-300 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800 transition-all text-left group">
                <div class="flex items-center gap-3 mb-2">
                    <div
                        class="w-10 h-10 rounded-lg bg-teal-100 text-teal-600 flex items-center justify-center dark:bg-teal-900/30 dark:text-teal-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z">
                            </path>
                        </svg>
                    </div>
                    <span class="font-bold text-gray-700 dark:text-gray-200">Laporan Kas Harian</span>
                </div>
                <p class="text-xs text-gray-500">Fisik Uang & Shift</p>
            </button>

            <!-- Sales Force Report -->
            <button @click="activeTab = 'salesforce'"
                :class="activeTab === 'salesforce' ? 'border-purple-500 ring-1 ring-purple-500 bg-purple-50 dark:bg-purple-900/20' : 'border-gray-200 hover:border-purple-300 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800'"
                class="relative p-4 rounded-2xl border transition-all text-left group">
                <div class="flex items-center gap-3 mb-2">
                    <div
                        class="w-10 h-10 rounded-lg bg-purple-100 text-purple-600 flex items-center justify-center dark:bg-purple-900/30 dark:text-purple-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                            </path>
                        </svg>
                    </div>
                    <span class="font-bold text-gray-700 dark:text-gray-200">Sales Force</span>
                </div>
                <p class="text-xs text-gray-500">Performance Salesman</p>
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
                        <h3 class="text-3xl font-bold" x-text="formatCurrency(salesData.totalRevenue)">Rp 0</h3>
                        <div class="mt-4 flex items-center gap-2 text-xs bg-white/20 w-fit px-2 py-1 rounded-lg">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                            </svg>
                            <span x-text="'Periode ' + (dateFrom || '-') + ' s/d ' + (dateTo || '-')"></span>
                        </div>
                    </div>
                    <div
                        class="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-gray-100 dark:border-gray-700 shadow-sm">
                        <p class="text-gray-500 dark:text-gray-400 text-sm font-medium mb-1">Total Transaksi</p>
                        <h3 class="text-3xl font-bold text-gray-800 dark:text-white" x-text="salesData.totalTransactions">0</h3>
                        <p class="text-xs text-brand-500 mt-2 font-medium">Transaksi Selesai</p>
                    </div>
                    <div
                        class="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-gray-100 dark:border-gray-700 shadow-sm">
                        <p class="text-gray-500 dark:text-gray-400 text-sm font-medium mb-1">Rata-rata Keranjang</p>
                        <h3 class="text-3xl font-bold text-gray-800 dark:text-white"
                            x-text="salesData.totalTransactions > 0 ? formatCurrency(salesData.totalRevenue / salesData.totalTransactions) : 'Rp 0'">Rp 0</h3>
                        <p class="text-xs text-gray-400 mt-2">Per transaksi</p>
                    </div>
                </div>

                <!-- Sales Chart Placeholder -->
                <div
                    class="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-gray-100 dark:border-gray-700 shadow-sm">
                    <h3 class="font-bold text-gray-800 dark:text-white mb-6">Grafik Penjualan</h3>
                    <div id="sales-chart-container"
                        class="h-64 flex items-center justify-center bg-gray-50 dark:bg-gray-700/30 rounded-xl border border-dashed border-gray-200 dark:border-gray-600">
                        <template x-if="salesData.chartData.length === 0">
                            <span class="text-gray-400 text-sm">Tidak ada data untuk periode ini</span>
                        </template>
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
                            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase font-semibold">Terdapat</p>
                            <p class="text-xl font-bold text-yellow-600" x-text="categories.length + ' Kategori'"></p>
                        </div>
                    </div>
                </div>

                <div
                    class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm p-6">
                    <h3 class="font-bold text-gray-800 dark:text-white mb-4">Top 5 Pergerakan Stok (Fast Moving)</h3>
                    <div class="space-y-4">
                        <template x-for="(product, index) in topProducts" :key="product.id">
                            <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700/30 rounded-xl">
                                <div class="flex items-center gap-3">
                                    <span
                                        class="w-8 h-8 flex items-center justify-center bg-brand-100 text-brand-600 rounded-lg font-bold text-sm" x-text="index + 1"></span>
                                    <div>
                                        <p class="font-medium text-gray-800 dark:text-white" x-text="product.product?.name"></p>
                                        <p class="text-xs text-gray-500" x-text="formatCurrency(product.total_revenue)"></p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="font-bold text-gray-800 dark:text-white" x-text="product.total_qty + ' Unit'"></p>
                                    <p class="text-xs text-green-500">Terjual</p>
                                </div>
                            </div>
                        </template>
                        <template x-if="topProducts.length === 0">
                            <p class="text-center text-gray-500 py-4">Belum ada data produk terlaris.</p>
                        </template>
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
                                    x-text="formatCurrency(salesData.totalRevenue)">Rp 0</span>
                            </div>
                            <div
                                class="flex justify-between items-center pb-4 border-b border-gray-100 dark:border-gray-700">
                                <span class="text-gray-600 dark:text-gray-400">Total Modal (COGS)</span>
                                <span class="font-bold text-red-500" x-text="'- ' + formatCurrency(salesData.totalRevenue - salesData.totalProfit)"></span>
                            </div>
                            <div class="flex justify-between items-center pt-2">
                                <span class="text-lg font-bold text-gray-800 dark:text-white">Estimasi Laba Kotor</span>
                                <span class="text-xl font-bold text-green-600"
                                    x-text="formatCurrency(salesData.totalProfit)">Rp 0</span>
                            </div>
                        </div>
                    </div>

                        <div
                            class="bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl p-6 text-white flex flex-col justify-center items-center text-center">
                            <p class="text-indigo-100 mb-2">Margin Keuntungan</p>
                            <h3 class="text-5xl font-bold mb-4" x-text="salesData.totalRevenue > 0 ? ((salesData.totalProfit / salesData.totalRevenue) * 100).toFixed(1) + '%' : '0%'"></h3>
                            <p class="text-sm opacity-80" x-text="salesData.totalProfit > 0 ? 'Margin keuntungan kotor berdasarkan data transaksi.' : 'Belum ada data profit.'"></p>
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
                                <template x-for="cat in categories" :key="cat.name">
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                        <td class="px-6 py-4 font-medium text-gray-800 dark:text-white" x-text="cat.name">
                                        </td>
                                        <td class="px-6 py-4 text-right text-gray-600 dark:text-gray-300" x-text="'-'"></td>
                                        <td class="px-6 py-4 text-right text-gray-600 dark:text-gray-300"
                                            x-text="formatCurrency(cat.total_revenue)"></td>
                                        <td class="px-6 py-4 text-right text-gray-600 dark:text-gray-300" x-text="'-'"></td>
                                        <td class="px-6 py-4 text-right font-bold text-green-600" x-text="formatCurrency(cat.total_revenue)"></td>
                                    </tr>
                                </template>
                                <template x-if="categories.length === 0">
                                    <tr>
                                        <td colspan="5" class="px-6 py-8 text-center text-gray-500">Tidak ada data performa kategori.</td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- SALES FORCE PERFORMANCE TAB -->
            <div x-show="activeTab === 'salesforce'" x-cloak class="space-y-6"
                x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4"
                x-transition:enter-end="opacity-100 translate-y-0">
                
                <!-- Sales Force Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-2xl p-6 text-white shadow-lg shadow-purple-500/20">
                        <p class="text-purple-100 text-sm font-medium mb-1">Total Salesman</p>
                        <h3 class="text-3xl font-bold" x-text="salesForceData.summary.total_salesmen">0</h3>
                        <p class="text-xs text-purple-100 mt-2">Active sales team</p>
                    </div>

                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-gray-100 dark:border-gray-700 shadow-sm">
                        <p class="text-gray-500 dark:text-gray-400 text-sm font-medium mb-1">Total Orders (Sales Force)</p>
                        <h3 class="text-3xl font-bold text-gray-800 dark:text-white" x-text="salesForceData.summary.total_orders">0</h3>
                        <p class="text-xs text-brand-500 mt-2 font-medium">From sales team</p>
                    </div>

                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-gray-100 dark:border-gray-700 shadow-sm">
                        <p class="text-gray-500 dark:text-gray-400 text-sm font-medium mb-1">Avg Order Value</p>
                        <h3 class="text-3xl font-bold text-gray-800 dark:text-white" x-text="formatCurrency(salesForceData.summary.avg_order_value)">Rp 0</h3>
                        <p class="text-xs text-gray-500 mt-2">Per transaction</p>
                    </div>

                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-gray-100 dark:border-gray-700 shadow-sm">
                        <p class="text-gray-500 dark:text-gray-400 text-sm font-medium mb-1">Conversion Rate</p>
                        <h3 class="text-3xl font-bold text-gray-800 dark:text-white" x-text="salesForceData.summary.avg_conversion_rate.toFixed(1) + '%'">0%</h3>
                        <p class="text-xs text-green-500 mt-2 font-medium">Success rate</p>
                    </div>
                </div>

                <!-- Salesman Performance Table -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
                    <div class="p-6 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
                        <h3 class="font-bold text-gray-800 dark:text-white">Performance per Salesman</h3>
                        <button @click="exportSalesForceReport()" class="px-4 py-2 bg-brand-600 text-white rounded-lg text-sm font-medium hover:bg-brand-700 transition flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Export
                        </button>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="bg-gray-50 dark:bg-gray-700/50 text-gray-500 uppercase text-xs">
                                <tr>
                                    <th class="px-6 py-3">Salesman</th>
                                    <th class="px-6 py-3 text-center">Total Orders</th>
                                    <th class="px-6 py-3 text-right">Total Sales</th>
                                    <th class="px-6 py-3 text-right">Avg Order</th>
                                    <th class="px-6 py-3 text-center">Customers</th>
                                    <th class="px-6 py-3 text-center">Last Sale</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                <template x-for="salesman in salesForceData.salesmen" :key="salesman.id">
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                        <td class="px-6 py-4 font-medium text-gray-800 dark:text-white">
                                            <div class="flex items-center gap-3">
                                                <div class="w-8 h-8 rounded-full bg-purple-100 text-purple-600 flex items-center justify-center text-sm font-bold" x-text="getInitials(salesman.name)">
                                                </div>
                                                <span x-text="salesman.name"></span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-center text-gray-600 dark:text-gray-300" x-text="salesman.total_orders">0</td>
                                        <td class="px-6 py-4 text-right text-gray-600 dark:text-gray-300" x-text="formatCurrency(salesman.total_sales)">Rp 0</td>
                                        <td class="px-6 py-4 text-right text-gray-600 dark:text-gray-300" x-text="formatCurrency(salesman.avg_order_value)">Rp 0</td>
                                        <td class="px-6 py-4 text-center text-gray-600 dark:text-gray-300" x-text="salesman.unique_customers">0</td>
                                        <td class="px-6 py-4 text-center">
                                            <span class="text-xs text-gray-500" x-text="salesman.last_sale_date ? formatDate(salesman.last_sale_date) : '-'"></span>
                                        </td>
                                    </tr>
                                </template>
                                <template x-if="salesForceData.salesmen.length === 0">
                                    <tr>
                                        <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                            Belum ada data salesman
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Top Performers -->
                <template x-if="salesForceData.top_performer">
                    <div class="bg-gradient-to-br from-amber-50 to-orange-50 dark:from-amber-900/20 dark:to-orange-900/20 rounded-2xl border border-amber-200 dark:border-amber-800 p-6">
                        <h3 class="font-bold text-gray-800 dark:text-white mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-amber-500" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                            </svg>
                            Top Performer This Month
                        </h3>
                        <div class="flex items-center gap-4">
                            <div class="w-16 h-16 rounded-full bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center text-white text-xl font-bold shadow-lg" x-text="getInitials(salesForceData.top_performer.name)">
                            </div>
                            <div>
                                <p class="font-bold text-gray-800 dark:text-white text-lg" x-text="salesForceData.top_performer.name">John Doe</p>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Total Sales: <span class="font-bold text-brand-600" x-text="formatCurrency(salesForceData.top_performer.total_sales)">Rp 0</span></p>
                                <p class="text-xs text-gray-500 mt-1">🏆 Highest performer based on total sales</p>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
function getInitials(name) {
    return name.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2);
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
}

function exportSalesForceReport() {
    const token = localStorage.getItem('saga_token');
    window.open(`/api/reports/sales-force/export?days=30&format=csv`, '_blank');
}
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\Project App\laravelsaga\resources\views/pages/reports/index.blade.php ENDPATH**/ ?>