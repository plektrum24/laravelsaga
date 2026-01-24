@extends('layouts.app')

@section('title', 'Suppliers | SAGA TOKO APP')

@section('content')
    <div x-data="{
                page: 'suppliers',
                showModal: false,
                viewModal: false,
                isLoading: false,
                searchQuery: '',

                suppliers: [
                    { id: 1, code: 'SUP-001', name: 'PT. Distribusi Maju', email: 'sales@distmaju.com', phone: '081234567890', address: 'Jl. Sudirman No. 45, Jakarta', total_orders: 15, total_debt: 2500000 },
                    { id: 2, code: 'SUP-002', name: 'CV. Berkah Abadi', email: 'admin@berkahabadi.com', phone: '081987654321', address: 'Jl. Ahmad Yani No. 12, Bandung', total_orders: 8, total_debt: 0 },
                    { id: 3, code: 'SUP-003', name: 'UD. Sumber Rejeki', email: 'sumber@rejeki.com', phone: '085678901234', address: 'Jl. Merdeka No. 88, Surabaya', total_orders: 22, total_debt: 12500000 }
                ],

                formData: {
                    code: '',
                    name: '',
                    email: '',
                    phone: '',
                    address: ''
                },

                selectedSupplier: null,

                get filteredSuppliers() {
                    if (!this.searchQuery) return this.suppliers;
                    const q = this.searchQuery.toLowerCase();
                    return this.suppliers.filter(s => 
                        s.name.toLowerCase().includes(q) || 
                        s.code.toLowerCase().includes(q) ||
                        s.phone.includes(q)
                    );
                },

                openModal(supplier = null) {
                    if (supplier) {
                        this.selectedSupplier = supplier;
                        this.formData = { ...supplier };
                    } else {
                        this.selectedSupplier = null;
                        this.formData = {
                            code: 'SUP-' + String(this.suppliers.length + 1).padStart(3, '0'),
                            name: '',
                            email: '',
                            phone: '',
                            address: ''
                        };
                    }
                    this.showModal = true;
                },

                saveSupplier() {
                    this.isLoading = true;
                    setTimeout(() => {
                        if (this.selectedSupplier) {
                            // Update
                            const index = this.suppliers.findIndex(s => s.id === this.selectedSupplier.id);
                            if(index !== -1) {
                                this.suppliers[index] = { ...this.formData, id: this.selectedSupplier.id, total_orders: this.selectedSupplier.total_orders, total_debt: this.selectedSupplier.total_debt };
                            }
                            Swal.fire({ icon: 'success', title: 'Berhasil', text: 'Data supplier diperbarui', timer: 1500, showConfirmButton: false });
                        } else {
                            // Create
                            this.suppliers.push({ 
                                ...this.formData, 
                                id: Date.now(), 
                                total_orders: 0, 
                                total_debt: 0 
                            });
                            Swal.fire({ icon: 'success', title: 'Berhasil', text: 'Supplier baru ditambahkan', timer: 1500, showConfirmButton: false });
                        }
                        this.isLoading = false;
                        this.showModal = false;
                    }, 800);
                },

                deleteSupplier(id) {
                    Swal.fire({
                        title: 'Hapus Supplier?',
                        text: 'Data yang dihapus tidak dapat dikembalikan!',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColors: '#EF4444',
                        confirmButtonText: 'Ya, Hapus!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            this.suppliers = this.suppliers.filter(s => s.id !== id);
                            Swal.fire('Terhapus!', 'Data supplier telah dihapus.', 'success');
                        }
                    });
                },

                formatCurrency(amount) {
                    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(amount || 0);
                }
            }" x-init="
                // Initialize things if needed
            ">

        <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 dark:text-white flex items-center gap-2">
                    <span class="p-2 bg-brand-50 rounded-lg text-brand-600 dark:bg-brand-900/30 dark:text-brand-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                            </path>
                        </svg>
                    </span>
                    Data Supplier
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 ml-12">Kelola data pemasok barang anda</p>
            </div>
            <button @click="openModal()"
                class="px-5 py-2.5 bg-blue-600 text-white rounded-xl hover:bg-blue-700 shadow-lg shadow-blue-500/30 flex items-center gap-2 font-medium transition-all transform hover:-translate-y-0.5">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Tambah Supplier
            </button>
        </div>

        <!-- Search & Filters -->
        <div class="mb-6 bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm">
            <div class="relative max-w-md">
                <input type="text" x-model="searchQuery" placeholder="Cari nama, kode, atau no. telp..."
                    class="w-full pl-10 pr-4 py-2.5 border border-gray-200 rounded-lg dark:bg-gray-900 dark:border-gray-600 dark:text-white focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </div>
        </div>

        <!-- Supplier List -->
        <div
            class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead
                        class="bg-gray-50 dark:bg-gray-700 text-gray-500 dark:text-gray-400 uppercase text-xs font-semibold">
                        <tr>
                            <th class="px-6 py-4">Kode</th>
                            <th class="px-6 py-4">Nama Perusahaan</th>
                            <th class="px-6 py-4">Kontak</th>
                            <th class="px-6 py-4 text-center">Total Order</th>
                            <th class="px-6 py-4 text-right">Total Hutang</th>
                            <th class="px-6 py-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        <template x-for="s in filteredSuppliers" :key="s.id">
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                <td class="px-6 py-4 font-mono text-gray-600 dark:text-gray-400" x-text="s.code"></td>
                                <td class="px-6 py-4">
                                    <div class="font-bold text-gray-800 dark:text-white" x-text="s.name"></div>
                                    <div class="text-xs text-gray-500 truncate max-w-[200px]" x-text="s.address"></div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col gap-1">
                                        <span class="flex items-center gap-1.5 text-gray-600 dark:text-gray-300">
                                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z">
                                                </path>
                                            </svg>
                                            <span x-text="s.phone"></span>
                                        </span>
                                        <span class="flex items-center gap-1.5 text-gray-500 text-xs">
                                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                                                </path>
                                            </svg>
                                            <span x-text="s.email"></span>
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span
                                        class="bg-blue-50 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400 px-2.5 py-1 rounded-full text-xs font-bold"
                                        x-text="s.total_orders"></span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <span class="font-bold" :class="s.total_debt > 0 ? 'text-red-500' : 'text-green-500'"
                                        x-text="formatCurrency(s.total_debt)"></span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <button @click="openModal(s)"
                                            class="p-1.5 text-blue-500 hover:bg-blue-50 dark:hover:bg-blue-900/30 rounded-lg transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                                </path>
                                            </svg>
                                        </button>
                                        <button @click="deleteSupplier(s.id)"
                                            class="p-1.5 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-lg transition-colors">
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
                        <tr x-show="filteredSuppliers.length === 0">
                            <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                Tidak ada data supplier ditemukan.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ADD/EDIT MODAL -->
        <div x-show="showModal"
            class="fixed inset-0 z-[9999] flex items-center justify-center bg-gray-900/80 backdrop-blur-sm p-4 transition-all duration-300"
            x-cloak x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

            <div class="bg-white dark:bg-gray-800 rounded-2xl w-full max-w-lg shadow-2xl transform transition-all"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                @click.outside="showModal = false">

                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                    <h3 class="text-xl font-bold text-gray-800 dark:text-white"
                        x-text="selectedSupplier ? 'Edit Supplier' : 'Tambah Supplier'"></h3>
                    <button @click="showModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                            </path>
                        </svg>
                    </button>
                </div>

                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Kode
                            Supplier</label>
                        <input type="text" x-model="formData.code" readonly
                            class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-gray-500 font-mono text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Nama Perusahaan
                            <span class="text-red-500">*</span></label>
                        <input type="text" x-model="formData.name" placeholder="Contoh: PT. Sumber Rejeki"
                            class="w-full px-4 py-2.5 border border-gray-200 rounded-xl dark:bg-gray-900 dark:border-gray-600 dark:text-white focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">No.
                                Telepon</label>
                            <input type="text" x-model="formData.phone"
                                class="w-full px-4 py-2.5 border border-gray-200 rounded-xl dark:bg-gray-900 dark:border-gray-600 dark:text-white focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Email</label>
                            <input type="email" x-model="formData.email"
                                class="w-full px-4 py-2.5 border border-gray-200 rounded-xl dark:bg-gray-900 dark:border-gray-600 dark:text-white focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Alamat</label>
                        <textarea x-model="formData.address" rows="3"
                            class="w-full px-4 py-2.5 border border-gray-200 rounded-xl dark:bg-gray-900 dark:border-gray-600 dark:text-white focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500"></textarea>
                    </div>
                </div>

                <div
                    class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 flex justify-end gap-3 bg-gray-50 dark:bg-gray-800/50 rounded-b-2xl">
                    <button @click="showModal = false"
                        class="px-5 py-2.5 text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700 rounded-xl font-medium transition-colors">Batal</button>
                    <button @click="saveSupplier()" :disabled="isLoading || !formData.name"
                        class="px-5 py-2.5 bg-blue-600 text-white rounded-xl hover:bg-blue-700 shadow-lg shadow-blue-500/30 disabled:opacity-50 disabled:shadow-none flex items-center gap-2 font-medium transition-all transform active:scale-95">
                        <span x-show="isLoading"
                            class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                        <span x-text="isLoading ? 'Menyimpan...' : 'Simpan Supplier'"></span>
                    </button>
                </div>
            </div>
        </div>

    </div>
@endsection