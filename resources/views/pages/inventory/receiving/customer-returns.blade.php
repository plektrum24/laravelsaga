@extends('layouts.app')

@section('title', 'Customer Returns | SAGA TOKO APP')

@section('content')

    <div x-data="{ 
                  page: 'returns', 
                  loaded: true, 
                  darkMode: false, 
                  showModal: false, 
                  isLoading: false,
                  searchQuery: '',

                  returns: [],
                  customers: [],

                  returnForm: {
                    customer: '',
                    reason: '',
                    items: []
                  },

                  products: [],
                  searchProduct: '',

                  async init() {
                    const token = localStorage.getItem('saga_token');
                    // if (!token) return window.location.href = '/signin';

                    try {
                      this.products = [];

                      const custRes = await fetch('/api/customers', { headers: { 'Authorization': 'Bearer ' + token } });
                      const custData = await custRes.json();
                      if (custData.success) this.customers = custData.data || [];

                      this.fetchReturns();
                    } catch(e) { console.error(e); }
                  },

                  async fetchReturns() {
                     const token = localStorage.getItem('saga_token');
                     try {
                        const res = await fetch('/api/returns', { headers: { 'Authorization': 'Bearer ' + token } });
                        const data = await res.json();
                        if (data.success) this.returns = data.data;
                     } catch(e) { console.error(e); }
                  },

                  searchDebounceTimer: null,
                  isSearching: false,

                  async searchProducts() {
                    if (!this.searchProduct || this.searchProduct.length < 2) {
                      this.products = [];
                      return;
                    }

                    clearTimeout(this.searchDebounceTimer);
                    this.searchDebounceTimer = setTimeout(async () => {
                      this.isSearching = true;
                      const token = localStorage.getItem('saga_token');
                      try {
                        const res = await fetch(`/api/products?search=${encodeURIComponent(this.searchProduct)}&limit=20`, { 
                          headers: { 'Authorization': 'Bearer ' + token } 
                        });
                        const data = await res.json();
                        if (data.success) this.products = data.data.products || [];
                      } catch(e) { 
                        console.error(e); 
                        this.products = [];
                      } finally {
                        this.isSearching = false;
                      }
                    }, 300);
                  },

                  get filteredProducts() {
                    return this.products.slice(0, 10);
                  },

                  get filteredReturns() {
                    if (!this.searchQuery) return this.returns;
                    return this.returns.filter(r => 
                      r.code.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
                      r.customer.toLowerCase().includes(this.searchQuery.toLowerCase())
                    );
                  },

                  addItemToReturn(product) {
                    const existing = this.returnForm.items.find(i => i.product_id === product.id);
                    if (existing) {
                      existing.quantity++;
                    } else {
                      const units = product.units || [{ unit_id: null, unit_name: 'Pcs', conversion_qty: 1, is_base_unit: true, buy_price: 0 }];
                      const baseUnit = units.find(u => u.is_base_unit) || units[0];
                      this.returnForm.items.push({
                        product_id: product.id,
                        name: product.name,
                        sku: product.sku,
                        cost_price: baseUnit.buy_price || product.buy_price || 0,
                        units: units,
                        selectedUnit: baseUnit,
                        quantity: 1
                      });
                    }
                    this.searchProduct = '';
                  },

                  removeItemFromReturn(index) {
                    this.returnForm.items.splice(index, 1);
                  },

                  get returnTotal() {
                    return this.returnForm.items.reduce((sum, i) => sum + (i.quantity * i.cost_price), 0);
                  },

                  async createReturn() {
                    if (this.returnForm.items.length === 0) {
                      Swal.fire({ icon: 'warning', title: 'Peringatan', text: 'Pilih item untuk direturn', timer: 1500, showConfirmButton: false });
                      return;
                    }
                    this.isLoading = true;

                    const payload = {
                        customer_id: this.returnForm.customer || null,
                        reason: this.returnForm.reason,
                        items: this.returnForm.items.map(i => {
                            const conversionQty = parseFloat(i.selectedUnit?.conversion_qty) || 1;
                            const displaySubtotal = i.quantity * i.cost_price; 
                            return {
                                product_id: i.product_id,
                                quantity: i.quantity * conversionQty, 
                                cost_price: i.cost_price,
                                subtotal: displaySubtotal, 
                                unit_id: i.selectedUnit?.unit_id || null
                            };
                        }),
                        date: new Date().toISOString().split('T')[0]
                    };

                    const token = localStorage.getItem('saga_token');
                    try {
                        const res = await fetch('/api/returns', { 
                            method: 'POST', 
                            headers: { 
                                'Authorization': 'Bearer ' + token,
                                'Content-Type': 'application/json' 
                            },
                            body: JSON.stringify(payload)
                        });
                        const data = await res.json();
                        if(data.success) {
                            this.showModal = false;
                            this.returnForm = { customer: '', reason: '', items: [] };
                            this.fetchReturns();
                            Swal.fire({ icon: 'success', title: 'Berhasil!', text: 'Return Customer berhasil dibuat', timer: 1500, showConfirmButton: false });
                        } else {
                            Swal.fire({ icon: 'error', title: 'Gagal', text: data.message || 'Unknown error' });
                        }
                    } catch(e) {
                        console.error(e);
                        Swal.fire({ icon: 'error', title: 'Error', text: 'Gagal membuat return' });
                    } finally {
                        this.isLoading = false;
                    }
                  },

                  getStatusBadge(status) {
                    const badges = {
                      'pending': { text: 'Pending', class: 'bg-yellow-100 text-yellow-600 dark:bg-yellow-900/30 dark:text-yellow-400' },
                      'approved': { text: 'Approved', class: 'bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400' },
                      'completed': { text: 'Completed', class: 'bg-green-100 text-green-600 dark:bg-green-900/30 dark:text-green-400' },
                      'rejected': { text: 'Rejected', class: 'bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400' }
                    };
                    return badges[status] || badges['pending'];
                  },

                  formatCurrency(amount) {
                    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(amount || 0);
                  },

                  formatDate(dateStr) {
                    if(!dateStr) return '-';
                    return new Date(dateStr).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
                  },

                  exportExcel() {
                    window.location.href = '/api/export/returns/excel';
                  },

                  viewModal: false,
                  selectedReturn: null,
                  selectedReturnItems: [],
                  userRole: 'cashier', 

                  async viewDetail(id) {
                    this.isLoading = true;
                    const token = localStorage.getItem('saga_token');
                    try {
                      const res = await fetch(`/api/returns/${id}`, { headers: { 'Authorization': 'Bearer ' + token } });
                      const data = await res.json();
                      if(data.success) {
                        this.selectedReturn = data.data;
                        this.selectedReturnItems = data.data.items || [];
                        this.viewModal = true;
                      } else {
                        Swal.fire('Error', data.message || 'Gagal memuat detail', 'error');
                      }
                    } catch(e) {
                      console.error(e);
                      Swal.fire('Error', 'Gagal memuat detail', 'error');
                    } finally {
                      this.isLoading = false;
                    }
                  },

                  async updateStatus(id, newStatus) {
                    const confirmMsg = newStatus === 'approved' ? 'Approve return ini? Stok akan BERTAMBAH.' 
                                     : newStatus === 'rejected' ? 'Reject return ini?' 
                                     : `Ubah status ke ${newStatus}?`;

                    const result = await Swal.fire({
                      title: 'Konfirmasi',
                      text: confirmMsg,
                      icon: 'question',
                      showCancelButton: true,
                      confirmButtonText: 'Ya',
                      cancelButtonText: 'Batal'
                    });

                    if(!result.isConfirmed) return;

                    this.isLoading = true;
                    const token = localStorage.getItem('saga_token');
                    const selectedBranch = localStorage.getItem('saga_selected_branch');

                    try {
                      const res = await fetch(`/api/returns/${id}/status`, {
                        method: 'PUT',
                        headers: { 'Authorization': 'Bearer ' + token, 'Content-Type': 'application/json' },
                        body: JSON.stringify({ 
                            status: newStatus,
                            branch_id: selectedBranch ? parseInt(selectedBranch) : null
                        })
                      });
                      const data = await res.json();
                      if(data.success) {
                        Swal.fire({ icon: 'success', title: 'Berhasil', text: data.message, timer: 1500, showConfirmButton: false });
                        this.viewModal = false;
                        this.fetchReturns();
                      } else {
                        Swal.fire('Error', data.message || 'Gagal update status', 'error');
                      }
                    } catch(e) {
                      console.error(e);
                      Swal.fire('Error', 'Gagal update status', 'error');
                    } finally {
                      this.isLoading = false;
                    }
                  },

                  async deleteReturn(id) {
                    const result = await Swal.fire({
                      title: 'Hapus Return?',
                      text: 'Data tidak dapat dikembalikan',
                      icon: 'warning',
                      showCancelButton: true,
                      confirmButtonColor: '#d33',
                      confirmButtonText: 'Hapus',
                      cancelButtonText: 'Batal'
                    });

                    if(!result.isConfirmed) return;

                    this.isLoading = true;
                    const token = localStorage.getItem('saga_token');
                    try {
                      const res = await fetch(`/api/returns/${id}`, {
                        method: 'DELETE',
                        headers: { 'Authorization': 'Bearer ' + token }
                      });
                      const data = await res.json();
                      if(data.success) {
                        Swal.fire({ icon: 'success', title: 'Berhasil', text: 'Return dihapus', timer: 1500, showConfirmButton: false });
                        this.viewModal = false;
                        this.fetchReturns();
                      } else {
                        Swal.fire('Error', data.message || 'Gagal hapus', 'error');
                      }
                    } catch(e) {
                      console.error(e);
                      Swal.fire('Error', 'Gagal hapus', 'error');
                    } finally {
                      this.isLoading = false;
                    }
                  },

                  checkUserRole() {
                    try {
                      const token = localStorage.getItem('saga_token');
                      if(token) {
                        const payload = JSON.parse(atob(token.split('.')[1]));
                        this.userRole = payload.role || 'cashier';
                      }
                    } catch(e) { this.userRole = 'cashier'; }
                  }
                " x-init="
                  // darkMode handled by layout
                  checkUserRole();
                  init();
                ">

        <div>
            <div class="mx-auto max-w-(--breakpoint-2xl) p-4 md:p-6">
                <!-- Header -->
                <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
                    <div>
                        <h1 class="text-xl md:text-2xl font-bold text-gray-800 dark:text-white">Customer Returns
                        </h1>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Manage returns from customers</p>
                    </div>
                    <div class="flex gap-2">
                        <button @click="showModal = true"
                            class="px-4 py-2 bg-brand-500 text-white rounded-lg hover:bg-brand-600 flex items-center gap-2 font-medium shadow-lg shadow-brand-500/30 transition-all hover:-translate-y-0.5">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            <span class="hidden md:inline">New Return</span>
                        </button>
                    </div>
                </div>

                <!-- Search -->
                <div class="mb-4">
                    <input type="text" x-model="searchQuery" placeholder="Search by return code or customer..."
                        class="w-full max-w-md px-4 py-2.5 border border-gray-200 rounded-xl dark:bg-gray-800 dark:border-gray-700 dark:text-white">
                </div>

                <!-- Table -->
                <div
                    class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl overflow-hidden shadow-sm">
                    <!-- Desktop Table -->
                    <div class="hidden md:block overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                <tr>
                                    <th class="px-6 py-3">Code</th>
                                    <th class="px-6 py-3">Customer</th>
                                    <th class="px-6 py-3">Reason</th>
                                    <th class="px-6 py-3 text-center">Items</th>
                                    <th class="px-6 py-3 text-right">Total</th>
                                    <th class="px-6 py-3 text-center">Date</th>
                                    <th class="px-6 py-3 text-center">Status</th>
                                    <th class="px-6 py-3 text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                <template x-for="ret in filteredReturns" :key="ret.id">
                                    <tr
                                        class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                        <td class="px-6 py-4 font-medium text-gray-900 dark:text-white" x-text="ret.code">
                                        </td>
                                        <td class="px-6 py-4">
                                            <span x-text="ret.customer"
                                                class="font-medium text-gray-700 dark:text-gray-300"></span>
                                        </td>
                                        <td class="px-6 py-4 text-gray-500" x-text="ret.reason"></td>
                                        <td class="px-6 py-4 text-center">
                                            <span
                                                class="px-2.5 py-0.5 rounded-full bg-blue-50 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400 text-xs font-bold"
                                                x-text="ret.items"></span>
                                        </td>
                                        <td class="px-6 py-4 text-right font-semibold text-gray-900 dark:text-white"
                                            x-text="formatCurrency(ret.total)"></td>
                                        <td class="px-6 py-4 text-center" x-text="formatDate(ret.date)"></td>
                                        <td class="px-6 py-4 text-center">
                                            <span :class="getStatusBadge(ret.status).class"
                                                class="px-2.5 py-1 rounded-full text-xs font-semibold uppercase tracking-wide flex items-center justify-center gap-1 mx-auto w-max"
                                                x-text="getStatusBadge(ret.status).text"></span>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <div class="flex items-center justify-center gap-2">
                                                <button @click="viewDetail(ret.id)"
                                                    class="p-1.5 text-blue-500 hover:bg-blue-50 rounded-lg dark:hover:bg-blue-900/30"
                                                    title="View">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z">
                                                        </path>
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                                        </path>
                                                    </svg>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                        <div x-show="filteredReturns.length === 0" class="p-8 text-center text-gray-400">
                            No returns found
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- New Return Modal -->
    <div x-show="showModal"
        class="fixed inset-0 z-[9999] flex items-center justify-center bg-gray-900/80 backdrop-blur-sm p-4 transition-all duration-300"
        x-cloak x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div class="bg-white dark:bg-gray-800 rounded-2xl w-full max-w-2xl max-h-[90vh] overflow-hidden flex flex-col shadow-2xl transform transition-all"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                <h2 class="text-xl font-bold text-gray-800 dark:text-white">New Customer Return 👤</h2>
                <button @click="showModal = false" class="text-gray-400 hover:text-gray-600"><svg class="w-6 h-6"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg></button>
            </div>
            <div class="flex-1 overflow-y-auto p-6 space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Customer</label>
                        <select x-model="returnForm.customer"
                            class="w-full px-4 py-2.5 border border-gray-200 rounded-xl dark:bg-gray-900 dark:border-gray-700 dark:text-white">
                            <option value="">-- Walk-in Customer --</option>
                            <template x-for="c in customers" :key="c.id">
                                <option :value="c.id" x-text="c.name"></option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Reason</label>
                        <select x-model="returnForm.reason"
                            class="w-full px-4 py-2.5 border border-gray-200 rounded-xl dark:bg-gray-900 dark:border-gray-700 dark:text-white">
                            <option value="">Select reason</option>
                            <option value="Barang rusak">Barang rusak</option>
                            <option value="Tidak sesuai pesanan">Tidak sesuai pesanan</option>
                            <option value="Expired">Expired</option>
                            <option value="Lainnya">Lainnya</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Add
                        Products</label>
                    <div class="relative">
                        <input type="text" x-model="searchProduct" @input="searchProducts()"
                            placeholder="Ketik min. 2 huruf untuk mencari..."
                            class="w-full px-4 py-2.5 pl-10 border border-gray-200 rounded-xl dark:bg-gray-900 dark:border-gray-700 dark:text-white">
                        <svg x-show="!isSearching" class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        <div x-show="searchProduct && searchProduct.length >= 2"
                            class="absolute z-10 mt-1 w-full bg-white border border-gray-200 rounded-xl shadow-lg max-h-48 overflow-y-auto dark:bg-gray-900 dark:border-gray-700">
                            <div x-show="filteredProducts.length === 0 && !isSearching"
                                class="p-4 text-center text-gray-400 text-sm">
                                Tidak ada produk ditemukan
                            </div>
                            <template x-for="product in filteredProducts" :key="product.id">
                                <button @click="addItemToReturn(product)"
                                    class="w-full px-4 py-2.5 text-left hover:bg-gray-50 flex items-center justify-between dark:hover:bg-gray-800">
                                    <div>
                                        <p class="font-medium text-gray-800 dark:text-white text-sm" x-text="product.name">
                                        </p>
                                        <div class="flex items-center gap-2 mt-0.5">
                                            <span class="text-xs text-gray-500" x-text="product.sku"></span>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <span
                                            class="text-xs font-semibold text-brand-600 bg-brand-50 px-2 py-1 rounded dark:bg-brand-900/30 dark:text-brand-400"
                                            x-text="formatCurrency(product.units?.[0]?.buy_price || product.cost_price || 0)"></span>
                                    </div>
                                </button>
                            </template>
                        </div>
                    </div>
                </div>

                <div class="border border-gray-200 rounded-xl overflow-hidden dark:border-gray-700">
                    <div x-show="returnForm.items.length === 0" class="p-6 text-center text-gray-400">Add products above
                    </div>
                    <template x-for="(item, idx) in returnForm.items" :key="idx">
                        <div
                            class="px-4 py-3 border-b border-gray-100 last:border-b-0 dark:border-gray-700 bg-white dark:bg-transparent">
                            <div class="flex justify-between items-start mb-2">
                                <div class="flex-1 pr-4">
                                    <p class="font-medium text-gray-800 dark:text-white text-sm" x-text="item.name"></p>
                                    <p class="text-xs text-gray-400" x-text="'HPP: ' + formatCurrency(item.cost_price)">
                                    </p>
                                </div>
                                <button @click="removeItemFromReturn(idx)" class="text-gray-400 hover:text-red-500 p-1">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>

                            <div class="flex items-center gap-2 mt-2">
                                <select
                                    @change="item.selectedUnit = item.units[$event.target.selectedIndex]; item.cost_price = item.units[$event.target.selectedIndex].buy_price || 0"
                                    class="flex-1 text-xs border border-gray-200 rounded-lg px-2 py-2 dark:bg-gray-800 dark:border-gray-700 dark:text-white focus:ring-1 focus:ring-brand-500 focus:border-brand-500">
                                    <template x-for="(u, uIdx) in item.units" :key="u.unit_id">
                                        <option :value="uIdx" :selected="item.selectedUnit?.unit_id === u.unit_id"
                                            x-text="u.unit_name"></option>
                                    </template>
                                </select>
                                <div class="relative w-24">
                                    <input type="number" x-model.number="item.quantity" min="1"
                                        class="w-full text-center px-2 py-2 border border-gray-200 rounded-lg text-sm dark:bg-gray-800 dark:border-gray-700 dark:text-white focus:ring-1 focus:ring-brand-500 focus:border-brand-500"
                                        placeholder="Qty">
                                    <span
                                        class="absolute right-8 top-1/2 -translate-y-1/2 text-xs text-gray-400 pointer-events-none">pcs</span>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <div class="flex justify-between items-center p-4 bg-gray-50 rounded-xl dark:bg-gray-800">
                    <span class="font-medium text-gray-700 dark:text-gray-300">Total Value</span>
                    <span class="text-xl font-bold text-brand-600" x-text="formatCurrency(returnTotal)">Rp 0</span>
                </div>
            </div>
            <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 flex justify-end gap-3">
                <button @click="showModal = false"
                    class="px-4 py-2 border border-gray-200 rounded-xl hover:bg-gray-50 dark:border-gray-600 dark:hover:bg-gray-700 dark:text-white">Cancel</button>
                <button @click="createReturn()" :disabled="isLoading"
                    class="px-4 py-2 bg-brand-500 text-white rounded-xl hover:bg-brand-600 disabled:opacity-50 flex items-center gap-2">
                    <svg x-show="isLoading" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                        </circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z">
                        </path>
                    </svg>
                    <span x-text="isLoading ? 'Creating...' : 'Create Return'">Create Return</span>
                </button>
            </div>
        </div>
    </div>

    <!-- View Detail Modal -->
    <div x-show="viewModal"
        class="fixed inset-0 z-[9999] flex items-center justify-center bg-gray-900/80 backdrop-blur-sm p-4 transition-all duration-300"
        x-cloak x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div class="bg-white dark:bg-gray-800 rounded-2xl w-full max-w-2xl max-h-[90vh] overflow-hidden flex flex-col shadow-2xl transform transition-all"
            x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100" x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95">
            <!-- Modal Header -->
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-bold text-gray-800 dark:text-white">Return Detail</h2>
                    <p class="text-sm text-gray-500" x-text="selectedReturn?.code"></p>
                </div>
                <button @click="viewModal = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="flex-1 overflow-y-auto p-6 space-y-4">
                <!-- Return Info -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Customer</p>
                        <p class="font-medium text-gray-800 dark:text-white" x-text="selectedReturn?.customer || 'Walk-in'">
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Date</p>
                        <p class="font-medium text-gray-800 dark:text-white" x-text="formatDate(selectedReturn?.date)">
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Reason</p>
                        <p class="font-medium text-gray-800 dark:text-white" x-text="selectedReturn?.reason || '-'"></p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Status</p>
                        <span class="px-2.5 py-1 rounded-full text-xs font-medium"
                            :class="getStatusBadge(selectedReturn?.status).class"
                            x-text="getStatusBadge(selectedReturn?.status).text"></span>
                    </div>
                </div>

                <!-- Items List -->
                <div class="border border-gray-200 rounded-xl overflow-hidden dark:border-gray-700">
                    <div class="bg-gray-50 dark:bg-gray-900 px-4 py-2 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="font-medium text-sm text-gray-700 dark:text-gray-300">Items</h3>
                    </div>
                    <div class="divide-y divide-gray-100 dark:divide-gray-700">
                        <template x-for="item in selectedReturnItems" :key="item.id">
                            <div class="px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                                <div class="flex justify-between items-start">
                                    <div class="flex-1 pr-4">
                                        <p class="font-medium text-gray-800 dark:text-white text-sm line-clamp-2"
                                            x-text="item.product_name"></p>
                                        <p class="text-xs text-gray-500 mt-0.5" x-text="item.sku"></p>
                                    </div>
                                    <div class="text-right shrink-0">
                                        <p class="font-medium text-gray-800 dark:text-white text-sm"
                                            x-text="item.quantity + ' pcs'"></p>
                                        <p class="text-xs text-gray-500 font-medium mt-0.5"
                                            x-text="formatCurrency(item.subtotal)"></p>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                    <div
                        class="bg-gray-50 dark:bg-gray-900 px-4 py-3 border-t border-gray-200 dark:border-gray-700 flex justify-between">
                        <span class="font-medium text-gray-700 dark:text-gray-300">Total</span>
                        <span class="font-bold text-brand-600" x-text="formatCurrency(selectedReturn?.total_amount)"></span>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 flex justify-between">
                <div>
                    <!-- Delete Button -->
                    <button @click="deleteReturn(selectedReturn?.id)"
                        class="px-4 py-2 bg-red-50 text-red-600 rounded-xl hover:bg-red-100 flex items-center gap-2 dark:bg-red-900/30 dark:hover:bg-red-900/50">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                            </path>
                        </svg>
                        Delete
                    </button>
                </div>
                <div class="flex gap-2">
                    <!-- Reject Button (Manager+ only, Pending only) -->
                    <button
                        x-show="['manager', 'tenant_owner', 'superadmin'].includes(userRole) && selectedReturn?.status === 'pending'"
                        @click="updateStatus(selectedReturn?.id, 'rejected')"
                        class="px-4 py-2 bg-red-50 text-white rounded-xl hover:bg-red-600 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                            </path>
                        </svg>
                        Reject
                    </button>
                    <!-- Approve Button (Manager+ only, Pending only) -->
                    <button
                        x-show="['manager', 'tenant_owner', 'superadmin'].includes(userRole) && selectedReturn?.status === 'pending'"
                        @click="updateStatus(selectedReturn?.id, 'approved')"
                        class="px-4 py-2 bg-green-500 text-white rounded-xl hover:bg-green-600 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                            </path>
                        </svg>
                        Approve
                    </button>
                    <!-- Close Button -->
                    <button @click="viewModal = false"
                        class="px-4 py-2 border border-gray-200 rounded-xl hover:bg-gray-50 dark:border-gray-600 dark:hover:bg-gray-700 dark:text-white">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div id="split-point"></div>
@endsection