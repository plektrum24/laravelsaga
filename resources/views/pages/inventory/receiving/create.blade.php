@extends('layouts.app')

@section('title', 'Create Goods In | SAGA TOKO APP')

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-gray-900 p-6" x-data="goodsInPage()">
    <!-- Header -->
    <div class="max-w-7xl mx-auto mb-8">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-emerald-600 rounded-2xl flex items-center justify-center shadow-lg shadow-green-500/30">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-gray-800 dark:text-white">Create Goods In</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Tambah stok barang masuk ke inventory</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('inventory.receiving.index') }}" class="px-6 py-3 border-2 border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 rounded-xl font-semibold hover:bg-gray-50 dark:hover:bg-gray-900 transition-all flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Back
                </a>
                <button @click="submitGoodsIn()" class="px-6 py-3 bg-gradient-to-r from-green-600 to-emerald-600 text-white rounded-xl font-semibold hover:from-green-700 hover:to-emerald-700 transition-all shadow-lg shadow-green-500/30 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Save Goods In
                </button>
            </div>
        </div>
    </div>

    <!-- Main Form -->
    <div class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left: Form Input -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Goods In Info -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-xl font-bold text-gray-800 dark:text-white mb-6 flex items-center gap-2">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Goods In Information
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Reference Number *</label>
                        <input type="text" x-model="formData.reference_number" readonly class="w-full px-4 py-3 border-2 border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 text-gray-500 dark:text-gray-400">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Date *</label>
                        <input type="date" x-model="formData.date" required class="w-full px-4 py-3 border-2 border-gray-200 dark:border-gray-700 rounded-xl bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-green-500/20 focus:border-green-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Supplier</label>
                        <select x-model="formData.supplier_id" class="w-full px-4 py-3 border-2 border-gray-200 dark:border-gray-700 rounded-xl bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-green-500/20 focus:border-green-500">
                            <option value="">Select Supplier</option>
                            <template x-for="supplier in suppliers" :key="supplier.id">
                                <option :value="supplier.id" x-text="supplier.name"></option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Warehouse/Branch</label>
                        <select x-model="formData.branch_id" class="w-full px-4 py-3 border-2 border-gray-200 dark:border-gray-700 rounded-xl bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-green-500/20 focus:border-green-500">
                            <option value="">Select Branch</option>
                            <template x-for="branch in branches" :key="branch.id">
                                <option :value="branch.id" x-text="branch.name"></option>
                            </template>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Products Table -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-bold text-gray-800 dark:text-white flex items-center gap-2">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                        Products
                    </h2>
                    <button @click="openProductModal()" class="px-4 py-2 bg-green-600 text-white rounded-xl font-semibold hover:bg-green-700 transition-all flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Add Product
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 dark:bg-gray-900/50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Product</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Qty</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Unit</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Price</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Subtotal</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <template x-for="(item, index) in formData.items" :key="index">
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/30">
                                    <td class="px-4 py-3">
                                        <div>
                                            <p class="font-semibold text-gray-800 dark:text-white" x-text="item.product_name"></p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400" x-text="item.sku"></p>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <input type="number" x-model.number="item.qty" @input="calculateTotals()" min="1" class="w-24 px-3 py-2 border-2 border-gray-200 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-green-500/20 focus:border-green-500">
                                    </td>
                                    <td class="px-4 py-3"><span class="text-gray-700 dark:text-gray-300 text-sm" x-text="item.unit_name"></span></td>
                                    <td class="px-4 py-3">
                                        <input type="number" x-model.number="item.buy_price" @input="calculateTotals()" min="0" step="0.01" class="w-28 px-3 py-2 border-2 border-gray-200 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-green-500/20 focus:border-green-500">
                                    </td>
                                    <td class="px-4 py-3"><span class="font-bold text-gray-800 dark:text-white" x-text="formatCurrency(item.qty * item.buy_price)"></span></td>
                                    <td class="px-4 py-3 text-right">
                                        <button @click="removeItem(index)" class="p-2 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                        <tfoot x-show="formData.items.length === 0" class="bg-gray-50 dark:bg-gray-900/50">
                            <tr>
                                <td colspan="6" class="px-4 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <div class="w-16 h-16 bg-gray-200 dark:bg-gray-700 rounded-full flex items-center justify-center mb-3">
                                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                            </svg>
                                        </div>
                                        <p class="text-gray-500 dark:text-gray-400 font-medium">No products added yet</p>
                                        <p class="text-gray-400 dark:text-gray-500 text-sm mt-1">Click "Add Product" to start adding items</p>
                                    </div>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- Right: Summary -->
        <div class="space-y-6">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 sticky top-6">
                <h2 class="text-xl font-bold text-gray-800 dark:text-white mb-6 flex items-center gap-2">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                    Summary
                </h2>
                <div class="space-y-4">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-500 dark:text-gray-400">Total Items</span>
                        <span class="font-bold text-gray-800 dark:text-white" x-text="totalItems"></span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-500 dark:text-gray-400">Subtotal</span>
                        <span class="font-bold text-gray-800 dark:text-white" x-text="formatCurrency(subtotal)"></span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-500 dark:text-gray-400">Tax (10%)</span>
                        <span class="font-bold text-gray-800 dark:text-white" x-text="formatCurrency(tax)"></span>
                    </div>
                    <div class="border-t border-gray-200 dark:border-gray-700 pt-4 flex justify-between items-center">
                        <span class="text-lg font-bold text-gray-800 dark:text-white">Total</span>
                        <span class="text-2xl font-bold text-green-600" x-text="formatCurrency(total)"></span>
                    </div>
                </div>

                <div class="mt-6 p-4 bg-green-50 dark:bg-green-900/20 rounded-xl border border-green-200 dark:border-green-800">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-green-600 dark:text-green-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <div>
                            <p class="text-sm font-semibold text-green-800 dark:text-green-400">Important Note</p>
                            <p class="text-xs text-green-600 dark:text-green-500 mt-1">Make sure all product information and quantities are correct before saving. This will update your inventory stock levels.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function goodsInPage() {
    return {
        suppliers: [
            { id: 1, name: 'PT. Supplier Utama' },
            { id: 2, name: 'CV. Berkah Jaya' },
            { id: 3, name: 'Toko Makmur' }
        ],
        branches: [
            { id: 1, name: 'Head Office' },
            { id: 2, name: 'Branch West' },
            { id: 3, name: 'Branch North' }
        ],
        formData: {
            reference_number: 'GR-' + Date.now(),
            date: new Date().toISOString().split('T')[0],
            supplier_id: '',
            branch_id: '',
            items: []
        },

        get totalItems() {
            return this.formData.items.reduce((sum, item) => sum + item.qty, 0);
        },

        get subtotal() {
            return this.formData.items.reduce((sum, item) => sum + (item.qty * item.buy_price), 0);
        },

        get tax() {
            return this.subtotal * 0.1;
        },

        get total() {
            return this.subtotal + this.tax;
        },

        calculateTotals() {
            // Trigger reactivity
            this.formData.items = [...this.formData.items];
        },

        removeItem(index) {
            this.formData.items.splice(index, 1);
        },

        openProductModal() {
            Swal.fire({
                title: 'Add Product',
                html: `
                    <div class="space-y-3 text-left">
                        <input id="product-name" class="swal2-input" placeholder="Product Name">
                        <input id="product-sku" class="swal2-input" placeholder="SKU">
                        <input id="product-qty" type="number" class="swal2-input" placeholder="Quantity" min="1">
                        <select id="product-unit" class="swal2-input">
                            <option value="Pcs">Pcs</option>
                            <option value="Box">Box</option>
                            <option value="Kg">Kg</option>
                        </select>
                        <input id="product-price" type="number" class="swal2-input" placeholder="Buy Price" min="0" step="0.01">
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Add Product',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#16a34a',
                preConfirm: () => {
                    return {
                        product_name: document.getElementById('product-name').value,
                        sku: document.getElementById('product-sku').value,
                        qty: parseInt(document.getElementById('product-qty').value),
                        unit_name: document.getElementById('product-unit').value,
                        buy_price: parseFloat(document.getElementById('product-price').value)
                    };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    this.formData.items.push(result.value);
                    this.calculateTotals();
                    Swal.fire({
                        icon: 'success',
                        title: 'Product Added',
                        timer: 1500,
                        showConfirmButton: false
                    });
                }
            });
        },

        formatCurrency(amount) {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0
            }).format(amount);
        },

        submitGoodsIn() {
            if (this.formData.items.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'No Products',
                    text: 'Please add at least one product',
                    confirmButtonColor: '#16a34a'
                });
                return;
            }

            Swal.fire({
                title: 'Save Goods In?',
                text: 'This will update your inventory stock levels',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, Save',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#16a34a',
                cancelButtonColor: '#6b7280'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Goods In Saved!',
                        text: 'Inventory has been updated',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.href = '{{ route("inventory.receiving.index") }}';
                    });
                }
            });
        }
    }
}
</script>
@endpush
@endsection
