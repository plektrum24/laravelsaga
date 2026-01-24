@extends('layouts.app')

@section('title', 'Branches | SAGA TOKO APP')

@section('content')
    <div x-data="{
        page: 'branches',
        showModal: false,
        isLoading: false,
        searchQuery: '',

        branches: [
            { id: 1, name: 'Pusat (Jakarta)', address: 'Jl. Sudirman No 1', phone: '021-5551234', head: 'Reza Akbar', type: 'Pusat' },
            { id: 2, name: 'Cabang Bandung', address: 'Jl. Asia Afrika No 88', phone: '022-7778888', head: 'Dina Marlina', type: 'Cabang' },
            { id: 3, name: 'Cabang Surabaya', address: 'Jl. Tunjungan No 45', phone: '031-9990000', head: 'Budi Santoso', type: 'Cabang' }
        ],

        formData: {
            id: null,
            name: '',
            address: '',
            phone: '',
            head: '',
            type: 'Cabang'
        },

        get filteredBranches() {
            if (!this.searchQuery) return this.branches;
            const q = this.searchQuery.toLowerCase();
            return this.branches.filter(b => 
                b.name.toLowerCase().includes(q) || 
                b.address.toLowerCase().includes(q)
            );
        },

        openModal(branch = null) {
            if (branch) {
                this.formData = { ...branch };
            } else {
                this.formData = {
                    id: null,
                    name: '',
                    address: '',
                    phone: '',
                    head: '',
                    type: 'Cabang'
                };
            }
            this.showModal = true;
        },

        saveBranch() {
            this.isLoading = true;
            setTimeout(() => {
                if (this.formData.id) {
                    // Update
                    const index = this.branches.findIndex(b => b.id === this.formData.id);
                    if(index !== -1) {
                        this.branches[index] = { ...this.formData };
                    }
                    Swal.fire({ icon: 'success', title: 'Berhasil', text: 'Data cabang diperbarui', timer: 1500, showConfirmButton: false });
                } else {
                    // Create
                    this.branches.push({ 
                        ...this.formData,
                        id: Date.now()
                    });
                    Swal.fire({ icon: 'success', title: 'Berhasil', text: 'Cabang baru ditambahkan', timer: 1500, showConfirmButton: false });
                }
                this.isLoading = false;
                this.showModal = false;
            }, 800);
        },

        deleteBranch(id) {
            Swal.fire({
                title: 'Hapus Cabang?',
                text: 'Data inventori dan transaksi terkait mungkin bermasalah!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColors: '#EF4444',
                confirmButtonText: 'Ya, Hapus!'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.branches = this.branches.filter(b => b.id !== id);
                    Swal.fire('Terhapus!', 'Data cabang telah dihapus.', 'success');
                }
            });
        }
    }" x-init="">

        <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 dark:text-white flex items-center gap-2">
                    <span class="p-2 bg-orange-50 rounded-lg text-orange-600 dark:bg-orange-900/30 dark:text-orange-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                            </path>
                        </svg>
                    </span>
                    Data Cabang (Branches)
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 ml-12">Kelola lokasi toko dan gudang</p>
            </div>
            <button @click="openModal()"
                class="px-5 py-2.5 bg-brand-600 text-white rounded-xl hover:bg-brand-700 shadow-lg shadow-brand-500/30 flex items-center gap-2 font-medium transition-all transform hover:-translate-y-0.5">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Tambah Cabang
            </button>
        </div>

        <!-- Search -->
        <div class="mb-6 bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm">
            <div class="relative max-w-md">
                <input type="text" x-model="searchQuery" placeholder="Cari nama cabang..."
                    class="w-full pl-10 pr-4 py-2.5 border border-gray-200 rounded-lg dark:bg-gray-900 dark:border-gray-600 dark:text-white focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </div>
        </div>

        <!-- Branch List -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <template x-for="b in filteredBranches" :key="b.id">
                <div
                    class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden hover:shadow-md transition-all group">
                    <div class="p-6">
                        <div class="flex justify-between items-start mb-4">
                            <div
                                class="w-12 h-12 bg-orange-50 dark:bg-orange-900/30 rounded-xl flex items-center justify-center text-orange-600 dark:text-orange-400">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                                    </path>
                                </svg>
                            </div>
                            <div class="flex gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                <button @click="openModal(b)"
                                    class="p-2 text-blue-500 hover:bg-blue-50 dark:hover:bg-blue-900/30 rounded-lg">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z">
                                        </path>
                                    </svg>
                                </button>
                                <button @click="deleteBranch(b.id)"
                                    class="p-2 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-lg">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                        </path>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-1" x-text="b.name"></h3>
                        <span class="inline-block px-2 py-0.5 rounded text-xs font-semibold uppercase tracking-wide mb-3"
                            :class="b.type === 'Pusat' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400' : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300'"
                            x-text="b.type"></span>

                        <div class="space-y-2 text-sm text-gray-600 dark:text-gray-300">
                            <div class="flex items-start gap-2">
                                <svg class="w-4 h-4 mt-0.5 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z">
                                    </path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                <span x-text="b.address"></span>
                            </div>
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z">
                                    </path>
                                </svg>
                                <span x-text="b.phone"></span>
                            </div>
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                <span x-text="'Kepala: ' + b.head"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
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
                        x-text="formData.id ? 'Edit Cabang' : 'Tambah Cabang'"></h3>
                    <button @click="showModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                            </path>
                        </svg>
                    </button>
                </div>

                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Nama Cabang <span
                                class="text-red-500">*</span></label>
                        <input type="text" x-model="formData.name" placeholder="Contoh: Cabang Bekasi"
                            class="w-full px-4 py-2.5 border border-gray-200 rounded-xl dark:bg-gray-900 dark:border-gray-600 dark:text-white focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Alamat
                            Lengkap</label>
                        <textarea x-model="formData.address" rows="3"
                            class="w-full px-4 py-2.5 border border-gray-200 rounded-xl dark:bg-gray-900 dark:border-gray-600 dark:text-white focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500"></textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">No.
                                Telepon</label>
                            <input type="text" x-model="formData.phone"
                                class="w-full px-4 py-2.5 border border-gray-200 rounded-xl dark:bg-gray-900 dark:border-gray-600 dark:text-white focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Kepala
                                Toko</label>
                            <input type="text" x-model="formData.head" placeholder="Nama Kepala"
                                class="w-full px-4 py-2.5 border border-gray-200 rounded-xl dark:bg-gray-900 dark:border-gray-600 dark:text-white focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Tipe Cabang</label>
                        <select x-model="formData.type"
                            class="w-full px-4 py-2.5 border border-gray-200 rounded-xl dark:bg-gray-900 dark:border-gray-600 dark:text-white focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                            <option value="Cabang">Cabang</option>
                            <option value="Pusat">Pusat</option>
                            <option value="Gudang">Gudang</option>
                        </select>
                    </div>
                </div>

                <div
                    class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 flex justify-end gap-3 bg-gray-50 dark:bg-gray-800/50 rounded-b-2xl">
                    <button @click="showModal = false"
                        class="px-5 py-2.5 text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700 rounded-xl font-medium transition-colors">Batal</button>
                    <button @click="saveBranch()" :disabled="isLoading || !formData.name"
                        class="px-5 py-2.5 bg-brand-600 text-white rounded-xl hover:bg-brand-700 shadow-lg shadow-brand-500/30 disabled:opacity-50 disabled:shadow-none flex items-center gap-2 font-medium transition-all transform active:scale-95">
                        <span x-show="isLoading"
                            class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                        <span x-text="isLoading ? 'Menyimpan...' : 'Simpan Cabang'"></span>
                    </button>
                </div>
            </div>
        </div>

    </div>
@endsection