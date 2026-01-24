@extends('layouts.app')

@section('title', 'Point of Sales | SAGA TOKO APP')

@section('content')
<div class="h-[calc(100vh-90px)] -m-4 md:-m-6 p-4 md:p-4 flex flex-col md:flex-row gap-4" x-data="posSystem()">
    <!-- Left Column: Product Grid -->
    <div class="flex-1 flex flex-col bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden h-full">
        <!-- Search & Category Filter -->
        <div class="p-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 flex flex-col sm:flex-row gap-3">
            <div class="relative flex-1">
                <input type="text" x-model="searchQuery" @input.debounce="fetchProducts()" placeholder="Scan barcode or search item..."
                    class="w-full pl-10 pr-4 py-2.5 border border-gray-200 rounded-xl dark:bg-gray-800 dark:border-gray-600 dark:text-white focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </div>
            <select x-model="selectedCategory" @change="fetchProducts()" class="px-4 py-2.5 border border-gray-200 rounded-xl dark:bg-gray-800 dark:border-gray-600 dark:text-white">
                <option value="">All Categories</option>
                <template x-for="cat in categories" :key="cat.id">
                    <option :value="cat.id" x-text="cat.name"></option>
                </template>
            </select>
        </div>

        <!-- Product Grid -->
        <div class="flex-1 overflow-y-auto p-4 bg-gray-100/50 dark:bg-gray-900">
            <div x-show="isLoading" class="flex justify-center items-center h-full">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-brand-600"></div>
            </div>
            
            <div x-show="!isLoading && products.length === 0" class="flex flex-col justify-center items-center h-full text-gray-500">
                <svg class="w-16 h-16 mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                </svg>
                <p class="text-lg font-medium">No products found</p>
                <p class="text-sm text-gray-400">Try adjusting your search or category.</p>
            </div>

            <div x-show="!isLoading && products.length > 0" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                <template x-for="product in products" :key="product.id">
                    <div @click="addToCart(product)" 
                        class="bg-white dark:bg-gray-800 rounded-xl p-3 cursor-pointer shadow-sm hover:shadow-md border border-gray-100 dark:border-gray-700 transition-all group relative overflow-hidden">
                        <div class="aspect-square rounded-lg bg-gray-100 dark:bg-gray-700 mb-3 relative overflow-hidden">
                            <img :src="product.image_url || 'https://placehold.co/100?text=No+Img'" class="w-full h-full object-cover">
                            <div class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                <span class="text-white font-medium text-sm">Add +</span>
                            </div>
                        </div>
                        <h4 class="font-medium text-gray-800 dark:text-white text-sm line-clamp-2 min-h-[2.5em]" x-text="product.name"></h4>
                        <div class="flex justify-between items-end mt-2">
                            <div class="flex flex-col">
                                <span class="text-xs text-gray-500 dark:text-gray-400" x-text="product.unit_name"></span>
                                <span class="font-bold text-brand-600 dark:text-brand-400 text-sm" x-text="formatCurrency(product.price)"></span>
                            </div>
                            <div class="text-xs font-medium px-1.5 py-0.5 rounded bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300" 
                                x-text="'Stok: ' + formatNumber(product.stock)"></div>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <!-- Right Column: Cart -->
    <div class="w-full md:w-96 flex flex-col bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 h-[40vh] md:h-full">
        <!-- Cart Header -->
        <div class="p-4 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center bg-gray-50 dark:bg-gray-900/50 rounded-t-xl">
            <h3 class="font-bold text-lg text-gray-800 dark:text-white flex items-center gap-2">
                <svg class="w-5 h-5 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
                Current Order
            </h3>
            <button @click="clearCart()" class="text-xs text-red-500 hover:text-red-700 font-medium">Clear All</button>
        </div>

        <!-- Cart Items -->
        <div class="flex-1 overflow-y-auto p-4 space-y-3 relative">
            <template x-if="cart.length === 0">
                <div class="absolute inset-0 flex flex-col items-center justify-center text-gray-400 p-8 text-center">
                    <svg class="w-16 h-16 mb-4 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                    </svg>
                    <p class="text-sm">Cart is empty.</p>
                    <p class="text-xs mt-1">Select products to start selling.</p>
                </div>
            </template>
            
            <template x-for="(item, index) in cart" :key="index">
                <div class="flex items-start gap-3 p-3 bg-gray-50 dark:bg-gray-700/30 rounded-lg group">
                    <div class="flex-1 min-w-0">
                        <h5 class="text-sm font-medium text-gray-800 dark:text-white truncate" x-text="item.name"></h5>
                        <div class="flex items-center gap-2 mt-1">
                            <span class="text-xs text-brand-600 font-medium" x-text="formatCurrency(item.price)"></span>
                            <span class="text-xs text-gray-400" x-text="'x ' + item.qty"></span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <button @click="updateQty(index, -1)" class="w-6 h-6 rounded bg-white hover:bg-gray-100 border border-gray-200 flex items-center justify-center text-gray-600 shadow-sm transition-colors">-</button>
                        <span class="text-sm font-bold w-6 text-center" x-text="item.qty"></span>
                        <button @click="updateQty(index, 1)" class="w-6 h-6 rounded bg-white hover:bg-gray-100 border border-gray-200 flex items-center justify-center text-gray-600 shadow-sm transition-colors">+</button>
                    </div>
                    <div class="text-right min-w-[60px]">
                        <div class="text-sm font-bold text-gray-800 dark:text-white" x-text="formatCurrency(item.price * item.qty)"></div>
                        <button @click="removeFromCart(index)" class="text-[10px] text-red-400 hover:text-red-600 mt-1 opacity-0 group-hover:opacity-100 transition-opacity">Remove</button>
                    </div>
                </div>
            </template>
        </div>

        <!-- Footer / Checkout -->
        <div class="p-4 border-t border-gray-100 dark:border-gray-700 bg-white dark:bg-gray-800 rounded-b-xl shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)] z-10">
            <div class="space-y-2 mb-4">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Subtotal</span>
                    <span class="font-medium text-gray-800 dark:text-white" x-text="formatCurrency(subtotal)"></span>
                </div>
                <!-- Tax or Discount can go here -->
                <div class="flex justify-between text-lg font-bold">
                    <span class="text-gray-800 dark:text-white">Total</span>
                    <span class="text-brand-600 dark:text-brand-400" x-text="formatCurrency(total)"></span>
                </div>
            </div>
            
            <button @click="processCheckout()" :disabled="cart.length === 0"
                class="w-full py-3 bg-brand-600 text-white rounded-xl font-bold hover:bg-brand-700 shadow-lg shadow-brand-500/30 transition-all transform active:scale-95 disabled:opacity-50 disabled:shadow-none flex items-center justify-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
                Pay Now
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
function posSystem() {
    return {
        products: [],
        categories: [],
        cart: [],
        searchQuery: '',
        selectedCategory: '',
        isLoading: false,
        
        get subtotal() {
            return this.cart.reduce((sum, item) => sum + (item.price * item.qty), 0);
        },
        get total() {
            return this.subtotal; // Add tax calc here later
        },

        async init() {
            await this.fetchCategories();
            await this.fetchProducts();
        },

        async fetchCategories() {
            // Mock or API
            // this.categories = await (await fetch('/api/products/categories')).json();
            // Fallback mock
            this.categories = [
                { id: 1, name: 'Makanan' }, { id: 2, name: 'Minuman' }, { id: 3, name: 'Sembako' }
            ];
        },

        async fetchProducts() {
            this.isLoading = true;
            try {
                // Simulate API call or real API
                const token = localStorage.getItem('saga_token');
                let url = '/api/products?limit=50';
                if(this.searchQuery) url += '&search=' + this.searchQuery;
                
                const response = await fetch(url, { headers: { 'Authorization': 'Bearer ' + token } });
                const data = await response.json();
                if(data.success) {
                    this.products = data.data.products.map(p => ({
                        id: p.id,
                        name: p.name,
                        price: parseFloat(p.units?.[0]?.sell_price || 0), // Simplification: pick first unit
                        stock: parseFloat(p.stock),
                        image_url: p.image_url,
                        unit_name: p.units?.[0]?.unit?.name || 'Pcs'
                    }));
                }
            } catch(e) {
                console.error(e);
            } finally {
                this.isLoading = false;
            }
        },

        addToCart(product) {
            const index = this.cart.findIndex(i => i.id === product.id);
            if(index !== -1) {
                this.cart[index].qty++;
            } else {
                this.cart.push({ ...product, qty: 1 });
            }
        },

        updateQty(index, change) {
            if(this.cart[index].qty + change <= 0) {
                this.removeFromCart(index);
            } else {
                this.cart[index].qty += change;
            }
        },

        removeFromCart(index) {
            this.cart.splice(index, 1);
        },

        clearCart() {
            if(this.cart.length > 0) {
                Swal.fire({
                    title: 'Clear Cart?', icon: 'warning', showCancelButton: true, confirmButtonText: 'Yes', confirmButtonColor: '#d33'
                }).then(res => { if(res.isConfirmed) this.cart = []; });
            }
        },

        processCheckout() {
            Swal.fire({
                title: 'Checkout',
                text: 'Total: ' + this.formatCurrency(this.total),
                icon: 'info',
                showCancelButton: true,
                confirmButtonText: 'Confirm Payment',
                confirmButtonColor: '#4F46E5' // Indigo/Brand
            }).then(result => {
                if(result.isConfirmed) {
                    // Call API to create transaction
                    Swal.fire('Success!', 'Transaction completed.', 'success');
                    this.cart = [];
                }
            });
        },

        formatCurrency(amount) {
            return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(amount);
        },
        formatNumber(num) {
            return new Intl.NumberFormat('id-ID').format(num);
        }
    }
}
</script>
@endpush
@endsection