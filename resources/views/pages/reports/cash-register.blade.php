@extends('layouts.app')

@section('title', 'Laporan Kas Harian | SAGA TOKO APP')

@section('content')
    <div x-data="{
                registers: [],
                isLoading: true,
                dateFrom: new Date().toISOString().split('T')[0],
                dateTo: new Date().toISOString().split('T')[0],

                async fetchRegisters() {
                    this.isLoading = true;
                    try {
                        // Mock Data for now (since API endpoint for listing registers is not yet created, only current/open/close)
                        // We should add a listing endpoint later. 
                        // For now, let's create a realistic mock to visualize the UI.

                        await new Promise(resolve => setTimeout(resolve, 500));

                        this.registers = [
                            {
                                id: 1,
                                user_name: 'Kasir Retail',
                                opened_at: '2024-01-25 08:00:00',
                                closed_at: '2024-01-25 16:00:00',
                                start_cash: 500000,
                                total_cash_sales: 2500000,
                                total_expenses: 150000,
                                end_cash: 2850000,
                                diff_amount: 0,
                                status: 'closed'
                            },
                            {
                                id: 2,
                                user_name: 'Kasir Retail',
                                opened_at: '2024-01-25 16:00:00',
                                closed_at: null,
                                start_cash: 500000,
                                total_cash_sales: 1200000,
                                total_expenses: 0,
                                end_cash: null,
                                diff_amount: 0,
                                status: 'open'
                            }
                        ];
                    } catch (error) {
                        console.error(error);
                    } finally {
                        this.isLoading = false;
                    }
                },

                formatCurrency(amount) {
                     return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(amount || 0);
                },

                formatDateTime(dateStr) {
                    if(!dateStr) return '-';
                    return new Date(dateStr).toLocaleString('id-ID');
                },

                init() {
                    this.fetchRegisters();
                }
            }">
        <!-- Header -->
        <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <a href="{{ route('reports.index') }}" class="text-gray-500 hover:text-brand-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                    </a>
                    <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Laporan Kas Harian</h1>
                </div>
                <p class="text-sm text-gray-500 dark:text-gray-400 ml-7">Rekapitulasi Shift Kasir & Uang Fisik</p>
            </div>

            <div class="flex items-center gap-3">
                <input type="date" x-model="dateFrom"
                    class="px-4 py-2 border border-gray-200 rounded-lg text-sm dark:bg-gray-800 dark:border-gray-700">
                <span class="text-gray-400">-</span>
                <input type="date" x-model="dateTo"
                    class="px-4 py-2 border border-gray-200 rounded-lg text-sm dark:bg-gray-800 dark:border-gray-700">
                <button @click="fetchRegisters()"
                    class="px-4 py-2 bg-brand-600 text-white rounded-lg hover:bg-brand-700 transition">Filter</button>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-white dark:bg-gray-800 rounded-xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm">
                <p class="text-sm text-gray-500 mb-1">Total Setoran Tunai</p>
                <h3 class="text-2xl font-bold text-gray-800 dark:text-white">Rp 3.700.000</h3>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm">
                <p class="text-sm text-gray-500 mb-1">Total Pengeluaran Kas</p>
                <h3 class="text-2xl font-bold text-red-500">Rp 150.000</h3>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl p-5 border border-gray-100 dark:border-gray-700 shadow-sm">
                <p class="text-sm text-gray-500 mb-1">Selisih Kas (Varian)</p>
                <h3 class="text-2xl font-bold text-green-500">Rp 0</h3>
            </div>
        </div>

        <!-- Table -->
        <div
            class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead
                        class="bg-gray-50 dark:bg-gray-700/50 text-gray-500 font-medium border-b border-gray-100 dark:border-gray-700">
                        <tr>
                            <th class="px-6 py-4">Kasir</th>
                            <th class="px-6 py-4">Waktu Buka</th>
                            <th class="px-6 py-4">Waktu Tutup</th>
                            <th class="px-6 py-4 text-right">Modal Awal</th>
                            <th class="px-6 py-4 text-right">Penjualan Tunai</th>
                            <th class="px-6 py-4 text-right">Pengeluaran</th>
                            <th class="px-6 py-4 text-right">Saldo Akhir</th>
                            <th class="px-6 py-4 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        <template x-for="reg in registers" :key="reg.id">
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                <td class="px-6 py-4 font-medium" x-text="reg.user_name"></td>
                                <td class="px-6 py-4" x-text="formatDateTime(reg.opened_at)"></td>
                                <td class="px-6 py-4" x-text="formatDateTime(reg.closed_at)"></td>
                                <td class="px-6 py-4 text-right" x-text="formatCurrency(reg.start_cash)"></td>
                                <td class="px-6 py-4 text-right text-green-600"
                                    x-text="formatCurrency(reg.total_cash_sales)"></td>
                                <td class="px-6 py-4 text-right text-red-500" x-text="formatCurrency(reg.total_expenses)">
                                </td>
                                <td class="px-6 py-4 text-right font-bold"
                                    x-text="formatCurrency(reg.end_cash || (reg.start_cash + reg.total_cash_sales - reg.total_expenses))">
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span
                                        :class="reg.status === 'open' ? 'bg-green-100 text-green-600' : 'bg-gray-100 text-gray-600'"
                                        class="px-2 py-1 rounded-full text-xs font-bold uppercase"
                                        x-text="reg.status"></span>
                                </td>
                            </tr>
                        </template>
                        <tr x-show="registers.length === 0 && !isLoading">
                            <td colspan="8" class="text-center py-8 text-gray-500">Belum ada data laporan kas.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection