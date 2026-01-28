@extends('layouts.app')

@section('title', 'Categories | SAGA TOKO APP')

@section('content')
    <div x-data="{
                    page: 'categories',
                    categories: [],
                    isLoading: true,
                    showModal: false,
                    editMode: false,
                    currentCategory: { name: '' },

                    async init() {
                        await this.fetchCategories();
                    },

                    async fetchCategories() {
                        try {
                            const token = localStorage.getItem('saga_token');
                            const response = await fetch('/api/products/categories', { headers: { 'Authorization': 'Bearer ' + token } });
                            const data = await response.json();
                            if (data.success) this.categories = data.data;
                        } catch (error) {
                            console.error('Fetch error:', error);
                        } finally {
                            this.isLoading = false;
                        }
                    },

                    openAddModal() {
                        this.editMode = false;
                        this.currentCategory = { name: '' };
                        this.showModal = true;
                    },

                    openEditModal(cat) {
                        this.editMode = true;
                        this.currentCategory = { ...cat };
                        this.showModal = true;
                    },

                    async saveCategory() {
                        const token = localStorage.getItem('saga_token');
                        const url = this.editMode ? '/api/products/categories/' + this.currentCategory.id : '/api/products/categories';
                        const method = this.editMode ? 'PUT' : 'POST';

                        const response = await fetch(url, {
                            method,
                            headers: { 'Authorization': 'Bearer ' + token, 'Content-Type': 'application/json' },
                            body: JSON.stringify({ name: this.currentCategory.name })
                        });
                        const data = await response.json();
                        if (data.success) {
                            this.showModal = false;
                            await this.fetchCategories();
                            Swal.fire({ icon: 'success', title: 'Berhasil!', text: 'Kategori berhasil disimpan', timer: 1500, showConfirmButton: false });
                        } else {
                            Swal.fire({ icon: 'error', title: 'Gagal', text: data.message });
                        }
                    },

                    async deleteCategory(id) {
                        const result = await Swal.fire({
                            title: 'Hapus Kategori?',
                            text: 'Data kategori akan dihapus secara permanen!',
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#d33',
                            cancelButtonColor: '#6b7280',
                            confirmButtonText: 'Ya, Hapus!',
                            cancelButtonText: 'Batal'
                        });

                        if (!result.isConfirmed) return;

                        const token = localStorage.getItem('saga_token');
                        try {
                            const response = await fetch('/api/products/categories/' + id, {
                                method: 'DELETE',
                                headers: { 'Authorization': 'Bearer ' + token }
                            });
                            const data = await response.json();
                            if (!data.success) {
                                Swal.fire({ icon: 'error', title: 'Gagal Menghapus', text: data.message || 'Kategori tidak dapat dihapus' });
                                return;
                            }
                            Swal.fire({ icon: 'success', title: 'Terhapus!', text: 'Kategori berhasil dihapus', timer: 1500, showConfirmButton: false });
                            await this.fetchCategories();
                        } catch (error) {
                            Swal.fire({ icon: 'error', title: 'Error', text: error.message });
                        }
                    }
                }" x-init="init()">
        <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
            <div>
                <h1 class="text-xl md:text-2xl font-bold text-gray-800 dark:text-white">Categories</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">Kelola kategori produk</p>
            </div>
            <button @click="openAddModal()"
                class="px-4 py-2 bg-brand-500 text-white rounded-lg hover:bg-brand-600 flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6">
                    </path>
                </svg>
                Add Category
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            <template x-for="cat in categories" :key="cat.id">
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div
                                class="w-10 h-10 bg-brand-100 rounded-lg flex items-center justify-center dark:bg-brand-900/30">
                                <span class="text-brand-600 font-bold" x-text="cat.name?.charAt(0)">C</span>
                            </div>
                            <div>
                                <h3 class="font-medium text-gray-800 dark:text-white" x-text="cat.name">
                                    Category</h3>
                                <p class="text-xs text-gray-400" x-text="(cat.products_count || 0) + ' products'">0 products
                                </p>
                            </div>
                        </div>
                        <div class="flex gap-1">
                            <button @click="openEditModal(cat)" class="p-2 text-gray-400 hover:text-brand-500"><svg
                                    class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z">
                                    </path>
                                </svg></button>
                            <button @click="deleteCategory(cat.id)" class="p-2 text-gray-400 hover:text-red-500"><svg
                                    class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                    </path>
                                </svg></button>
                        </div>
                    </div>
                </div>
            </template>
        </div>
        <div x-show="isLoading" class="text-center py-12 text-gray-400">Loading...</div>
        <div x-show="!isLoading && categories.length === 0" class="text-center py-12 text-gray-400">Belum
            ada kategori</div>

        <!-- Modal -->
        <div x-show="showModal"
            class="fixed inset-0 z-[9999] flex items-center justify-center bg-gray-900/80 backdrop-blur-sm p-4" x-cloak
            x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
            <div class="bg-white dark:bg-gray-800 rounded-2xl w-full max-w-md overflow-hidden"
                @click.away="showModal = false">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-100 dark:bg-gray-800">
                    <h2 class="text-xl font-bold text-gray-800 dark:text-white"
                        x-text="editMode ? 'Edit Category' : 'Add Category'"></h2>
                </div>
                <form @submit.prevent="saveCategory()">
                    <div class="p-6">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nama
                                Kategori</label>
                            <input type="text" x-model="currentCategory.name" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 dark:bg-gray-900 dark:border-gray-600 dark:text-white">
                        </div>
                    </div>
                    <div
                        class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 bg-gray-100 dark:bg-gray-800 flex justify-end gap-3">
                        <button type="button" @click="showModal = false"
                            class="flex-1 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 dark:border-gray-600 dark:hover:bg-gray-700 dark:text-gray-300">Batal</button>
                        <button type="submit"
                            class="flex-1 py-2 bg-brand-500 text-white rounded-lg hover:bg-brand-600 shadow-sm">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection