@extends('layouts.app')

@section('title', 'New Sale | SAGA POS')

@section('content')
    <div class="mb-6">
        <a href="{{ route('sales.index') }}" class="text-brand-500 hover:text-brand-600 flex items-center gap-1 mb-4">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Back to Sales
        </a>
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Create New Sale</h1>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3" x-data="salesCreate()">
        <!-- Left Column: Product Selection & Items -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Product Search -->
            <x-card.card title="Add Products">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Search Product</label>
                        <div class="relative">
                            <input type="text" x-model="searchQuery" @input.debounce.300ms="searchProducts"
                                placeholder="Type product name or SKU..."
                                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-brand-500 focus:border-brand-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            <svg class="absolute left-3 top-2.5 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                    </div>

                    <!-- Product Search Results -->
                    <div x-show="searchResults.length > 0" class="border border-gray-200 dark:border-gray-700 rounded-lg max-h-64 overflow-y-auto">
                        <template x-for="product in searchResults" :key="product.id">
                            <div @click="addProduct(product)" 
                                class="flex items-center justify-between p-3 hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer border-b border-gray-100 dark:border-gray-800 last:border-0">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 bg-gray-100 dark:bg-gray-700 rounded-lg flex items-center justify-center">
                                        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-800 dark:text-white" x-text="product.name"></p>
                                        <p class="text-sm text-gray-500" x-text="'SKU: ' + (product.sku || 'N/A')"></p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="font-semibold text-brand-600" x-text="formatCurrency(product.sell_price)"></p>
                                    <p class="text-xs text-gray-500" x-text="'Stock: ' + product.stock"></p>
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- Selected Items Table -->
                    <div x-show="cartItems.length > 0" class="mt-6">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-3">Selected Items</h3>
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="text-left py-2 px-3">Product</th>
                                    <th class="text-center py-2 px-3 w-24">Qty</th>
                                    <th class="text-right py-2 px-3 w-28">Price</th>
                                    <th class="text-right py-2 px-3 w-28">Subtotal</th>
                                    <th class="text-center py-2 px-3 w-16">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(item, index) in cartItems" :key="item.product_id + '-' + index">
                                    <tr class="border-b border-gray-100 dark:border-gray-800">
                                        <td class="py-2 px-3">
                                            <p class="font-medium text-gray-800 dark:text-white" x-text="item.product_name"></p>
                                            <p class="text-xs text-gray-500" x-text="item.product_sku"></p>
                                        </td>
                                        <td class="py-2 px-3">
                                            <input type="number" x-model.number="item.qty" @change="calculateTotals" min="1"
                                                class="w-full text-center border border-gray-300 rounded px-2 py-1 dark:bg-gray-700 dark:border-gray-600">
                                        </td>
                                        <td class="py-2 px-3 text-right" x-text="formatCurrency(item.price)"></td>
                                        <td class="py-2 px-3 text-right font-semibold" x-text="formatCurrency(item.qty * item.price)"></td>
                                        <td class="py-2 px-3 text-center">
                                            <button @click="removeItem(index)" class="text-red-500 hover:text-red-600">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </x-card.card>
        </div>

        <!-- Right Column: Order Summary -->
        <div class="space-y-6">
            <!-- Customer Selection -->
            <x-card.card title="Customer">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Select Customer (Optional)</label>
                    <select x-model="selectedCustomer" 
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-brand-500 focus:border-brand-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        <option value="">General Customer</option>
                        <template x-for="customer in customers" :key="customer.id">
                            <option :value="customer.id" x-text="customer.name"></option>
                        </template>
                    </select>
                </div>
            </x-card.card>

            <!-- Order Details -->
            <x-card.card title="Order Details">
                <div class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Order Date</label>
                        <input type="date" x-model="orderDate"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-brand-500 focus:border-brand-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
                        <select x-model="orderStatus"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-brand-500 focus:border-brand-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            <option value="pending">Pending</option>
                            <option value="confirmed">Confirmed</option>
                            <option value="processing">Processing</option>
                            <option value="completed">Completed</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Payment Method</label>
                        <select x-model="paymentMethod"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-brand-500 focus:border-brand-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            <option value="cash">Cash</option>
                            <option value="transfer">Bank Transfer</option>
                            <option value="credit">Credit</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Notes</label>
                        <textarea x-model="notes" rows="2"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-brand-500 focus:border-brand-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"></textarea>
                    </div>
                </div>
            </x-card.card>

            <!-- Sale Summary -->
            <x-card.card title="Sale Summary">
                <div class="space-y-3">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600 dark:text-gray-400">Subtotal:</span>
                        <span class="font-semibold" x-text="formatCurrency(subtotal)"></span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600 dark:text-gray-400">Discount:</span>
                        <span class="font-semibold" x-text="formatCurrency(discount)"></span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600 dark:text-gray-400">Tax (11%):</span>
                        <span class="font-semibold" x-text="formatCurrency(tax)"></span>
                    </div>
                    <div class="border-t border-gray-200 dark:border-gray-700 pt-3 flex justify-between text-lg font-bold">
                        <span>Total:</span>
                        <span class="text-brand-600" x-text="formatCurrency(grandTotal)"></span>
                    </div>
                    <button @click="submitOrder" :disabled="isSubmitting || cartItems.length === 0"
                        class="w-full rounded-lg bg-brand-500 px-4 py-2.5 text-white font-semibold hover:bg-brand-600 mt-4 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2">
                        <svg x-show="isSubmitting" class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span x-text="isSubmitting ? 'Processing...' : 'Complete Sale'"></span>
                    </button>
                </div>
            </x-card.card>
        </div>
    </div>
