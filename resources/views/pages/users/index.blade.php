@extends('layouts.app')
@section('title', 'User Management | SAGA TOKO APP')
@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-gray-900 p-6" x-data="userManagement()">
    <div class="max-w-7xl mx-auto">
        <div class="flex items-center justify-between mb-8">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-pink-600 rounded-2xl flex items-center justify-center shadow-lg">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                </div>
                <div><h1 class="text-3xl font-bold text-gray-800 dark:text-white">User Management</h1><p class="text-sm text-gray-500 dark:text-gray-400">Kelola pengguna sistem</p></div>
            </div>
            <button @click="openModal()" class="px-6 py-3 bg-gradient-to-r from-purple-600 to-pink-600 text-white rounded-xl font-semibold hover:from-purple-700 hover:to-pink-700 transition-all shadow-lg flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Add User
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-gradient-to-br from-purple-500 to-pink-600 rounded-2xl p-6 text-white"><div class="flex items-center justify-between"><div><p class="text-purple-100 text-sm">Total Users</p><h3 class="text-4xl font-bold mt-2" x-text="users.length"></h3></div><div class="w-16 h-16 bg-white/20 rounded-2xl flex items-center justify-center"><svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg></div></div></div>
            <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-gray-200 dark:border-gray-700"><div class="flex items-center justify-between"><div><p class="text-gray-500 dark:text-gray-400 text-sm">Admin</p><h3 class="text-4xl font-bold text-gray-800 dark:text-white mt-2" x-text="users.filter(u=>u.role==='admin').length"></h3></div><div class="w-16 h-16 bg-blue-100 dark:bg-blue-900/30 rounded-2xl flex items-center justify-center"><svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg></div></div></div>
            <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-gray-200 dark:border-gray-700"><div class="flex items-center justify-between"><div><p class="text-gray-500 dark:text-gray-400 text-sm">Staff</p><h3 class="text-4xl font-bold text-gray-800 dark:text-white mt-2" x-text="users.filter(u=>u.role==='staff').length"></h3></div><div class="w-16 h-16 bg-green-100 dark:bg-green-900/30 rounded-2xl flex items-center justify-center"><svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg></div></div></div>
            <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-gray-200 dark:border-gray-700"><div class="flex items-center justify-between"><div><p class="text-gray-500 dark:text-gray-400 text-sm">Active</p><h3 class="text-4xl font-bold text-gray-800 dark:text-white mt-2" x-text="users.filter(u=>u.status==='active').length"></h3></div><div class="w-16 h-16 bg-green-100 dark:bg-green-900/30 rounded-2xl flex items-center justify-center"><svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg></div></div></div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                <h2 class="text-xl font-bold text-gray-800 dark:text-white">Users List</h2>
                <input type="text" x-model="search" placeholder="🔍 Search users..." class="px-4 py-2.5 border-2 border-gray-200 dark:border-gray-700 rounded-xl bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500">
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 dark:bg-gray-900/50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">User</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Role</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Status</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Last Active</th>
                            <th class="px-6 py-4 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        <template x-for="user in filteredUsers" :key="user.id">
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/30">
                                <td class="px-6 py-4"><div class="flex items-center gap-3"><div class="w-10 h-10 bg-gradient-to-br from-purple-400 to-pink-500 rounded-full flex items-center justify-center text-white font-bold" x-text="user.name.charAt(0)"></div><div><p class="font-semibold text-gray-800 dark:text-white" x-text="user.name"></p><p class="text-sm text-gray-500 dark:text-gray-400" x-text="user.email"></p></div></div></td>
                                <td class="px-6 py-4"><span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold" :class="{'bg-blue-100 text-blue-700':user.role==='admin','bg-green-100 text-green-700':user.role==='staff','bg-purple-100 text-purple-700':user.role==='cashier'}" x-text="user.role.charAt(0).toUpperCase()+user.role.slice(1)"></span></td>
                                <td class="px-6 py-4"><span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold" :class="{'bg-green-100 text-green-700':user.status==='active','bg-red-100 text-red-700':user.status==='inactive'}" x-text="user.status.charAt(0).toUpperCase()+user.status.slice(1)"></span></td>
                                <td class="px-6 py-4"><span class="text-gray-700 dark:text-gray-300 text-sm" x-text="user.lastActive"></span></td>
                                <td class="px-6 py-4 text-right"><button @click="editUser(user)" class="px-4 py-2 text-sm font-medium text-purple-600 dark:text-purple-400 hover:bg-purple-50 dark:hover:bg-purple-900/20 rounded-lg transition-colors">Edit</button></td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@push('scripts')
<script>
function userManagement(){return{search:'',users:[{id:1,name:'Admin User',email:'admin@saga.com',role:'admin',status:'active',lastActive:'2 min ago'},{id:2,name:'John Doe',email:'john@saga.com',role:'staff',status:'active',lastActive:'1 hour ago'},{id:3,name:'Jane Smith',email:'jane@saga.com',role:'cashier',status:'active',lastActive:'5 min ago'},{id:4,name:'Bob Wilson',email:'bob@saga.com',role:'staff',status:'inactive',lastActive:'2 days ago'}],get filteredUsers(){if(!this.search)return this.users;const q=this.search.toLowerCase();return this.users.filter(u=>u.name.toLowerCase().includes(q)||u.email.toLowerCase().includes(q))},openModal(){Swal.fire({title:'Add User',html:'<input id="swal-name" class="swal2-input" placeholder="Name"><input id="swal-email" class="swal2-input" placeholder="Email"><select id="swal-role" class="swal2-input"><option value="admin">Admin</option><option value="staff">Staff</option><option value="cashier">Cashier</option></select>',showCancelButton:true,confirmButtonText:'Save',confirmButtonColor:'#a855f7'})},editUser(user){Swal.fire({title:'Edit User',html:`<input id="edit-name" class="swal2-input" value="${user.name}"><input id="edit-email" class="swal2-input" value="${user.email}">`,showCancelButton:true,confirmButtonText:'Update',confirmButtonColor:'#a855f7'})}}}
</script>
@endpush
@endsection
