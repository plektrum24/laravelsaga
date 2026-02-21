@extends('layouts.app')

@section('title', 'Stock Movements | SAGA TOKO APP')

@section('content')
<div x-data="{
    movements: [],
    isLoading: true,
    currentPage: 1,
    totalPages: 1,
    totalItems: 0,
    itemsPerPage: 50,
    filters: {
        type: '',
        search: ''
    },

    async init() {
        await this.fetchMovements();
    },

    async fetchMovements(page = 1) {
        this.isLoading = true;
        this.currentPage = page;
        try {
            const token = localStorage.getItem('saga_token');
            let url = `/api/reports/inventory-movements?page=${this.currentPage}&limit=${this.itemsPerPage}`;
            
            if (this.filters.type) url += `&type=${this.filters.type}`;
            if (this.filters.search) url += `&search=${this.filters.search}`;

            const response = await fetch(url, {
                headers: { 'Authorization': 'Bearer ' + token }
            });
            const data = await response.json();
            if (data.success) {
                this.movements = data.data.data;
                this.totalPages = data.data.last_page;
                this.totalItems = data.data.total;
            }
        } catch (error) {
            console.error('Fetch movements error:', error);
        } finally {
            this.isLoading = false;
        }
    },

    formatDate(dateStr) {
        if (!dateStr) return '-';
        return new Date(dateStr).toLocaleString('id-ID', {
            day: '2-digit',
            month: 'short',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    },

    getTypeBadgeClass(type) {
        switch(type) {
            case 'in': return 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400';
            case 'out': return 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400';
            case 'adjustment': return 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400';
            case 'transfer': return 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400';
            default: return 'bg-gray-100 text-gray-700 dark:bg-gray-900/30 dark:text-gray-400';
        }
    },

    formatNumber(num) {
        return parseFloat(num).toLocaleString('id-ID');
    }
}" x-init="init()">
    <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Stock Movements</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Audit log untuk setiap perubahan stok barang</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-gray-800 p-4 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 mb-6 flex flex-wrap gap-4 items-end">
        <div class="flex-1 min-w-[200px]">
            <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Cari Produk / SKU</label>
            <input type="text" x-model="filters.search" @input.debounce.500ms="fetchMovements(1)" 
                placeholder="Cari..." 
                class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white focus:ring-2 focus:ring-brand-500">
        </div>
        <div class="w-40">
            <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Tipe</label>
            <select x-model="filters.type" @change="fetchMovements(1)" 
                class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white focus:ring-2 focus:ring-brand-500">
                <option value="">Semua</option>
                <option value="in">Masuk (Buy)</option>
                <option value="out">Keluar (Sale)</option>
                <option value="adjustment">Adjustment</option>
                <option value="transfer">Transfer</option>
            </select>
        </div>
        <button @click="fetchMovements(1)" class="px-4 py-2 bg-brand-500 text-white rounded-lg hover:bg-brand-600 transition-colors">
            Refresh
        </button>
    </div>

    <!-- Table -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-gray-50 dark:bg-gray-700/50 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                    <tr>
                        <th class="px-6 py-4">Waktu</th>
                        <th class="px-6 py-4">Produk</th>
                        <th class="px-6 py-4">Tipe</th>
                        <th class="px-6 py-4 text-right">Qty</th>
                        <th class="px-6 py-4 text-right">Stok Akhir</th>
                        <th class="px-6 py-4">Ref / Keterangan</th>
                        <th class="px-6 py-4">User</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    <template x-if="isLoading">
                        <tr>
                            <td colspan="7" class="px-6 py-10 text-center">
                                <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-brand-500 border-t-transparent"></div>
                                <p class="mt-2 text-gray-400">Memuat data...</p>
                            </td>
                        </tr>
                    </template>
                    <template x-if="!isLoading && movements.length === 0">
                        <tr>
                            <td colspan="7" class="px-6 py-10 text-center text-gray-400">
                                Belum ada history pergerakan stok.
                            </td>
                        </tr>
                    </template>
                    <template x-for="m in movements" :key="m.id">
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-700/30 transition-colors">
                            <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400 whitespace-nowrap" x-text="formatDate(m.created_at)"></td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-bold text-gray-800 dark:text-white" x-text="m.product?.name || 'Unknown'"></div>
                                <div class="text-[10px] text-gray-500 font-mono" x-text="m.product?.sku || '-'"></div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 rounded-full text-[10px] font-bold uppercase" :class="getTypeBadgeClass(m.type)" x-text="m.type"></span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <span class="font-bold text-sm" :class="m.qty < 0 ? 'text-red-500' : 'text-green-500'" x-text="(m.qty > 0 ? '+' : '') + formatNumber(m.qty)"></span>
                            </td>
                            <td class="px-6 py-4 text-right font-semibold text-gray-700 dark:text-gray-300" x-text="formatNumber(m.current_stock)"></td>
                            <td class="px-6 py-4">
                                <div class="text-xs text-brand-600 dark:text-brand-400 font-medium" x-text="m.reference_number || '-'"></div>
                                <div class="text-[10px] text-gray-500 mt-0.5" x-text="m.notes || '-'"></div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400" x-text="m.user?.name || '-'"></td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700/30 border-t border-gray-100 dark:border-gray-700 flex items-center justify-between">
            <span class="text-xs text-gray-500">
                Menampilkan <span x-text="movements.length"></span> dari <span x-text="totalItems"></span> logs
            </span>
            <div class="flex gap-2">
                <button @click="fetchMovements(currentPage - 1)" :disabled="currentPage === 1" 
                    class="px-3 py-1 text-xs border border-gray-300 rounded hover:bg-white disabled:opacity-50 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300">
                    Prev
                </button>
                <div class="px-3 py-1 text-xs font-bold text-brand-600 dark:text-brand-400 bg-brand-50 dark:bg-brand-900/20 rounded">
                    <span x-text="currentPage"></span> / <span x-text="totalPages"></span>
                </div>
                <button @click="fetchMovements(currentPage + 1)" :disabled="currentPage === totalPages" 
                    class="px-3 py-1 text-xs border border-gray-300 rounded hover:bg-white disabled:opacity-50 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300">
                    Next
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
