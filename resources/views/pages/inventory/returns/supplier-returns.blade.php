@extends('layouts.app')

@section('title', 'Return Supplier | SAGA TOKO APP')

@section('content')

<div x-data="{ 
      page: 'supplierReturns', 
      loaded: true, 
      darkMode: false, 
      showModal: false, 
      isLoading: false,
      
      returns: [],
      suppliers: [],
      products: [],
      availableBatches: [],
      
      searchQuery: '',
      filterSupplier: '',
      filterStatus: '',
      
      selectedSupplier: '',
      returnDate: new Date().toISOString().split('T')[0],
      returnReason: 'expired',
      returnNotes: '',
      returnItems: [],
      searchProduct: '',
      searchResults: [],
      
      viewModal: false,
      selectedReturn: null,
      selectedReturnItems: [],
      
      reasonLabels: {
          'expired': 'Kadaluarsa',
          'damaged': 'Rusak/Cacat',
          'wrong_item': 'Salah Kirim',
          'quality_issue': 'Masalah Kualitas',
          'other': 'Lainnya'
      },
      
      searchTimeout: null,
      async performSearch() {
          if (!this.searchProduct) {
              this.searchResults = this.products.slice(0, 10);
              return;
          }
          clearTimeout(this.searchTimeout);
          this.searchTimeout = setTimeout(async () => {
              try {
                  const token = localStorage.getItem('saga_token');
                  const res = await fetch(`/api/products?search=${this.searchProduct}&limit=20`, { 
                      headers: { 'Authorization': 'Bearer ' + token } 
                  });
                  const data = await res.json();
                  if (data.success) {
                      this.searchResults = data.data.products;
                  }
              } catch (e) { console.error(e); }
          }, 300);
      },
      
      resetForm() {
          this.selectedSupplier = '';
          this.returnDate = new Date().toISOString().split('T')[0];
          this.returnReason = 'expired';
          this.returnNotes = '';
          this.returnItems = [];
          this.searchProduct = '';
          this.searchResults = [];
      },

      async init() {
        const token = localStorage.getItem('saga_token');
        // if (!token) return window.location.href = '/signin';
        
        try {
          await this.fetchReturns();
        } catch(e) { console.error('Error fetching returns:', e); }

        try {
            const supRes = await fetch('/api/suppliers?limit=1000', { headers: { 'Authorization': 'Bearer ' + token } }); 
            const supData = await supRes.json();
            if (supData.success) { 
                this.suppliers = supData.data || []; 
            } 
        } catch(e) { console.error('Error fetching suppliers:', e); } 

        try { 
            const prodRes = await fetch('/api/products', { headers: { 'Authorization' : 'Bearer ' + token } }); 
            const prodData = await prodRes.json(); 
            if (prodData.success) this.products = prodData.data.products || []; 
        } catch(e) { console.error('Error fetching products:', e); } 
      }, 

      async fetchReturns() { 
        try { 
            const token = localStorage.getItem('saga_token'); 
            let url = '/api/purchase-returns?limit=100';
            if (this.filterSupplier) url += '&supplier_id=' + this.filterSupplier; 
            if (this.filterStatus) url += '&status=' + this.filterStatus; 
            const response = await fetch(url, { headers: { 'Authorization' : 'Bearer ' + token } }); 
            const data = await response.json(); 
            if (data.success) this.returns = data.data; 
        } catch(e) { console.error('Fetch error:', e); } 
      }, 

      async onSupplierChange() { 
        if (!this.selectedSupplier) { 
            this.availableBatches = []; 
            return; 
        } 
        this.returnItems = []; 
      }, 

      async fetchBatchesForProduct(productId) { 
        if (!this.selectedSupplier || !productId) return []; 
        try { 
            const token = localStorage.getItem('saga_token'); 
            const res = await fetch('/api/purchase-returns/batches/' + productId + '?supplier_id=' + this.selectedSupplier, { headers: { 'Authorization' : 'Bearer ' + token } }); 
            const data = await res.json(); 
            if (data.success) return data.data; 
        } catch(e) { console.error(e); } 
        return []; 
      }, 

      async selectProduct(product) { 
        if (!this.selectedSupplier) { 
            Swal.fire('Peringatan', 'Pilih supplier terlebih dahulu' , 'warning' ); 
            return; 
        } 
        
        const batches = await this.fetchBatchesForProduct(product.id); 
        if (batches.length === 0) { 
            Swal.fire('Info', 'Tidak ada batch tersedia untuk produk ini dari supplier yang dipilih' , 'info' ); 
            return; 
        } 
        
        const batch = batches[0];
        const currentStock = parseFloat(batch.current_stock) || parseFloat(batch.initial_qty); 
        
        this.returnItems.push({ 
            product_id: product.id, 
            product_name: product.name, 
            sku: product.sku, 
            purchase_item_id: batch.batch_id, 
            unit_id: batch.unit_id,
            unit_name: batch.unit_name || 'Pcs' , 
            unit_price: parseFloat(batch.unit_price) || 0, 
            expiry_date: batch.expiry_date,
            invoice_number: batch.invoice_number, 
            max_qty: currentStock, 
            quantity: 1, 
            batches: batches, 
            selectedBatchId: batch.batch_id 
        }); 
        
        this.searchProduct='' ; 
        this.searchResults=[]; 
      }, 
      
      onBatchChange(item) {
        const batch=item.batches.find(b=> b.batch_id == item.selectedBatchId);
        if (batch) {
            item.purchase_item_id = batch.batch_id;
            item.unit_id = batch.unit_id;
            item.unit_name = batch.unit_name || 'Pcs';
            item.unit_price = parseFloat(batch.unit_price) || 0;
            item.expiry_date = batch.expiry_date;
            item.invoice_number = batch.invoice_number;
            item.max_qty = parseFloat(batch.current_stock) || parseFloat(batch.initial_qty);
            if (item.quantity > item.max_qty) item.quantity = item.max_qty;
        }
      },

      removeItem(index) {
        this.returnItems.splice(index, 1);
      },

      get total() {
        return this.returnItems.reduce((sum, i) => sum + (i.quantity * i.unit_price), 0);
      },

      get filteredProducts() {
        if (!this.searchProduct) return this.products.slice(0, 10);
        const term = this.searchProduct.toLowerCase();
        return this.products.filter(p =>
            (p.name || '').toLowerCase().includes(term) ||
            (p.sku || '').toLowerCase().includes(term)
        ).slice(0, 10);
      },

      get filteredReturns() {
        if (!this.searchQuery) return this.returns;
        return this.returns.filter(r =>
            r.return_number.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
            (r.supplier_name && r.supplier_name.toLowerCase().includes(this.searchQuery.toLowerCase()))
        );
      },

      async saveReturn(status = 'draft') {
        if (this.returnItems.length === 0) return Swal.fire('Error', 'Tambahkan minimal 1 item', 'warning');
        if (!this.selectedSupplier) return Swal.fire('Error', 'Pilih Supplier', 'warning');

        for (const item of this.returnItems) {
            if (item.quantity <= 0) { 
                return Swal.fire('Error', `Qty untuk ${item.product_name} harus > 0`, 'warning');
            }
            if (item.quantity > item.max_qty) {
                return Swal.fire('Error', `Qty untuk ${item.product_name} melebihi stok batch (max: ${item.max_qty})`, 'warning');
            }
        }

        this.isLoading = true;

        const selectedBranch = localStorage.getItem('saga_selected_branch');
        const payload = {
            supplier_id: this.selectedSupplier,
            date: this.returnDate,
            reason: this.returnReason,
            notes: this.returnNotes,
            status: status,
            branch_id: selectedBranch ? parseInt(selectedBranch) : null,
            items: this.returnItems.map(i => ({
                purchase_item_id: i.purchase_item_id,
                product_id: i.product_id,
                quantity: i.quantity,
                unit_id: i.unit_id,
                unit_price: i.unit_price,
                subtotal: i.quantity * i.unit_price
            }))
        };

        try {
            const token = localStorage.getItem('saga_token');
            const response = await fetch('/api/purchase-returns', {
                method: 'POST',
                headers: {
                    'Authorization': 'Bearer ' + token,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(payload)
            });
            const data = await response.json();

            if(data.success) {
                Swal.fire({
                    title: 'Berhasil!',
                    text: status === 'completed' ? 'Return selesai & stok dikurangi!' : 'Draft return tersimpan!',
                    icon: 'success',
                    timer: 1500,
                    showConfirmButton: false
                });
                this.showModal = false;
                this.resetForm();
                await this.fetchReturns();
            } else {
                Swal.fire('Gagal', data.message || 'Terjadi kesalahan', 'error');
            }
        } catch(e) {
            console.error(e);
            Swal.fire('Error', 'Gagal menyimpan return', 'error');
        } finally {
            this.isLoading = false;
        }
      },

      async completeReturn(id) {
        const result = await Swal.fire({
            title: 'Selesaikan Return?',
            text: 'Stok akan dikurangi dari batch. Tindakan ini tidak dapat dibatalkan.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#10B981',
            cancelButtonColor: '#6B7280',
            confirmButtonText: 'Ya, Selesaikan!'
        });

        if (!result.isConfirmed) return;

        try {
            const token = localStorage.getItem('saga_token');
            const res = await fetch(`/api/purchase-returns/${id}/complete`, {
                method: 'PATCH',
                headers: { 'Authorization': 'Bearer ' + token }
            });
            const data = await res.json();

            if (data.success) {
                Swal.fire('Berhasil!', 'Return telah diselesaikan', 'success');
                await this.fetchReturns();
            } else {
                Swal.fire('Gagal', data.message, 'error');
            }
        } catch(e) {
            console.error(e);
            Swal.fire('Error', 'Gagal menyelesaikan return', 'error');
        }
      },

      async cancelReturn(id) {
        const result = await Swal.fire({
            title: 'Batalkan Return?',
            text: 'Jika return sudah selesai, stok akan dikembalikan ke batch.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#EF4444',
            cancelButtonColor: '#6B7280',
            confirmButtonText: 'Ya, Batalkan!'
        });

        if (!result.isConfirmed) return;

        try {
            const token = localStorage.getItem('saga_token');
            const res = await fetch(`/api/purchase-returns/${id}/cancel`, {
                method: 'PATCH',
                headers: { 'Authorization': 'Bearer ' + token }
            });
            const data = await res.json();

            if (data.success) {
                Swal.fire('Berhasil!', 'Return telah dibatalkan', 'success');
                await this.fetchReturns();
            } else {
                Swal.fire('Gagal', data.message, 'error');
            }
        } catch(e) {
            console.error(e);
            Swal.fire('Error', 'Gagal membatalkan return', 'error');
        }
      },

      async deleteReturn(id) {
        const result = await Swal.fire({
            title: 'Hapus Return?',
            text: 'Draft return akan dihapus permanen.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#EF4444',
            cancelButtonColor: '#6B7280',
            confirmButtonText: 'Ya, Hapus!'
        });

        if (!result.isConfirmed) return;

        try {
            const token = localStorage.getItem('saga_token');
            const res = await fetch(`/api/purchase-returns/${id}`, {
                method: 'DELETE',
                headers: { 'Authorization': 'Bearer ' + token }
            });
            const data = await res.json();

            if (data.success) {
                Swal.fire('Berhasil!', 'Return telah dihapus', 'success');
                await this.fetchReturns();
            } else {
                Swal.fire('Gagal', data.message, 'error');
            }
        } catch(e) {
            console.error(e);
            Swal.fire('Error', 'Gagal menghapus return', 'error');
        }
      },

      async viewDetail(id) {
        this.isLoading = true;
        try {
            const token = localStorage.getItem('saga_token');
            const res = await fetch(`/api/purchase-returns/${id}`, { headers: { 'Authorization': 'Bearer ' + token } });
            const data = await res.json();
            if(data.success) {
                this.selectedReturn = data.data;
                this.selectedReturnItems = data.data.items;
                this.viewModal = true;
            }
        } catch(e) {
            console.error(e);
            Swal.fire('Error', 'Gagal memuat detail', 'error');
        } finally {
            this.isLoading = false;
        }
      },

      formatCurrency(amount) {
        return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0
        }).format(amount || 0);
      },

      formatDate(dateString) {
        if(!dateString) return '-';
        return new Date(dateString).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
      },

      formatNumber(num) {
        if (num === null || num === undefined) return '0';
        const n = parseFloat(num);
        return Number.isInteger(n) ? n.toString() : n.toFixed(2).replace(/\.?0+$/, '');
      },

      getStatusColor(status) {
        switch(status) {
            case 'completed': return 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400';
            case 'cancelled': return 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400';
            case 'draft': return 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400';
            default: return 'bg-gray-100 text-gray-700';
        }
      },

      get draftCount() {
        return this.returns.filter(r => r.status === 'draft').length;
      },

      get completedCount() {
        return this.returns.filter(r => r.status === 'completed').length;
      }
    " x-init="
    // darkMode handled by layout
    init();
    ">

    <div>
        <div class="mx-auto max-w-(--breakpoint-2xl) p-4 md:p-6">

            <!-- Header -->
            <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
                <div>
                    <h1 class="text-xl md:text-2xl font-bold text-gray-800 dark:text-white">Return Supplier
                    </h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Kelola pengembalian barang ke
                        supplier
                    </p>
                </div>
                <button @click="showModal = true; resetForm()"
                    class="px-4 py-2 bg-brand-500 text-white rounded-lg hover:bg-brand-600 flex items-center gap-2 font-medium shadow-lg shadow-brand-500/30 transition-all hover:-translate-y-0.5">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    <span class="hidden md:inline">Buat Return</span>
                </button>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                <!-- Draft -->
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                    <div class="flex items-center gap-3">
                        <div
                            class="w-12 h-12 bg-yellow-100 rounded-xl flex items-center justify-center dark:bg-yellow-900/30">
                            <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                </path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Draft</p>
                            <p class="text-2xl font-bold text-yellow-600" x-text="draftCount">0</p>
                        </div>
                    </div>
                </div>

                <!-- Completed -->
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                    <div class="flex items-center gap-3">
                        <div
                            class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center dark:bg-green-900/30">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Selesai</p>
                            <p class="text-2xl font-bold text-green-600" x-text="completedCount">0</p>
                        </div>
                    </div>
                </div>

                <!-- Total -->
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                    <div class="flex items-center gap-3">
                        <div
                            class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center dark:bg-blue-900/30">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2">
                                </path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Total Return</p>
                            <p class="text-2xl font-bold text-blue-600" x-text="returns.length">0</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="flex flex-wrap gap-3 mb-4">
                <input type="text" x-model="searchQuery" placeholder="Cari no. return atau supplier..."
                    class="flex-1 min-w-[200px] max-w-md px-4 py-2.5 border border-gray-200 rounded-xl dark:bg-gray-800 dark:border-gray-700 dark:text-white">
                <select x-model="filterStatus" @change="fetchReturns()"
                    class="px-4 py-2.5 border border-gray-200 rounded-xl dark:bg-gray-800 dark:border-gray-700 dark:text-white">
                    <option value="">Semua Status</option>
                    <option value="draft">Draft</option>
                    <option value="completed">Selesai</option>
                    <option value="cancelled">Dibatalkan</option>
                </select>
            </div>

            <!-- Table -->
            <div
                class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl overflow-hidden shadow-sm">
                <!-- Desktop Table -->
                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                            <tr>
                                <th class="px-6 py-3">Tanggal</th>
                                <th class="px-6 py-3">No. Return</th>
                                <th class="px-6 py-3">Supplier</th>
                                <th class="px-6 py-3">Alasan</th>
                                <th class="px-6 py-3 text-right">Total</th>
                                <th class="px-6 py-3 text-center">Status</th>
                                <th class="px-6 py-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            <template x-for="r in filteredReturns" :key="r.id">
                                <tr
                                    class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                    <td class="px-6 py-4 font-medium text-gray-900 dark:text-white"
                                        x-text="formatDate(r.date)"></td>
                                    <td class="px-6 py-4 font-mono text-sm" x-text="r.return_number"></td>
                                    <td class="px-6 py-4">
                                        <span x-text="r.supplier_name || 'Unknown'"
                                            class="font-medium text-gray-700 dark:text-gray-300"></span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="text-sm" x-text="reasonLabels[r.reason] || r.reason"></span>
                                    </td>
                                    <td class="px-6 py-4 font-semibold text-right text-gray-800 dark:text-gray-200"
                                        x-text="formatCurrency(r.total_amount)"></td>
                                    <td class="px-6 py-4 text-center">
                                        <span :class="getStatusColor(r.status)"
                                            class="px-2.5 py-1 rounded-full text-xs font-semibold uppercase tracking-wide"
                                            x-text="r.status"></span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <div class="flex items-center justify-center gap-1">
                                            <button @click="viewDetail(r.id)"
                                                class="text-blue-500 hover:text-blue-700 p-1.5 rounded-lg hover:bg-blue-50 dark:hover:bg-blue-900/30"
                                                title="Lihat Detail">
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
                                            <button x-show="r.status === 'draft'" @click="completeReturn(r.id)"
                                                class="text-green-500 hover:text-green-700 p-1.5 rounded-lg hover:bg-green-50 dark:hover:bg-green-900/30"
                                                title="Selesaikan">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                </svg>
                                            </button>
                                            <button x-show="r.status !== 'cancelled'" @click="cancelReturn(r.id)"
                                                class="text-orange-500 hover:text-orange-700 p-1.5 rounded-lg hover:bg-orange-50 dark:hover:bg-orange-900/30"
                                                title="Batalkan">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636">
                                                    </path>
                                                </svg>
                                            </button>
                                            <button x-show="r.status === 'draft'" @click="deleteReturn(r.id)"
                                                class="text-red-500 hover:text-red-700 p-1.5 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/30"
                                                title="Hapus">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                    </path>
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Cards -->
                <div class="md:hidden">
                    <template x-for="r in filteredReturns" :key="r.id">
                        <div class="border-b border-gray-100 dark:border-gray-700 p-4 last:border-0">
                            <div class="flex justify-between items-start mb-2">
                                <div>
                                    <div class="font-bold text-gray-800 dark:text-white font-mono text-sm"
                                        x-text="r.return_number"></div>
                                    <div class="text-xs text-gray-500" x-text="formatDate(r.date)"></div>
                                </div>
                                <div class="text-right">
                                    <div class="font-bold text-brand-600" x-text="formatCurrency(r.total_amount)"></div>
                                    <span :class="getStatusColor(r.status)"
                                        class="px-2 py-0.5 rounded text-[10px] font-bold uppercase inline-block mt-1"
                                        x-text="r.status"></span>
                                </div>
                            </div>
                            <div class="text-sm text-gray-600 dark:text-gray-300 mb-2" x-text="r.supplier_name">
                            </div>
                            <div class="flex gap-2 mt-2">
                                <button @click="viewDetail(r.id)"
                                    class="flex-1 py-2 text-sm bg-blue-50 text-blue-600 rounded-lg dark:bg-blue-900/20">Detail</button>
                                <button x-show="r.status === 'draft'" @click="completeReturn(r.id)"
                                    class="flex-1 py-2 text-sm bg-green-50 text-green-600 rounded-lg dark:bg-green-900/20">Selesai</button>
                            </div>
                        </div>
                    </template>
                    <div x-show="!isLoading && filteredReturns.length === 0"
                        class="p-8 text-center text-gray-400 text-sm">
                        Belum ada data return.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- CREATE MODAL -->
