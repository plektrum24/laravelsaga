@extends('layouts.app')

@section('title', 'Goods In | SAGA TOKO APP')

@section('content')

<div x-data="{ 
      page: 'goodsIn', 
      loaded: true, 
      darkMode: false, 
      showModal: false, 
      isLoading: false,
      
      suppliers: [
          { id: 1, name: 'PT. Sumber Makmur', code: 'SUP-001' },
          { id: 2, name: 'CV. Maju Jaya', code: 'SUP-002' },
          { id: 3, name: 'Toko Sejahtera', code: 'SUP-003' }
      ],
      products: [],
      selectedSupplier: '',
      invoiceNumber: '',
      items: [],
      searchProduct: '',
      searchResults: [],
      purchaseDate: new Date().toISOString().split('T')[0],
      paymentStatus: 'paid',
      dueDate: '',
      paidAmount: 0,
      
      // Mock Data for UI Visualization
      purchases: [
          { id: 1, invoice_number: 'GRN-20240120-001', date: '2024-01-20', supplier_name: 'PT. Sumber Makmur', total_amount: 5200000, payment_status: 'paid', due_date: null, supplier_id: 1 },
          { id: 2, invoice_number: 'GRN-20240121-002', date: '2024-01-21', supplier_name: 'CV. Maju Jaya', total_amount: 1500000, payment_status: 'unpaid', due_date: '2024-02-21', supplier_id: 2 },
          { id: 3, invoice_number: 'GRN-20240122-003', date: '2024-01-22', supplier_name: 'Toko Sejahtera', total_amount: 750000, payment_status: 'paid', due_date: null, supplier_id: 3 },
      ],
      searchQuery: '',
      
      viewModal: false,
      selectedPurchase: null,
      selectedPurchaseItems: [],
      
      isEditing: false,
      editingId: null,
      
      scannerEnabled: false,
      
      async init() {
        const token = localStorage.getItem('saga_token');
        try {
          const prodRes = await fetch('/api/products', { headers: { 'Authorization': 'Bearer ' + token } });
          const prodData = await prodRes.json();
          if (prodData.success) this.products = prodData.data.products || [];
        } catch(e) { console.error(e); }
        this.generateInvoice();
      },

      generateInvoice() {
        if(this.isEditing) return;
        const date = new Date();
        this.invoiceNumber = 'GRN-' + date.getFullYear() + (date.getMonth()+1).toString().padStart(2,'0') + date.getDate().toString().padStart(2,'0') + '-' + Math.floor(Math.random() * 1000).toString().padStart(3,'0');
      },

      get filteredPurchases() {
        if (!this.searchQuery) return this.purchases;
        return this.purchases.filter(p =>
            p.invoice_number.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
            (p.supplier_name && p.supplier_name.toLowerCase().includes(this.searchQuery.toLowerCase()))
        );
      },

      addItem(product) {
        const existing = this.items.find(i => i.product_id === product.id);
        if (existing) {
            existing.quantity++;
        } else {
            this.items.push({
                product_id: product.id,
                name: product.name,
                sku: product.sku,
                units: product.units || [],
                quantity: 1,
                cost_price: product.cost_price || 0
            });
        }
        this.searchProduct = '';
      },

      removeItem(index) {
        this.items.splice(index, 1);
      },

      get total() {
        return this.items.reduce((sum, i) => sum + (i.quantity * i.cost_price), 0);
      },

      formatCurrency(amount) {
        return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(amount || 0);
      },

      formatDate(dateString) {
        if(!dateString) return '-';
        return new Date(dateString).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
      },

      getStatusColor(status) {
        switch(status) {
            case 'paid': return 'bg-green-100 text-green-700 border-green-200';
            case 'unpaid': return 'bg-red-100 text-red-700 border-red-200';
            case 'partial': return 'bg-yellow-100 text-yellow-700 border-yellow-200';
            default: return 'bg-gray-100 text-gray-700 border-gray-200';
        }
      },

      get pendingCount() {
        return this.purchases.filter(p => p.payment_status !== 'paid').length;
      },

      get todayCount() {
        const today = new Date().toISOString().split('T')[0];
        return this.purchases.filter(p => p.date === today).length;
      },
      
      resetForm() {
        this.items = [];
        this.selectedSupplier = '';
        this.paymentStatus = 'paid';
        this.dueDate = '';
        this.isEditing = false;
        this.editingId = null;
        this.generateInvoice();
      },

      // Keep simplified edit/delete/save for cleaner code
      editPurchase(id) { 
          // Implementation... 
          /* Reuse existing logic or simplified */
          const p = this.purchases.find(x => x.id == id);
          if(p) {
              this.isEditing = true;
              this.editingId = p.id;
              this.invoiceNumber = p.invoice_number;
              this.paymentStatus = p.payment_status;
              this.selectedSupplier = p.supplier_id;
              this.showModal = true;
          }
      },
      deletePurchase(id) { return; },
      async saveReceipt() { 
          this.isLoading = true;
          setTimeout(() => {
              this.isLoading = false;
              this.showModal = false;
              Swal.fire('Berhasil', 'Data tersimpan (Simulasi)', 'success');
              if(!this.isEditing) {
                  this.purchases.unshift({
                      id: Date.now(),
                      invoice_number: this.invoiceNumber,
                      date: this.purchaseDate,
                      supplier_name: this.suppliers.find(s => s.id == this.selectedSupplier)?.name || 'Unknown',
                      total_amount: this.total,
                      payment_status: this.paymentStatus,
                      due_date: this.dueDate
                  });
              }
              this.resetForm();
          }, 1000);
      }
    }" x-init="init()">

    <div class="mx-auto max-w-7xl p-4 md:p-6">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 dark:text-white flex items-center gap-2">
                    <span class="p-2 bg-blue-100 rounded-lg text-blue-600 dark:bg-blue-900/30 dark:text-blue-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10">
                            </path>
                        </svg>
                    </span>
                    Goods In (Penerimaan)
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 ml-12">Kelola stok masuk dan pembelian dari
                    supplier.</p>
            </div>
            <button @click="showModal = true; resetForm()"
                class="px-5 py-2.5 bg-brand-600 text-white rounded-xl hover:bg-brand-700 flex items-center gap-2 font-medium shadow-lg shadow-brand-500/20 transition-all hover:-translate-y-0.5">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                <span>Terima Barang Baru</span>
            </button>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-8">
            <div
                class="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-gray-100 dark:border-gray-700 shadow-sm relative overflow-hidden group">
                <div
                    class="absolute right-0 top-0 w-24 h-24 bg-blue-50 dark:bg-blue-900/20 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110">
                </div>
                <div class="relative">
                    <p class="text-gray-500 dark:text-gray-400 text-sm font-medium mb-1">Total Goods Received</p>
                    <h3 class="text-3xl font-bold text-gray-800 dark:text-white" x-text="purchases.length">0</h3>
                    <p class="text-xs text-blue-500 mt-2 font-medium flex items-center gap-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        All Time
                    </p>
                </div>
            </div>

            <div
                class="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-gray-100 dark:border-gray-700 shadow-sm relative overflow-hidden group">
                <div
                    class="absolute right-0 top-0 w-24 h-24 bg-green-50 dark:bg-green-900/20 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110">
                </div>
                <div class="relative">
                    <p class="text-gray-500 dark:text-gray-400 text-sm font-medium mb-1">Hari Ini</p>
                    <h3 class="text-3xl font-bold text-gray-800 dark:text-white" x-text="todayCount">0</h3>
                    <p class="text-xs text-green-500 mt-2 font-medium flex items-center gap-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Transaksi Baru
                    </p>
                </div>
            </div>

            <div
                class="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-gray-100 dark:border-gray-700 shadow-sm relative overflow-hidden group">
                <div
                    class="absolute right-0 top-0 w-24 h-24 bg-red-50 dark:bg-red-900/20 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110">
                </div>
                <div class="relative">
                    <p class="text-gray-500 dark:text-gray-400 text-sm font-medium mb-1">Belum Lunas (Hutang)</p>
                    <h3 class="text-3xl font-bold text-red-500" x-text="pendingCount">0</h3>
                    <p class="text-xs text-red-400 mt-2 font-medium flex items-center gap-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                            </path>
                        </svg>
                        Perlu Tindakan
                    </p>
                </div>
            </div>
        </div>

        <!-- Filters & Search -->
        <div class="flex flex-col md:flex-row gap-4 mb-6">
            <div class="relative flex-1">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
                <input type="text" x-model="searchQuery" placeholder="Cari No. Invoice atau Supplier..."
                    class="w-full pl-10 px-4 py-2.5 border border-gray-200 rounded-xl dark:bg-gray-800 dark:border-gray-700 dark:text-white focus:ring-brand-500 focus:border-brand-500">
            </div>
            <div class="flex gap-2">
                <button
                    class="px-4 py-2.5 border border-gray-200 rounded-xl hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800 text-gray-600 dark:text-gray-300 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z">
                        </path>
                    </svg>
                    Filter
                </button>
            </div>
        </div>

        <!-- Table -->
        <div
            class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-50 dark:bg-gray-900/50 text-gray-500 uppercase text-xs font-semibold">
                        <tr>
                            <th class="px-6 py-4">No. Invoice (GRN)</th>
                            <th class="px-6 py-4">Tanggal</th>
                            <th class="px-6 py-4">Supplier</th>
                            <th class="px-6 py-4 text-center">Status Bayar</th>
                            <th class="px-6 py-4 text-right">Total Nominal</th>
                            <th class="px-6 py-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        <template x-for="p in filteredPurchases" :key="p.id">
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                                <td class="px-6 py-4 font-bold text-gray-800 dark:text-white" x-text="p.invoice_number">
                                </td>
                                <td class="px-6 py-4 text-gray-600 dark:text-gray-300">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                                            </path>
                                        </svg>
                                        <span x-text="formatDate(p.date)"></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 font-medium text-gray-700 dark:text-gray-200"
                                    x-text="p.supplier_name"></td>
                                <td class="px-6 py-4 text-center">
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold border capitalize"
                                        :class="getStatusColor(p.payment_status)" x-text="p.payment_status"></span>
                                    <div x-show="p.payment_status !== 'paid' && p.due_date"
                                        class="text-xs text-red-500 mt-1">
                                        Jatuh Tempo: <span x-text="formatDate(p.due_date)"></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-right font-bold text-gray-800 dark:text-white"
                                    x-text="formatCurrency(p.total_amount)"></td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-center gap-2">
                                        <button
                                            class="p-2 text-gray-500 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors dark:hover:bg-blue-900/30 dark:hover:text-blue-400"
                                            title="Lihat Detail">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                                </path>
                                            </svg>
                                        </button>
                                        <button
                                            class="p-2 text-gray-500 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors dark:hover:bg-red-900/30 dark:hover:text-red-400"
                                            title="Hapus">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
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

            <div x-show="filteredPurchases.length === 0" class="p-12 text-center">
                <div
                    class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4 dark:bg-gray-800">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                        </path>
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Belum ada data</h3>
                <p class="text-gray-500 dark:text-gray-400 mt-1">Belum ada barang masuk yang
                    tercatat.</p>
            </div>
            </div>
        </div>
    </div>
    <!-- Modals are now inside x-data -->

