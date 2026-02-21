@extends('layouts.app')

@section('title', 'Payroll & Attendance | SAGA TOKO APP')

@section('content')
    <div x-data="{
                    activeTab: 'payroll',
                    showModal: false,
                    isLoading: false,
                    searchQuery: '',
                    selectedMonth: new Date().toISOString().substring(0, 7), // YYYY-MM
                    
                    // Real Data
                    salaries: [],
                    employees: [],
                    attendance: [],

                    // Preview Data
                    previewData: null,
                    selectedEmployeeId: null,

                    // Bulk Data
                    showBulkModal: false,
                    bulkPreviewData: null,

                    async init() {
                        await this.fetchPayrolls();
                        await this.fetchEmployees();
                    },

                    async fetchPayrolls() {
                        this.isLoading = true;
                        try {
                            const response = await fetch(`/api/payrolls?period=${this.selectedMonth}`);
                            const result = await response.json();
                            this.salaries = result.data.data || result.data || [];
                        } catch (e) { console.error(e); }
                        finally { this.isLoading = false; }
                    },

                    async fetchEmployees() {
                        try {
                            const response = await fetch('/api/employees');
                            const result = await response.json();
                            this.employees = result.data.data || [];
                        } catch (e) { console.error(e); }
                    },

                    async getBulkPreview() {
                        this.isLoading = true;
                        this.showBulkModal = true;
                        try {
                            const response = await fetch(`/api/payrolls/bulk-preview?period=${this.selectedMonth}`);
                            const result = await response.json();
                            if (response.ok) {
                                this.bulkPreviewData = result.data;
                            } else {
                                Swal.fire('Error', result.message, 'error');
                            }
                        } catch (e) { console.error(e); }
                        finally { this.isLoading = false; }
                    },

                    async runBulkGeneration() {
                        this.isLoading = true;
                        try {
                            const response = await fetch('/api/payrolls/bulk', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify({
                                    period: this.selectedMonth
                                })
                            });
                            const result = await response.json();
                            if (response.ok) {
                                Swal.fire('Berhasil', `${result.count} Slip gaji telah digenerate. Total: ${this.formatCurrency(result.total_payout)}`, 'success');
                                this.showBulkModal = false;
                                await this.fetchPayrolls();
                            } else {
                                Swal.fire('Gagal', result.message, 'error');
                            }
                        } catch (e) { console.error(e); }
                        finally { this.isLoading = false; }
                    },

                    async getPreview() {
                        if (!this.selectedEmployeeId) return;
                        this.isLoading = true;
                        try {
                            const response = await fetch(`/api/employees/${this.selectedEmployeeId}/payroll-preview?period=${this.selectedMonth}`);
                            const result = await response.json();
                            if (response.ok) {
                                this.previewData = result.data;
                            } else {
                                Swal.fire('Error', result.message, 'error');
                            }
                        } catch (e) { console.error(e); }
                        finally { this.isLoading = false; }
                    },

                    async savePayroll() {
                        if (!this.previewData) return;
                        this.isLoading = true;
                        try {
                            const response = await fetch('/api/payrolls', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify({
                                    employee_id: this.previewData.employee_id,
                                    period: this.previewData.period
                                })
                            });
                            if (response.ok) {
                                Swal.fire('Berhasil', 'Slip gaji telah disimpan', 'success');
                                this.showModal = false;
                                await this.fetchPayrolls();
                            } else {
                                const err = await response.json();
                                Swal.fire('Gagal', err.message, 'error');
                            }
                        } catch (e) { console.error(e); }
                        finally { this.isLoading = false; }
                    },

                    async downloadPdf(id) {
                        window.open(`/api/payrolls/${id}/pdf`, '_blank');
                    },

                    async exportExcel() {
                        window.open(`/api/payrolls/export/excel?period=${this.selectedMonth}`, '_blank');
                    },

                    formatCurrency(amount) {
                        return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(amount);
                    }
                }" x-init="init()">
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
            
            <!-- Summary Stats -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                    <div class="flex items-center gap-4">
                        <div class="p-3 bg-brand-50 text-brand-600 rounded-lg">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Estimasi Total Gaji</p>
                            <h4 class="text-xl font-bold text-gray-800 dark:text-white" x-text="formatCurrency(salaries.reduce((acc, s) => acc + parseFloat(s.total_amount), 0))"></h4>
                        </div>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                    <div class="flex items-center gap-4">
                        <div class="p-3 bg-green-50 text-green-600 rounded-lg">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Slip Terbit</p>
                            <h4 class="text-xl font-bold text-gray-800 dark:text-white" x-text="salaries.length + ' Slip'"></h4>
                        </div>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                    <div class="flex items-center gap-4">
                        <div class="p-3 bg-red-50 text-red-600 rounded-lg">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Total Potongan</p>
                            <h4 class="text-xl font-bold text-gray-800 dark:text-white" x-text="formatCurrency(salaries.reduce((acc, s) => acc + parseFloat(s.deductions), 0))"></h4>
                        </div>
                    </div>
                </div>
            </div>

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
                    <div class="flex gap-2">
                        <button @click="getBulkPreview()"
                            class="px-4 py-2 bg-brand-600 text-white rounded-lg hover:bg-brand-700 text-sm font-medium transition-all shadow-sm flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10">
                                </path>
                            </svg>
                            Generate Massal
                        </button>
                        <button @click="showModal = true; selectedEmployeeId = ''; previewData = null"
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium transition-all shadow-sm flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            Buat Slip
                        </button>
                        <button @click="exportExcel()"
                            class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm font-medium transition-all shadow-sm flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Export Excel
                        </button>
                    </div>
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
                                    <td class="px-6 py-4 font-medium text-gray-800 dark:text-white" x-text="s.employee.name">
                                    </td>
                                    <td class="px-6 py-4 text-gray-600 dark:text-gray-400" x-text="s.employee.role"></td>
                                    <td class="px-6 py-4 text-right font-mono" x-text="formatCurrency(s.basic_salary)"></td>
                                    <td class="px-6 py-4 text-right font-mono text-green-600"
                                        x-text="'+ ' + formatCurrency(s.allowances + s.bonuses)"></td>
                                    <td class="px-6 py-4 text-right font-mono text-red-500"
                                        x-text="s.deductions > 0 ? '- ' + formatCurrency(s.deductions) : '-'"></td>
                                    <td class="px-6 py-4 text-right font-bold text-gray-800 dark:text-white"
                                        x-text="formatCurrency(s.total_amount)"></td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-green-100 text-green-800">Paid</span>
                                    </td>
                                    <td class="px-6 py-4 text-center flex justify-center gap-2">
                                        <!-- PDF Download -->
                                        <button @click="downloadPdf(s.id)"
                                            class="text-red-600 hover:text-red-800 bg-red-50 hover:bg-red-100 p-2 rounded-lg transition-colors border border-red-100"
                                            title="Download PDF">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                            </svg>
                                        </button>
                                        <!-- Detail -->
                                        <button
                                            class="text-blue-600 hover:text-blue-800 bg-blue-50 hover:bg-blue-100 p-2 rounded-lg transition-colors border border-blue-100">
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

        <!-- Modal: Buat Slip Gaji -->
        <div x-show="showModal"
            class="fixed inset-0 z-[9999] flex items-center justify-center bg-gray-900/80 backdrop-blur-sm p-4" x-cloak
            x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
            <div class="bg-white dark:bg-gray-800 rounded-2xl w-full max-w-lg shadow-2xl transform"
                @click.away="showModal = false">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
                    <h3 class="text-xl font-bold text-gray-800 dark:text-white">Buat Slip Gaji</h3>
                    <button @click="showModal = false" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                            </path>
                        </svg>
                    </button>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Pilih Karyawan</label>
                        <select x-model="selectedEmployeeId" @change="getPreview()"
                            class="w-full px-4 py-2.5 border border-gray-200 rounded-xl dark:bg-gray-900 dark:border-gray-600 dark:text-white focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                            <option value="">-- Pilih Karyawan --</option>
                            <template x-for="emp in employees" :key="emp.id">
                                <option :value="emp.id" x-text="emp.name + ' (' + emp.nik + ')'"></option>
                            </template>
                        </select>
                    </div>

                    <!-- Preview Section -->
                    <template x-if="previewData">
                        <div class="p-4 bg-gray-50 dark:bg-gray-900/50 rounded-xl border border-gray-100 dark:border-gray-700 space-y-3">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Gaji Pokok:</span>
                                <span class="font-bold text-gray-800 dark:text-white" x-text="formatCurrency(previewData.basic_salary)"></span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Tunjangan:</span>
                                <span class="font-bold text-green-600" x-text="'+ ' + formatCurrency(previewData.allowances + previewData.bonuses)"></span>
                            </div>
                            
                            <!-- Attendance Details -->
                            <div class="pt-2 border-t border-dashed border-gray-200 dark:border-gray-700">
                                <span class="text-[10px] font-bold uppercase text-gray-400 tracking-wider">Ringkasan Absensi</span>
                                <div class="grid grid-cols-3 gap-2 mt-1">
                                    <div class="text-center p-1.5 bg-white dark:bg-gray-800 rounded-lg border border-gray-100 dark:border-gray-700">
                                        <div class="text-[10px] text-gray-500">Hadir</div>
                                        <div class="text-xs font-bold" x-text="previewData.attendance_summary.present"></div>
                                    </div>
                                    <div class="text-center p-1.5 bg-white dark:bg-gray-800 rounded-lg border border-gray-100 dark:border-gray-700">
                                        <div class="text-[10px] text-gray-500">Telat</div>
                                        <div class="text-xs font-bold text-yellow-600" x-text="previewData.attendance_summary.late"></div>
                                    </div>
                                    <div class="text-center p-1.5 bg-white dark:bg-gray-800 rounded-lg border border-gray-100 dark:border-gray-700">
                                        <div class="text-[10px] text-gray-500">Absen</div>
                                        <div class="text-xs font-bold text-red-500" x-text="previewData.attendance_summary.absent"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="flex justify-between text-sm pt-2 text-red-500">
                                <span>Potongan Absensi:</span>
                                <span class="font-bold" x-text="'- ' + formatCurrency(previewData.deductions)"></span>
                            </div>
                            <div class="flex justify-between text-lg font-bold pt-3 border-t border-gray-200 dark:border-gray-700">
                                <span class="text-gray-800 dark:text-white">Total Gaji:</span>
                                <span class="text-brand-600" x-text="formatCurrency(previewData.total_amount)"></span>
                            </div>
                        </div>
                    </template>

                    <div x-show="isLoading" class="flex justify-center py-4">
                        <span class="w-8 h-8 border-4 border-brand-500/30 border-t-brand-600 rounded-full animate-spin"></span>
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 flex justify-end gap-3 bg-gray-50 dark:bg-gray-800/50 rounded-b-2xl">
                    <button @click="showModal = false"
                        class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg">Batal</button>
                    <button @click="savePayroll()" :disabled="isLoading || !previewData"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 shadow-lg shadow-blue-500/30 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Konfirmasi & Simpan
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal: Bulk Generation -->
        <div x-show="showBulkModal"
            class="fixed inset-0 z-[9999] flex items-center justify-center bg-gray-900/80 backdrop-blur-sm p-4" x-cloak
            x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
            <div class="bg-white dark:bg-gray-800 rounded-2xl w-full max-w-lg shadow-2xl transform"
                @click.away="showBulkModal = false">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
                    <h3 class="text-xl font-bold text-gray-800 dark:text-white">Generate Payroll Masal</h3>
                    <button @click="showBulkModal = false" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                            </path>
                        </svg>
                    </button>
                </div>
                <div class="p-6 space-y-4">
                    <p class="text-gray-600 dark:text-gray-400 text-sm">
                        Anda akan meng-generate slip gaji untuk seluruh karyawan aktif pada periode
                        <span class="font-bold text-gray-800 dark:text-white" x-text="selectedMonth"></span>.
                    </p>

                    <!-- Preview Section -->
                    <template x-if="bulkPreviewData">
                        <div class="p-4 bg-brand-50 dark:bg-brand-900/20 rounded-xl border border-brand-100 dark:border-brand-800 space-y-3">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Total Karyawan:</span>
                                <span class="font-bold text-gray-800 dark:text-white" x-text="bulkPreviewData.total_employees"></span>
                            </div>
                            <div class="flex justify-between text-lg font-bold pt-3 border-t border-brand-200/50 dark:border-brand-700/50">
                                <span class="text-gray-800 dark:text-white">Estimasi Total Payout:</span>
                                <span class="text-brand-600" x-text="formatCurrency(bulkPreviewData.total_payout)"></span>
                            </div>
                        </div>
                    </template>

                    <div x-show="isLoading" class="flex flex-col items-center justify-center py-4">
                        <span class="w-8 h-8 border-4 border-brand-500/30 border-t-brand-600 rounded-full animate-spin"></span>
                        <p class="text-xs text-gray-500 mt-2">Menghitung gaji seluruh tim...</p>
                    </div>

                    <div class="bg-yellow-50 dark:bg-yellow-900/20 p-3 rounded-lg flex gap-3 items-start border border-yellow-100 dark:border-yellow-800">
                        <svg class="w-5 h-5 text-yellow-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                        <p class="text-xs text-yellow-800 dark:text-yellow-400">
                            <strong>Penting:</strong> Slip yang sudah ada pada periode yang sama akan diupdate (overwrite) dengan perhitungan terbaru.
                        </p>
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 flex justify-end gap-3 bg-gray-50 dark:bg-gray-800/50 rounded-b-2xl">
                    <button @click="showBulkModal = false"
                        class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg">Batal</button>
                    <button @click="runBulkGeneration()" :disabled="isLoading || !bulkPreviewData"
                        class="px-4 py-2 bg-brand-600 text-white rounded-lg hover:bg-brand-700 shadow-lg shadow-brand-500/30 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                        Eksekusi Massal
                    </button>
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