<div x-show="showModal"
    class="fixed inset-0 z-[9999] flex items-center justify-center bg-slate-900/80 backdrop-blur-sm p-4" x-cloak
    @keydown.escape.window="showModal = false" style="display: none;">
    <div
        class="bg-white dark:bg-gray-800 rounded-2xl w-full max-w-4xl max-h-[90vh] overflow-hidden flex flex-col shadow-2xl">
        <!-- Modal Header -->
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
            <h2 class="text-xl font-bold text-gray-800 dark:text-white">Buat Return Supplier 📦</h2>
            <button @click="showModal = false"
                class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                    </path>
                </svg>
            </button>
        </div>

        <!-- Modal Body -->
        <div class="flex-1 overflow-y-auto p-6 bg-gray-50 dark:bg-gray-900/50">
            <div class="space-y-6">
                <!-- Info Section -->
                <div class="bg-white dark:bg-gray-800 p-5 rounded-xl border border-gray-200 dark:border-gray-700">
                    <h3 class="font-semibold text-gray-800 dark:text-white mb-4">Informasi Return</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Tanggal</label>
                            <input type="date" x-model="returnDate"
                                class="w-full px-3 py-2 border border-gray-200 rounded-lg dark:bg-gray-900 dark:border-gray-600 dark:text-white text-sm">
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Supplier</label>
                            <select x-model="selectedSupplier" @change="onSupplierChange()"
                                class="w-full px-3 py-2 border border-gray-200 rounded-lg dark:bg-gray-900 dark:border-gray-600 dark:text-white text-sm">
                                <option value="">-- Pilih Supplier --</option>
                                <template x-for="s in suppliers" :key="s.id">
                                    <option :value="s.id" x-text="s.name"></option>
                                </template>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Alasan
                                Return</label>
                            <select x-model="returnReason"
                                class="w-full px-3 py-2 border border-gray-200 rounded-lg dark:bg-gray-900 dark:border-gray-600 dark:text-white text-sm">
                                <option value="expired">Kadaluarsa</option>
                                <option value="damaged">Rusak/Cacat</option>
                                <option value="wrong_item">Salah Kirim</option>
                                <option value="quality_issue">Masalah Kualitas</option>
                                <option value="other">Lainnya</option>
                            </select>
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Catatan</label>
                            <input type="text" x-model="returnNotes" placeholder="Catatan tambahan..."
                                class="w-full px-3 py-2 border border-gray-200 rounded-lg dark:bg-gray-900 dark:border-gray-600 dark:text-white text-sm">
                        </div>
                    </div>
                </div>

                <!-- Product Search -->
                <div x-show="selectedSupplier"
                    class="bg-white dark:bg-gray-800 p-5 rounded-xl border border-gray-200 dark:border-gray-700">
                    <h3 class="font-semibold text-gray-800 dark:text-white mb-4">Tambah Produk</h3>
                    <div class="relative">
                        <input type="text" x-model="searchProduct" @input="performSearch()"
                            placeholder="Cari produk untuk di-return..."
                            class="w-full px-4 py-2.5 border border-gray-200 rounded-lg dark:bg-gray-900 dark:border-gray-600 dark:text-white">

                        <!-- Search Results Dropdown -->
                        <div x-show="searchProduct || searchResults.length"
                            class="absolute z-50 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg max-h-60 overflow-y-auto">
                            <template x-for="p in filteredProducts" :key="p.id">
                                <div @click="selectProduct(p)"
                                    class="px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer border-b border-gray-100 dark:border-gray-700 last:border-0">
                                    <div class="font-medium text-gray-800 dark:text-white" x-text="p.name">
                                    </div>
                                    <div class="text-xs text-gray-500" x-text="p.sku"></div>
                                </div>
                            </template>
                            <div x-show="searchProduct && filteredProducts.length === 0"
                                class="px-4 py-3 text-gray-400 text-sm">
                                Produk tidak ditemukan
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Return Items Table -->
                <div x-show="returnItems.length > 0"
                    class="bg-white dark:bg-gray-800 p-5 rounded-xl border border-gray-200 dark:border-gray-700">
                    <h3 class="font-semibold text-gray-800 dark:text-white mb-4">Item Return</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-3 py-2 text-left">Produk</th>
                                    <th class="px-3 py-2 text-left">Batch/Invoice</th>
                                    <th class="px-3 py-2 text-center">Expiry</th>
                                    <th class="px-3 py-2 text-center">Stok</th>
                                    <th class="px-3 py-2 text-center">Qty Return</th>
                                    <th class="px-3 py-2 text-right">Harga</th>
                                    <th class="px-3 py-2 text-right">Subtotal</th>
                                    <th class="px-3 py-2"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                <template x-for="(item, idx) in returnItems" :key="idx">
                                    <tr>
                                        <td class="px-3 py-2">
                                            <div class="font-medium text-gray-800 dark:text-white"
                                                x-text="item.product_name"></div>
                                            <div class="text-xs text-gray-500" x-text="item.sku"></div>
                                        </td>
                                        <td class="px-3 py-2">
                                            <select x-model="item.selectedBatchId" @change="onBatchChange(item)"
                                                class="w-full px-2 py-1 text-xs border border-gray-200 rounded dark:bg-gray-900 dark:border-gray-600 dark:text-white">
                                                <template x-for="b in item.batches" :key="b.batch_id">
                                                    <option :value="b.batch_id"
                                                        x-text="b.invoice_number + ' (' + formatNumber(b.current_stock || b.initial_qty) + ')'">
                                                    </option>
                                                </template>
                                            </select>
                                        </td>
                                        <td class="px-3 py-2 text-center">
                                            <span x-text="item.expiry_date ? formatDate(item.expiry_date) : '-'"
                                                :class="item.expiry_date && new Date(item.expiry_date) < new Date() ? 'text-red-500 font-medium' : 'text-gray-500'"></span>
                                        </td>
                                        <td class="px-3 py-2 text-center font-medium"
                                            x-text="formatNumber(item.max_qty) + ' ' + item.unit_name"></td>
                                        <td class="px-3 py-2 text-center">
                                            <input type="number" x-model.number="item.quantity" min="1"
                                                :max="item.max_qty"
                                                class="w-20 px-2 py-1 text-center border border-gray-200 rounded dark:bg-gray-900 dark:border-gray-600 dark:text-white">
                                        </td>
                                        <td class="px-3 py-2 text-right" x-text="formatCurrency(item.unit_price)">
                                        </td>
                                        <td class="px-3 py-2 text-right font-semibold"
                                            x-text="formatCurrency(item.quantity * item.unit_price)"></td>
                                        <td class="px-3 py-2 text-center">
                                            <button @click="removeItem(idx)" class="text-red-500 hover:text-red-700">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                            <tfoot class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <td colspan="6"
                                        class="px-3 py-3 text-right font-bold text-gray-800 dark:text-white">
                                        Total
                                        Return:</td>
                                    <td class="px-3 py-3 text-right font-bold text-lg text-brand-600"
                                        x-text="formatCurrency(total)"></td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Footer -->
        <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 flex justify-end gap-3">
            <button @click="showModal = false"
                class="px-4 py-2 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                Batal
            </button>
            <button @click="saveReturn('draft')" :disabled="isLoading || returnItems.length === 0"
                class="px-4 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 disabled:opacity-50 flex items-center gap-2">
                <svg x-show="isLoading" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                    </circle>
                    <path class="opacity-75" fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                    </path>
                </svg>
                Simpan Draft
            </button>
            <button @click="saveReturn('completed')" :disabled="isLoading || returnItems.length === 0"
                class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 disabled:opacity-50 flex items-center gap-2">
                <svg x-show="isLoading" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                    </circle>
                    <path class="opacity-75" fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                    </path>
                </svg>
                Selesaikan Return
            </button>
        </div>
    </div>
