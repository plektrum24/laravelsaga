@extends('layouts.app')

@section('title', 'Stock Management | SAGA TOKO APP')

@section('content')
    <div x-data="{
                    page: 'stockManagement',
                    currentTab: 'stock', // 'stock' or 'expiry'
                    products: [],
                    expiryItems: [],
                    categories: [],
                    isLoading: true,
                    searchQuery: '',
                    selectedCategory: '',
                    sortBy: 'name_asc',
                    showSortMenu: false,
                    showExportMenu: false,
                    showAdjustModal: false,
                    selectedProduct: null,
                    adjustType: 'add',
                    adjustQty: 0,
                    adjustUnit: null,
                    adjustReason: '',
                    currentPage: 1,
                    totalPages: 1,
                    totalItems: 0,
                    itemsPerPage: 20,
                    totalAsset: 0,
                    sortOptions: [
                        { value: 'name_asc', label: 'Nama A-Z' },
                        { value: 'name_desc', label: 'Nama Z-A' },
                        { value: 'stock_asc', label: 'Stock Rendah' },
                        { value: 'stock_desc', label: 'Stock Tinggi' }
                    ],

                    async init() {
                        await Promise.all([
                            this.fetchProducts(),
                            this.fetchCategories(),
                            this.fetchTotalAsset()
                        ]);
                    },

                    async fetchTotalAsset() {
                        try {
                            const token = localStorage.getItem('saga_token');
                            const selectedBranch = localStorage.getItem('saga_selected_branch');
                            let url = '/api/reports/assets';
                            if (selectedBranch) url += '?branch_id=' + selectedBranch;

                            const response = await fetch(url, { headers: { 'Authorization': 'Bearer ' + token } });
                            const data = await response.json();
                            if (data.success) {
                                this.totalAsset = data.data.totalAsset;
                            }
                        } catch (error) {
                            console.error('Fetch asset error:', error);
                        }
                    },

                    async fetchProducts(resetPage = true) {
                        try {
                            if (resetPage) this.currentPage = 1;
                            const token = localStorage.getItem('saga_token');
                            const selectedBranch = localStorage.getItem('saga_selected_branch');

                            let url = `/api/products?page=${this.currentPage}&limit=${this.itemsPerPage}&sort=${this.sortBy}&`;
                            if (this.selectedCategory) url += 'category_id=' + this.selectedCategory + '&';
                            if (this.searchQuery) url += 'search=' + this.searchQuery + '&';
                            if (selectedBranch) url += 'branch_id=' + selectedBranch;

                            const response = await fetch(url, { headers: { 'Authorization': 'Bearer ' + token } });
                            const data = await response.json();
                            if (data.success) {
                                this.products = data.data.products;
                                this.totalPages = data.data.pagination.totalPages || 1;
                                this.totalItems = data.data.pagination.total || 0;
                                this.fetchTotalAsset();
                            }
                        } catch (error) {
                            console.error('Fetch error:', error);
                        } finally {
                            this.isLoading = false;
                        }
                    },

                    async fetchCategories() {
                        try {
                            const token = localStorage.getItem('saga_token');
                            const response = await fetch('/api/products/categories', { headers: { 'Authorization': 'Bearer ' + token } });
                            const data = await response.json();
                            if (data.success) this.categories = data.data;
                        } catch (error) {
                            console.error('Fetch categories error:', error);
                        }
                    },

                    async fetchExpiryItems() {
                        this.isLoading = true;
                        try {
                            const token = localStorage.getItem('saga_token');
                            const response = await fetch('/api/products/expiry?days=60', { headers: { 'Authorization': 'Bearer ' + token } });
                            const data = await response.json();
                            if (data.success) {
                                this.expiryItems = data.data;
                            }
                        } catch (e) {
                            console.error(e);
                        } finally {
                            this.isLoading = false;
                        }
                    },

                    formatDate(dateStr) {
                        if (!dateStr) return '-';
                        return new Date(dateStr).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
                    },

                    getExpiryColorStatus(days) {
                        if (days <= 30) return 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400';
                        if (days <= 60) return 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400';
                        return 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400';
                    },

                    sortProducts() {
                        this.showSortMenu = false;
                        this.fetchProducts();
                    },

                    sortLabel() {
                        return this.sortOptions.find(o => o.value === this.sortBy)?.label || 'Sort';
                    },

                    goToPage(page) {
                        if (page >= 1 && page <= this.totalPages && page !== this.currentPage) {
                            this.currentPage = page;
                            this.fetchProducts(false);
                        }
                    },
                    prevPage() { if (this.currentPage > 1) this.goToPage(this.currentPage - 1); },
                    nextPage() { if (this.currentPage < this.totalPages) this.goToPage(this.currentPage + 1); },

                    get paginationRange() {
                        const range = [];
                        const delta = 2;
                        const left = Math.max(1, this.currentPage - delta);
                        const right = Math.min(this.totalPages, this.currentPage + delta);
                        if (left > 1) range.push({ type: 'page', value: 1 });
                        if (left > 2) range.push({ type: 'ellipsis', value: 'left' });
                        for (let i = left; i <= right; i++) {
                            range.push({ type: 'page', value: i });
                        }
                        if (right < this.totalPages - 1) range.push({ type: 'ellipsis', value: 'right' });
                        if (right < this.totalPages) range.push({ type: 'page', value: this.totalPages });
                        return range;
                    },

                    openAdjustModal(product, unit = null) {
                        this.selectedProduct = product;
                        this.adjustType = 'add';
                        this.adjustQty = 0;
                        this.adjustReason = '';
                        this.adjustUnit = unit || (product.units?.find(u => u.is_base_unit) || product.units?.[0] || { conversion_qty: 1, unit_name: 'Pcs' });
                        this.showAdjustModal = true;
                    },

                    async saveAdjustment() {
                        if (this.adjustQty <= 0) { Swal.fire({ icon: 'error', title: 'Error', text: 'Quantity harus lebih dari 0' }); return; }
                        const token = localStorage.getItem('saga_token');
                        const conversionQty = parseFloat(this.adjustUnit?.conversion_qty) || 1;
                        const baseUnitQty = this.adjustQty * conversionQty;
                        const selectedBranch = localStorage.getItem('saga_selected_branch');

                        const payload = {
                            type: this.adjustType,
                            quantity: baseUnitQty,
                            reason: this.adjustReason + (conversionQty > 1 ? ` (${this.adjustQty} ${this.adjustUnit?.unit_name})` : ''),
                            branch_id: selectedBranch ? parseInt(selectedBranch) : null
                        };

                        const response = await fetch('/api/products/adjust-stock/' + this.selectedProduct.id, {
                            method: 'POST',
                            headers: { 'Authorization': 'Bearer ' + token, 'Content-Type': 'application/json' },
                            body: JSON.stringify(payload)
                        });
                        const data = await response.json();
                        if (data.success) {
                            Swal.fire({ icon: 'success', title: 'Berhasil', text: data.message, timer: 1500, showConfirmButton: false });
                            this.showAdjustModal = false;
                            await this.fetchProducts(false);
                        } else { Swal.fire({ icon: 'error', title: 'Gagal', text: data.message }); }
                    },

                    async resetAllStock() {
                        const result = await Swal.fire({
                            title: 'KOSONGKAN SEMUA STOCK?',
                            text: 'Semua stock produk akan diset ke 0. Aksi ini tidak dapat dibatalkan!',
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#d33',
                            confirmButtonText: 'Ya, Kosongkan!'
                        });
                        if (!result.isConfirmed) return;

                        const token = localStorage.getItem('saga_token');
                        const response = await fetch('/api/products/reset-all-stock', {
                            method: 'POST',
                            headers: { 'Authorization': 'Bearer ' + token }
                        });
                        const data = await response.json();
                        if (data.success) {
                            Swal.fire({ icon: 'success', title: 'Berhasil', text: data.message, timer: 1500, showConfirmButton: false });
                            await this.fetchProducts(false);
                        } else { Swal.fire({ icon: 'error', title: 'Gagal', text: data.message }); }
                    },

                    exportStock(type) {
                        this.showExportMenu = false;
                        const token = localStorage.getItem('saga_token');
                        const url = type === 'excel' ?
                            '/api/export/stock/excel?token=' + token :
                            '/api/export/stock/pdf?token=' + token;
                        window.open(url, '_blank');
                    },

                    formatCurrency(amount) {
                        return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(amount || 0);
                    },

                    formatNumber(num) {
                        const n = parseFloat(num) || 0;
                        return n % 1 === 0 ? n.toString() : n.toFixed(1);
                    },

                    getStockColor(product) {
                        const stock = parseFloat(product.stock) || 0;
                        const minStock = parseFloat(product.min_stock) || 0;
                        if (stock <= 0) return 'bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400';
                        if (stock <= minStock) return 'bg-yellow-100 text-yellow-600 dark:bg-yellow-900/30 dark:text-yellow-400';
                        return 'bg-green-100 text-green-600 dark:bg-green-900/30 dark:text-green-400';
                    }
                }" x-init="init()">
        <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
            <div>
                <h1 class="text-xl md:text-2xl font-bold text-gray-800 dark:text-white">Stock Management</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">Adjust dan monitor stock produk</p>
            </div>

            <!-- Tab Navigation -->
            <div class="flex p-1 bg-gray-100 dark:bg-gray-800 rounded-lg">
                <button @click="currentTab = 'stock'; fetchProducts()"
                    :class="currentTab === 'stock' ? 'bg-white shadow-sm text-brand-600 dark:bg-gray-700 dark:text-white' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400'"
                    class="px-4 py-2 text-sm font-medium rounded-md transition-all">
                    Stock List
                </button>
                <button @click="currentTab = 'expiry'; fetchExpiryItems()"
                    :class="currentTab === 'expiry' ? 'bg-white shadow-sm text-red-600 dark:bg-gray-700 dark:text-white' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400'"
                    class="px-4 py-2 text-sm font-medium rounded-md transition-all flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Cek Kadaluarsa
                </button>
            </div>
            <div class="flex gap-2">
                <!-- Reset Stock Button -->
                <button @click="resetAllStock()"
                    class="px-3 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                        </path>
                    </svg>
                    <span class="hidden md:inline">Kosongkan Stock</span>
                </button>
                <!-- Export Dropdown -->
                <div class="relative">
                    <button @click="showExportMenu = !showExportMenu"
                        class="px-3 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                            </path>
                        </svg>
                        <span class="hidden md:inline">Export</span>
                        <svg class="w-4 h-4 hidden md:block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div x-show="showExportMenu" @click.away="showExportMenu = false"
                        class="absolute right-0 mt-2 w-40 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 z-10"
                        x-cloak>
                        <button @click="exportStock('excel')"
                            class="w-full px-4 py-2 text-left text-sm hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 flex items-center gap-2">
                            <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6zm-1 2l5 5h-5V4zm-2 9l-2.5 3.5L11 14l-2.5-3zm2.5 3L11 13l2.5 4 2.5-3-2 3h-3z" />
                            </svg>
                            Excel
                        </button>
                        <button @click="exportStock('pdf')"
                            class="w-full px-4 py-2 text-left text-sm hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 flex items-center gap-2">
                            <svg class="w-4 h-4 text-red-600" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6zM9.5 11.5c.8 0 1.5.7 1.5 1.5s-.7 1.5-1.5 1.5H8V17H6v-6h3.5zm5 0c.8 0 1.5.7 1.5 1.5v2c0 .8-.7 1.5-1.5 1.5H12v-5h2.5zm4-.5h3v1h-2v1h2v1h-2v2h-1v-5z" />
                            </svg>
                            PDF
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- STOCK TAB CONTENT -->
        <div x-show="currentTab === 'stock'">
            <div class="flex flex-wrap items-center gap-3 mb-4">
                <div class="relative w-full md:w-64">
                    <input type="text" x-model="searchQuery" @input.debounce.300ms="fetchProducts()"
                        placeholder="Cari produk..."
                        class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg dark:bg-gray-800 dark:border-gray-600 dark:text-white text-sm focus:ring-2 focus:ring-brand-500">
                    <svg class="w-4 h-4 absolute left-3 top-2.5 text-gray-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
                <div
                    class="flex w-full md:w-auto items-center gap-2 px-4 py-2 bg-gradient-to-r from-emerald-500 to-teal-500 rounded-lg text-white shadow-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                        </path>
                    </svg>
                    <div>
                        <div class="text-xs opacity-80">Total Aset</div>
                        <div class="font-bold text-lg" x-text="formatCurrency(totalAsset)"></div>
                    </div>
                </div>
                <select x-model="selectedCategory" @change="fetchProducts()"
                    class="w-full md:w-auto px-4 py-2 border border-gray-300 rounded-lg dark:bg-gray-800 dark:border-gray-600 dark:text-white focus:ring-2 focus:ring-brand-500">
                    <option value="">Semua Kategori</option>
                    <template x-for="cat in categories" :key="cat.id">
                        <option :value="cat.id" x-text="cat.name"></option>
                    </template>
                </select>
                <div class="relative">
                    <button @click="showSortMenu = !showSortMenu"
                        class="px-4 py-2 border border-gray-300 rounded-lg flex items-center gap-2 dark:bg-gray-800 dark:border-gray-600 dark:text-white">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12"></path>
                        </svg>
                        <span x-text="sortLabel()"></span>
                    </button>
                    <div x-show="showSortMenu" @click.away="showSortMenu = false"
                        class="absolute left-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 z-10"
                        x-cloak>
                        <template x-for="option in sortOptions" :key="option.value">
                            <button @click="sortBy = option.value; sortProducts()"
                                class="w-full px-4 py-2 text-left text-sm hover:bg-gray-50 dark:hover:bg-gray-700 flex items-center gap-2"
                                :class="sortBy === option.value ? 'bg-indigo-50 text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-400' : 'text-gray-700 dark:text-gray-300'">
                                <svg x-show="sortBy === option.value" class="w-4 h-4" fill="currentColor"
                                    viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                        clip-rule="evenodd" />
                                </svg>
                                <span x-text="option.label"></span>
                            </button>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div
                class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] overflow-hidden">
                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase w-12">No.</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">SKU</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kategori</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Unit</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Stock</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total Aset</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <template x-for="(p, productIdx) in products" :key="p.id">
                                <template
                                    x-for="(unit, unitIdx) in (p.units && p.units.length > 0 ? p.units : [{unit_name: p.base_unit_name || '-', conversion_qty: 1}])"
                                    :key="unitIdx">
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50"
                                        style="content-visibility: auto; contain-intrinsic-size: 60px;">
                                        <td class="px-3 py-2 text-center text-sm text-gray-500 dark:text-gray-400"
                                            x-show="unitIdx === 0" :rowspan="p.units?.length || 1"
                                            x-text="products.length - productIdx"></td>
                                        <td class="px-4 py-2">
                                            <div class="flex items-center gap-3">
                                                <div
                                                    class="w-8 h-8 bg-gray-100 rounded-lg flex items-center justify-center dark:bg-gray-700">
                                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4">
                                                        </path>
                                                    </svg>
                                                </div>
                                                <span class="font-medium text-gray-800 dark:text-white"
                                                    x-text="p.name"></span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-2 text-sm font-mono text-brand-600 dark:text-brand-400"
                                            x-text="p.sku"></td>
                                        <td class="px-4 py-2 text-sm text-gray-500 dark:text-gray-400"
                                            x-text="p.category_name || '-'"></td>
                                        <td class="px-4 py-2">
                                            <div class="flex items-center gap-2">
                                                <span class="text-sm font-medium text-gray-800 dark:text-white"
                                                    x-text="unit.unit_name"></span>
                                                <span x-show="unit.conversion_qty > 1" class="text-xs text-gray-400"
                                                    x-text="'(1:' + formatNumber(unit.conversion_qty) + ')'"></span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-2 text-right">
                                            <span class="text-lg font-bold"
                                                :class="parseFloat(p.stock) <= parseFloat(p.min_stock) ? 'text-red-500' : 'text-gray-800 dark:text-white'"
                                                x-text="formatNumber(parseFloat(p.stock) / (unit.conversion_qty || 1))"></span>
                                        </td>
                                        <td class="px-4 py-2 text-center">
                                            <span class="px-2 py-1 rounded-full text-xs font-medium"
                                                :class="getStockColor(p)"
                                                x-text="parseFloat(p.stock) <= 0 ? 'Out of Stock' : parseFloat(p.stock) <= parseFloat(p.min_stock) ? 'Low Stock' : 'In Stock'"></span>
                                        </td>
                                        <td class="px-4 py-2 text-right" x-show="unitIdx === 0"
                                            :rowspan="p.units?.length || 1">
                                            <span class="font-semibold text-emerald-600 dark:text-emerald-400"
                                                x-text="formatCurrency(parseFloat(p.stock) * parseFloat(p.buy_price || 0))"></span>
                                        </td>
                                        <td class="px-4 py-2 text-center" x-show="unitIdx === 0"
                                            :rowspan="p.units?.length || 1">
                                            <button @click="openAdjustModal(p, p.units?.[0] || unit)"
                                                class="text-brand-500 hover:underline text-sm">Adjust</button>
                                        </td>
                                    </tr>
                                </template>
                            </template>
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Card List View -->
                <div class="md:hidden">
                    <template x-for="(p, productIdx) in products" :key="p.id">
                        <div
                            class="border-b border-gray-100 dark:border-gray-800 p-4 last:border-0 hover:bg-gray-50 dark:hover:bg-gray-800/30">
                            <div class="flex justify-between items-start mb-2">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center flex-shrink-0 dark:bg-gray-700">
                                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4">
                                            </path>
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="font-bold text-gray-800 dark:text-white" x-text="p.name"></h3>
                                        <p class="text-xs text-brand-600 font-mono" x-text="p.sku || '-'"></p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <div class="text-right">
                                        <div class="text-[10px] text-gray-500 uppercase tracking-wide">Total Aset</div>
                                        <div class="font-bold text-emerald-600 dark:text-emerald-400"
                                            x-text="formatCurrency(parseFloat(p.stock) * parseFloat(p.buy_price || 0))">
                                        </div>
                                    </div>
                                    <button @click="openAdjustModal(p)"
                                        class="p-2 bg-brand-50 text-brand-600 dark:bg-brand-900/20 dark:text-brand-400 rounded-lg hover:bg-brand-100 transition-colors border border-brand-100 dark:border-brand-900/50"
                                        title="Adjust Stock">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                            </path>
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <div class="mb-3 pl-[3.25rem]">
                                <p class="text-xs text-gray-500" x-text="p.category_name || 'Tanpa Kategori'"></p>
                            </div>

                            <div class="space-y-2 mt-3">
                                <template
                                    x-for="(unit, unitIdx) in (p.units && p.units.length > 0 ? p.units : [{unit_name: p.base_unit_name || '-', conversion_qty: 1}])"
                                    :key="unitIdx">
                                    <div
                                        class="bg-gray-50 dark:bg-gray-700/30 p-3 rounded-lg flex justify-between items-center">
                                        <div>
                                            <div class="font-medium text-sm text-gray-700 dark:text-gray-200">
                                                <span x-text="unit.unit_name"></span>
                                                <span x-show="unit.conversion_qty > 1" class="text-xs text-gray-400"
                                                    x-text="'(1:' + formatNumber(unit.conversion_qty) + ')'"></span>
                                            </div>
                                            <div class="text-xs mt-1 flex items-center gap-2">
                                                <span :class="getStockColor(p)"
                                                    class="px-1.5 py-0.5 rounded text-[10px] font-bold">
                                                    <span
                                                        x-text="parseFloat(p.stock) <= 0 ? 'Empty' : parseFloat(p.stock) <= parseFloat(p.min_stock) ? 'Low' : 'OK'"></span>
                                                </span>
                                            </div>
                                        </div>

                                        <div class="text-right">
                                            <div class="text-xl font-bold"
                                                :class="parseFloat(p.stock) <= parseFloat(p.min_stock) ? 'text-red-500' : 'text-gray-800 dark:text-white'"
                                                x-text="formatNumber(parseFloat(p.stock) / (unit.conversion_qty || 1))">
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
                <div x-show="isLoading" class="p-8 text-center text-gray-400">Loading...</div>
                <div x-show="!isLoading && products.length === 0" class="p-8 text-center text-gray-400">Tidak ada produk
                </div>

                <!-- Pagination -->
                <div x-show="totalPages > 1"
                    class="flex flex-wrap items-center justify-between gap-4 p-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                    <div class="text-sm text-gray-500 dark:text-gray-400 w-full md:w-auto text-center md:text-left">
                        Menampilkan <span class="font-medium" x-text="((currentPage - 1) * itemsPerPage) + 1"></span> -
                        <span class="font-medium" x-text="Math.min(currentPage * itemsPerPage, totalItems)"></span> dari
                        <span class="font-medium" x-text="totalItems"></span> produk
                    </div>
                    <div class="flex items-center justify-center gap-2 w-full md:w-auto">
                        <button @click="prevPage()" :disabled="currentPage === 1"
                            class="px-3 py-1.5 text-sm rounded-lg border border-gray-300 disabled:opacity-50 disabled:cursor-not-allowed dark:border-gray-600 dark:text-gray-400">
                            Previous
                        </button>
                        <template x-for="pg in paginationRange" :key="pg.type + '-' + pg.value">
                            <button x-show="pg.type === 'page'" @click="goToPage(pg.value)"
                                class="px-3 py-1.5 text-sm rounded-lg border transition-colors min-w-[36px]"
                                :class="pg.value === currentPage ? 'bg-indigo-500 border-indigo-500 text-white' : 'border-gray-300 text-gray-600 hover:bg-gray-100 dark:border-gray-600 dark:text-gray-400 dark:hover:bg-gray-700'"
                                x-text="pg.value">
                            </button>
                            <span x-show="pg.type === 'ellipsis'" class="px-2 text-gray-400">...</span>
                        </template>
                        <button @click="nextPage()" :disabled="currentPage === totalPages"
                            class="px-3 py-1.5 text-sm rounded-lg border border-gray-300 disabled:opacity-50 disabled:cursor-not-allowed dark:border-gray-600 dark:text-gray-400">
                            Next
                        </button>
                    </div>
                </div>
            </div>
        </div> <!-- End Stock Tab -->

        <!-- EXPIRY TAB CONTENT -->
        <div x-show="currentTab === 'expiry'" style="display: none;">
            <div
                class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden shadow-sm">
                <div class="p-4 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
                    <h3 class="font-bold text-gray-800 dark:text-white">Batch Barang Masuk (Goods In)</h3>
                    <span class="text-xs text-gray-500">Menampilkan batch yang expired dalam 60 Hari ke depan</span>
                </div>
                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-gray-50 dark:bg-gray-700 text-gray-500 dark:text-gray-300 uppercase text-xs">
                            <tr>
                                <th class="px-6 py-3">Produk</th>
                                <th class="px-6 py-3">Tanggal Masuk</th>
                                <th class="px-6 py-3">Expired Date</th>
                                <th class="px-6 py-3 text-center">Sisa Hari</th>
                                <th class="px-6 py-3 text-center">Qty Masuk</th>
                                <th class="px-6 py-3">Supplier / Invoice</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            <template x-for="item in expiryItems" :key="item.invoice_number + item.sku">
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="font-medium text-gray-900 dark:text-white" x-text="item.product_name">
                                        </div>
                                        <div class="text-xs text-gray-500" x-text="item.sku"></div>
                                    </td>
                                    <td class="px-6 py-4" x-text="formatDate(item.purchase_date)"></td>
                                    <td class="px-6 py-4 font-semibold text-gray-800 dark:text-gray-200"
                                        x-text="formatDate(item.expiry_date)"></td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="px-2.5 py-1 rounded-full text-xs font-bold"
                                            :class="getExpiryColorStatus(item.days_remaining)"
                                            x-text="item.days_remaining + ' Hari'"></span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="font-bold text-gray-800 dark:text-white"
                                            x-text="item.batch_remaining_qty"></span>
                                        <span class="text-xs text-gray-400" x-text="'/ ' + item.batch_initial_qty"></span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-xs text-gray-500" x-text="item.supplier_name || 'No Supplier'">
                                        </div>
                                        <div class="text-xs font-mono text-gray-400" x-text="item.invoice_number"></div>
                                    </td>
                                </tr>
                            </template>
                            <tr x-show="expiryItems.length === 0 && !isLoading">
                                <td colspan="6" class="text-center py-8 text-gray-400">Belum ada data barang expired</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Card View for Expiry -->
                <div class="md:hidden">
                    <template x-for="item in expiryItems" :key="item.invoice_number + item.sku">
                        <div
                            class="border-b border-gray-100 dark:border-gray-800 p-4 hover:bg-gray-50 dark:hover:bg-gray-800/30">
                            <div class="flex justify-between items-start mb-2">
                                <div>
                                    <div class="font-bold text-gray-800 dark:text-white" x-text="item.product_name"></div>
                                    <div class="text-xs text-gray-500 font-mono" x-text="item.sku"></div>
                                </div>
                                <div class="text-right">
                                    <span class="px-2 py-1 rounded-lg text-xs font-bold"
                                        :class="getExpiryColorStatus(item.days_remaining)"
                                        x-text="item.days_remaining + ' Hari'"></span>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-3 bg-gray-50 dark:bg-gray-700/30 p-3 rounded-lg text-sm mb-2">
                                <div>
                                    <div class="text-[10px] text-gray-500 uppercase">Tgl Masuk</div>
                                    <div class="font-medium" x-text="formatDate(item.purchase_date)"></div>
                                </div>
                                <div class="text-right">
                                    <div class="text-[10px] text-gray-500 uppercase">Expired</div>
                                    <div class="font-bold text-red-600 dark:text-red-400"
                                        x-text="formatDate(item.expiry_date)"></div>
                                </div>
                            </div>
                            <div class="flex justify-between items-center text-sm">
                                <div>
                                    <div class="text-xs text-gray-500" x-text="item.supplier_name || 'No Supplier'"></div>
                                    <div class="text-[10px] text-gray-400" x-text="item.invoice_number"></div>
                                </div>
                                <div class="text-right">
                                    <span class="text-xs text-gray-500">Qty:</span>
                                    <span class="font-bold text-gray-800 dark:text-white"
                                        x-text="item.batch_remaining_qty"></span>
                                    <span class="text-xs text-gray-400" x-text="'/ ' + item.batch_initial_qty"></span>
                                </div>
                            </div>
                        </div>
                    </template>
                    <div x-show="expiryItems.length === 0 && !isLoading" class="p-8 text-center text-gray-400 text-sm">Belum
                        ada data barang expired</div>
                </div>
            </div>
        </div>

        <!-- Adjust Modal -->
        <div x-show="showAdjustModal"
            class="fixed inset-0 z-[9999] flex items-center justify-center bg-gray-900/80 backdrop-blur-sm p-4" x-cloak>
            <div class="bg-white dark:bg-gray-800 rounded-2xl w-full max-w-md overflow-hidden"
                @click.away="showAdjustModal = false">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-100 dark:bg-gray-800">
                    <h2 class="text-xl font-bold text-gray-800 dark:text-white">Adjust Stock</h2>
                </div>
                <div class="p-6">
                    <div class="mb-4 p-4 bg-gray-50 rounded-lg dark:bg-gray-900">
                        <p class="font-medium text-gray-800 dark:text-white" x-text="selectedProduct?.name"></p>
                        <p class="text-sm text-gray-500">
                            Stock: <span class="font-bold"
                                x-text="formatNumber(parseFloat(selectedProduct?.stock) / (adjustUnit?.conversion_qty || 1))"></span>
                            <span class="text-brand-500" x-text="adjustUnit?.unit_name"></span>
                            <span x-show="adjustUnit?.conversion_qty > 1" class="text-gray-400"
                                x-text="'(' + selectedProduct?.stock + ' base unit)'"></span>
                        </p>
                    </div>
                    <form @submit.prevent="saveAdjustment()" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Type</label>
                            <div class="flex gap-4">
                                <label
                                    class="flex items-center gap-2 cursor-pointer border p-2 rounded hover:bg-gray-50 dark:hover:bg-gray-700 dark:border-gray-600 w-full justify-center">
                                    <input type="radio" x-model="adjustType" value="add"
                                        class="text-brand-500 focus:ring-brand-500">
                                    <span class="text-gray-700 dark:text-gray-300 font-medium">Add Stock</span>
                                </label>
                                <label
                                    class="flex items-center gap-2 cursor-pointer border p-2 rounded hover:bg-gray-50 dark:hover:bg-gray-700 dark:border-gray-600 w-full justify-center">
                                    <input type="radio" x-model="adjustType" value="subtract"
                                        class="text-red-500 focus:ring-red-500">
                                    <span class="text-gray-700 dark:text-gray-300 font-medium">Reduce Stock</span>
                                </label>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Unit</label>
                            <select @change="adjustUnit = selectedProduct?.units[$event.target.selectedIndex]"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg dark:bg-gray-900 dark:border-gray-600 dark:text-white focus:ring-2 focus:ring-brand-500">
                                <template x-for="(u, idx) in selectedProduct?.units" :key="u.unit_id">
                                    <option :value="idx" :selected="adjustUnit?.unit_id === u.unit_id"
                                        x-text="u.unit_name + (u.conversion_qty > 1 ? ' (1:' + u.conversion_qty + ')' : '')">
                                    </option>
                                </template>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Quantity</label>
                            <input type="number" x-model="adjustQty" min="0" step="0.01" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg dark:bg-gray-900 dark:border-gray-600 dark:text-white focus:ring-2 focus:ring-brand-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Reason</label>
                            <textarea x-model="adjustReason" rows="2"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg dark:bg-gray-900 dark:border-gray-600 dark:text-white focus:ring-2 focus:ring-brand-500"
                                placeholder="Optional..."></textarea>
                        </div>
                        <div
                            class="flex gap-3 pt-4 border-t border-gray-100 dark:border-gray-700 -mx-6 px-6 bg-gray-100 dark:bg-gray-800 -mb-6 pb-6 mt-6 rounded-b-2xl">
                            <button type="button" @click="showAdjustModal = false"
                                class="flex-1 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 dark:border-gray-600 dark:hover:bg-gray-700 dark:text-gray-300">Cancel</button>
                            <button type="submit"
                                class="flex-1 py-2 bg-brand-500 text-white rounded-lg hover:bg-brand-600 shadow-sm font-medium">Save
                                Adjustment</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection