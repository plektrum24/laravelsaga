@extends('layouts.app')

@section('title', 'Data Karyawan | SAGA TOKO APP')

@section('content')
    <div x-data="{
        page: 'employees',
        showModal: false,
        isLoading: false,
        searchQuery: '',
        viewMode: 'cards', // 'cards' or 'table'
        employees: [],
        formData: {
            id: null,
            name: '',
            nik: '',
            role: 'staff',
            phone: '',
            join_date: '',
            basic_salary: 0,
            allowance: 0,
            transport_allowance: 0,
            meal_allowance: 0,
            position_allowance: 0,
            performance_bonus: 0,
            bank_name: '',
            bank_account_number: '',
            bank_account_holder: '',
            is_active: true
        },
        get filteredEmployees() {
            if (!this.searchQuery) return this.employees;
            const q = this.searchQuery.toLowerCase();
            return this.employees.filter(e =>
                (e.name && e.name.toLowerCase().includes(q)) ||
                (e.nik && e.nik.toLowerCase().includes(q)) ||
                (e.role && e.role.toLowerCase().includes(q))
            );
        },
        async init() {
            this.isLoading = true;
            try {
                const response = await fetch('/api/employees');
                const result = await response.json();
                this.employees = result.data.data || [];
            } catch (error) {
                console.error('Error fetching employees:', error);
            } finally {
                this.isLoading = false;
            }
        },
        openModal(employee = null) {
            if (employee) {
                this.formData = {
                    ...employee,
                    basic_salary: parseFloat(employee.basic_salary) || 0,
                    allowance: parseFloat(employee.allowance) || 0,
                    transport_allowance: parseFloat(employee.transport_allowance) || 0,
                    meal_allowance: parseFloat(employee.meal_allowance) || 0,
                    position_allowance: parseFloat(employee.position_allowance) || 0,
                    performance_bonus: parseFloat(employee.performance_bonus) || 0,
                };
            } else {
                this.formData = {
                    id: null,
                    name: '',
                    nik: '',
                    role: 'staff',
                    phone: '',
                    join_date: new Date().toISOString().split('T')[0],
                    basic_salary: 0,
                    allowance: 0,
                    transport_allowance: 0,
                    meal_allowance: 0,
                    position_allowance: 0,
                    performance_bonus: 0,
                    bank_name: '',
                    bank_account_number: '',
                    bank_account_holder: '',
                    is_active: true
                };
            }
            this.showModal = true;
        },
        async saveEmployee() {
            this.isLoading = true;
            const method = this.formData.id ? 'PUT' : 'POST';
            const url = this.formData.id ? `/api/employees/${this.formData.id}` : '/api/employees';

            try {
                const response = await fetch(url, {
                    method: method,
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(this.formData)
                });

                if (response.ok) {
                    await this.init();
                    Swal.fire({ icon: 'success', title: 'Berhasil', text: 'Data karyawan disimpan', timer: 1500, showConfirmButton: false });
                    this.showModal = false;
                } else {
                    const error = await response.json();
                    Swal.fire({ icon: 'error', title: 'Gagal', text: error.message || 'Terjadi kesalahan' });
                }
            } catch (error) {
                Swal.fire({ icon: 'error', title: 'Gagal', text: 'Koneksi ke server terputus' });
            } finally {
                this.isLoading = false;
            }
        },
        async deleteEmployee(id) {
            const result = await Swal.fire({
                title: 'Hapus Karyawan?',
                text: 'Data yang dihapus tidak dapat dikembalikan!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#EF4444',
                confirmButtonText: 'Ya, Hapus!'
            });

            if (result.isConfirmed) {
                try {
                    const response = await fetch(`/api/employees/${id}`, { method: 'DELETE' });
                    if (response.ok) {
                        await this.init();
                        Swal.fire('Terhapus!', 'Data karyawan telah dihapus.', 'success');
                    } else {
                        Swal.fire('Gagal!', 'Terjadi kesalahan saat menghapus data.', 'error');
                    }
                } catch (error) {
                    Swal.fire('Gagal!', 'Koneksi ke server terputus.', 'error');
                }
            }
        },
        formatRupiah(number) {
            if (!number) return 'Rp 0';
            return 'Rp ' + number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        },
        getRoleIcon(role) {
            const icons = {
                'Manager': '👔',
                'Kasir': '💳',
                'Gudang': '📦',
                'staff': '👤'
            };
            return icons[role] || '👤';
        }
    }" x-init="init()">

        <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 dark:text-white flex items-center gap-3">
                    <span class="p-2.5 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl text-white shadow-lg shadow-blue-500/30">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </span>
                    Data Karyawan
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-2 ml-14">Kelola informasi lengkap karyawan dan peran mereka</p>
            </div>
            <div class="flex items-center gap-3">
                <button @click="viewMode = viewMode === 'cards' ? 'table' : 'cards'" class="px-4 py-2.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors flex items-center gap-2">
                    <svg x-show="viewMode === 'cards'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
                    </svg>
                    <svg x-show="viewMode === 'table'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                    </svg>
                    <span x-text="viewMode === 'cards' ? 'List View' : 'Card View'"></span>
                </button>
                <button @click="openModal()" class="px-5 py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-xl hover:from-blue-700 hover:to-blue-800 shadow-lg shadow-blue-500/30 flex items-center gap-2 font-medium transition-all transform hover:-translate-y-0.5">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Tambah Karyawan
                </button>
            </div>
        </div>

        <!-- Search & Filter -->
        <div class="mb-6 bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm flex flex-col md:flex-row gap-4">
            <div class="relative flex-1">
                <input type="text" x-model="searchQuery" placeholder="Cari nama, NIK, atau posisi..."
                    class="w-full pl-11 pr-4 py-3 border border-gray-200 rounded-xl dark:bg-gray-900 dark:border-gray-600 dark:text-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all">
                <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </div>
            <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                <span x-text="filteredEmployees.length + ' dari ' + employees.length + ' karyawan'"></span>
            </div>
        </div>

        <!-- Loading State -->
        <div x-show="isLoading" class="flex items-center justify-center py-12">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
        </div>

        <!-- Horizontal Cards View -->
        <div x-show="!isLoading && viewMode === 'cards' && filteredEmployees.length > 0" class="space-y-4">
            <template x-for="e in filteredEmployees" :key="e.id">
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm hover:shadow-lg transition-all duration-300 overflow-hidden group">
                    <div class="flex flex-col md:flex-row items-stretch">
                        <!-- Left: Avatar & Basic Info -->
                        <div class="flex items-center gap-4 p-5 md:w-64 bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 border-r border-gray-100 dark:border-gray-700">
                            <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center text-white font-bold text-lg shadow-lg shadow-blue-500/30" x-text="e.name.substring(0,2).toUpperCase()"></div>
                            <div class="flex-1 min-w-0">
                                <h3 class="font-bold text-gray-800 dark:text-white truncate" x-text="e.name"></h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5" x-text="e.nik"></p>
                                <span class="inline-flex items-center gap-1 mt-2 px-2.5 py-1 rounded-lg text-xs font-semibold"
                                    :class="{
                                        'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400': e.role === 'Manager',
                                        'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400': e.role === 'Kasir',
                                        'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400': e.role === 'Gudang',
                                        'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300': !['Manager', 'Kasir', 'Gudang'].includes(e.role)
                                    }">
                                    <span x-text="getRoleIcon(e.role)"></span>
                                    <span x-text="e.role"></span>
                                </span>
                            </div>
                        </div>

                        <!-- Middle: Details -->
                        <div class="flex-1 p-5 grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Kontak</p>
                                <p class="font-semibold text-gray-800 dark:text-white text-sm" x-text="e.phone || '-'"></p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Tgl Bergabung</p>
                                <p class="font-semibold text-gray-800 dark:text-white text-sm" x-text="e.join_date"></p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Gaji Pokok</p>
                                <p class="font-semibold text-blue-600 dark:text-blue-400 text-sm" x-text="formatRupiah(e.basic_salary)"></p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Total Allowance</p>
                                <p class="font-semibold text-green-600 dark:text-green-400 text-sm" x-text="formatRupiah((e.allowance || 0) + (e.transport_allowance || 0) + (e.meal_allowance || 0))"></p>
                            </div>
                        </div>

                        <!-- Right: Status & Actions -->
                        <div class="flex items-center gap-3 p-5 border-t md:border-t-0 md:border-l border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50">
                            <span class="px-3 py-1.5 rounded-full text-xs font-bold uppercase tracking-wider"
                                :class="e.is_active ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400'"
                                x-text="e.is_active ? 'Aktif' : 'Non-Aktif'"></span>
                            
                            <div class="flex items-center gap-1 ml-auto">
                                <button @click="openModal(e)" class="p-2.5 text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/30 rounded-xl transition-colors" title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                </button>
                                <button @click="deleteEmployee(e.id)" class="p-2.5 text-red-600 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-xl transition-colors" title="Hapus">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <!-- Table View (Alternative) -->
        <div x-show="!isLoading && viewMode === 'table' && filteredEmployees.length > 0" class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-50 dark:bg-gray-700 text-gray-500 dark:text-gray-400 uppercase text-xs font-semibold">
                        <tr>
                            <th class="px-6 py-4">Karyawan</th>
                            <th class="px-6 py-4">NIK</th>
                            <th class="px-6 py-4">Posisi</th>
                            <th class="px-6 py-4">Kontak</th>
                            <th class="px-6 py-4">Tgl Bergabung</th>
                            <th class="px-6 py-4 text-center">Status</th>
                            <th class="px-6 py-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        <template x-for="e in filteredEmployees" :key="e.id">
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center text-white font-bold text-sm shadow-md" x-text="e.name.substring(0,2).toUpperCase()"></div>
                                        <div class="font-bold text-gray-800 dark:text-white" x-text="e.name"></div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-gray-600 dark:text-gray-300" x-text="e.nik"></td>
                                <td class="px-6 py-4">
                                    <span class="px-2.5 py-1 rounded-lg text-xs font-semibold"
                                        :class="{
                                            'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400': e.role === 'Manager',
                                            'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400': e.role === 'Kasir',
                                            'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400': e.role === 'Gudang',
                                            'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300': !['Manager', 'Kasir', 'Gudang'].includes(e.role)
                                        }" x-text="e.role"></span>
                                </td>
                                <td class="px-6 py-4 text-gray-600 dark:text-gray-300" x-text="e.phone"></td>
                                <td class="px-6 py-4 text-gray-600 dark:text-gray-300" x-text="e.join_date"></td>
                                <td class="px-6 py-4 text-center">
                                    <span class="px-3 py-1.5 rounded-full text-[10px] font-bold uppercase tracking-wider"
                                        :class="e.is_active ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400'"
                                        x-text="e.is_active ? 'Aktif' : 'Non-Aktif'"></span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <button @click="openModal(e)" class="p-2 text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/30 rounded-xl transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                        </button>
                                        <button @click="deleteEmployee(e.id)" class="p-2 text-red-600 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-xl transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
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

        <!-- Empty State -->
        <div x-show="!isLoading && filteredEmployees.length === 0" class="text-center py-16">
            <svg class="w-20 h-20 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
            </svg>
            <p class="text-gray-500 dark:text-gray-400 text-lg mb-4">Belum ada data karyawan</p>
            <button @click="openModal()" class="px-5 py-2.5 bg-blue-600 text-white rounded-xl hover:bg-blue-700 shadow-lg shadow-blue-500/30 font-medium transition-all">
                Tambah Karyawan Pertama
            </button>
        </div>

        <!-- MODAL -->
        <div x-show="showModal" class="fixed inset-0 z-[9999] flex items-center justify-center bg-gray-900/80 backdrop-blur-sm p-4 transition-all duration-300" x-cloak>
            <div class="bg-white dark:bg-gray-800 rounded-2xl w-full max-w-lg shadow-2xl" @click.outside="showModal = false">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                    <h3 class="text-xl font-bold text-gray-800 dark:text-white" x-text="formData.id ? 'Edit Karyawan' : 'Tambah Karyawan'"></h3>
                    <button @click="showModal = false" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Nama Lengkap <span class="text-red-500">*</span></label>
                        <input type="text" x-model="formData.name" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl dark:bg-gray-900 dark:border-gray-600 dark:text-white focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">NIK</label>
                            <input type="text" x-model="formData.nik" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl dark:bg-gray-900 dark:border-gray-600 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Posisi / Role</label>
                            <select x-model="formData.role" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl dark:bg-gray-900 dark:border-gray-600 dark:text-white">
                                <option value="Manager">Manager</option>
                                <option value="Kasir">Kasir</option>
                                <option value="Gudang">Gudang</option>
                                <option value="staff">Staff</option>
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">No. HP</label>
                            <input type="text" x-model="formData.phone" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl dark:bg-gray-900 dark:border-gray-600 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Tgl Bergabung</label>
                            <input type="date" x-model="formData.join_date" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl dark:bg-gray-900 dark:border-gray-600 dark:text-white">
                        </div>
                    </div>

                    <!-- Salary Section -->
                    <div class="pt-4 border-t border-gray-100 dark:border-gray-700">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-gray-500 mb-4">Gaji & Tunjangan</h4>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Gaji Pokok</label>
                                <input type="number" x-model="formData.basic_salary" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl dark:bg-gray-900 dark:border-gray-600 dark:text-white">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Tunjangan Tetap</label>
                                <input type="number" x-model="formData.allowance" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl dark:bg-gray-900 dark:border-gray-600 dark:text-white">
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4 mt-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Tj. Transport</label>
                                <input type="number" x-model="formData.transport_allowance" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl dark:bg-gray-900 dark:border-gray-600 dark:text-white">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Tj. Makan</label>
                                <input type="number" x-model="formData.meal_allowance" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl dark:bg-gray-900 dark:border-gray-600 dark:text-white">
                            </div>
                        </div>
                    </div>

                    <!-- Bank Section -->
                    <div class="pt-4 border-t border-gray-100 dark:border-gray-700">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-gray-500 mb-4">Informasi Bank</h4>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="col-span-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Nama Bank</label>
                                <input type="text" x-model="formData.bank_name" placeholder="Contoh: BCA, Mandiri" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl dark:bg-gray-900 dark:border-gray-600 dark:text-white">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">No. Rekening</label>
                                <input type="text" x-model="formData.bank_account_number" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl dark:bg-gray-900 dark:border-gray-600 dark:text-white">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Nama Pemilik</label>
                                <input type="text" x-model="formData.bank_account_holder" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl dark:bg-gray-900 dark:border-gray-600 dark:text-white">
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 pt-2">
                        <input type="checkbox" x-model="formData.is_active" id="is_active" class="w-5 h-5 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <label for="is_active" class="text-sm font-medium text-gray-700 dark:text-gray-300">Akun Aktif</label>
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 flex justify-end gap-3 bg-gray-50 dark:bg-gray-800/50 rounded-b-2xl">
                    <button @click="showModal = false" class="px-5 py-2.5 text-gray-600 hover:bg-gray-100 rounded-xl font-medium transition-colors">Batal</button>
                    <button @click="saveEmployee()" :disabled="isLoading || !formData.name" class="px-5 py-2.5 bg-blue-600 text-white rounded-xl hover:bg-blue-700 shadow-lg shadow-blue-500/30 flex items-center gap-2 font-medium">
                        <span x-show="isLoading" class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                        <span x-text="isLoading ? 'Menyimpan...' : 'Simpan Karyawan'"></span>
                    </button>
                </div>
            </div>
        </div>

    </div>
@endsection
