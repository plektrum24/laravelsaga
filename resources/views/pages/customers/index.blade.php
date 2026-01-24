@extends('layouts.app')

@section('title', 'Customers | SAGA TOKO APP')

@section('content')
    <div x-data="{
                                page: 'customers',
                                showModal: false,
                                isLoading: false,
                                searchQuery: '',

                                customers: [
                                    { id: 1, name: 'Budi Santoso', email: 'budi@gmail.com', phone: '081234567890', city: 'Jakarta', total_purchases: 2500000, credit_limit: 5000000, total_debt: 750000 },
                                    { id: 2, name: 'Siti Aminah', email: 'siti@yahoo.com', phone: '081987654321', city: 'Bandung', total_purchases: 1500000, credit_limit: 2000000, total_debt: 0 },
                                    { id: 3, name: 'Warung Pojok', email: 'pojok@warung.com', phone: '085678901234', city: 'Surabaya', total_purchases: 500000, credit_limit: 10000000, total_debt: 250000 }
                                ],

                                formData: {
                                    id: null,
                                    name: '',
                                    email: '',
                                    phone: '',
                                    city: '',
                                    address: '',
                                    credit_limit: 0
                                },

                                get filteredCustomers() {
                                    if (!this.searchQuery) return this.customers;
                                    const q = this.searchQuery.toLowerCase();
                                    return this.customers.filter(c => 
                                        c.name.toLowerCase().includes(q) || 
                                        c.email.toLowerCase().includes(q) ||
                                        c.phone.includes(q)
                                    );
                                },

                                openModal(customer = null) {
                                    if (customer) {
                                        this.formData = { ...customer };
                                    } else {
                                        this.formData = {
                                            id: null,
                                            name: '',
                                            email: '',
                                            phone: '',
                                            city: '',
                                            address: ''
                                        };
                                    }
                                    this.showModal = true;
                                },

                                saveCustomer() {
                                    this.isLoading = true;
                                    setTimeout(() => {
                                        if (this.formData.id) {
                                            // Update
                                            const index = this.customers.findIndex(c => c.id === this.formData.id);
                                            if(index !== -1) {
                                                this.customers[index] = { ...this.formData, total_purchases: this.customers[index].total_purchases };
                                            }
                                            Swal.fire({ icon: 'success', title: 'Berhasil', text: 'Data pelanggan diperbarui', timer: 1500, showConfirmButton: false });
                                        } else {
                                            // Create
                                            this.customers.push({ 
                                                ...this.formData, 
                                                id: Date.now(), 
                                                total_purchases: 0 
                                            });
                                            Swal.fire({ icon: 'success', title: 'Berhasil', text: 'Pelanggan baru ditambahkan', timer: 1500, showConfirmButton: false });
                                        }
                                        this.isLoading = false;
                                        this.showModal = false;
                                    }, 800);
                                },

                                deleteCustomer(id) {
                                    Swal.fire({
                                        title: 'Hapus Pelanggan?',
                                        text: 'Data yang dihapus tidak dapat dikembalikan!',
                                        icon: 'warning',
                                        showCancelButton: true,
                                        confirmButtonColors: '#EF4444',
                                        confirmButtonText: 'Ya, Hapus!'
                                    }).then((result) => {
                                        if (result.isConfirmed) {
                                            this.customers = this.customers.filter(c => c.id !== id);
                                            Swal.fire('Terhapus!', 'Data pelanggan telah dihapus.', 'success');
                                        }
                                    });
                                },

                                formatCurrency(amount) {
                                    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(amount || 0);
                                }
                            }" x-init="">

        <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 dark:text-white flex items-center gap-2">
                    <span class="p-2 bg-brand-50 rounded-lg text-brand-600 dark:bg-brand-900/30 dark:text-brand-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z">
                            </path>
                        </svg>
                    </span>
                    Data Pelanggan
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 ml-12">Kelola data pelanggan member & non-member</p>
            </div>
            <button @click="openModal()"
                class="px-5 py-2.5 bg-blue-600 text-white rounded-xl hover:bg-blue-700 shadow-lg shadow-blue-500/30 flex items-center gap-2 font-medium transition-all transform hover:-translate-y-0.5">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Tambah Pelanggan
            </button>
        </div>

        <!-- Search -->
        <div class="mb-6 bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm">
            <div class="relative max-w-md">
                <input type="text" x-model="searchQuery" placeholder="Cari nama, email, atau no. hp..."
                    class="w-full pl-10 pr-4 py-2.5 border border-gray-200 rounded-lg dark:bg-gray-900 dark:border-gray-600 dark:text-white focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </div>
        </div>

        <!-- Customer List -->
        <div
            class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead
                        class="bg-gray-50 dark:bg-gray-700 text-gray-500 dark:text-gray-400 uppercase text-xs font-semibold">
                        <tr>
                            <th class="px-6 py-4">Nama Pelanggan</th>
                            <th class="px-6 py-4">Kontak</th>
                            <th class="px-6 py-4">Kota</th>
                            <th class="px-6 py-4 text-right">Limit Hutang</th>
                            <th class="px-6 py-4 text-right">Sisa Hutang</th>
                            <th class="px-6 py-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        <template x-for="c in filteredCustomers" :key="c.id">
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-brand-100 dark:bg-brand-900/30 flex items-center justify-center text-brand-600 font-bold text-xs uppercase"
                                            x-text="c.name.substring(0,2)"></div>
                                        <div class="font-bold text-gray-800 dark:text-white" x-text="c.name"></div>
                                    </div>
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
                                            <span x-text="c.phone"></span>
                                        </span>
                                        <span class="flex items-center gap-1.5 text-gray-500 text-xs">
                                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                                                </path>
                                            </svg>
                                            <span x-text="c.email"></span>
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-gray-600 dark:text-gray-400" x-text="c.city"></td>
                                <td class="px-6 py-4 text-right">
                                    <span class="font-mono text-gray-600 dark:text-gray-400"
                                        x-text="formatCurrency(c.credit_limit)"></span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex flex-col items-end">
                                        <span class="font-bold"
                                            :class="c.total_debt > 0 ? 'text-red-500' : 'text-green-500'"
                                            x-text="formatCurrency(c.total_debt)"></span>
                                        <div
                                            class="w-20 h-1.5 bg-gray-100 rounded-full mt-1 overflow-hidden dark:bg-gray-700">
                                            <div class="h-full bg-red-500 rounded-full"
                                                :style="`width: ${Math.min((c.total_debt / (c.credit_limit || 1)) * 100, 100)}%`">
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <button @click="openModal(c)"
                                            class="p-1.5 text-blue-500 hover:bg-blue-50 dark:hover:bg-blue-900/30 rounded-lg transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                                </path>
                                            </svg>
                                        </button>
                                        <button @click="deleteCustomer(c.id)"
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
                        <tr x-show="filteredCustomers.length === 0">
                            <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                                Tidak ada data pelanggan ditemukan.
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
                        x-text="formData.id ? 'Edit Pelanggan' : 'Tambah Pelanggan'"></h3>
                    <button @click="showModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                            </path>
                        </svg>
                    </button>
                </div>

                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Nama Lengkap <span
                                class="text-red-500">*</span></label>
                        <input type="text" x-model="formData.name" placeholder="Nama Pelanggan"
                            class="w-full px-4 py-2.5 border border-gray-200 rounded-xl dark:bg-gray-900 dark:border-gray-600 dark:text-white focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">No. HP /
                                WA</label>
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
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Kota</label>
                        <input type="text" x-model="formData.city"
                            class="w-full px-4 py-2.5 border border-gray-200 rounded-xl dark:bg-gray-900 dark:border-gray-600 dark:text-white focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Limit Hutang
                            (Credit Limit)</label>
                        <div class="relative">
                            <span
                                class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 dark:text-gray-400 text-sm font-medium">Rp</span>
                            <input type="number" x-model.number="formData.credit_limit" placeholder="0"
                                class="w-full pl-10 px-4 py-2.5 border border-gray-200 rounded-xl dark:bg-gray-900 dark:border-gray-600 dark:text-white focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 font-mono text-right">
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Batas maksimal hutang yang diperbolehkan untuk pelanggan ini.
                        </p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Alamat
                            Lengkap</label>
                        <textarea x-model="formData.address" rows="2"
                            class="w-full px-4 py-2.5 border border-gray-200 rounded-xl dark:bg-gray-900 dark:border-gray-600 dark:text-white focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500"></textarea>
                    </div>
                </div>

                <div
                    class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 flex justify-end gap-3 bg-gray-50 dark:bg-gray-800/50 rounded-b-2xl">
                    <button @click="showModal = false"
                        class="px-5 py-2.5 text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700 rounded-xl font-medium transition-colors">Batal</button>
                    <button @click="saveCustomer()" :disabled="isLoading || !formData.name"
                        class="px-5 py-2.5 bg-blue-600 text-white rounded-xl hover:bg-blue-700 shadow-lg shadow-blue-500/30 disabled:opacity-50 disabled:shadow-none flex items-center gap-2 font-medium transition-all transform active:scale-95">
                        <span x-show="isLoading"
                            class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                        <span x-text="isLoading ? 'Menyimpan...' : 'Simpan Pelanggan'"></span>
                    </button>
                </div>
            </div>
        </div>

    </div>
@endsection