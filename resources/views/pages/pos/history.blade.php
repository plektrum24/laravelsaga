@extends('layouts.app')

@section('title', 'Riwayat Transaksi')

@section('content')
    <div class="px-6 py-4">
        <div class="mb-6 flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Riwayat Transaksi</h1>
                <p class="text-gray-600 dark:text-gray-400">Lihat dan kelola history penjualan kasir</p>
            </div>
            <div>
                <button
                    class="bg-brand-600 text-white px-4 py-2 rounded-lg hover:bg-brand-700 font-medium text-sm flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                        </path>
                    </svg>
                    Export Laporan
                </button>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Tanggal Mulai</label>
                    <input type="date"
                        class="w-full bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-brand-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Tanggal Akhir</label>
                    <input type="date"
                        class="w-full bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-brand-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Kasir</label>
                    <select
                        class="w-full bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-brand-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200">
                        <option value="">Semua Kasir</option>
                        <option value="1">Budi</option>
                        <option value="2">Siti</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button
                        class="w-full bg-gray-100 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-200 font-medium text-sm dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">
                        Terapkan Filter
                    </button>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm overflow-hidden">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr
                        class="bg-gray-50 dark:bg-gray-700/[0.5] text-xs uppercase text-gray-500 font-semibold border-b border-gray-100 dark:border-gray-700">
                        <th class="px-6 py-4">No. Transaksi</th>
                        <th class="px-6 py-4">Waktu</th>
                        <th class="px-6 py-4">Pelanggan</th>
                        <th class="px-6 py-4">Total</th>
                        <th class="px-6 py-4">Metode</th>
                        <th class="px-6 py-4">Kasir</th>
                        <th class="px-6 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700 text-sm">
                    <!-- Dummy Data -->
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/[0.3] transition-colors">
                        <td class="px-6 py-4 font-medium text-brand-600">TRX-00192</td>
                        <td class="px-6 py-4 text-gray-600 dark:text-gray-400">25 Jan, 14:30</td>
                        <td class="px-6 py-4 font-medium text-gray-800 dark:text-gray-200">Budi Santoso</td>
                        <td class="px-6 py-4 font-bold text-gray-800 dark:text-white">Rp 150.000</td>
                        <td class="px-6 py-4"><span
                                class="px-2 py-1 rounded bg-green-100 text-green-700 text-xs font-semibold">Cash</span></td>
                        <td class="px-6 py-4 text-gray-600 dark:text-gray-400">Siti (Kasir 1)</td>
                        <td class="px-6 py-4 text-right">
                            <button class="text-indigo-500 hover:text-indigo-700 mx-1" title="Print Ulang">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z">
                                    </path>
                                </svg>
                            </button>
                            <button class="text-blue-500 hover:text-blue-700 mx-1" title="Detail">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                    </path>
                                </svg>
                            </button>
                            <button class="text-amber-500 hover:text-amber-700 mx-1" title="Edit Transaksi">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                    </path>
                                </svg>
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
@endsection