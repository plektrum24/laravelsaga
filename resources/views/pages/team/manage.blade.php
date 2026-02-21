@extends('layouts.app')

@section('title', 'Kelola Team | SAGA TOKO APP')

@section('content')
    <div x-data="{
        showModal: false,
        teams: [
            { id: 1, name: 'Team Pagi', leader: 'Andi Pratama', members: 5, branch: 'Cabang Jakarta' },
            { id: 2, name: 'Team Malam', leader: 'Sinta Dewi', members: 3, branch: 'Cabang Jakarta' }
        ],
        formData: {
            id: null,
            name: '',
            leader: '',
            branch: ''
        },
        openModal(team = null) {
            if (team) {
                this.formData = { ...team };
            } else {
                this.formData = { id: null, name: '', leader: '', branch: '' };
            }
            this.showModal = true;
        }
    }">
        <div class="mb-6 flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Kelola Team</h1>
                <p class="text-gray-600 dark:text-gray-400">Atur struktur team dan penugasan karyawan</p>
            </div>
            <button @click="openModal()" class="bg-brand-600 text-white px-4 py-2 rounded-lg hover:bg-brand-700 font-medium">
                Tambah Team
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <template x-for="team in teams" :key="team.id">
                <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700">
                    <div class="flex justify-between items-start mb-4">
                        <h3 class="text-lg font-bold text-gray-800 dark:text-white" x-text="team.name"></h3>
                        <span class="text-xs px-2 py-1 bg-blue-100 text-blue-700 rounded-full" x-text="team.branch"></span>
                    </div>
                    <div class="space-y-3 mb-6">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Leader:</span>
                            <span class="font-medium text-gray-800 dark:text-gray-200" x-text="team.leader"></span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Anggota:</span>
                            <span class="font-medium text-gray-800 dark:text-gray-200" x-text="team.members + ' Orang'"></span>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <button @click="openModal(team)" class="flex-1 py-2 text-sm bg-gray-50 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-lg hover:bg-gray-100 transition-colors">Edit</button>
                        <button class="flex-1 py-2 text-sm bg-brand-50 text-brand-600 rounded-lg hover:bg-brand-100 transition-colors">Detail</button>
                    </div>
                </div>
            </template>
        </div>

        <!-- Placeholder Modal -->
        <div x-show="showModal" class="fixed inset-0 z-[9999] flex items-center justify-center bg-gray-900/50" x-cloak>
            <div class="bg-white dark:bg-gray-800 p-6 rounded-xl w-full max-w-md">
                <h2 class="text-xl font-bold mb-4" x-text="formData.id ? 'Edit Team' : 'Tambah Team'"></h2>
                <div class="space-y-4">
                    <input type="text" x-model="formData.name" placeholder="Nama Team" class="w-full border rounded-lg p-2 dark:bg-gray-700">
                    <input type="text" x-model="formData.branch" placeholder="Cabang" class="w-full border rounded-lg p-2 dark:bg-gray-700">
                </div>
                <div class="mt-6 flex justify-end gap-2">
                    <button @click="showModal = false" class="px-4 py-2 text-gray-500">Batal</button>
                    <button @click="showModal = false; Swal.fire('Berhasil', 'Team disimpan', 'success')" class="bg-brand-600 text-white px-4 py-2 rounded-lg">Simpan</button>
                </div>
            </div>
        </div>
    </div>
@endsection