</div>

<!-- VIEW DETAIL MODAL -->
<div x-show="viewModal"
    class="fixed inset-0 z-[9999] flex items-center justify-center bg-slate-900/80 backdrop-blur-sm p-4" x-cloak
    @keydown.escape.window="viewModal = false" style="display: none;">
    <div
        class="bg-white dark:bg-gray-800 rounded-2xl w-full max-w-2xl max-h-[80vh] overflow-hidden flex flex-col shadow-2xl">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
            <h2 class="text-xl font-bold text-gray-800 dark:text-white">Detail Return</h2>
            <button @click="viewModal = false" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                    </path>
                </svg>
            </button>
        </div>
        <div class="flex-1 overflow-y-auto p-6" x-show="selectedReturn">
            <div class="grid grid-cols-2 gap-4 mb-6">
                <div>
                    <p class="text-sm text-gray-500">No. Return</p>
                    <p class="font-bold font-mono" x-text="selectedReturn?.return_number"></p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Tanggal</p>
                    <p class="font-medium" x-text="formatDate(selectedReturn?.date)"></p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Supplier</p>
                    <p class="font-medium" x-text="selectedReturn?.supplier_name"></p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Status</p>
                    <span :class="getStatusColor(selectedReturn?.status)"
                        class="px-2.5 py-1 rounded-full text-xs font-semibold uppercase"
                        x-text="selectedReturn?.status"></span>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Alasan</p>
                    <p class="font-medium" x-text="reasonLabels[selectedReturn?.reason] || selectedReturn?.reason">
                    </p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Total</p>
                    <p class="font-bold text-brand-600" x-text="formatCurrency(selectedReturn?.total_amount)">
                    </p>
                </div>
            </div>

            <h3 class="font-semibold mb-3">Item Return</h3>
            <div class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-3 py-2 text-left">Produk</th>
                            <th class="px-3 py-2 text-center">Qty</th>
                            <th class="px-3 py-2 text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        <template x-for="item in selectedReturnItems" :key="item.id">
                            <tr>
                                <td class="px-3 py-2">
                                    <div class="font-medium" x-text="item.product_name"></div>
                                    <div class="text-xs text-gray-500"
                                        x-text="'Invoice: ' + (item.original_invoice || '-')"></div>
                                </td>
                                <td class="px-3 py-2 text-center"
                                    x-text="formatNumber(item.quantity) + ' ' + (item.unit_name || 'Pcs')"></td>
                                <td class="px-3 py-2 text-right font-medium" x-text="formatCurrency(item.subtotal)">
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</div>
@endsection