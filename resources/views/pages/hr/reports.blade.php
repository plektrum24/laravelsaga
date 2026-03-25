@extends('layouts.app')

@section('title', 'Laporan HR | SAGA TOKO APP')

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-gray-900 p-6" x-data="hrReports()">
    <!-- Header -->
    <div class="max-w-7xl mx-auto mb-8">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-800 dark:text-white">📊 Laporan HR & Payroll</h1>
                <p class="text-gray-500 dark:text-gray-400 mt-1">Analisis dan laporan kepegawaian</p>
            </div>
            <div class="flex items-center gap-3">
                <button @click="exportReport('excel')" 
                    class="px-6 py-2.5 bg-green-600 text-white rounded-xl font-semibold hover:bg-green-700 transition-all shadow-lg flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Export Excel
                </button>
                <button @click="exportReport('pdf')" 
                    class="px-6 py-2.5 bg-red-600 text-white rounded-xl font-semibold hover:bg-red-700 transition-all shadow-lg flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                    </svg>
                    Export PDF
                </button>
            </div>
        </div>
    </div>

    <!-- Period Selector -->
    <div class="max-w-7xl mx-auto mb-8">
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="flex flex-wrap items-end gap-4">
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Periode</label>
                    <select x-model="selectedPeriod" @change="fetchReports()"
                        class="w-full px-4 py-3 border-2 border-gray-200 dark:border-gray-700 rounded-xl bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all">
                        <option value="daily">Harian</option>
                        <option value="weekly">Mingguan</option>
                        <option value="monthly">Bulanan</option>
                        <option value="yearly">Tahunan</option>
                    </select>
                </div>
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Dari Tanggal</label>
                    <input type="date" x-model="dateFrom" 
                        class="w-full px-4 py-3 border-2 border-gray-200 dark:border-gray-700 rounded-xl bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all">
                </div>
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Sampai Tanggal</label>
                    <input type="date" x-model="dateTo" 
                        class="w-full px-4 py-3 border-2 border-gray-200 dark:border-gray-700 rounded-xl bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all">
                </div>
                <button @click="fetchReports()" 
                    class="px-6 py-3 bg-brand-600 text-white rounded-xl font-semibold hover:bg-brand-700 transition-all shadow-lg">
                    🔍 Tampilkan
                </button>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="max-w-7xl mx-auto mb-8 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Employees -->
        <div class="bg-gradient-to-br from-brand-500 to-indigo-600 rounded-2xl p-6 shadow-lg text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-brand-100 text-sm font-medium">Total Karyawan</p>
                    <h3 class="text-4xl font-bold mt-2" x-text="reports.total_employees"></h3>
                    <p class="text-brand-100 text-xs mt-2">Aktif: <span x-text="reports.active_employees"></span></p>
                </div>
                <div class="w-16 h-16 bg-white/20 rounded-2xl flex items-center justify-center">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Attendance Rate -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 dark:text-gray-400 text-sm font-medium">Tingkat Kehadiran</p>
                    <h3 class="text-4xl font-bold text-gray-800 dark:text-white mt-2" x-text="reports.attendance_rate + '%'"></h3>
                    <p class="text-green-600 text-xs mt-2">↑ 2.5% dari periode lalu</p>
                </div>
                <div class="w-16 h-16 bg-green-100 dark:bg-green-900/30 rounded-2xl flex items-center justify-center">
                    <svg class="w-8 h-8 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Total Payroll -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 dark:text-gray-400 text-sm font-medium">Total Payroll</p>
                    <h3 class="text-3xl font-bold text-gray-800 dark:text-white mt-2" x-text="formatCurrency(reports.total_payroll)"></h3>
                    <p class="text-gray-400 text-xs mt-2">Periode ini</p>
                </div>
                <div class="w-16 h-16 bg-brand-100 dark:bg-brand-900/30 rounded-2xl flex items-center justify-center">
                    <svg class="w-8 h-8 text-brand-600 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Overtime Hours -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 dark:text-gray-400 text-sm font-medium">Jam Lembur</p>
                    <h3 class="text-4xl font-bold text-gray-800 dark:text-white mt-2" x-text="reports.overtime_hours"></h3>
                    <p class="text-gray-400 text-xs mt-2">Total jam</p>
                </div>
                <div class="w-16 h-16 bg-purple-100 dark:bg-purple-900/30 rounded-2xl flex items-center justify-center">
                    <svg class="w-8 h-8 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts & Tables -->
    <div class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Attendance Chart -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-4">Grafik Kehadiran</h3>
            <div class="h-64 flex items-center justify-center bg-gray-50 dark:bg-gray-900 rounded-xl">
                <p class="text-gray-400">📈 Chart akan ditampilkan di sini</p>
            </div>
        </div>

        <!-- Payroll Distribution -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-4">Distribusi Payroll</h3>
            <div class="h-64 flex items-center justify-center bg-gray-50 dark:bg-gray-900 rounded-xl">
                <p class="text-gray-400">📊 Chart akan ditampilkan di sini</p>
            </div>
        </div>
    </div>

    <!-- Employee Performance Table -->
    <div class="max-w-7xl mx-auto">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-xl font-bold text-gray-800 dark:text-white">Performa Karyawan</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Ranking berdasarkan kehadiran dan performa</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 dark:bg-gray-900/50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Rank</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Karyawan</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Kehadiran</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Tepat Waktu</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Lembur</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Score</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        <template x-for="(emp, index) in employeePerformance" :key="emp.id">
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/30 transition-colors">
                                <td class="px-6 py-4">
                                    <span class="w-8 h-8 inline-flex items-center justify-center rounded-full font-bold text-sm"
                                        :class="{
                                            'bg-yellow-100 text-yellow-700': index === 0,
                                            'bg-gray-100 text-gray-700': index === 1,
                                            'bg-orange-100 text-orange-700': index === 2,
                                            'bg-gray-50 text-gray-500': index > 2
                                        }"
                                        x-text="#" + (index + 1)">
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 bg-gradient-to-br from-brand-400 to-indigo-500 rounded-full flex items-center justify-center text-white font-bold text-sm"
                                            x-text="getInitials(emp.name)">
                                        </div>
                                        <div>
                                            <p class="font-semibold text-gray-800 dark:text-white" x-text="emp.name"></p>
                                            <p class="text-sm text-gray-500 dark:text-gray-400" x-text="emp.position"></p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <div class="flex-1 h-2 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                                            <div class="h-full bg-green-500 rounded-full" :style="`width: ${emp.attendance_rate}%`"></div>
                                        </div>
                                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300" x-text="emp.attendance_rate + '%'"></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-sm font-medium" 
                                        :class="emp.on_time_rate >= 95 ? 'text-green-600' : emp.on_time_rate >= 80 ? 'text-yellow-600' : 'text-red-600'"
                                        x-text="emp.on_time_rate + '%'"></span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300" x-text="emp.overtime_hours + ' jam'"></td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold"
                                        :class="{
                                            'bg-green-100 text-green-700': emp.score >= 90,
                                            'bg-blue-100 text-blue-700': emp.score >= 80 && emp.score < 90,
                                            'bg-yellow-100 text-yellow-700': emp.score >= 70 && emp.score < 80,
                                            'bg-red-100 text-red-700': emp.score < 70
                                        }"
                                        x-text="emp.score"></span>
                                </td>
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
function hrReports() {
    return {
        selectedPeriod: 'monthly',
        dateFrom: new Date(new Date().setDate(1)).toISOString().split('T')[0],
        dateTo: new Date().toISOString().split('T')[0],
        isLoading: false,
        reports: {
            total_employees: 0,
            active_employees: 0,
            attendance_rate: 0,
            total_payroll: 0,
            overtime_hours: 0
        },
        employeePerformance: []
    }
}
</script>
@endpush
@endsection
