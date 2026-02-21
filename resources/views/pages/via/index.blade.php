@extends('layouts.app')

@section('title', 'Via Management | SAGA TOKO APP')

@section('content')
    <div x-data="{
        showModal: false,
        vias: [
            { id: 1, name: 'Offline / Store', type: 'Direct', status: 'Active' },
            { id: 2, name: 'GrabFood', type: 'Online', status: 'Active' },
            { id: 3, name: 'GoFood', type: 'Online', status: 'Active' },
            { id: 4, name: 'WhatsApp', type: 'Direct', status: 'Active' }
        ],
        formData: { id: null, name: '', type: 'Online' },
        openModal(via = null) {
            if (via) {
                this.formData = { ...via };
            } else {
                this.formData = { id: null, name: '', type: 'Online' };
            }
            this.showModal = true;
        }
    }">
        <div class="mb-6 flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Via Management</h1>
                <p class="text-gray-600 dark:text-gray-400">Atur channel penjualan / sumber pesanan</p>
            </div>
            <button @click="openModal()" class="bg-brand-600 text-white px-4 py-2 rounded-lg hover:bg-brand-700 font-medium">
                Tambah Channel
            </button>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
            <table class="w-full text-left">
                <thead class="bg-gray-50 dark:bg-gray-700 text-gray-500 uppercase text-xs font-semibold">
                    <tr>
                        <th class="px-6 py-4">Nama Channel</th>
                        <th class="px-6 py-4">Tipe</th>
                        <th class="px-6 py-4 text-center">Status</th>
                        <th class="px-6 py-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700 text-sm">
                    <template x-for="via in vias" :key="via.id">
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <td class="px-6 py-4 font-medium text-gray-800 dark:text-white" x-text="via.name"></td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 rounded text-xs" :class="via.type === 'Online' ? 'bg-blue-100 text-blue-700' : 'bg-green-100 text-green-700'" x-text="via.type"></span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="text-green-500 font-medium" x-text="via.status"></span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <button @click="openModal(via)" class="text-blue-500 hover:text-blue-700 mr-2 font-medium">Edit</button>
                                <button class="text-red-500 hover:text-red-700 font-medium">Hapus</button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <!-- Placeholder Modal -->
        <div x-show="showModal" class="fixed inset-0 z-[9999] flex items-center justify-center bg-gray-900/50" x-cloak>
            <div class="bg-white dark:bg-gray-800 p-6 rounded-xl w-full max-w-md">
                <h2 class="text-xl font-bold mb-4">Channel Baru</h2>
                <div class="space-y-4">
                    <input type="text" x-model="formData.name" placeholder="Nama Channel (contoh: GrabFood)" class="w-full border rounded-lg p-2 dark:bg-gray-700">
                    <select x-model="formData.type" class="w-full border rounded-lg p-2 dark:bg-gray-700">
                        <option value="Online">Online</option>
                        <option value="Direct">Direct / Offline</option>
                    </select>
                </div>
                <div class="mt-6 flex justify-end gap-2">
                    <button @click="showModal = false" class="px-4 py-2 text-gray-500">Batal</button>
                    <button @click="showModal = false; Swal.fire('Berhasil', 'Channel disimpan', 'success')" class="bg-brand-600 text-white px-4 py-2 rounded-lg">Simpan</button>
                </div>
            </div>
        </div>
    </div>
@endsection
