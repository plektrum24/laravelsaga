@extends('layouts.app')

@section('title', 'Payroll & Attendance | SAGA TOKO APP')

@section('content')
    <div x-data="{
                    activeTab: 'payroll',
                    showModal: false,
                    isLoading: false,
                    searchQuery: '',

                    // Mock Data Gaji
                    salaries: [
                        { id: 1, employee: 'Budi Santoso', role: 'Cashier', basic_salary: 3500000, allowance: 500000, deductions: 0, status: 'Paid', date: '2023-10-25' },
                        { id: 2, employee: 'Siti Aminah', role: 'Manager', basic_salary: 5500000, allowance: 1000000, deductions: 200000, status: 'Pending', date: '2023-10-25' },
                    ],

                    // Mock Data Absen
                    attendance: [
                        { id: 1, employee: 'Budi Santoso', date: '2023-10-26', check_in: '07:55', check_out: '17:05', status: 'On Time', work_hours: '9h 10m' },
                        { id: 2, employee: 'Siti Aminah', date: '2023-10-26', check_in: '08:10', check_out: '17:00', status: 'Late', work_hours: '8h 50m' },
                        { id: 3, employee: 'Agus Setiawan', date: '2023-10-26', check_in: '-', check_out: '-', status: 'Absent', work_hours: '-' },
                    ],

                    formatCurrency(amount) {
                        return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(amount);
                    }
                }">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white flex items-center gap-2">
                <span class="p-2 bg-brand-50 rounded-lg text-brand-600 dark:bg-brand-900/30 dark:text-brand-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                        </path>
                    </svg>
                </span>
                Payroll & Attendance
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 ml-12">Manajemen gaji dan absensi karyawan terintegrasi
            </p>
        </div>

        <!-- Navigation Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <!-- Card Payroll -->
            <button @click="activeTab = 'payroll'"
                class="relative overflow-hidden p-6 rounded-xl border-2 transition-all text-left group"
                :class="activeTab === 'payroll' 
                            ? 'bg-white border-brand-600 shadow-md ring-1 ring-brand-600' 
                            : 'bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 hover:border-brand-300 dark:hover:border-brand-700'">
                <div class="relative z-10 flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium"
                            :class="activeTab === 'payroll' ? 'text-brand-600' : 'text-gray-500 dark:text-gray-400'">Menu
                            Utama</p>
                        <h3 class="text-xl font-bold mt-1 text-gray-800 dark:text-white">Penggajian (Payroll)</h3>
                        <p class="text-sm mt-2"
                            :class="activeTab === 'payroll' ? 'text-gray-600' : 'text-gray-500 dark:text-gray-400'">Hitung
                            gaji, tunjangan & bonus</p>
                    </div>
                    <div class="p-3 rounded-lg"
                        :class="activeTab === 'payroll' ? 'bg-brand-50 text-brand-600' : 'bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400'">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z">
                            </path>
                        </svg>
                    </div>
                </div>
            </button>

            <!-- Card Attendance -->
            <button @click="activeTab = 'attendance'"
                class="relative overflow-hidden p-6 rounded-xl border-2 transition-all text-left group"
                :class="activeTab === 'attendance' 
                            ? 'bg-white border-blue-600 shadow-md ring-1 ring-blue-600' 
                            : 'bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 hover:border-blue-300 dark:hover:border-blue-700'">
                <div class="relative z-10 flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium"
                            :class="activeTab === 'attendance' ? 'text-blue-600' : 'text-gray-500 dark:text-gray-400'">
                            Harian</p>
                        <h3 class="text-xl font-bold mt-1 text-gray-800 dark:text-white">Absensi Karyawan</h3>
                        <p class="text-sm mt-2"
                            :class="activeTab === 'attendance' ? 'text-gray-600' : 'text-gray-500 dark:text-gray-400'">Rekap
                            kehadiran & jam kerja</p>
                    </div>
                    <div class="p-3 rounded-lg"
                        :class="activeTab === 'attendance' ? 'bg-blue-50 text-blue-600' : 'bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400'">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </button>
        </div>

        <!-- Section: Payroll -->
        <div x-show="activeTab === 'payroll'" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
            <div
                class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div
                    class="p-4 border-b border-gray-100 dark:border-gray-700 flex flex-col sm:flex-row justify-between items-center gap-4">
                    <div class="flex items-center gap-3">
                        <h3 class="font-bold text-gray-800 dark:text-white">Daftar Gaji</h3>
                        <div class="relative">
                            <select x-model="selectedMonth"
                                class="pl-3 pr-8 py-1.5 text-sm bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                <option value="2023-10">Oktober 2023</option>
                                <option value="2023-09">September 2023</option>
                                <option value="2023-08">Agustus 2023</option>
                            </select>
                        </div>
                    </div>
                    <button @click="showModal = true"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium transition-all shadow-sm">
                        + Buat Slip Gaji
                    </button>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead
                            class="bg-gray-50 dark:bg-gray-700 text-gray-500 dark:text-gray-400 uppercase text-xs font-semibold">
                            <tr>
                                <th class="px-6 py-4">Karyawan</th>
                                <th class="px-6 py-4">Jabatan</th>
                                <th class="px-6 py-4 text-right">Gaji Pokok</th>
                                <th class="px-6 py-4 text-right">Tunjangan</th>
                                <th class="px-6 py-4 text-right">Potongan</th>
                                <th class="px-6 py-4 text-right">Total Terima</th>
                                <th class="px-6 py-4 text-center">Status</th>
                                <th class="px-6 py-4 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            <template x-for="s in salaries" :key="s.id">
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                    <td class="px-6 py-4 font-medium text-gray-800 dark:text-white" x-text="s.employee">
                                    </td>
                                    <td class="px-6 py-4 text-gray-600 dark:text-gray-400" x-text="s.role"></td>
                                    <td class="px-6 py-4 text-right font-mono" x-text="formatCurrency(s.basic_salary)"></td>
                                    <td class="px-6 py-4 text-right font-mono text-green-600"
                                        x-text="'+ ' + formatCurrency(s.allowance)"></td>
                                    <td class="px-6 py-4 text-right font-mono text-red-500"
                                        x-text="s.deductions > 0 ? '- ' + formatCurrency(s.deductions) : '-'"></td>
                                    <td class="px-6 py-4 text-right font-bold text-gray-800 dark:text-white"
                                        x-text="formatCurrency(s.basic_salary + s.allowance - s.deductions)"></td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="px-2.5 py-1 rounded-full text-xs font-bold"
                                            :class="s.status === 'Paid' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'"
                                            x-text="s.status"></span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <button
                                            class="text-blue-600 hover:text-blue-800 bg-blue-50 hover:bg-blue-100 p-2 rounded-lg transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                                </path>
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Section: Attendance -->
        <div x-show="activeTab === 'attendance'" x-cloak x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
            <div
                class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="p-4 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
                    <h3 class="font-bold text-gray-800 dark:text-white">Rekap Absensi (Hari Ini)</h3>
                    <div class="flex gap-2">
                        <div class="relative">
                            <input type="date" x-model="filterDate"
                                class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm font-medium focus:ring-2 focus:ring-blue-500 cursor-pointer">
                        </div>
                        <button @click="openAttModal()"
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium flex items-center gap-2 transition-all shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4">
                                </path>
                            </svg>
                            Input Manual
                        </button>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead
                            class="bg-blue-50/50 dark:bg-gray-700 text-gray-500 dark:text-gray-400 uppercase text-xs font-semibold">
                            <tr>
                                <th class="px-6 py-4">Karyawan</th>
                                <th class="px-6 py-4">Tanggal</th>
                                <th class="px-6 py-4 text-center">Masuk</th>
                                <th class="px-6 py-4 text-center">Keluar</th>
                                <th class="px-6 py-4 text-center">Total Jam</th>
                                <th class="px-6 py-4 text-center">Status</th>
                                <th class="px-6 py-4 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            <template x-for="att in attendance" :key="att.id">
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                    <td class="px-6 py-4 font-medium text-gray-800 dark:text-white" x-text="att.employee">
                                    </td>
                                    <td class="px-6 py-4 text-gray-600 dark:text-gray-400" x-text="att.date"></td>
                                    <td class="px-6 py-4 text-center font-mono text-blue-600 font-bold"
                                        x-text="att.check_in"></td>
                                    <td class="px-6 py-4 text-center font-mono text-gray-600 dark:text-gray-300"
                                        x-text="att.check_out"></td>
                                    <td class="px-6 py-4 text-center font-medium" x-text="att.work_hours"></td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="px-2.5 py-1 rounded-full text-xs font-bold" :class="{
                                                            'bg-green-100 text-green-800': att.status === 'On Time',
                                                            'bg-yellow-100 text-yellow-800': att.status === 'Late',
                                                            'bg-red-100 text-red-800': att.status === 'Absent'
                                                        }" x-text="att.status"></span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <button class="p-1.5 text-gray-500 hover:text-blue-600 transition-colors"
                                            title="Edit">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z">
                                                </path>
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Modal Placeholder (Development Info) -->
        <div x-show="showModal"
            class="fixed inset-0 z-[9999] flex items-center justify-center bg-gray-900/80 backdrop-blur-sm p-4" x-cloak>
            <div class="bg-white dark:bg-gray-800 rounded-2xl w-full max-w-md p-6 shadow-xl"
                @click.away="showModal = false">
                <h3 class="text-xl font-bold mb-4 dark:text-white">Fitur Dalam Pengembangan</h3>
                <p class="text-gray-500 mb-6">Modul perhitungan gaji otomatis via QR Code sedang dikerjakan sesuai roadmap.
                </p>
                <div class="flex justify-end">
                    <button @click="showModal = false"
                        class="px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg text-gray-700">Mengerti</button>
                </div>
            </div>
        </div>

        <!-- Attendance Input Modal -->
        <div x-show="showAttModal"
            class="fixed inset-0 z-[9999] flex items-center justify-center bg-gray-900/80 backdrop-blur-sm p-4" x-cloak
            x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
            <div class="bg-white dark:bg-gray-800 rounded-2xl w-full max-w-lg shadow-2xl transform"
                @click.away="showAttModal = false">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
                    <h3 class="text-xl font-bold text-gray-800 dark:text-white">Input Absensi Manual</h3>
                    <button @click="showAttModal = false" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                            </path>
                        </svg>
                    </button>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nama Karyawan</label>
                        <input type="text" x-model="attForm.employee"
                            class="w-full px-4 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tanggal</label>
                        <input type="date" x-model="attForm.date"
                            class="w-full px-4 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Jam Masuk</label>
                            <input type="time" x-model="attForm.check_in"
                                class="w-full px-4 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Jam
                                Keluar</label>
                            <input type="time" x-model="attForm.check_out"
                                class="w-full px-4 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        </div>
                    </div>
                </div>
                <div
                    class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 flex justify-end gap-3 bg-gray-50 dark:bg-gray-800/50 rounded-b-2xl">
                    <button @click="showAttModal = false"
                        class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg">Batal</button>
                    <button @click="saveAttendance()"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 shadow-lg shadow-blue-500/30">Simpan
                        Absen</button>
                </div>
            </div>
        </div>
    </div>
@endsection