@extends('layouts.app')

@section('title', 'Admin Users')

@push('head')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.sheetjs.com/xlsx-0.20.1/package/dist/xlsx.full.min.js"></script>
@endpush

@section('content')
    <div x-data="adminUsersPage()" x-init="initPage()">
        <!-- Header -->
        <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Admin Users</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">Manage all system users across tenants</p>
            </div>
            <div class="flex gap-2">
                <button @click="exportToExcel()"
                    class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                        </path>
                    </svg>
                    Export
                </button>
                <button @click="showImportModal = true"
                    class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                    </svg>
                    Import
                </button>
                <button @click="openAddModal()"
                    class="px-4 py-2 bg-brand-500 text-white rounded-lg hover:bg-brand-600 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Add User
                </button>
            </div>
        </div>

        <!-- Search -->
        <div class="mb-4">
            <input type="text" x-model="searchQuery" placeholder="Search by name, email, or role..." autocomplete="off"
                class="w-full max-w-md px-4 py-2.5 border border-gray-200 rounded-xl dark:bg-gray-800 dark:border-gray-700 dark:text-white">
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 mb-6">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <p class="text-sm text-gray-500">Total Users</p>
                <p class="text-2xl font-bold text-gray-800 dark:text-white" x-text="users.length">0</p>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <p class="text-sm text-gray-500">Super Admins</p>
                <p class="text-2xl font-bold text-purple-600" x-text="users.filter(u => u.role === 'super_admin').length">0
                </p>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <p class="text-sm text-gray-500">Tenant Owners</p>
                <p class="text-2xl font-bold text-blue-600" x-text="users.filter(u => u.role === 'tenant_owner').length">0
                </p>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <p class="text-sm text-gray-500">Staff (Backoffice+Cashier)</p>
                <p class="text-2xl font-bold text-green-600"
                    x-text="users.filter(u => u.role === 'backoffice' || u.role === 'cashier').length">0</p>
            </div>
        </div>

        <!-- Table -->
        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-gray-800/50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Role</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tenant</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    <template x-for="user in filteredUsers" :key="user.id">
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/30 transition-colors">
                            <td class="px-4 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-brand-100 flex items-center justify-center">
                                        <span class="text-brand-600 font-medium"
                                            x-text="user.name.charAt(0).toUpperCase()">U</span>
                                    </div>
                                    <span class="font-medium text-gray-800 dark:text-white" x-text="user.name"></span>
                                </div>
                            </td>
                            <td class="px-4 py-4 text-gray-600 dark:text-gray-300" x-text="user.email"></td>
                            <td class="px-4 py-4 text-center">
                                <span class="px-2.5 py-1 rounded-full text-xs font-medium"
                                    :class="getRoleBadge(user.role).class" x-text="getRoleBadge(user.role).text"></span>
                            </td>
                            <td class="px-4 py-4 text-gray-600 dark:text-gray-300" x-text="user.tenant?.name || '-'"></td>
                            <td class="px-4 py-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <button @click="openEditModal(user)"
                                        class="p-1.5 text-blue-500 hover:bg-blue-50 rounded-lg" title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                            </path>
                                        </svg>
                                    </button>
                                    <button @click="deleteUser(user.id)"
                                        class="p-1.5 text-red-500 hover:bg-red-50 rounded-lg" title="Delete">
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
            <div x-show="isLoading" class="p-8 text-center text-gray-400">Loading...</div>
            <div x-show="!isLoading && filteredUsers.length === 0" class="p-8 text-center text-gray-400">No users found
            </div>
        </div>

        <!-- Add/Edit Modal -->
        <div x-show="showModal"
            class="fixed inset-0 z-[9999] flex items-center justify-center bg-slate-900/80 backdrop-blur-sm p-4" x-cloak>
            <div class="bg-white dark:bg-gray-800 rounded-2xl w-full max-w-md p-6">
                <h2 class="text-xl font-bold text-gray-800 dark:text-white mb-4"
                    x-text="userForm.id ? 'Edit User' : 'Add User'">Add User</h2>
                <form autocomplete="off" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Name</label>
                        <input type="text" x-model="userForm.name" autocomplete="off"
                            class="w-full px-4 py-2.5 border border-gray-200 rounded-xl dark:bg-gray-900 dark:border-gray-700 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email</label>
                        <input type="text" x-model="userForm.email" autocomplete="new-email"
                            class="w-full px-4 py-2.5 border border-gray-200 rounded-xl dark:bg-gray-900 dark:border-gray-700 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Password</label>
                        <input type="password" x-model="userForm.password"
                            :placeholder="userForm.id ? 'Leave blank to keep current' : 'Enter password'"
                            class="w-full px-4 py-2.5 border border-gray-200 rounded-xl dark:bg-gray-900 dark:border-gray-700 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Role</label>
                        <select x-model="userForm.role"
                            class="w-full px-4 py-2.5 border border-gray-200 rounded-xl dark:bg-gray-900 dark:border-gray-700 dark:text-white">
                            <option value="tenant_owner">Tenant Owner</option>
                            <option value="backoffice">Backoffice</option>
                            <option value="cashier">Cashier</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tenant</label>
                        <select x-model="userForm.tenant_id"
                            class="w-full px-4 py-2.5 border border-gray-200 rounded-xl dark:bg-gray-900 dark:border-gray-700 dark:text-white">
                            <option value="">Select Tenant</option>
                            <template x-for="tenant in tenants" :key="tenant.id">
                                <option :value="tenant.id" x-text="tenant.name"></option>
                            </template>
                        </select>
                    </div>
                </form>
                <div class="flex gap-3 mt-6">
                    <button @click="showModal = false"
                        class="flex-1 py-2.5 border border-gray-200 rounded-xl hover:bg-gray-50 dark:border-gray-600 dark:hover:bg-gray-700 dark:text-white">Cancel</button>
                    <button @click="saveUser()"
                        class="flex-1 py-2.5 bg-brand-500 text-white rounded-xl hover:bg-brand-600">Save</button>
                </div>
            </div>
        </div>

        <!-- Import Modal -->
        <div x-show="showImportModal"
            class="fixed inset-0 z-[9999] flex items-center justify-center bg-slate-900/80 backdrop-blur-sm" x-cloak>
            <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 w-full max-w-md mx-4">
                <h2 class="text-xl font-bold text-gray-800 dark:text-white mb-4">Import Users</h2>
                <div class="space-y-4">
                    <div class="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-xl text-sm text-blue-800 dark:text-blue-300">
                        <p class="font-medium mb-2">Excel Header yang diperlukan:</p>
                        <p class="font-mono bg-white/50 dark:bg-gray-700 p-2 rounded text-xs">Name | Email | Password | Role
                            | Tenant</p>
                        <p class="text-xs mt-2 opacity-75">Role: tenant_owner, cashier, backoffice</p>
                        <button @click="downloadTemplate()" type="button"
                            class="mt-3 w-full py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium">
                            📥 Download Template
                        </button>
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600 dark:text-gray-400 mb-2">Select Excel File (.xlsx)</label>
                        <input type="file" accept=".xlsx,.xls" @change="importFile = $event.target.files[0]"
                            class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100">
                    </div>
                    <div class="flex gap-3 pt-2">
                        <button @click="showImportModal = false; importFile = null" :disabled="isImporting"
                            class="flex-1 py-2.5 border border-gray-200 rounded-xl hover:bg-gray-50 dark:border-gray-600 dark:hover:bg-gray-700 dark:text-white disabled:opacity-50">Cancel</button>
                        <button @click="importUsers()" :disabled="isImporting || !importFile"
                            class="flex-1 py-2.5 bg-blue-500 text-white rounded-xl hover:bg-blue-600 disabled:opacity-50"
                            x-text="isImporting ? 'Importing...' : 'Import'">Import</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function adminUsersPage() {
            return {
                isLoading: true,
                users: [],
                showModal: false,
                showImportModal: false,
                importFile: null,
                isImporting: false,
                userForm: { id: null, name: '', email: '', role: 'tenant_owner', tenant_id: '', password: '' },
                tenants: [],
                searchQuery: '',

                async initPage() {
                    await this.fetchData();
                },

                async fetchData() {
                    this.isLoading = true;
                    const token = localStorage.getItem('saga_token');
                    const headers = { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' };

                    try {
                        const [usersRes, tenantsRes] = await Promise.all([
                            fetch('/api/admin/users', { headers }),
                            fetch('/api/admin/tenants', { headers })
                        ]);

                        const usersData = await usersRes.json();
                        const tenantsData = await tenantsRes.json();

                        if (usersData.success) this.users = usersData.data || [];
                        if (tenantsData.success) this.tenants = tenantsData.data || [];
                    } catch (e) {
                        console.error(e);
                        Swal.fire({ icon: 'error', title: 'Connection Error', text: 'Failed to fetch data' });
                    }

                    this.isLoading = false;
                },

                get filteredUsers() {
                    if (!this.searchQuery) return this.users;
                    const q = this.searchQuery.toLowerCase();
                    return this.users.filter(u =>
                        u.name.toLowerCase().includes(q) ||
                        u.email.toLowerCase().includes(q) ||
                        u.role.toLowerCase().includes(q)
                    );
                },

                openAddModal() {
                    this.userForm = { id: null, name: '', email: '', role: 'tenant_owner', tenant_id: '', password: '' };
                    this.showModal = true;
                },

                openEditModal(user) {
                    this.userForm = { ...user, password: '', tenant_id: user.tenant_id || '' };
                    this.showModal = true;
                },

                async saveUser() {
                    const token = localStorage.getItem('saga_token');
                    const isEdit = !!this.userForm.id;
                    const url = isEdit ? `/api/admin/users/${this.userForm.id}` : '/api/admin/users';

                    try {
                        const res = await fetch(url, {
                            method: isEdit ? 'PUT' : 'POST',
                            headers: { 'Authorization': 'Bearer ' + token, 'Content-Type': 'application/json', 'Accept': 'application/json' },
                            body: JSON.stringify(this.userForm)
                        });
                        const data = await res.json();
                        if (data.success) {
                            this.showModal = false;
                            this.userForm = { id: null, name: '', email: '', role: 'tenant_owner', tenant_id: '', password: '' };
                            await this.fetchData();
                            Swal.fire({ icon: 'success', title: 'Success', text: 'User saved successfully', timer: 1500, showConfirmButton: false });
                        } else {
                            Swal.fire({ icon: 'error', title: 'Failed to Save', html: `<b>Error:</b> ${data.message || 'Unknown error'}` });
                        }
                    } catch (e) {
                        console.error('Save user error:', e);
                        Swal.fire({ icon: 'error', title: 'Error', text: 'Connection error: ' + e.message });
                    }
                },

                async deleteUser(id) {
                    const result = await Swal.fire({
                        title: 'Delete User?',
                        text: 'This action cannot be undone',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#ef4444',
                        confirmButtonText: 'Yes, delete'
                    });
                    if (!result.isConfirmed) return;

                    const token = localStorage.getItem('saga_token');
                    try {
                        const res = await fetch(`/api/admin/users/${id}`, {
                            method: 'DELETE',
                            headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
                        });
                        const data = await res.json();
                        if (data.success) {
                            await this.fetchData();
                            Swal.fire({ icon: 'success', title: 'Deleted', timer: 1500, showConfirmButton: false });
                        }
                    } catch (e) { console.error(e); }
                },

                getRoleBadge(role) {
                    const badges = {
                        'super_admin': { text: 'Super Admin', class: 'bg-purple-100 text-purple-600' },
                        'tenant_owner': { text: 'Owner', class: 'bg-blue-100 text-blue-600' },
                        'backoffice': { text: 'Backoffice', class: 'bg-green-100 text-green-600' },
                        'cashier': { text: 'Cashier', class: 'bg-gray-100 text-gray-600' }
                    };
                    return badges[role] || badges['cashier'];
                },

                exportToExcel() {
                    if (!this.users || this.users.length === 0) {
                        Swal.fire('No Data', 'No users to export', 'warning');
                        return;
                    }

                    const data = this.users.map(user => ({
                        'Name': user.name,
                        'Email': user.email,
                        'Role': user.role,
                        'Tenant': user.tenant?.name || '-',
                        'Status': user.is_active !== false ? 'Active' : 'Inactive'
                    }));

                    const ws = XLSX.utils.json_to_sheet(data);
                    const wb = XLSX.utils.book_new();
                    XLSX.utils.book_append_sheet(wb, ws, 'Users');
                    XLSX.writeFile(wb, `Users_${new Date().toISOString().split('T')[0]}.xlsx`);
                },

                async importUsers() {
                    if (!this.importFile) {
                        Swal.fire('Error', 'Please select a file', 'error');
                        return;
                    }

                    this.isImporting = true;
                    const reader = new FileReader();
                    const self = this;

                    reader.onload = async (e) => {
                        try {
                            const data = new Uint8Array(e.target.result);
                            const workbook = XLSX.read(data, { type: 'array' });
                            const sheet = workbook.Sheets[workbook.SheetNames[0]];
                            const rows = XLSX.utils.sheet_to_json(sheet);

                            const token = localStorage.getItem('saga_token');
                            let successCount = 0;
                            let errorCount = 0;

                            for (const row of rows) {
                                try {
                                    const tenant = self.tenants.find(t => t.name === row.Tenant || t.code === row.Tenant);
                                    const res = await fetch('/api/admin/users', {
                                        method: 'POST',
                                        headers: { 'Authorization': 'Bearer ' + token, 'Content-Type': 'application/json' },
                                        body: JSON.stringify({
                                            name: row.Name,
                                            email: row.Email,
                                            password: row.Password || 'Password123',
                                            role: row.Role || 'cashier',
                                            tenant_id: tenant?.id || null
                                        })
                                    });
                                    if (res.ok) successCount++;
                                    else errorCount++;
                                } catch { errorCount++; }
                            }

                            self.showImportModal = false;
                            self.importFile = null;
                            self.isImporting = false;
                            await Swal.fire('Import Complete', `${successCount} users imported, ${errorCount} errors`, successCount > 0 ? 'success' : 'warning');
                            await self.fetchData();
                        } catch (err) {
                            console.error('Import parse error:', err);
                            Swal.fire('Error', 'Failed to parse Excel file: ' + err.message, 'error');
                        } finally {
                            self.isImporting = false;
                        }
                    };

                    reader.readAsArrayBuffer(this.importFile);
                },

                downloadTemplate() {
                    const template = [
                        { Name: 'John Doe', Email: 'john@example.com', Password: 'Password123', Role: 'cashier', Tenant: 'SAGA TOKO' },
                        { Name: 'Jane Smith', Email: 'jane@example.com', Password: 'Password456', Role: 'tenant_owner', Tenant: 'BKT0001' }
                    ];

                    const ws = XLSX.utils.json_to_sheet(template);
                    const wb = XLSX.utils.book_new();
                    XLSX.utils.book_append_sheet(wb, ws, 'Users Template');
                    XLSX.writeFile(wb, 'User_Import_Template.xlsx');
                }
            }
        }
    </script>
@endsection