@endsection

@push('scripts')
<script>
function salesCreate() {
    return {
        searchQuery: '',
        searchResults: [],
        cartItems: [],
        customers: [],
        selectedCustomer: '',
        orderDate: new Date().toISOString().split('T')[0],
        orderStatus: 'pending',
        paymentMethod: 'cash',
        notes: '',
        subtotal: 0,
        discount: 0,
        tax: 0,
        grandTotal: 0,
        isSubmitting: false,

        async init() {
            await this.fetchCustomers();
        },

        async searchProducts() {
            if (this.searchQuery.length < 2) {
                this.searchResults = [];
                return;
            }

            try {
                const token = localStorage.getItem('saga_token');
                const response = await fetch(`/api/products?search=${encodeURIComponent(this.searchQuery)}`, {
                    headers: {
                        'Authorization': 'Bearer ' + token,
                        'Accept': 'application/json'
                    }
                });
                const result = await response.json();
                
                if (result.success) {
                    this.searchResults = result.data.data.slice(0, 10);
                }
            } catch (e) {
                console.error('Error searching products:', e);
            }
        },

        addProduct(product) {
            const existingIndex = this.cartItems.findIndex(item => item.product_id === product.id);
            
            if (existingIndex > -1) {
                this.cartItems[existingIndex].qty += 1;
            } else {
                this.cartItems.push({
                    product_id: product.id,
                    product_name: product.name,
                    product_sku: product.sku,
                    price: product.sell_price || 0,
                    qty: 1
                });
            }
            
            this.searchQuery = '';
            this.searchResults = [];
            this.calculateTotals();
        },

        removeItem(index) {
            this.cartItems.splice(index, 1);
            this.calculateTotals();
        },

        calculateTotals() {
            this.subtotal = this.cartItems.reduce((sum, item) => sum + (item.qty * item.price), 0);
            this.tax = this.subtotal * 0.11;
            this.grandTotal = this.subtotal + this.tax;
        },

        async fetchCustomers() {
            try {
                const token = localStorage.getItem('saga_token');
                const response = await fetch('/api/customers', {
                    headers: {
                        'Authorization': 'Bearer ' + token,
                        'Accept': 'application/json'
                    }
                });
                const result = await response.json();
                
                if (result.success) {
                    this.customers = result.data.data.slice(0, 50);
                }
            } catch (e) {
                console.error('Error fetching customers:', e);
            }
        },

        async submitOrder() {
            if (this.cartItems.length === 0) {
                alert('Please add at least one product');
                return;
            }

            if (!confirm('Complete this sales order?')) return;

            this.isSubmitting = true;

            try {
                const token = localStorage.getItem('saga_token');
                const orderData = {
                    customer_id: this.selectedCustomer || null,
                    order_date: this.orderDate,
                    status: this.orderStatus,
                    payment_method: this.paymentMethod,
                    notes: this.notes,
                    items: this.cartItems.map(item => ({
                        product_id: item.product_id,
                        qty: item.qty,
                        price: item.price,
                        discount: 0
                    }))
                };

                const response = await fetch('/api/sales-orders', {
                    method: 'POST',
                    headers: {
                        'Authorization': 'Bearer ' + token,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(orderData)
                });
                const result = await response.json();

                if (result.success) {
                    alert('Sales order created successfully! Order #' + result.data.order_number);
                    window.location.href = '/sales';
                } else {
                    alert('Error: ' + (result.message || 'Failed to create order'));
                }
            } catch (e) {
                console.error('Error creating order:', e);
                alert('Error creating sales order');
            } finally {
                this.isSubmitting = false;
            }
        },

        formatCurrency(amount) {
            return new Intl.NumberFormat('id-ID', { 
                style: 'currency', 
                currency: 'IDR', 
                minimumFractionDigits: 0 
            }).format(amount);
        }
    }
}
</script>
@endpush
