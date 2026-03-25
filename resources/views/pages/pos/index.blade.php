@extends('layouts.app')

@section('title', 'Kasir | SAGA POS')

@section('content')
<div class="h-[calc(100vh-90px)] -m-4 md:-m-6 p-4 md:p-4 flex flex-col lg:flex-row gap-4" x-data="posSystem()">
    <!-- Left Column: Product Grid -->
    <div class="flex-1 flex flex-col bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden h-full">
        <!-- Search & Category Filter -->
        <div class="p-4 border-b border-gray-100 dark:border-gray-700 bg-gradient-to-r from-brand-50 to-indigo-50 dark:from-gray-900 dark:to-gray-900 flex flex-col sm:flex-row gap-3">
            <div class="relative flex-1">
                <input type="text" 
                    x-model="searchQuery" 
                    @input.debounce="fetchProducts()" 
                    placeholder="🔍 Scan barcode atau cari produk..."
                    class="w-full pl-12 pr-4 py-3 border-2 border-gray-200 rounded-xl dark:bg-gray-800 dark:border-gray-600 dark:text-white focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all text-sm font-medium">
                <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </div>
            <select x-model="selectedCategory" @change="fetchProducts()" class="px-4 py-3 border-2 border-gray-200 rounded-xl dark:bg-gray-800 dark:border-gray-600 dark:text-white font-medium focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all">
                <option value="">📂 Semua Kategori</option>
                <template x-for="cat in categories" :key="cat.id">
                    <option :value="cat.id" x-text="cat.name"></option>
                </template>
            </select>
            <button @click="toggleGridView()" class="px-4 py-3 border-2 border-gray-200 rounded-xl dark:bg-gray-800 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 transition-all" title="Toggle View">
                <svg class="w-5 h-5 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                </svg>
            </button>
        </div>

        <!-- Product Grid -->
        <div class="flex-1 overflow-y-auto p-4 bg-gray-50/50 dark:bg-gray-900">
            <!-- Loading State -->
            <div x-show="isLoading" class="flex justify-center items-center h-full">
                <div class="relative">
                    <div class="animate-spin rounded-full h-12 w-12 border-4 border-brand-200"></div>
                    <div class="animate-spin rounded-full h-12 w-12 border-t-4 border-brand-600 absolute top-0 left-0"></div>
                </div>
            </div>

            <!-- Empty State -->
            <div x-show="!isLoading && products.length === 0" class="flex flex-col justify-center items-center h-full text-gray-500 dark:text-gray-400">
                <div class="w-24 h-24 mb-6 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center">
                    <svg class="w-12 h-12 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                </div>
                <p class="text-xl font-semibold mb-2">Tidak ada produk</p>
                <p class="text-sm text-gray-400">Coba sesuaikan pencarian atau kategori Anda.</p>
            </div>

            <!-- Product Grid -->
            <div x-show="!isLoading && products.length > 0" 
                :class="gridView === 'grid' ? 'grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4' : 'grid grid-cols-1 gap-3'"
                class="transition-all duration-300">
                <template x-for="product in products" :key="product.id">
                    <div @click="addToCart(product)"
                        class="bg-white dark:bg-gray-800 rounded-xl p-4 cursor-pointer shadow-sm hover:shadow-xl hover:-translate-y-1 border border-gray-100 dark:border-gray-700 transition-all duration-200 group relative overflow-hidden">
                        <!-- Hover Overlay -->
                        <div class="absolute inset-0 bg-gradient-to-t from-brand-600/90 to-transparent opacity-0 group-hover:opacity-100 transition-opacity flex items-end justify-center pb-4">
                            <span class="text-white font-bold text-sm flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                Tambah ke Keranjang
                            </span>
                        </div>
                        
                        <!-- Product Image -->
                        <div class="aspect-square rounded-xl bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-700 dark:to-gray-600 mb-3 relative overflow-hidden">
                            <img :src="product.image_url || 'https://placehold.co/200x200?text=No+Img'" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300">
                            <!-- Stock Badge -->
                            <div class="absolute top-2 right-2 px-2 py-1 rounded-full text-xs font-bold"
                                :class="product.stock > 10 ? 'bg-green-100 text-green-700' : product.stock > 0 ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700'">
                                <span x-text="product.stock > 0 ? 'Stok: ' + product.stock : 'Habis'"></span>
                            </div>
                        </div>
                        
                        <!-- Product Info -->
                        <h4 class="font-bold text-gray-800 dark:text-white text-sm line-clamp-2 min-h-[2.5em] mb-2" x-text="product.name"></h4>
                        <div class="flex justify-between items-end">
                            <div class="flex flex-col">
                                <span class="text-xs text-gray-500 dark:text-gray-400 mb-1" x-text="product.units?.[0]?.name || 'Pcs'"></span>
                                <span class="font-bold text-brand-600 dark:text-brand-400 text-base" x-text="formatCurrency(product.units?.[0]?.price)"></span>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <!-- Right Column: Cart -->
    <div class="w-full lg:w-96 flex flex-col bg-white dark:bg-gray-800 rounded-2xl shadow-xl border-2 border-gray-200 dark:border-gray-700 h-[40vh] lg:h-full">
        <!-- Cart Header -->
        <div class="p-4 border-b-2 border-gray-100 dark:border-gray-700 bg-gradient-to-r from-brand-50 to-indigo-50 dark:from-gray-900 dark:to-gray-900 flex justify-between items-center rounded-t-2xl">
            <h3 class="font-bold text-lg text-gray-800 dark:text-white flex items-center gap-2">
                <svg class="w-6 h-6 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
                Keranjang
            </h3>
            <button @click="clearCart()" class="text-xs text-red-500 hover:text-red-700 font-bold px-3 py-1.5 rounded-lg bg-red-50 dark:bg-red-900/20 hover:bg-red-100 dark:hover:bg-red-900/30 transition-all">
                🗑️ Hapus Semua
            </button>
        </div>

        <!-- Cart Items -->
        <div class="flex-1 overflow-y-auto p-4 space-y-3 relative">
            <!-- Empty Cart State -->
            <template x-if="cart.length === 0">
                <div class="absolute inset-0 flex flex-col items-center justify-center text-gray-400 p-8 text-center">
                    <div class="w-20 h-20 mb-4 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center">
                        <svg class="w-10 h-10 text-gray-300 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                        </svg>
                    </div>
                    <p class="text-sm font-semibold mb-1">Keranjang Kosong</p>
                    <p class="text-xs text-gray-400">Pilih produk untuk mulai menjual</p>
                </div>
            </template>

            <!-- Cart Items -->
            <template x-for="(item, index) in cart" :key="index">
                <div class="flex items-start gap-3 p-3 bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-700/30 dark:to-gray-700/50 rounded-xl group border border-gray-100 dark:border-gray-600 hover:border-brand-300 dark:hover:border-brand-500 transition-all">
                    <div class="flex-1 min-w-0">
                        <h5 class="text-sm font-bold text-gray-800 dark:text-white truncate" x-text="item.name"></h5>
                        <div class="flex items-center gap-2 mt-1.5 flex-wrap">
                            <template x-if="item.units.length > 1">
                                <select @change="changeUnit(index, $event.target.value)"
                                    class="text-[10px] py-1 px-2 border border-gray-200 rounded-lg bg-white dark:bg-gray-800 dark:border-gray-600 font-medium">
                                    <template x-for="u in item.units" :key="u.id">
                                        <option :value="u.id" :selected="u.id === item.unitId" x-text="u.name"></option>
                                    </template>
                                </select>
                            </template>
                            <template x-if="item.units.length === 1">
                                <span class="text-[10px] text-gray-500 font-medium" x-text="item.unitName"></span>
                            </template>
                            <span class="text-xs text-brand-600 font-bold" x-text="formatCurrency(item.price)"></span>
                            <span class="text-xs bg-gray-200 dark:bg-gray-600 px-2 py-0.5 rounded-full font-bold" x-text="'× ' + item.qty"></span>
                        </div>
                    </div>
                    <div class="flex flex-col items-end gap-2">
                        <div class="flex items-center gap-1">
                            <button @click="updateQty(index, -1)" class="w-7 h-7 rounded-lg bg-white hover:bg-red-50 border-2 border-gray-200 dark:border-gray-600 flex items-center justify-center text-gray-600 dark:text-gray-300 font-bold shadow-sm transition-all hover:border-red-300">−</button>
                            <span class="text-sm font-bold w-8 text-center" x-text="item.qty"></span>
                            <button @click="updateQty(index, 1)" class="w-7 h-7 rounded-lg bg-white hover:bg-green-50 border-2 border-gray-200 dark:border-gray-600 flex items-center justify-center text-gray-600 dark:text-gray-300 font-bold shadow-sm transition-all hover:border-green-300">+</button>
                        </div>
                        <div class="text-right">
                            <div class="text-sm font-bold text-gray-800 dark:text-white" x-text="formatCurrency(item.price * item.qty)"></div>
                            <button @click="removeFromCart(index)" class="text-[10px] text-red-400 hover:text-red-600 font-medium mt-1 opacity-0 group-hover:opacity-100 transition-opacity">Hapus</button>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <!-- Footer / Checkout -->
        <div class="p-4 border-t-2 border-gray-100 dark:border-gray-700 bg-gradient-to-r from-brand-50 to-indigo-50 dark:from-gray-900 dark:to-gray-900 rounded-b-2xl shadow-lg z-10">
            <div class="space-y-2 mb-4">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500 dark:text-gray-400">Subtotal</span>
                    <span class="font-bold text-gray-800 dark:text-white" x-text="formatCurrency(subtotal)"></span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500 dark:text-gray-400">Pajak (10%)</span>
                    <span class="font-bold text-gray-800 dark:text-white" x-text="formatCurrency(subtotal * 0.1)"></span>
                </div>
                <div class="flex justify-between text-xl font-bold pt-2 border-t-2 border-gray-200 dark:border-gray-600">
                    <span class="text-gray-800 dark:text-white">Total</span>
                    <span class="text-brand-600 dark:text-brand-400" x-text="formatCurrency(total)"></span>
                </div>
            </div>

            <button @click="processCheckout()" :disabled="cart.length === 0"
                class="w-full py-4 bg-gradient-to-r from-brand-600 to-indigo-600 text-white rounded-xl font-bold text-lg hover:from-brand-700 hover:to-indigo-700 shadow-xl shadow-brand-500/40 transition-all transform active:scale-95 disabled:opacity-50 disabled:shadow-none disabled:cursor-not-allowed flex items-center justify-center gap-3">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
                <span x-text="cart.length === 0 ? 'Pilih Produk' : 'Bayar Sekarang'"></span>
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
        gridView: 'grid',

        get subtotal() {
            return this.cart.reduce((sum, item) => sum + (item.price * item.qty), 0);
        },
        get total() {
            return this.subtotal * 1.1; // 10% tax
        },

        async init() {
            await this.fetchCategories();
            await this.fetchProducts();
        },

        toggleGridView() {
            this.gridView = this.gridView === 'grid' ? 'list' : 'grid';
        },

        async fetchCategories() {
            try {
                const token = localStorage.getItem('saga_token');
                const response = await fetch('/api/products/categories', {
                    headers: { 'Authorization': 'Bearer ' + token }
                });
                const result = await response.json();
                if(result.success) {
                    this.categories = result.data;
                }
            } catch(e) {
                console.error('Error fetching categories:', e);
            }
        },

        async fetchProducts() {
            this.isLoading = true;
            try {
                let url = '/api/products?limit=100';
                if(this.searchQuery) url += '&search=' + encodeURIComponent(this.searchQuery);
                if(this.selectedCategory) url += '&category_id=' + this.selectedCategory;

                const token = localStorage.getItem('saga_token');
                const response = await fetch(url, { headers: { 'Authorization': 'Bearer ' + token } });
                const data = await response.json();
                if(data.success) {
                    this.products = data.data.products.map(p => ({
                        id: p.id,
                        name: p.name,
                        stock: parseFloat(p.stock || 0),
                        image_url: p.image_url,
                        units: (p.units || []).map(u => ({
                            id: u.unit_id,
                            name: u.unit?.name || 'Pcs',
                            price: parseFloat(u.sell_price),
                            conversion_qty: parseFloat(u.conversion_qty)
                        }))
                    }));
                }
            } catch(e) {
                console.error('Error fetching products:', e);
                Swal.fire('Error', 'Gagal memuat produk', 'error');
            } finally {
                this.isLoading = false;
            }
        },

        addToCart(product) {
            if(product.stock <= 0) {
                Swal.fire('Stok Habis', 'Produk ini sedang habis', 'warning');
                return;
            }

            const unit = product.units[0] || { id: null, name: 'Pcs', price: product.price || 0 };
            const cartIndex = this.cart.findIndex(i => i.id === product.id && i.unitId === unit.id);

            if(cartIndex !== -1) {
                if(this.cart[cartIndex].qty >= product.stock) {
                    Swal.fire('Stok Tidak Cukup', 'Jumlah dalam keranjang melebihi stok', 'warning');
                    return;
                }
                this.cart[cartIndex].qty++;
            } else {
                this.cart.push({
                    id: product.id,
                    name: product.name,
                    qty: 1,
                    unitId: unit.id,
                    unitName: unit.name,
                    price: unit.price,
                    units: product.units,
                    maxStock: product.stock
                });
            }

            // Success feedback
            this.showNotification('Produk ditambahkan ke keranjang', 'success');
        },

        updateQty(index, change) {
            const item = this.cart[index];
            if(item.qty + change <= 0) {
                this.removeFromCart(index);
            } else if(item.qty + change > item.maxStock) {
                Swal.fire('Stok Tidak Cukup', 'Stok tersedia: ' + item.maxStock, 'warning');
            } else {
                item.qty += change;
            }
        },

        removeFromCart(index) {
            this.cart.splice(index, 1);
        },

        changeUnit(index, unitId) {
            const item = this.cart[index];
            const unit = item.units.find(u => u.id == unitId);
            if(unit) {
                item.unitId = unit.id;
                item.unitName = unit.name;
                item.price = unit.price;
            }
        },

        clearCart() {
            if(this.cart.length > 0) {
                Swal.fire({
                    title: 'Hapus Keranjang?',
                    text: 'Semua produk akan dihapus dari keranjang',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Hapus',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#6b7280'
                }).then(res => { 
                    if(res.isConfirmed) {
                        this.cart = [];
                        this.showNotification('Keranjang dihapus', 'success');
                    }
                });
            }
        },

        async processCheckout() {
            if (this.cart.length === 0) {
                Swal.fire('Peringatan', 'Keranjang belanja masih kosong', 'warning');
                return;
            }

            const result = await Swal.fire({
                title: '💳 Konfirmasi Pembayaran',
                html: `
                    <div class="text-left">
                        <p class="mb-2"><strong>Total Tagihan:</strong></p>
                        <p class="text-2xl font-bold text-brand-600 mb-4">${this.formatCurrency(this.total)}</p>
                        <p class="text-sm text-gray-600">Metode pembayaran: Tunai</p>
                    </div>
                `,
                icon: 'info',
                showCancelButton: true,
                confirmButtonText: '✅ Bayar Sekarang',
                cancelButtonText: '❌ Batal',
                confirmButtonColor: '#4f46e5',
                cancelButtonColor: '#6b7280',
                reverseButtons: true
            });

            if(result.isConfirmed) {
                this.isLoading = true;
                try {
                    const token = localStorage.getItem('saga_token');
                    
                    if (!token) {
                        Swal.fire('Error', 'Session expired. Silakan login ulang.', 'error');
                        window.location.href = '/signin';
                        return;
                    }

                    // Validate cart items
                    const cartItems = this.cart.map(item => {
                        if (!item.id || !item.price || item.qty <= 0) {
                            throw new Error('Data produk tidak valid: ' + (item.name || 'Unknown'));
                        }
                        return {
                            product_id: item.id,
                            unit_id: item.unitId || null,
                            qty: item.qty,
                            price: item.price,
                            subtotal: item.price * item.qty
                        };
                    });

                    const payload = {
                        cart_items: cartItems,
                        payment_method: 'cash',
                        paid_amount: this.total,
                        customer_id: null,
                        notes: null
                    };

                    console.log('Sending checkout request:', payload);

                    const response = await fetch('/api/transactions', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Authorization': 'Bearer ' + token,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(payload)
                    });

                    // Check if response is HTML (error page)
                    const contentType = response.headers.get('content-type');
                    if (contentType && contentType.includes('text/html')) {
                        throw new Error('Server mengembalikan HTML error page. Session mungkin expired.');
                    }

                    const data = await response.json();
                    
                    if(data.success) {
                        Swal.fire({
                            title: '✅ Berhasil!',
                            html: `
                                <div class="text-left">
                                    <p class="mb-2"><strong>Transaksi Selesai</strong></p>
                                    <p class="text-sm">No Invoice: <strong class="text-brand-600">${data.data.invoice_number}</strong></p>
                                    <p class="text-sm">Total: <strong>${this.formatCurrency(this.total)}</strong></p>
                                </div>
                            `,
                            icon: 'success',
                            showCancelButton: true,
                            confirmButtonText: '🖨️ Cetak Struk',
                            cancelButtonText: '✔️ Selesai',
                            confirmButtonColor: '#4f46e5',
                            cancelButtonColor: '#10b981'
                        }).then((choice) => {
                            if(choice.isConfirmed) {
                                window.open(`/api/transactions/${data.data.id}/receipt`, '_blank');
                            }
                            this.cart = [];
                            this.fetchProducts();
                        });
                    } else {
                        Swal.fire('❌ Gagal', data.message || 'Terjadi kesalahan', 'error');
                    }
                } catch(e) {
                    console.error('Checkout error:', e);
                    let errorMessage = 'Terjadi kesalahan sistem';
                    
                    if (e.message.includes('HTML')) {
                        errorMessage = 'Session expired. Silakan login ulang.';
                        localStorage.removeItem('saga_token');
                        setTimeout(() => window.location.href = '/signin', 2000);
                    } else if (e.message.includes('produk tidak valid')) {
                        errorMessage = e.message;
                    } else if (e.message.includes('Failed to fetch')) {
                        errorMessage = 'Tidak dapat terhubung ke server';
                    }
                    
                    Swal.fire('❌ Error', errorMessage, 'error');
                } finally {
                    this.isLoading = false;
                }
            }
        },

        showNotification(message, type = 'info') {
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer)
                    toast.addEventListener('mouseleave', Swal.resumeTimer)
                }
            });

            Toast.fire({
                icon: type,
                title: message
            });
        },

        formatCurrency(amount) {
            return new Intl.NumberFormat('id-ID', { 
                style: 'currency', 
                currency: 'IDR', 
                minimumFractionDigits: 0 
            }).format(amount);
        },
        formatNumber(num) {
            return new Intl.NumberFormat('id-ID').format(num);
        }
    }
}
</script>
@endpush
@endsection
