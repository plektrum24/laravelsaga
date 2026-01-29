@extends('layouts.app')

@section('title', 'Inventory | SAGA TOKO APP')

@section('content')
    <div x-data="inventoryPage()" x-init="initPage()">
        <style>
            .swal2-container {
                z-index: 100000 !important;
            }
        </style>
        <input type="file" id="importFileInput" accept=".xlsx,.xls" @change="handleImport($event)" class="hidden">

        <!-- Header -->
        <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
            <div>
                <h1 class="text-xl md:text-2xl font-bold text-gray-800 dark:text-white">Inventory</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">Manage your products and stock</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <!-- Add Product -->
                <button @click="openAddModal()"
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center gap-2 shadow-sm transition-all focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6v6m0 0v6m0-6h6m-6 0H6">
                        </path>
                    </svg>
                    Add Product
                </button>

                <button @click="deleteAllProducts()"
                    class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 flex items-center gap-2 shadow-sm transition-all focus:ring-2 focus:ring-offset-2 focus:ring-red-500 ml-1">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                        </path>
                    </svg>
                    Delete All
                </button>

                <!-- Scanner Toggle Removed (Auto-detect implemented) -->
                <div
                    class="px-3 py-2 bg-gray-100 dark:bg-gray-800 rounded-lg text-xs font-mono text-gray-500 flex items-center gap-2 border border-gray-200 dark:border-gray-700 select-none">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                    Scanner Ready
                </div>

                <!-- Import Button -->
                <button @click="triggerImport()"
                    class="px-3 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                    </svg>
                    <span class="hidden md:inline">Import</span>
                </button>

                <!-- Export Dropdown -->
                <div class="relative">
                    <button @click="showExportMenu = !showExportMenu"
                        class="px-3 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                        </svg>
                        <span class="hidden md:inline">Export</span>
                        <svg class="w-4 h-4 hidden md:block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div x-show="showExportMenu" @click.away="showExportMenu = false"
                        class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-xl dark:bg-gray-800 z-[100]" x-cloak>
                        <button @click="exportExcel()"
                            class="w-full px-4 py-3 text-left text-sm hover:bg-gray-50 dark:hover:bg-gray-700 flex items-center gap-3 transition-colors first:rounded-t-xl">
                            <div
                                class="w-8 h-8 rounded-lg bg-green-50 dark:bg-green-900/30 flex items-center justify-center">
                                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 00-2-2h-2a2 2 0 00-2 2">
                                    </path>
                                </svg>
                            </div>
                            <div class="flex flex-col">
                                <span class="font-semibold text-gray-700 dark:text-gray-200">Excel (.xlsx)</span>
                                <span class="text-[10px] text-gray-400">Microsoft Excel Format</span>
                            </div>
                        </button>
                        <button @click="exportPDF()"
                            class="w-full px-4 py-3 text-left text-sm hover:bg-gray-50 dark:hover:bg-gray-700 flex items-center gap-3 transition-colors">
                            <div class="w-8 h-8 rounded-lg bg-red-50 dark:bg-red-900/30 flex items-center justify-center">
                                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z">
                                    </path>
                                </svg>
                            </div>
                            <div class="flex flex-col">
                                <span class="font-semibold text-gray-700 dark:text-gray-200">PDF Document</span>
                                <span class="text-[10px] text-gray-400">Portable PDF Format</span>
                            </div>
                        </button>
                        <div class="border-t border-gray-100 dark:border-gray-700"></div>
                        <button @click="downloadTemplate()"
                            class="w-full px-4 py-3 text-left text-sm hover:bg-gray-50 dark:hover:bg-gray-700 flex items-center gap-3 transition-colors last:rounded-b-xl">
                            <div class="w-8 h-8 rounded-lg bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                    </path>
                                </svg>
                            </div>
                            <div class="flex flex-col">
                                <span class="font-semibold text-gray-700 dark:text-gray-200">Download Template</span>
                                <span class="text-[10px] text-gray-400">Standard Import Format</span>
                            </div>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters & Search -->
        <div class="bg-white dark:bg-gray-900 p-4 rounded-lg shadow-sm border border-gray-200 dark:border-gray-800 mb-6">
            <div class="flex flex-col md:flex-row gap-4 justify-between">
                <div class="flex-1 relative">
                    <input type="text" x-model="searchQuery" @input.debounce.300ms="fetchProducts()"
                        placeholder="Search by name, SKU, or barcode..."
                        class="w-full pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-brand-500 dark:bg-gray-800 dark:text-white">
                    <svg class="w-5 h-5 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
                <div class="flex gap-2">
                    <select x-model="selectedCategory" @change="fetchProducts()"
                        class="px-4 py-2 border border-gray-300 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-brand-500 dark:bg-gray-800 dark:text-white">
                        <option value="">All Categories</option>
                        <template x-for="cat in categories" :key="cat.id">
                            <option :value="cat.id" x-text="cat.name"></option>
                        </template>
                    </select>

                </div>
            </div>
        </div>

        <!-- Product Table -->
        <div
            class="bg-white dark:bg-gray-900 rounded-lg shadow-sm border border-gray-200 dark:border-gray-800 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead
                        class="bg-gray-50 dark:bg-gray-900 text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider text-left border-b border-gray-200 dark:border-gray-700">
                        <tr>
                            <th class="px-4 py-3 text-center w-12">No</th>
                            <th class="px-4 py-3 hidden md:table-cell w-16">Img</th>
                            <th class="px-4 py-3 cursor-pointer hover:text-brand-600 transition-colors group"
                                @click="toggleSort('name')">
                                <div class="flex items-center gap-1">
                                    Product Name
                                    <span x-show="sortBy === 'name_asc'">↑</span>
                                    <span x-show="sortBy === 'name_desc'">↓</span>
                                </div>
                            </th>
                            <th class="px-4 py-3">Category</th>
                            <th class="px-4 py-3">Unit</th>
                            <th class="px-4 py-3 text-right">Buy Price</th>
                            <th class="px-4 py-3 text-right cursor-pointer hover:text-brand-600 transition-colors group"
                                @click="toggleSort('price')">
                                <div class="flex items-center justify-end gap-1">
                                    Sell Price
                                    <span x-show="sortBy === 'price_asc'">↑</span>
                                    <span x-show="sortBy === 'price_desc'">↓</span>
                                </div>
                            </th>
                            <th class="px-4 py-3 text-center cursor-pointer hover:text-brand-600 transition-colors group"
                                @click="toggleSort('stock')">
                                <div class="flex items-center justify-center gap-1">
                                    Stock
                                    <span x-show="sortBy === 'stock_asc'">↑</span>
                                    <span x-show="sortBy === 'stock_desc'">↓</span>
                                </div>
                            </th>
                            <th class="px-4 py-3 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800 transition-opacity duration-200"
                        :class="isLoading ? 'opacity-50 pointer-events-none' : ''">
                        <tr x-show="!isLoading && products.length === 0">
                            <td colspan="9" class="px-4 py-8 text-center text-gray-500">
                                <div class="flex flex-col items-center justify-center">
                                    <svg class="w-12 h-12 text-gray-300 mb-2" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4">
                                        </path>
                                    </svg>
                                    <p>No products found</p>
                                </div>
                            </td>
                        </tr>
                        <template x-for="(product, index) in products" :key="index">
                            <template
                                x-for="(unit, uIndex) in (product.units && product.units.length > 0 ? product.units : [{unit_name: product.base_unit_name || '-', conversion_qty: 1, buy_price: product.buy_price, sell_price: product.sell_price}])"
                                :key="product.id + '-' + uIndex">
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors group"
                                    style="content-visibility: auto; contain-intrinsic-size: 60px;">
                                    <td class="px-4 py-3 text-center align-middle text-gray-500" x-show="uIndex === 0"
                                        :rowspan="product.units && product.units.length > 0 ? product.units.length : 1">
                                        <span x-text="(currentPage - 1) * itemsPerPage + index + 1"></span>
                                    </td>
                                    <td class="px-4 py-3 hidden md:table-cell align-middle" x-show="uIndex === 0"
                                        :rowspan="product.units && product.units.length > 0 ? product.units.length : 1">
                                        <div
                                            class="h-10 w-10 rounded-lg bg-gray-100 flex items-center justify-center overflow-hidden border border-gray-200 dark:border-gray-700">
                                            <img :src="product.image_url || 'https://placehold.co/100x100?text=No+Img'"
                                                alt="Product" class="h-full w-full object-cover" loading="lazy"
                                                decoding="async">
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 align-middle" x-show="uIndex === 0"
                                        :rowspan="product.units && product.units.length > 0 ? product.units.length : 1">
                                        <div>
                                            <p class="font-medium text-gray-800 dark:text-white" x-text="product.name"></p>
                                            <p class="text-xs text-brand-600 font-mono"
                                                x-text="product.sku + (product.barcode ? ' | ' + product.barcode : '')"></p>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400 align-middle"
                                        x-show="uIndex === 0"
                                        :rowspan="product.units && product.units.length > 0 ? product.units.length : 1"
                                        x-text="product.category ? product.category.name : '-'"></td>
                                    <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400 align-middle">
                                        <div class="flex items-center gap-2">
                                            <span x-text="unit.unit ? unit.unit.name : '-'"></span>
                                            <span x-show="unit.conversion_qty > 1" class="text-xs text-gray-400"
                                                x-text="'(1:' + formatNumber(unit.conversion_qty) + ')'"></span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-right font-medium text-gray-600 dark:text-gray-400 align-middle"
                                        x-text="formatCurrency(unit.buy_price || 0)"></td>
                                    <td class="px-4 py-3 text-right font-bold text-gray-800 dark:text-white align-middle"
                                        x-text="formatCurrency(unit.sell_price || 0)"></td>
                                    <td class="px-4 py-3 text-center align-middle">
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                            :class="product.stock <= product.min_stock ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'">
                                            <span
                                                x-text="formatNumber(parseFloat(product.stock) / (unit.conversion_qty || 1))"></span>
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-center align-middle" x-show="uIndex === 0"
                                        :rowspan="product.units && product.units.length > 0 ? product.units.length : 1">
                                        <div class="flex items-center justify-center gap-2">
                                            <button @click="openEditModal(product)"
                                                class="inline-flex items-center justify-center w-8 h-8 bg-blue-600 hover:bg-blue-700 text-white rounded-md transition-colors shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-blue-500"
                                                title="Edit">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                                    </path>
                                                </svg>
                                            </button>
                                            <button @click="deleteProduct(product.id)"
                                                class="inline-flex items-center justify-center w-8 h-8 bg-red-600 hover:bg-red-700 text-white rounded-md transition-colors shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-red-500"
                                                title="Delete">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                    </path>
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </template>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div
                class="px-4 py-3 border-t border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-900 flex items-center justify-between">
                <span class="text-sm text-gray-500 dark:text-gray-400">
                    Showing <span x-text="(currentPage-1)*itemsPerPage + 1"></span> to <span
                        x-text="Math.min(currentPage*itemsPerPage, totalItems)"></span> of <span x-text="totalItems"></span>
                    results
                </span>
                <div class="flex gap-1">
                    <button @click="prevPage()" :disabled="currentPage === 1"
                        class="px-3 py-1 rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-400 disabled:opacity-50 hover:bg-gray-50">
                        Prev
                    </button>
                    <template x-for="p in paginationRange" :key="p.value + p.type">
                        <button @click="p.type === 'page' ? goToPage(p.value) : null" class="px-3 py-1 rounded border"
                            :class="currentPage === p.value ? 'bg-brand-500 text-white border-brand-500' : 'border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-400 hover:bg-gray-50'"
                            x-text="p.type === 'ellipsis' ? '...' : p.value" :disabled="p.type === 'ellipsis'">
                        </button>
                    </template>
                    <button @click="nextPage()" :disabled="currentPage === totalPages"
                        class="px-3 py-1 rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-400 disabled:opacity-50 hover:bg-gray-50">
                        Next
                    </button>
                </div>
            </div>
        </div>

        <!-- Add/Edit Modal -->
        <div x-show="showModal"
            class="fixed inset-0 z-[99999] flex items-center justify-center bg-gray-900/70 backdrop-blur-sm p-4" x-cloak>
            <div class="bg-white dark:bg-gray-800 rounded-2xl w-full max-w-6xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh]"
                @click.away="if(!document.body.classList.contains('swal2-shown')) showModal = false">
                <!-- Modal Header -->
                <div
                    class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center bg-gray-100 dark:bg-gray-800">
                    <h3 class="text-xl font-bold text-gray-800 dark:text-white"
                        x-text="editMode ? 'Edit Product' : 'Add New Product'"></h3>
                    <button @click="showModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                            </path>
                        </svg>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="p-6 overflow-y-auto flex-1 bg-gray-50/30 dark:bg-gray-900/30">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Left Column: Basic Info & Image -->
                        <div class="space-y-4">
                            <!-- Image Upload -->
                            <div class="aspect-square bg-white dark:bg-gray-800 rounded-xl border-2 border-dashed border-gray-400 dark:border-gray-600 flex flex-col items-center justify-center relative hover:bg-gray-50 transition-colors group cursor-pointer shadow-sm"
                                @click="document.getElementById('productImageInput').click()">
                                <template x-if="currentProduct.image_url">
                                    <img :src="currentProduct.image_url"
                                        class="absolute inset-0 w-full h-full object-cover rounded-xl">
                                </template>
                                <template x-if="!currentProduct.image_url">
                                    <div class="flex flex-col items-center">
                                        <svg class="w-10 h-10 text-gray-400 mb-2" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                                            </path>
                                        </svg>
                                        <span class="text-sm text-gray-500 font-medium">Upload Image</span>
                                    </div>
                                </template>
                                <div
                                    class="absolute inset-0 bg-black/50 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity rounded-xl">
                                    <span class="text-white font-medium">Change</span>
                                </div>
                                <div x-show="isUploading"
                                    class="absolute inset-0 bg-white/80 flex items-center justify-center rounded-xl">
                                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-brand-600"></div>
                                </div>
                            </div>
                            <input type="file" id="productImageInput" accept="image/*" class="hidden"
                                @change="uploadProductImage($event)">

                            <!-- Basic Fields Container -->
                            <div
                                class="bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm space-y-4">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">SKU
                                        (Auto)</label>
                                    <div class="flex gap-2">
                                        <input type="text" x-model="generatedSku" readonly
                                            class="bg-gray-100 dark:bg-gray-800 block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm text-gray-500 cursor-not-allowed">
                                    </div>
                                </div>

                                <div>
                                    <label
                                        class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Barcode</label>
                                    <div class="flex gap-1">
                                        <input type="text" x-model="currentProduct.barcode" placeholder="Scan or type..."
                                            class="block w-full rounded-l-lg border-gray-400 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm dark:bg-gray-900 dark:border-gray-600 dark:text-white">
                                        <button @click="openScanner()"
                                            class="px-3 bg-gray-100 dark:bg-gray-700 border border-l-0 border-gray-400 dark:border-gray-600 rounded-r-lg hover:bg-gray-200">
                                            <svg class="w-5 h-5 text-gray-600 dark:text-gray-300" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z">
                                                </path>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column: Details & Units -->
                        <div class="md:col-span-2 space-y-4">
                            <!-- Main Info Card -->
                            <div
                                class="bg-white dark:bg-gray-800 p-5 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm space-y-4">
                                <div>
                                    <label
                                        class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1 required">Product
                                        Name</label>
                                    <input type="text" x-model="currentProduct.name" required
                                        placeholder="e.g. Kopi Kapal Api 65gr"
                                        class="block w-full rounded-lg border-gray-400 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm dark:bg-gray-900 dark:border-gray-600 dark:text-white py-2.5">
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label
                                            class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1 required">Category</label>
                                        <select x-model="currentProduct.category_id" @change="generateSku()"
                                            class="block w-full rounded-lg border-gray-400 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm dark:bg-gray-900 dark:border-gray-600 dark:text-white py-2.5">
                                            <option value="">Select Category</option>
                                            <template x-for="cat in categories" :key="cat.id">
                                                <option :value="cat.id" x-text="cat.name"></option>
                                            </template>
                                        </select>
                                    </div>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1"
                                                x-text="getStockUnitLabel()"></label>
                                            <input type="number" x-model="stockInputValue" min="0" step="0.01"
                                                class="block w-full rounded-lg border-gray-400 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm dark:bg-gray-900 dark:border-gray-600 dark:text-white py-2.5">
                                            <p class="text-[11px] text-gray-500 mt-1">Stok diinput dalam unit terbesar
                                                (Master).
                                            </p>
                                        </div>
                                        <div>
                                            <label
                                                class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Low
                                                Stock Alert</label>
                                            <input type="number" x-model="currentProduct.min_stock" min="0" step="1"
                                                placeholder="e.g. 10"
                                                class="block w-full rounded-lg border-gray-400 shadow-sm focus:border-brand-500 focus:ring-brand-500 sm:text-sm dark:bg-gray-900 dark:border-gray-600 dark:text-white py-2.5">
                                            <p class="text-[11px] text-gray-500 mt-1">Batas minimum untuk notifikasi.</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Units Section Card -->
                                <div
                                    class="bg-white dark:bg-gray-800 p-5 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
                                    <div
                                        class="flex justify-between items-center mb-4 pb-3 border-b border-gray-100 dark:border-gray-700">
                                        <div>
                                            <h4 class="font-bold text-gray-800 dark:text-white">Satuan & Harga</h4>
                                            <p class="text-xs text-gray-500">Atur harga beli & jual per satuan</p>
                                        </div>
                                        <div class="flex gap-2">
                                            <button @click="addMasterUnit()"
                                                class="text-xs text-blue-600 hover:text-blue-700 hover:underline flex items-center gap-1 font-medium bg-blue-50 px-2 py-1 rounded">
                                                + New Unit Master
                                            </button>
                                            <button @click="addUnitRow()"
                                                class="px-3 py-1.5 text-xs bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-100 font-medium shadow-sm transition-all active:scale-95 border border-blue-100">
                                                + Add Row
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Dynamic Unit Rows -->
                                    <div class="space-y-3 max-h-[300px] overflow-y-auto pr-1">
                                        <template x-for="(row, index) in currentProduct.units" :key="index">
                                            <div
                                                class="flex flex-wrap gap-2 items-end p-3 bg-gray-50 dark:bg-gray-700/30 rounded-lg border border-gray-200 dark:border-gray-600 hover:border-brand-300 transition-colors">
                                                <div class="w-16 sm:flex-1">
                                                    <label
                                                        class="text-[10px] text-gray-500 uppercase font-bold mb-1 block">Qty</label>
                                                    <input type="number" x-model="row.conversion_qty"
                                                        :readonly="row.is_base_unit" @input="autoCalculatePrices()"
                                                        class="w-full text-sm rounded-md border-gray-300 py-1.5 px-2 focus:ring-1 focus:ring-brand-500 focus:border-brand-500 dark:bg-gray-800 dark:border-gray-600"
                                                        :class="row.is_base_unit ? 'bg-gray-100 text-gray-500' : ''">
                                                </div>
                                                <div class="w-24 sm:flex-1">
                                                    <label
                                                        class="text-[10px] text-gray-500 uppercase font-bold mb-1 block">Unit</label>
                                                    <select x-model="row.unit_id"
                                                        class="w-full text-sm rounded-md border-gray-300 py-1.5 px-2 focus:ring-1 focus:ring-brand-500 focus:border-brand-500 dark:bg-gray-800 dark:border-gray-600">
                                                        <option value="">Select</option>
                                                        <template x-for="u in units" :key="u.id">
                                                            <option :value="u.id" x-text="u.name"></option>
                                                        </template>
                                                    </select>
                                                </div>
                                                <div class="w-28 sm:flex-1">
                                                    <label
                                                        class="text-[10px] text-gray-500 uppercase font-bold mb-1 block">Buy
                                                        Price</label>
                                                    <input type="text" :value="formatNumber(row.buy_price)"
                                                        @input="formatInputCurrency($event); row.buy_price = getRawValue($event.target.value)"
                                                        class="w-full text-sm rounded-md border-gray-300 py-1.5 px-2 focus:ring-1 focus:ring-brand-500 focus:border-brand-500 dark:bg-gray-800 dark:border-gray-600">
                                                </div>
                                                <div class="w-28 sm:flex-1">
                                                    <label
                                                        class="text-[10px] text-gray-500 uppercase font-bold mb-1 block">Sell
                                                        Price</label>
                                                    <input type="text" :value="formatNumber(row.sell_price)"
                                                        @input="formatInputCurrency($event); row.sell_price = getRawValue($event.target.value)"
                                                        class="w-full text-sm rounded-md border-gray-300 py-1.5 px-2 focus:ring-1 focus:ring-brand-500 focus:border-brand-500 font-semibold text-green-700 bg-green-50/50 dark:bg-green-900/20 dark:text-green-400 dark:border-green-800">
                                                </div>
                                                <div class="w-20 sm:flex-1">
                                                    <label
                                                        class="text-[10px] text-gray-500 uppercase font-bold mb-1 block">Weight
                                                        (g)</label>
                                                    <input type="number" x-model="row.weight" step="0.01"
                                                        class="w-full text-sm rounded-md border-gray-300 py-1.5 px-2 focus:ring-1 focus:ring-brand-500 focus:border-brand-500 bg-gray-50 dark:bg-gray-800 dark:border-gray-600">
                                                </div>
                                                <div class="w-8 flex items-center justify-center pb-1">
                                                    <button @click="removeUnitRow(index)"
                                                        class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-all"
                                                        title="Remove">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                            </path>
                                                        </svg>
                                                    </button>
                                                </div>
                                            </div>
                                        </template>
                                        <div x-show="currentProduct.units.length > 0" class="flex justify-end pt-2">
                                            <button @click="autoCalculatePrices()"
                                                class="text-xs text-blue-600 hover:underline flex items-center gap-1">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                                                    </path>
                                                </svg>
                                                Auto-calculate Prices
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div
                        class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-100 dark:bg-gray-800 flex justify-end gap-3">
                        <button @click="showModal = false"
                            class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-500 transition-colors">
                            Cancel
                        </button>
                        <button @click="saveProduct()"
                            class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 shadow-sm transition-colors font-medium">
                            Save Product
                        </button>
                    </div>
                </div>
            </div>

            <!-- Scanner Modal Removed -->

        </div>

        @push('scripts')
            <script>
                function inventoryPage() {
                    return {
                        page: 'inventory',
                        async addMasterUnit() {
                            const { value: unitName } = await Swal.fire({
                                title: 'Tambah Satuan Baru',
                                input: 'text',
                                inputLabel: 'Nama Satuan (misal: Karton, Lusin)',
                                inputPlaceholder: 'Masukan nama satuan',
                                showCancelButton: true,
                                confirmButtonText: 'Simpan',
                                cancelButtonText: 'Batal',
                                confirmButtonColor: '#4F46E5',
                                backdrop: true,
                                customClass: { container: 'swal-above-modal' },
                                inputValidator: (value) => { if (!value) return 'Nama satuan tidak boleh kosong!' }
                            });

                            if (unitName) {
                                try {
                                    const token = localStorage.getItem('saga_token');
                                    const response = await fetch('/api/products/units', {
                                        method: 'POST',
                                        headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + token },
                                        body: JSON.stringify({ name: unitName })
                                    });
                                    const data = await response.json();

                                    if (data.success) {
                                        await this.fetchUnits();
                                        Swal.fire({ icon: 'success', title: 'Berhasil', text: 'Satuan berhasil ditambahkan', toast: true, position: 'top-end', timer: 3000, showConfirmButton: false });
                                    } else {
                                        Swal.fire({ icon: 'error', title: 'Gagal', text: data.message || 'Gagal menambah satuan' });
                                    }
                                } catch (error) {
                                    console.error(error);
                                    Swal.fire({ icon: 'error', title: 'Error', text: 'Terjadi kesalahan saat menambah satuan' });
                                }
                            }
                        },
                        loaded: true,
                        darkMode: false,
                        products: [],
                        categories: [],
                        units: [],
                        selectedCategory: '',
                        searchQuery: '',
                        showLowStock: false,
                        sortBy: 'name_asc',
                        currentPage: 1,
                        totalPages: 1,
                        totalItems: 0,
                        itemsPerPage: 40,
                        isLoading: true,
                        showModal: false,
                        editMode: false,
                        isUploading: false,
                        currentProduct: { name: '', category_id: '', base_unit_id: '', stock: 0, min_stock: 5, barcode: '', image_url: '', units: [] },
                        generatedSku: '',
                        showExportMenu: false,
                        showSortMenu: false,
                        stockInputValue: 0, // Stock in largest unit

                        // Input Masking Helpers
                        formatInputCurrency(event) {
                            let value = event.target.value.replace(/\D/g, ''); // Remove non-digits
                            if (!value) {
                                event.target.value = '';
                                return;
                            }
                            // Format with dots
                            let formatted = new Intl.NumberFormat('id-ID').format(value);
                            event.target.value = formatted;
                        },

                        // Helper to get raw value from formatted input
                        getRawValue(formattedValue) {
                            if (!formattedValue) return 0;
                            return parseInt(String(formattedValue).replace(/\./g, '')) || 0;
                        },

                        toggleSort(column) {
                            if (this.sortBy.startsWith(column)) {
                                this.sortBy = this.sortBy.endsWith('asc') ? column + '_desc' : column + '_asc';
                            } else {
                                this.sortBy = column + '_asc';
                            }
                            this.fetchProducts();
                        },

                        // Helper for max 2 decimals (strips zeros)
                        formatDecimal(val) {
                            let num = parseFloat(val);
                            if (isNaN(num)) return 0;
                            return parseFloat(num.toFixed(2));
                        },

                        sortOptions: [
                            { value: 'name_asc', label: 'Nama A-Z' },
                            { value: 'name_desc', label: 'Nama Z-A' },
                            { value: 'stock_asc', label: 'Stock Rendah' },
                            { value: 'stock_desc', label: 'Stock Tinggi' },
                            { value: 'price_asc', label: 'Harga Rendah' },
                            { value: 'price_desc', label: 'Harga Tinggi' }
                        ],

                        get sortLabel() {
                            const opt = this.sortOptions.find(o => o.value === this.sortBy);
                            return opt ? opt.label : 'Sort';
                        },

                        async initPage() {
                            this.darkMode = JSON.parse(localStorage.getItem('darkMode'));
                            this.$watch('darkMode', value => localStorage.setItem('darkMode', JSON.stringify(value)));

                            this.showLowStock = new URLSearchParams(window.location.search).get('low_stock') === 'true';
                            await Promise.all([this.fetchProducts(), this.fetchCategories(), this.fetchUnits()]);

                            // Global Scanner Listener (Barcode Wedge)
                            window.addEventListener('keydown', (e) => {
                                // If user is not focusing on an input, and presses a potential barcode char
                                if (!['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement.tagName)) {
                                    // Focus search box 
                                    const searchBox = document.querySelector('input[placeholder*="Search"]');
                                    if (searchBox && e.key.length === 1 && !e.ctrlKey && !e.altKey && !e.metaKey) {
                                        searchBox.focus();
                                        // The key press will naturally be captured by the focused input
                                    }
                                }
                            });

                            // Deep Link Edit Logic
                            const editId = new URLSearchParams(window.location.search).get('edit_product');
                            if (editId) {
                                try {
                                    const pid = parseInt(editId);
                                    if (!isNaN(pid)) {
                                        await this.openEditModal({ id: pid, name: 'Loading...', sku: '' });
                                        const url = new URL(window.location);
                                        url.searchParams.delete('edit_product');
                                        window.history.replaceState({}, '', url);
                                    }
                                } catch (e) { console.error('Deep link error:', e); }
                            }
                        },

                        // Scanner methods removed (Auto-focus logic is in initPage)

                        async fetchProducts(resetPage = true) {
                            try {
                                this.isLoading = true;
                                if (resetPage) this.currentPage = 1;
                                const token = localStorage.getItem('saga_token');
                                let url = `/api/products?page=${this.currentPage}&limit=${this.itemsPerPage}&sort=${this.sortBy}&`;
                                if (this.selectedCategory) url += 'category_id=' + this.selectedCategory + '&';
                                if (this.searchQuery) url += 'search=' + this.searchQuery + '&';
                                if (this.showLowStock) url += 'low_stock=true';

                                const response = await fetch(url, { headers: { 'Authorization': 'Bearer ' + token } });
                                if (response.ok) {
                                    const data = await response.json();
                                    if (data.success) {
                                        this.products = [...data.data.products];

                                        this.totalPages = data.data.pagination.totalPages || 1;
                                        this.totalItems = data.data.pagination.total || 0;
                                    }
                                }
                            } catch (error) { console.error('Fetch error:', error); }
                            finally { this.isLoading = false; }
                        },

                        async fetchCategories() {
                            const token = localStorage.getItem('saga_token');
                            const response = await fetch('/api/products/categories', { headers: { 'Authorization': 'Bearer ' + token } });
                            const data = await response.json();
                            if (data.success) this.categories = data.data;
                        },

                        async fetchUnits() {
                            const token = localStorage.getItem('saga_token');
                            const response = await fetch('/api/products/units', { headers: { 'Authorization': 'Bearer ' + token } });
                            const data = await response.json();
                            if (data.success) this.units = data.data;
                        },

                        async generateSku() {
                            if (!this.currentProduct.category_id) { this.generatedSku = ''; return; }
                            const token = localStorage.getItem('saga_token');
                            try {
                                console.log('Generating SKU for category:', this.currentProduct.category_id);
                                const response = await fetch('/api/products/generate-sku/' + this.currentProduct.category_id,
                                    { headers: { 'Authorization': 'Bearer ' + token } });

                                if (!response.ok) {
                                    console.error('SKU generation failed:', response.status, await response.text());
                                    this.generatedSku = 'GEN-' + Date.now(); // Fallback SKU
                                    return;
                                }

                                const data = await response.json();
                                if (data.success) {
                                    this.generatedSku = data.data.sku;
                                } else {
                                    console.error('SKU generation error:', data.message);
                                    this.generatedSku = 'GEN-' + Date.now();
                                }
                            } catch (error) {
                                console.error('SKU generation exception:', error);
                                this.generatedSku = 'GEN-' + Date.now(); // Fallback SKU
                            }
                        },

                        openAddModal() {
                            this.editMode = false;
                            this.generatedSku = '';
                            this.stockInputValue = 0;
                            this.currentProduct = { name: '', category_id: '', base_unit_id: '', stock: 0, min_stock: 5, barcode: '', image_url: '', units: [] };
                            this.showModal = true;
                        },

                        async openEditModal(product) {
                            this.editMode = true;
                            this.generatedSku = product.sku;
                            this.stockInputValue = 0; // Reset visual stock

                            const token = localStorage.getItem('saga_token');
                            try {
                                const response = await fetch('/api/products/' + product.id, { headers: { 'Authorization': 'Bearer ' + token } });
                                const data = await response.json();
                                if (data.success && data.data) {
                                    const p = data.data;
                                    this.currentProduct = {
                                        ...p,
                                        units: (p.units || []).map(u => ({
                                            ...u,
                                            conversion_qty: this.formatDecimal(u.conversion_qty),
                                            weight: this.formatDecimal(u.weight),
                                            buy_price: Math.round(parseFloat(u.buy_price)),
                                            sell_price: Math.round(parseFloat(u.sell_price))
                                        }))
                                    };
                                    this.stockInputValue = this.formatDecimal(this.convertStockToLargest(p.stock));
                                }
                            } catch (error) { console.error('Error loading product:', error); }
                            this.showModal = true;
                        },

                        async deleteAllProducts() {
                            const result = await Swal.fire({
                                title: 'Are you sure?',
                                text: "You are about to delete ALL products. This cannot be undone!",
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonColor: '#d33',
                                cancelButtonColor: '#3085d6',
                                confirmButtonText: 'Yes, delete everything!'
                            });

                            if (result.isConfirmed) {
                                // Double confirmation
                                const finalCheck = await Swal.fire({
                                    title: 'Final Warning',
                                    text: "Type 'DELETE' to confirm.",
                                    input: 'text',
                                    icon: 'warning',
                                    showCancelButton: true,
                                    confirmButtonColor: '#d33'
                                });

                                if (finalCheck.value === 'DELETE') {
                                    try {
                                        const token = localStorage.getItem('saga_token');
                                        const response = await fetch('/api/products/actions/delete-all', {
                                            method: 'DELETE',
                                            headers: {
                                                'Authorization': 'Bearer ' + token,
                                                'Content-Type': 'application/json',
                                                'Accept': 'application/json'
                                            }
                                        });
                                        const data = await response.json();

                                        if (data.success) {
                                            await this.fetchProducts(true);
                                            Swal.fire('Deleted!', 'All products have been deleted.', 'success');
                                        } else {
                                            Swal.fire('Error', data.message || 'Failed to delete all products.', 'error');
                                        }
                                    } catch (e) { console.error(e); Swal.fire('Error', 'Server Error', 'error'); }
                                }
                            }
                        },

                        addUnitRow() {
                            const isFirst = this.currentProduct.units.length === 0;
                            this.currentProduct.units.push({
                                unit_id: '', conversion_qty: 1, buy_price: 0, sell_price: 0, weight: 0, is_base_unit: isFirst
                            });
                        },

                        removeUnitRow(index) {
                            this.currentProduct.units.splice(index, 1);
                            if (this.currentProduct.units.length > 0) {
                                this.currentProduct.units[0].is_base_unit = true;
                                this.currentProduct.units[0].conversion_qty = 1;
                                this.currentProduct.base_unit_id = this.currentProduct.units[0].unit_id;
                            }
                        },

                        autoCalculatePrices() {
                            if (this.currentProduct.units.length === 0) return;
                            const largestUnit = this.currentProduct.units[this.currentProduct.units.length - 1];
                            const factorBase = parseFloat(largestUnit.conversion_qty) || 1;

                            const baseBuyPrice = (parseFloat(largestUnit.buy_price) || 0) / factorBase;
                            const baseSellPrice = (parseFloat(largestUnit.sell_price) || 0) / factorBase;
                            const baseWeight = (parseFloat(largestUnit.weight) || 0) / factorBase;

                            this.currentProduct.units.forEach((unit, i) => {
                                if (i < this.currentProduct.units.length - 1) { // Skip largest
                                    const factor = parseFloat(unit.conversion_qty) || 1;
                                    unit.buy_price = Math.round(baseBuyPrice * factor);
                                    unit.sell_price = Math.round(baseSellPrice * factor);
                                    unit.weight = this.formatDecimal(baseWeight * factor);
                                }
                            });
                        },

                        async saveProduct() {
                            if (this.currentProduct.units.length === 0) {
                                Swal.fire({ icon: 'warning', title: 'Warning', text: 'Add at least 1 unit' });
                                return;
                            }
                            this.currentProduct.stock = this.convertStockToBase(parseFloat(this.stockInputValue) || 0);

                            const token = localStorage.getItem('saga_token');
                            const url = this.editMode ? '/api/products/' + this.currentProduct.id : '/api/products';
                            const method = this.editMode ? 'PUT' : 'POST';
                            const selectedBranch = localStorage.getItem('saga_selected_branch');

                            const body = { ...this.currentProduct, branch_id: selectedBranch ? parseInt(selectedBranch) : null };

                            const response = await fetch(url, {
                                method,
                                headers: { 'Authorization': 'Bearer ' + token, 'Content-Type': 'application/json' },
                                body: JSON.stringify(body)
                            });
                            const data = await response.json();

                            if (data.success) {
                                this.showModal = false;
                                await this.fetchProducts(false);
                                Swal.fire({ icon: 'success', title: 'Saved', text: 'Product saved successfully', toast: true, position: 'top-end', timer: 1500, showConfirmButton: false });
                            } else {
                                Swal.fire({ icon: 'error', title: 'Error', text: data.message || 'Failed to save' });
                            }
                        },

                        async deleteProduct(id) {
                            const result = await Swal.fire({
                                title: 'Are you sure?', text: "You won't be able to revert this!", icon: 'warning',
                                showCancelButton: true, confirmButtonColor: '#d33', cancelButtonColor: '#3085d6', confirmButtonText: 'Yes, delete it!'
                            });
                            if (result.isConfirmed) {
                                const token = localStorage.getItem('saga_token');
                                await fetch('/api/products/' + id, { method: 'DELETE', headers: { 'Authorization': 'Bearer ' + token } });
                                await this.fetchProducts(false);
                                Swal.fire({ icon: 'success', title: 'Deleted!', text: 'Product has been deleted.', toast: true, position: 'top-end', timer: 1500, showConfirmButton: false });
                            }
                        },

                        getLargestUnit() {
                            if (!this.currentProduct.units?.length) return null;
                            return this.currentProduct.units.reduce((max, u) => (parseFloat(u.conversion_qty) >= parseFloat(max.conversion_qty) ? u : max), this.currentProduct.units[0]);
                        },

                        getStockUnitLabel() {
                            if (!this.currentProduct.units || this.currentProduct.units.length === 0) return 'Stock (Base Unit)';
                            const largest = this.getLargestUnit();
                            if (!largest || !largest.unit_id) return 'Stock (Input)';

                            const unitObj = this.units.find(u => u.id == largest.unit_id);
                            return unitObj ? `Stock (in ${unitObj.name})` : 'Stock (Largest Unit)';
                        },

                        convertStockToLargest(baseStock) {
                            const largest = this.getLargestUnit();
                            return baseStock / (parseFloat(largest?.conversion_qty) || 1);
                        },

                        convertStockToBase(largestStock) {
                            const largest = this.getLargestUnit();
                            return largestStock * (parseFloat(largest?.conversion_qty) || 1);
                        },

                        formatCurrency(amount) {
                            return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(amount || 0);
                        },

                        formatNumber(num) {
                            // Users request: Thousand separators, max 2 decimals if > 0
                            return new Intl.NumberFormat('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 }).format(num || 0);
                        },

                        getBasePrice(product) {
                            if (!product.units || !product.units.length) return 0;
                            const base = product.units.find(u => u.is_base_unit);
                            return base ? base.sell_price : product.units[0].sell_price;
                        },

                        getUnitName(unitId) {
                            // unitId might be absent if using relationship logic.
                            // But for now, let's fix the call site to use product.units
                            if (!unitId) return '-';
                            return this.units.find(u => u.id == unitId)?.name || '-';
                        },

                        goToPage(p) { this.currentPage = p; this.fetchProducts(false); },
                        prevPage() { if (this.currentPage > 1) this.goToPage(this.currentPage - 1); },
                        nextPage() { if (this.currentPage < this.totalPages) this.goToPage(this.currentPage + 1); },

                        get paginationRange() {
                            const range = [];
                            const delta = 2;
                            const left = Math.max(1, this.currentPage - delta);
                            const right = Math.min(this.totalPages, this.currentPage + delta);
                            if (left > 1) range.push({ type: 'page', value: 1 });
                            if (left > 2) range.push({ type: 'ellipsis', value: '...' });
                            for (let i = left; i <= right; i++) range.push({ type: 'page', value: i });
                            if (right < this.totalPages - 1) range.push({ type: 'ellipsis', value: '...' });
                            if (right < this.totalPages) range.push({ type: 'page', value: this.totalPages });
                            return range;
                        },

                        async uploadProductImage(event) {
                            const file = event.target.files[0];
                            if (!file) return;
                            if (file.size > 5 * 1024 * 1024) { Swal.fire({ icon: 'error', title: 'Error', text: 'Max file size 5MB' }); return; }

                            this.isUploading = true;
                            const formData = new FormData();
                            formData.append('image', file);
                            const token = localStorage.getItem('saga_token');

                            try {
                                const res = await fetch('/api/upload/product-image', {
                                    method: 'POST', headers: { 'Authorization': 'Bearer ' + token }, body: formData
                                });
                                const data = await res.json();
                                if (data.success) {
                                    this.currentProduct.image_url = data.data.url;
                                    Swal.fire({ icon: 'success', title: 'Uploaded', toast: true, position: 'top-end', timer: 1500, showConfirmButton: false });
                                }
                            } catch (e) { console.error(e); }
                            finally { this.isUploading = false; event.target.value = ''; }
                        },

                        triggerImport() { document.getElementById('importFileInput').click(); },

                        async downloadFile(endpoint, filename) {
                            const token = localStorage.getItem('saga_token');
                            try {
                                const response = await fetch(endpoint, {
                                    headers: { 'Authorization': 'Bearer ' + token }
                                });
                                if (response.ok) {
                                    const blob = await response.blob();
                                    const url = window.URL.createObjectURL(blob);
                                    const a = document.createElement('a');
                                    a.href = url;
                                    a.download = filename;
                                    document.body.appendChild(a);
                                    a.click();
                                    window.URL.revokeObjectURL(url);
                                } else {
                                    Swal.fire('Error', 'Failed to download file', 'error');
                                }
                            } catch (error) {
                                console.error('Download error:', error);
                                Swal.fire('Error', 'Server error during download', 'error');
                            }
                        },

                        downloadTemplate() {
                            this.downloadFile('/api/product-exports/template', 'template_import_produk.xlsx');
                        },

                        exportExcel() {
                            this.downloadFile('/api/product-exports/excel', 'products_' + new Date().toISOString().split('T')[0] + '.xlsx');
                        },

                        exportPDF() {
                            this.downloadFile('/api/product-exports/pdf', 'products_' + new Date().toISOString().split('T')[0] + '.pdf');
                        },

                        async handleImport(event) {
                            const file = event.target.files[0];
                            if (!file) return;

                            const formData = new FormData();
                            formData.append('file', file);
                            const token = localStorage.getItem('saga_token');

                            try {
                                Swal.fire({ title: 'Processing...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
                                const res = await fetch('/api/products/import', { method: 'POST', headers: { 'Authorization': 'Bearer ' + token }, body: formData });
                                const data = await res.json();
                                if (data.success) {
                                    Swal.fire({ icon: 'success', title: 'Import Successful', text: data.message });
                                    this.fetchProducts(true);
                                } else {
                                    Swal.fire({ icon: 'error', title: 'Import Failed', text: data.message });
                                }
                            } catch (e) { console.error(e); Swal.fire({ icon: 'error', title: 'Error', text: 'Server error' }); }
                            finally { event.target.value = ''; }
                        }
                    }
                }
            </script>
        @endpush
@endsection