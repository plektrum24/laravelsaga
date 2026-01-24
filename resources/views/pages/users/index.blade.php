@extends('layouts.app')

@section('title', 'User Management | SAGA TOKO APP')

@section('content')
    <div x-data="{
        page: 'users',
        showModal: false,
        isLoading: false,
        searchQuery: '',

        users: [
            { id: 1, name: 'Reza Akbar', email: 'owner@sagaretail.com', role: 'owner', branch: 'Pusat (Jakarta)', status: 'active', last_login: '2024-01-24 10:00' },
            { id: 2, name: 'Dina Marlina', email: 'admin.jkt@sagaretail.com', role: 'manager', branch: 'Pusat (Jakarta)', status: 'active', last_login: '2024-01-24 08:30' },
            { id: 3, name: 'Budi Santoso', email: 'cashier.bdg@sagaretail.com', role: 'cashier', branch: 'Cabang Bandung', status: 'active', last_login: '2024-01-23 21:00' }
        ],

        branches: [
            { id: 1, name: 'Pusat (Jakarta)' },
            { id: 2, name: 'Cabang Bandung' },
            { id: 3, name: 'Cabang Surabaya' }
        ],

        roles: [
            { id: 'owner', name: 'Owner (Pemilik)' },
            { id: 'manager', name: 'Manager (Kepala Toko)' },
            { id: 'cashier', name: 'Cashier (Kasir)' },
            { id: 'warehouse', name: 'Warehouse (Gudang)' }
        ],

        formData: {
            id: null,
            name: '',
            email: '',
            password: '',
            role: 'cashier',
            branch_id: 1,
            status: 'active'
        },

        get filteredUsers() {
            if (!this.searchQuery) return this.users;
            const q = this.searchQuery.toLowerCase();
            return this.users.filter(u => 
                u.name.toLowerCase().includes(q) || 
                u.email.toLowerCase().includes(q)
            );
        },

        openModal(user = null) {
            if (user) {
                this.formData = { ...user, password: '' }; // Don't show password
            } else {
                this.formData = {
                    id: null,
                    name: '',
                    email: '',
                    password: '',
                    role: 'cashier',
                    branch_id: 1,
                    status: 'active'
                };
            }
            this.showModal = true;
        },

        saveUser() {
            this.isLoading = true;
            setTimeout(() => {
                if (this.formData.id) {
                    // Update
                    const index = this.users.findIndex(u => u.id === this.formData.id);
                    if(index !== -1) {
                        this.users[index] = { 
                            ...this.users[index], 
                            name: this.formData.name,
                            email: this.formData.email,
                            role: this.formData.role,
                            branch: this.branches.find(b => b.id == this.formData.branch_id)?.name || '-',
                            status: this.formData.status
                        };
                    }
                    Swal.fire({ icon: 'success', title: 'Berhasil', text: 'Data user diperbarui', timer: 1500, showConfirmButton: false });
                } else {
                    // Create
                    this.users.push({ 
                        id: Date.now(),
                        name: this.formData.name,
                        email: this.formData.email,
                        role: this.formData.role,
                        branch: this.branches.find(b => b.id == this.formData.branch_id)?.name || '-',
                        status: 'active',
                        last_login: '-'
                    });
                    Swal.fire({ icon: 'success', title: 'Berhasil', text: 'User baru ditambahkan', timer: 1500, showConfirmButton: false });
                }
                this.isLoading = false;
                this.showModal = false;
            }, 800);
        },

        deleteUser(id) {
            Swal.fire({
                title: 'Hapus User?',
                text: 'Akses user akan dicabut permanen!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColors: '#EF4444',
                confirmButtonText: 'Ya, Hapus!'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.users = this.users.filter(u => u.id !== id);
                    Swal.fire('Terhapus!', 'User telah dihapus.', 'success');
                }
            });
        },

        getRoleBadge(role) {
            switch(role) {
                case 'owner': return 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400';
                case 'manager': return 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400';
                case 'cashier': return 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400';
                default: return 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300';
            }
        }
    }" x-init="">

        <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 dark:text-white flex items-center gap-2">
                    <span class="p-2 bg-purple-50 rounded-lg text-purple-600 dark:bg-purple-900/30 dark:text-purple-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z">
                            </path>
                        </svg>
                    </span>
                    User Management
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 ml-12">Kelola akses pengguna sistem (Kasir, Manager,
                    Admin)</p>
            </div>
            <button @click="openModal()"
                class="px-5 py-2.5 bg-brand-600 text-white rounded-xl hover:bg-brand-700 shadow-lg shadow-brand-500/30 flex items-center gap-2 font-medium transition-all transform hover:-translate-y-0.5">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6">
                    </path>
                </svg>
                Tambah User
            </button>
        </div>

        <!-- Search -->
        <div class="mb-6 bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm">
            <div class="relative max-w-md">
                <input type="text" x-model="searchQuery" placeholder="Cari nama atau email user..."
                    class="w-full pl-10 pr-4 py-2.5 border border-gray-200 rounded-lg dark:bg-gray-900 dark:border-gray-600 dark:text-white focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </div>
        </div>

        <!-- User List -->
        <div
            class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead
                        class="bg-gray-50 dark:bg-gray-700 text-gray-500 dark:text-gray-400 uppercase text-xs font-semibold">
                        <tr>
                            <th class="px-6 py-4">User</th>
                            <th class="px-6 py-4">Role</th>
                            <th class="px-6 py-4">Cabang</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4">Last Login</th>
                            <th class="px-6 py-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        <template x-for="u in filteredUsers" :key="u.id">
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-8 h-8 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center text-gray-600 dark:text-gray-300 font-bold uppercase text-xs">
                                            <span x-text="u.name.substring(0,2)"></span>
                                        </div>
                                        <div>
                                            <div class="font-bold text-gray-800 dark:text-white" x-text="u.name"></div>
                                            <div class="text-xs text-gray-500" x-text="u.email"></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold uppercase tracking-wide"
                                        :class="getRoleBadge(u.role)" x-text="u.role"></span>
                                </td>
                                <td class="px-6 py-4 text-gray-600 dark:text-gray-400" x-text="u.branch"></td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <span class="w-2 h-2 rounded-full"
                                            :class="u.status === 'active' ? 'bg-green-500' : 'bg-red-500'"></span>
                                        <span class="text-gray-700 dark:text-gray-300 capitalize" x-text="u.status"></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-gray-500 text-xs font-mono" x-text="u.last_login"></td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <button @click="openModal(u)"
                                            class="p-1.5 text-blue-500 hover:bg-blue-50 dark:hover:bg-blue-900/30 rounded-lg transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z">
                                                </path>
                                            </svg>
                                        </button>
                                        <button @click="deleteUser(u.id)"
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
                        x-text="formData.id ? 'Edit User' : 'Tambah User Baru'"></h3>
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
                        <input type="text" x-model="formData.name"
                            class="w-full px-4 py-2.5 border border-gray-200 rounded-xl dark:bg-gray-900 dark:border-gray-600 dark:text-white focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Email (Username)
                            <span class="text-red-500">*</span></label>
                        <input type="email" x-model="formData.email"
                            class="w-full px-4 py-2.5 border border-gray-200 rounded-xl dark:bg-gray-900 dark:border-gray-600 dark:text-white focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Password</label>
                        <input type="password" x-model="formData.password" placeholder="Isi untuk ubah password..."
                            class="w-full px-4 py-2.5 border border-gray-200 rounded-xl dark:bg-gray-900 dark:border-gray-600 dark:text-white focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Role <span
                                    class="text-red-500">*</span></label>
                            <select x-model="formData.role"
                                class="w-full px-4 py-2.5 border border-gray-200 rounded-xl dark:bg-gray-900 dark:border-gray-600 dark:text-white focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                                <template x-for="r in roles" :key="r.id">
                                    <option :value="r.id" x-text="r.name"></option>
                                </template>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Cabang</label>
                            <select x-model="formData.branch_id"
                                class="w-full px-4 py-2.5 border border-gray-200 rounded-xl dark:bg-gray-900 dark:border-gray-600 dark:text-white focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                                <template x-for="b in branches" :key="b.id">
                                    <option :value="b.id" x-text="b.name"></option>
                                </template>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Status Akun</label>
                        <select x-model="formData.status"
                            class="w-full px-4 py-2.5 border border-gray-200 rounded-xl dark:bg-gray-900 dark:border-gray-600 dark:text-white focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                            <option value="active">Active (Bisa Login)</option>
                            <option value="inactive">Inactive (Dibekukan)</option>
                        </select>
                    </div>
                </div>

                <div
                    class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 flex justify-end gap-3 bg-gray-50 dark:bg-gray-800/50 rounded-b-2xl">
                    <button @click="showModal = false"
                        class="px-5 py-2.5 text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700 rounded-xl font-medium transition-colors">Batal</button>
                    <button @click="saveUser()" :disabled="isLoading || !formData.name || !formData.email"
                        class="px-5 py-2.5 bg-brand-600 text-white rounded-xl hover:bg-brand-700 shadow-lg shadow-brand-500/30 disabled:opacity-50 disabled:shadow-none flex items-center gap-2 font-medium transition-all transform active:scale-95">
                        <span x-show="isLoading"
                            class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                        <span x-text="isLoading ? 'Menyimpan...' : 'Simpan User'"></span>
                    </button>
                </div>
            </div>
        </div>

    </div>
@endsection