<!-- CREATE MODAL -->
<div x-show="showModal"
    class="fixed inset-0 z-[9999] flex items-center justify-center bg-slate-900/80 backdrop-blur-sm p-4" x-cloak
    style="display: none;">
    <div
        class="bg-white dark:bg-gray-800 rounded-2xl w-full max-w-7xl max-h-[90vh] overflow-hidden flex flex-col shadow-2xl">
        <!-- Modal Header -->
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
            <h2 class="text-xl font-bold text-gray-800 dark:text-white"
                x-text="isEditing ? 'Edit Goods Receipt' : 'New Goods Received Note'"></h2>
            <button @click="showModal = false"
                class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                    </path>
                </svg>
            </button>
        </div>

        <!-- Modal Body (Scrollable) -->
        <div class="flex-1 overflow-y-auto p-6 bg-gray-50 dark:bg-gray-900/50 overflow-visible">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Left: Form Input -->
                <div class="space-y-6">
                    <!-- 1. Receipt Information -->
                    <div
                        class="bg-white dark:bg-gray-800 p-5 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
                        <h3 class="font-semibold text-gray-800 dark:text-white mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                </path>
                            </svg>
                            Informasi Receipt
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">GRN
                                    Number</label>
                                <input type="text" x-model="invoiceNumber" readonly
                                    class="w-full px-3 py-2 border border-gray-200 rounded-lg bg-gray-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200 text-sm">
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Tanggal</label>
                                <input type="date" x-model="purchaseDate"
                                    class="w-full px-3 py-2 border border-gray-200 rounded-lg dark:bg-gray-900 dark:border-gray-600 dark:text-white text-sm">
                            </div>
                            <div class="md:col-span-2">
                                <label
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Supplier</label>
                                <select x-model="selectedSupplier"
                                    class="w-full px-3 py-2 border border-gray-200 rounded-lg dark:bg-gray-900 dark:border-gray-600 dark:text-white text-sm">
                                    <option value="">-- Pilih Supplier --</option>
                                    <template x-for="s in suppliers" :key="s.id">
                                        <option :value="s.id" x-text="s.name"></option>
                                    </template>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- 2. Payment Information -->
                    <div
                        class="bg-white dark:bg-gray-800 p-5 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
                        <h3 class="font-semibold text-gray-800 dark:text-white mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z">
                                </path>
                            </svg>
                            Pembayaran
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Metode
                                    Bayar</label>
                                <select x-model="paymentStatus"
                                    class="w-full px-3 py-2 border border-gray-200 rounded-lg dark:bg-gray-900 dark:border-gray-600 dark:text-white text-sm">
                                    <option value="paid">Lunas (Cash/Transfer)</option>
                                    <option value="unpaid">Hutang (Termin)</option>
                                    <option value="partial">Bayar Sebagian</option>
                                </select>
                            </div>
                            <div x-show="paymentStatus !== 'paid'">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Jatuh
                                    Tempo</label>
                                <input type="date" x-model="dueDate"
                                    class="w-full px-3 py-2 border border-gray-200 rounded-lg dark:bg-gray-900 dark:border-gray-600 dark:text-white text-sm">
                            </div>
                            <div x-show="paymentStatus === 'partial'" class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Jumlah
                                    Dibayar Awal</label>
                                <div class="relative">
                                    <span
                                        class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-sm">Rp</span>
                                    <input type="number" x-model.number="paidAmount"
                                        class="w-full pl-10 px-3 py-2 border border-gray-200 rounded-lg dark:bg-gray-900 dark:border-gray-600 dark:text-white text-sm">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 3. Add Products -->
                    <div
                        class="bg-white dark:bg-gray-800 p-5 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
                        <h3 class="font-semibold text-gray-800 dark:text-white mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4">
                                </path>
                            </svg>
                            Input Produk
                        </h3>
                        <div class="relative">
                            <div class="flex gap-2 mb-2">
                                <button @click="toggleScanner()" type="button"
                                    class="flex-1 px-3 py-2 text-xs font-medium rounded-lg border flex items-center justify-center gap-2 transition-colors"
                                    :class="scannerEnabled ? 'bg-blue-600 text-white border-blue-600' : 'bg-gray-50 text-gray-600 border-gray-200 hover:bg-gray-100 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300'">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z">
                                        </path>
                                    </svg>
                                    <span x-text="scannerEnabled ? 'Scanner Aktif' : 'Aktifkan Scanner'"></span>
                                </button>
                            </div>
                            <input type="text" x-model="searchProduct" @input="performSearch()"
                                placeholder="Cari produk (Nama atau SKU)..."
                                class="w-full px-4 py-3 pl-11 border border-brand-200 focus:border-brand-500 focus:ring-2 focus:ring-brand-200 rounded-xl dark:bg-gray-900 dark:border-gray-600 dark:text-white transition-all">
                            <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>

                            <!-- Dropdown Search -->
                            <div x-show="searchProduct && searchResults.length > 0"
                                class="absolute z-[100] mt-1 w-full bg-white border border-gray-200 rounded-xl shadow-xl max-h-60 overflow-y-auto dark:bg-gray-800 dark:border-gray-700">
                                <template x-for="product in searchResults" :key="product.id">
                                    <button @click="addItem(product); searchResults = []"
                                        class="w-full px-4 py-3 text-left hover:bg-gray-50 flex items-center justify-between dark:hover:bg-gray-700 border-b border-gray-50 dark:border-gray-700 last:border-0 transition-colors">
                                        <div>
                                            <p class="font-medium text-gray-800 dark:text-white" x-text="product.name">
                                            </p>
                                            <div class="flex items-center gap-2 mt-0.5">
                                                <span class="text-xs text-gray-500" x-text="product.sku"></span>
                                                <span class="text-xs text-gray-400">•</span>
                                                <span class="text-xs text-gray-500"
                                                    x-text="product.units?.[0]?.unit_name || 'Pcs'"></span>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <span
                                                class="text-xs font-semibold text-brand-600 bg-brand-50 px-2 py-1 rounded dark:bg-brand-900/30 dark:text-brand-400"
                                                x-text="formatCurrency(product.units?.[0]?.buy_price || product.buy_price || 0)"></span>
                                        </div>
                                    </button>
                                </template>
                            </div>
                            <div x-show="searchProduct && searchResults.length === 0"
                                class="absolute z-[100] mt-1 w-full bg-white border border-gray-200 rounded-xl shadow-xl p-4 text-center text-gray-500 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400">
                                Produk tidak ditemukan
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right: Summary & Items List -->
                <div class="space-y-6">
                    <div
                        class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm flex flex-col h-full">
                        <div class="p-4 border-b border-gray-100 dark:border-gray-700">
                            <h3 class="font-semibold text-gray-800 dark:text-white">Daftar Item</h3>
                        </div>
                        <div class="flex-1 overflow-y-auto min-h-[300px] max-h-[500px] p-3">
                            <template x-for="(item, idx) in items" :key="idx">
                                <div
                                    class="p-3 mb-2 bg-gray-50 dark:bg-gray-700/30 rounded-lg group relative hover:shadow-sm transition-all border border-transparent hover:border-gray-200 dark:hover:border-gray-600">
                                    <div class="flex justify-between items-start mb-2">
                                        <div class="pr-6">
                                            <p class="font-medium text-gray-800 dark:text-white text-sm line-clamp-2"
                                                x-text="item.name"></p>
                                            <div class="flex flex-wrap items-center gap-x-2 gap-y-1 mt-0.5">
                                                <p class="text-[10px] text-gray-400" x-text="item.sku">
                                                </p>
                                                <span
                                                    class="text-[10px] text-gray-300 dark:text-gray-600 hidden xs:inline">•</span>
                                                <p class="text-[10px] font-medium text-brand-600 dark:text-brand-400"
                                                    x-text="formatCurrency(item.quantity * item.cost_price)">
                                                </p>
                                            </div>
                                        </div>
                                        <button @click="removeItem(idx)"
                                            class="text-gray-400 hover:text-red-500 transition-colors absolute top-2 right-2 p-1 rounded-md hover:bg-red-50 dark:hover:bg-red-900/30">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                        </button>
                                    </div>

                                    <!-- Control Rows -->
                                    <div class="space-y-2 mt-3">
                                        <!-- Row 1: Unit & Quantity -->
                                        <div class="flex items-center gap-2">
                                            <select
                                                @change="item.selectedUnit = item.units[$event.target.selectedIndex]; item.cost_price = item.units[$event.target.selectedIndex].buy_price || 0"
                                                class="flex-1 text-xs border border-gray-200 rounded-lg px-2 py-1.5 dark:bg-gray-800 dark:border-gray-600 dark:text-white focus:ring-1 focus:ring-brand-500 focus:border-brand-500">
                                                <template x-for="(u, uIdx) in item.units" :key="u.unit_id">
                                                    <option :value="uIdx"
                                                        :selected="item.selectedUnit?.unit_id === u.unit_id"
                                                        x-text="u.unit_name"></option>
                                                </template>
                                            </select>
                                            <div
                                                class="shrink-0 flex items-center bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-600 h-[30px]">
                                                <button
                                                    class="w-7 h-full flex items-center justify-center text-gray-500 hover:text-brand-500 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-l-lg transition-colors"
                                                    @click="if(item.quantity > 1) item.quantity--">-</button>
                                                <input type="number" x-model.number="item.quantity"
                                                    class="w-10 h-full text-center text-xs border-0 p-0 focus:ring-0 dark:bg-gray-800 dark:text-white font-medium">
                                                <button
                                                    class="w-7 h-full flex items-center justify-center text-gray-500 hover:text-brand-500 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-r-lg transition-colors"
                                                    @click="item.quantity++">+</button>
                                            </div>
                                        </div>

                                        <!-- Row 2: Expired & Price -->
                                        <div class="flex items-center gap-2">
                                            <div class="flex-1 relative">
                                                <span
                                                    class="absolute left-2 top-1/2 -translate-y-1/2 text-[10px] text-gray-400 pointer-events-none">Exp:</span>
                                                <input type="date" x-model="item.expiry_date"
                                                    class="w-full text-xs border border-gray-200 rounded-lg pl-8 pr-2 py-1.5 dark:bg-gray-800 dark:border-gray-600 dark:text-white dark:[color-scheme:dark] focus:ring-1 focus:ring-brand-500 focus:border-brand-500">
                                            </div>
                                            <div class="flex-1 relative">
                                                <span
                                                    class="absolute left-2 top-1/2 -translate-y-1/2 text-[10px] text-gray-400 pointer-events-none">Rp</span>
                                                <input type="number" x-model.number="item.cost_price"
                                                    class="w-full text-right text-xs border border-gray-200 rounded-lg pl-6 pr-2 py-1.5 dark:bg-gray-800 dark:border-gray-600 dark:text-white focus:ring-1 focus:ring-brand-500 focus:border-brand-500"
                                                    placeholder="Harga Beli">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>
                            <div x-show="items.length === 0"
                                class="flex flex-col items-center justify-center h-40 text-gray-400">
                                <svg class="w-10 h-10 mb-2 opacity-50" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4">
                                    </path>
                                </svg>
                                <p class="text-sm">Belum ada item</p>
                            </div>
                        </div>
                        <!-- Summary Footer -->
                        <div
                            class="p-4 bg-gray-50 dark:bg-gray-900 border-t border-gray-200 dark:border-gray-700 rounded-b-xl">
                            <div class="space-y-2 mb-4">
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-500">Total Item</span>
                                    <span class="font-medium dark:text-white"
                                        x-text="items.reduce((s, i) => s + i.quantity, 0)">0</span>
                                </div>
                                <div class="flex justify-between text-lg font-bold">
                                    <span class="text-gray-800 dark:text-white">Total</span>
                                    <span class="text-brand-600" x-text="formatCurrency(total)">Rp
                                        0</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Footer -->
        <div
            class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 bg-white dark:bg-gray-800 flex justify-end gap-3">
            <button @click="showModal = false"
                class="px-5 py-2.5 border border-gray-200 text-gray-700 rounded-xl hover:bg-gray-50 font-medium dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-700 transition-colors">Batal</button>
            <button @click="saveReceipt()" :disabled="isLoading || items.length === 0"
                class="px-5 py-2.5 bg-brand-500 text-white rounded-xl hover:bg-brand-600 shadow-lg shadow-brand-500/30 disabled:opacity-50 disabled:shadow-none flex items-center gap-2 font-medium transition-all transform active:scale-95">
                <svg x-show="isLoading" class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                    </circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z">
                    </path>
                </svg>
                <span x-text="isLoading ? 'Menyimpan...' : 'Simpan Transaksi'">Simpan Transaksi</span>
            </button>
        </div>
    </div>
</div>

<!-- DETAIL MODAL (View Purchase) -->
<div x-show="viewModal"
    class="fixed inset-0 z-[9999] flex items-center justify-center bg-gray-900/80 backdrop-blur-sm p-4 transition-all duration-300"
    x-cloak x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
    x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
    <div class="bg-white dark:bg-gray-800 rounded-2xl w-full max-w-4xl max-h-[90vh] overflow-hidden flex flex-col shadow-2xl transform transition-all"
        x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100" x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
            <h2 class="text-xl font-bold text-gray-800 dark:text-white">Detail Goods Received</h2>
            <button @click="viewModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                    </path>
                </svg>
            </button>
        </div>

        <div class="flex-1 overflow-y-auto p-6 bg-gray-50 dark:bg-gray-900/50" x-show="selectedPurchase">
            <!-- Info Header -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div class="bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-200 dark:border-gray-700">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Invoice / GRN</p>
                    <p class="font-bold text-gray-800 dark:text-white" x-text="selectedPurchase?.invoice_number">
                    </p>
                </div>
                <div class="bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-200 dark:border-gray-700">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Supplier</p>
                    <p class="font-bold text-gray-800 dark:text-white" x-text="selectedPurchase?.supplier_name">
                    </p>
                </div>
                <div class="bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-200 dark:border-gray-700">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Tanggal</p>
                    <p class="font-bold text-gray-800 dark:text-white" x-text="formatDate(selectedPurchase?.date)">
                    </p>
                </div>
            </div>

            <!-- Items Table -->
            <div
                class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden mb-6">
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-50 dark:bg-gray-700 text-gray-500 dark:text-gray-400 uppercase text-xs">
                        <tr>
                            <th class="px-6 py-3">Produk</th>
                            <th class="px-6 py-3 text-right">Harga Beli</th>
                            <th class="px-6 py-3 text-center">Qty</th>
                            <th class="px-6 py-3 text-center">Expired</th>
                            <th class="px-6 py-3 text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        <template x-for="item in selectedPurchaseItems" :key="item.id">
                            <tr class="dark:text-gray-300">
                                <td class="px-6 py-4">
                                    <p class="font-medium text-gray-800 dark:text-white" x-text="item.product_name">
                                    </p>
                                    <p class="text-xs text-gray-500" x-text="item.sku"></p>
                                </td>
                                <td class="px-6 py-4 text-right" x-text="formatCurrency(item.unit_price)"></td>
                                <td class="px-6 py-4 text-center"
                                    x-text="item.quantity + ' ' + (item.unit_name || 'Pcs')"></td>
                                <td class="px-6 py-4 text-center">
                                    <span x-show="item.expiry_date" x-text="formatDate(item.expiry_date)"
                                        class="px-2 py-1 rounded bg-gray-100 dark:bg-gray-700 text-xs"></span>
                                    <span x-show="!item.expiry_date">-</span>
                                </td>
                                <td class="px-6 py-4 text-right font-medium" x-text="formatCurrency(item.subtotal)">
                                </td>
                            </tr>
                        </template>
                    </tbody>
                    <tfoot class="bg-gray-50 dark:bg-gray-700/50">
                        <tr>
                            <td colspan="3" class="px-6 py-4 text-right font-bold text-gray-700 dark:text-gray-300">
                                Total
                            </td>
                            <td class="px-6 py-4 text-right font-bold text-brand-600"
                                x-text="formatCurrency(selectedPurchase?.total_amount)"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- Payment Info -->
            <div
                class="bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-200 dark:border-gray-700 flex justify-between items-center">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Status Pembayaran</p>
                    <span :class="getStatusColor(selectedPurchase?.payment_status)"
                        class="px-2.5 py-1 rounded-full text-xs font-semibold uppercase tracking-wide inline-block mt-1"
                        x-text="selectedPurchase?.payment_status"></span>
                </div>
                <div class="text-right">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Total Dibayar</p>
                    <p class="font-bold text-green-600" x-text="formatCurrency(selectedPurchase?.paid_amount)">
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
    </div> <!-- End x-data -->
@endsection