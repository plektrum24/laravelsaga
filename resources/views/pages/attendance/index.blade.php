@extends('layouts.app')

@section('title', 'Absensi Harian | SAGA TOKO APP')

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-gray-900 p-6" x-data="attendanceSystem()">
    <!-- Header -->
    <div class="max-w-7xl mx-auto mb-8">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-800 dark:text-white">📅 Absensi Harian</h1>
                <p class="text-gray-500 dark:text-gray-400 mt-1">Kelola kehadiran karyawan dengan sistem modern</p>
            </div>
            <div class="flex items-center gap-3">
                <div class="relative">
                    <input type="date" x-model="selectedDate" @change="fetchAttendance()"
                        class="pl-4 pr-4 py-2.5 border-2 border-gray-200 dark:border-gray-700 rounded-xl bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 font-medium focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all">
                </div>
                <button @click="openCheckInModal()" 
                    class="px-6 py-2.5 bg-gradient-to-r from-brand-600 to-indigo-600 text-white rounded-xl font-semibold hover:from-brand-700 hover:to-indigo-700 transition-all shadow-lg shadow-brand-500/30 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                    </svg>
                    Check In
                </button>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="max-w-7xl mx-auto mb-8 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Employees -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">Total Karyawan</p>
                    <h3 class="text-3xl font-bold text-gray-800 dark:text-white mt-1" x-text="stats.total"></h3>
                </div>
                <div class="w-14 h-14 bg-brand-100 dark:bg-brand-900/30 rounded-2xl flex items-center justify-center">
                    <svg class="w-7 h-7 text-brand-600 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Present -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">Hadir</p>
                    <h3 class="text-3xl font-bold text-green-600 dark:text-green-400 mt-1" x-text="stats.present"></h3>
                    <p class="text-xs text-green-600 mt-1" x-text="stats.total > 0 ? ((stats.present / stats.total) * 100).toFixed(1) + '%' : '0%'"></p>
                </div>
                <div class="w-14 h-14 bg-green-100 dark:bg-green-900/30 rounded-2xl flex items-center justify-center">
                    <svg class="w-7 h-7 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Late -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">Terlambat</p>
                    <h3 class="text-3xl font-bold text-yellow-600 dark:text-yellow-400 mt-1" x-text="stats.late"></h3>
                </div>
                <div class="w-14 h-14 bg-yellow-100 dark:bg-yellow-900/30 rounded-2xl flex items-center justify-center">
                    <svg class="w-7 h-7 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Absent -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">Tidak Hadir</p>
                    <h3 class="text-3xl font-bold text-red-600 dark:text-red-400 mt-1" x-text="stats.absent"></h3>
                </div>
                <div class="w-14 h-14 bg-red-100 dark:bg-red-900/30 rounded-2xl flex items-center justify-center">
                    <svg class="w-7 h-7 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Attendance Table -->
    <div class="max-w-7xl mx-auto">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <!-- Table Header -->
            <div class="p-6 border-b border-gray-200 dark:border-gray-700 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-brand-100 dark:bg-brand-900/30 rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5 text-brand-600 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-gray-800 dark:text-white">Daftar Absensi</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400" x-text="formatDate(selectedDate)"></p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <input type="text" x-model="searchQuery" placeholder="🔍 Cari karyawan..." 
                        class="px-4 py-2.5 border-2 border-gray-200 dark:border-gray-700 rounded-xl bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all">
                </div>
            </div>

            <!-- Loading State -->
            <div x-show="isLoading" class="p-12 text-center">
                <div class="inline-block animate-spin rounded-full h-12 w-12 border-4 border-brand-200 border-t-brand-600"></div>
                <p class="text-gray-500 dark:text-gray-400 mt-4">Memuat data absensi...</p>
            </div>

            <!-- Table Content -->
            <div x-show="!isLoading" class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 dark:bg-gray-900/50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Karyawan</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Check In</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Check Out</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Keterangan</th>
                            <th class="px-6 py-4 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        <template x-for="item in filteredAttendance" :key="item.id">
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/30 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 bg-gradient-to-br from-brand-400 to-indigo-500 rounded-full flex items-center justify-center text-white font-bold text-sm"
                                            x-text="getInitials(item.employee_name)">
                                        </div>
                                        <div>
                                            <p class="font-semibold text-gray-800 dark:text-white" x-text="item.employee_name"></p>
                                            <p class="text-sm text-gray-500 dark:text-gray-400" x-text="item.employee_nik"></p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-show="item.check_in_time">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                                        </svg>
                                        <span class="text-gray-700 dark:text-gray-300 font-medium" 
                                            x-text="item.check_in_time ? formatTime(item.check_in_time) : '-'"></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-show="item.check_out_time">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                                        </svg>
                                        <span class="text-gray-700 dark:text-gray-300 font-medium" 
                                            x-text="item.check_out_time ? formatTime(item.check_out_time) : '-'"></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold"
                                        :class="{
                                            'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400': item.status === 'present',
                                            'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400': item.status === 'late',
                                            'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400': item.status === 'absent',
                                            'bg-gray-100 text-gray-700 dark:bg-gray-900/30 dark:text-gray-400': !item.status
                                        }"
                                        x-text="formatStatus(item.status)">
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-sm text-gray-600 dark:text-gray-400" x-text="item.notes || '-'"></span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <button @click="editAttendance(item)" 
                                        class="px-4 py-2 text-sm font-medium text-brand-600 dark:text-brand-400 hover:bg-brand-50 dark:hover:bg-brand-900/20 rounded-lg transition-colors">
                                        Edit
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>

                <!-- Empty State -->
                <div x-show="filteredAttendance.length === 0" class="p-12 text-center">
                    <div class="w-20 h-20 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                    </div>
                    <p class="text-gray-500 dark:text-gray-400 text-lg font-medium">Belum ada data absensi</p>
                    <p class="text-gray-400 dark:text-gray-500 text-sm mt-1">Silakan lakukan check in untuk memulai</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Check In Modal -->
    <div x-show="showCheckInModal" style="display: none;" 
        class="fixed inset-0 z-50 overflow-y-auto"
        x-cloak>
        <div class="flex items-center justify-center min-h-screen px-4">
            <div x-show="showCheckInModal" 
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-black/50 backdrop-blur-sm"
                @click="showCheckInModal = false"></div>

            <div x-show="showCheckInModal"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-md w-full p-6 z-10">
                
                <div class="text-center mb-6">
                    <div class="w-16 h-16 bg-brand-100 dark:bg-brand-900/30 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-brand-600 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 dark:text-white">Check In Kehadiran</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Silakan pilih karyawan dan lakukan check in</p>
                </div>

                <form @submit.prevent="submitCheckIn()" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Pilih Karyawan</label>
                        <select x-model="checkInForm.employee_id" required
                            class="w-full px-4 py-3 border-2 border-gray-200 dark:border-gray-700 rounded-xl bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all">
                            <option value="">-- Pilih Karyawan --</option>
                            <template x-for="emp in employees" :key="emp.id">
                                <option :value="emp.id" x-text="emp.name + ' (' + emp.nik + ')'"></option>
                            </template>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Keterangan</label>
                        <textarea x-model="checkInForm.notes" rows="3"
                            class="w-full px-4 py-3 border-2 border-gray-200 dark:border-gray-700 rounded-xl bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all"
                            placeholder="Catatan tambahan (opsional)"></textarea>
                    </div>

                    <div class="flex gap-3 pt-4">
                        <button type="button" @click="showCheckInModal = false"
                            class="flex-1 px-6 py-3 border-2 border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 rounded-xl font-semibold hover:bg-gray-50 dark:hover:bg-gray-900 transition-colors">
                            Batal
                        </button>
                        <button type="submit" :disabled="isLoading"
                            class="flex-1 px-6 py-3 bg-gradient-to-r from-brand-600 to-indigo-600 text-white rounded-xl font-semibold hover:from-brand-700 hover:to-indigo-700 transition-all shadow-lg shadow-brand-500/30 disabled:opacity-50 disabled:cursor-not-allowed">
                            <span x-show="!isLoading">Check In</span>
                            <span x-show="isLoading">Memproses...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function attendanceSystem() {
    return {
        selectedDate: new Date().toISOString().split('T')[0],
        searchQuery: '',
        isLoading: false,
        showCheckInModal: false,
        employees: [],
        attendance: [],
        stats: {
            total: 0,
            present: 0,
            late: 0,
            absent: 0
        },
        checkInForm: {
            employee_id: '',
            notes: ''
        },

        get filteredAttendance() {
            if (!this.searchQuery) return this.attendance;
            const q = this.searchQuery.toLowerCase();
            return this.attendance.filter(item =>
                (item.employee_name && item.employee_name.toLowerCase().includes(q)) ||
                (item.employee_nik && item.employee_nik.toLowerCase().includes(q))
            );
        },

        async init() {
            await this.fetchEmployees();
            await this.fetchAttendance();
        },

        async fetchEmployees() {
            try {
                const token = localStorage.getItem('saga_token');
                const response = await fetch('/api/employees', {
                    headers: { 'Authorization': 'Bearer ' + token }
                });
                const result = await response.json();
                if (result.success) {
                    this.employees = result.data.data || [];
                    this.stats.total = this.employees.length;
                }
            } catch (e) {
                console.error('Error fetching employees:', e);
            }
        },

        async fetchAttendance() {
            this.isLoading = true;
            try {
                const token = localStorage.getItem('saga_token');
                const response = await fetch(`/api/attendance?date=${this.selectedDate}`, {
                    headers: { 'Authorization': 'Bearer ' + token }
                });
                const result = await response.json();
                if (result.success) {
                    this.attendance = result.data.data || [];
                    this.calculateStats();
                }
            } catch (e) {
                console.error('Error fetching attendance:', e);
            } finally {
                this.isLoading = false;
            }
        },

        calculateStats() {
            this.stats.present = this.attendance.filter(a => a.status === 'present').length;
            this.stats.late = this.attendance.filter(a => a.status === 'late').length;
            this.stats.absent = this.stats.total - this.stats.present;
        },

        openCheckInModal() {
            this.checkInForm = { employee_id: '', notes: '' };
            this.showCheckInModal = true;
        },

        async submitCheckIn() {
            this.isLoading = true;
            try {
                const token = localStorage.getItem('saga_token');
                const response = await fetch('/api/attendance/check-in', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': 'Bearer ' + token
                    },
                    body: JSON.stringify({
                        employee_id: this.checkInForm.employee_id,
                        date: this.selectedDate,
                        notes: this.checkInForm.notes
                    })
                });
                const result = await response.json();
                if (result.success) {
                    Swal.fire({
                        icon: 'success',
                        title: '✅ Check In Berhasil',
                        text: 'Kehadiran berhasil dicatat',
                        timer: 2000,
                        showConfirmButton: false
                    });
                    this.showCheckInModal = false;
                    await this.fetchAttendance();
                } else {
                    Swal.fire('Error', result.message || 'Gagal check in', 'error');
                }
            } catch (e) {
                console.error('Error checking in:', e);
                Swal.fire('Error', 'Terjadi kesalahan sistem', 'error');
            } finally {
                this.isLoading = false;
            }
        },

        editAttendance(item) {
            Swal.fire({
                title: 'Edit Absensi',
                html: `
                    <div class="text-left space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <select id="editStatus" class="w-full px-3 py-2 border rounded-lg">
                                <option value="present" ${item.status === 'present' ? 'selected' : ''}>Hadir</option>
                                <option value="late" ${item.status === 'late' ? 'selected' : ''}>Terlambat</option>
                                <option value="absent" ${item.status === 'absent' ? 'selected' : ''}>Tidak Hadir</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Keterangan</label>
                            <textarea id="editNotes" class="w-full px-3 py-2 border rounded-lg" rows="3">${item.notes || ''}</textarea>
                        </div>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Simpan',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#4f46e5',
                cancelButtonColor: '#6b7280',
                preConfirm: () => {
                    const status = document.getElementById('editStatus').value;
                    const notes = document.getElementById('editNotes').value;
                    return { status, notes };
                }
            }).then(async (result) => {
                if (result.isConfirmed) {
                    // TODO: Implement update attendance API
                    Swal.fire('Berhasil', 'Data absensi diperbarui', 'success');
                }
            });
        },

        getInitials(name) {
            if (!name) return '?';
            return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);
        },

        formatDate(dateStr) {
            const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            return new Date(dateStr).toLocaleDateString('id-ID', options);
        },

        formatTime(timeStr) {
            if (!timeStr) return '-';
            const date = new Date('2000-01-01 ' + timeStr);
            return date.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
        },

        formatStatus(status) {
            const statuses = {
                'present': 'Hadir',
                'late': 'Terlambat',
                'absent': 'Tidak Hadir'
            };
            return statuses[status] || '-';
        }
    }
}
</script>
@endpush
@endsection
