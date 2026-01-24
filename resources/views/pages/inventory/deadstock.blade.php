@extends('layouts.app')

@section('title', 'Deadstock | SAGA TOKO APP')

@section('content')
    <div x-data="{
            page: 'deadstock',
            products: [],
            isLoading: true,

            async init() {
                await this.fetchDeadstock();
            },

            async fetchDeadstock() {
                try {
                    const token = localStorage.getItem('saga_token');
                    // Fetch products with low_stock=true, then filter locally for strict 0 stock
                    // This matches the reference implementation logic
                    const response = await fetch('/api/products?low_stock=true', { headers: { 'Authorization': 'Bearer ' + token } });
                    const data = await response.json();
                    if (data.success) {
                        this.products = data.data.products.filter(p => parseFloat(p.stock) <= 0);
                    }
                } catch (error) {
                    console.error('Fetch error:', error);
                } finally {
                    this.isLoading = false;
                }
            },

            formatCurrency(amount) {
                return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(amount || 0);
            }
        }" x-init="init()">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Deadstock</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Produk dengan stock 0 atau tidak bergerak</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            <template x-for="p in products" :key="p.id">
                <div class="rounded-2xl border border-red-200 bg-red-50 p-5 dark:border-red-900 dark:bg-red-900/20">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center dark:bg-red-900/30">
                            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                                </path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-800 dark:text-white" x-text="p.name">Product</h3>
                            <p class="text-xs text-brand-600 font-mono" x-text="p.sku || '-'">SKU</p>
                        </div>
                    </div>
                    <div class="flex justify-between items-center pt-3 border-t border-red-200 dark:border-red-800">
                        <span class="text-sm text-gray-500">Stock</span>
                        <span class="text-xl font-bold text-red-600" x-text="p.stock">0</span>
                    </div>
                    {{-- Note: This link points to a page we assume exists or will exist: Goods In --}}
                    <a :href="'{{ url('/inventory/receiving/goods-in') }}?restock=' + p.id"
                        class="mt-3 block text-center py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 text-sm font-medium transition-colors">
                        Restock
                    </a>
                </div>
            </template>
        </div>

        <div x-show="isLoading" class="text-center py-12 text-gray-400">Loading...</div>
        <div x-show="!isLoading && products.length === 0" class="text-center py-12 text-gray-400">
            <svg class="w-16 h-16 mx-auto mb-4 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <p class="text-green-600 font-medium">Tidak ada deadstock!</p>
            <p class="text-sm">Semua produk memiliki stock yang cukup</p>
        </div>
    </div>
@endsection