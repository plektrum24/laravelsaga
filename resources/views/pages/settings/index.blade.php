@extends('layouts.app')

@section('title', 'Settings | SAGA TOKO APP')

@section('content')
    <div x-data="{
                                        activeTab: new URLSearchParams(window.location.search).get('tab') || 'general',
                                        isLoading: false,

                                        printers: [
                                            { id: 1, name: 'EPSON TM-T82', type: 'Thermal 80mm', connection: 'USB', connected: true },
                                            { id: 2, name: 'Generic Text Only', type: 'Thermal 58mm', connection: 'Bluetooth', connected: false }
                                        ],

                                        connectPrinter(printer) {
                                            this.isLoading = true;
                                            setTimeout(() => {
                                                printer.connected = true;
                                                this.isLoading = false;
                                                Swal.fire({ icon: 'success', title: 'Terhubung', text: `${printer.name} berhasil terhubung!`, timer: 1500, showConfirmButton: false });
                                            }, 1000);
                                        },

                                        testPrint(printer) {
                                            Swal.fire({
                                                title: 'Mencetak...',
                                                text: `Mengirim test print ke ${printer.name}`,
                                                timer: 1500,
                                                didOpen: () => { Swal.showLoading(); }
                                            });
                                        },

                                        saveSettings() {
                                            this.isLoading = true;
                                            setTimeout(() => {
                                                this.isLoading = false;
                                                Swal.fire({
                                                    icon: 'success',
                                                    title: 'Disimpan',
                                                    text: 'Pengaturan berhasil disimpan!',
                                                    timer: 1500,
                                                    showConfirmButton: false
                                                });
                                            }, 1000);
                                        }
                                    }">

        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white flex items-center gap-2">
                <span class="p-2 bg-gray-100 rounded-lg text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z">
                        </path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                </span>
                Pengaturan Toko
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 ml-12">Konfigurasi umum aplikasi</p>
        </div>

        <div class="flex flex-col gap-6">
            <!-- Navigation Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <button @click="activeTab = 'general'"
                    :class="activeTab === 'general' ? 'border-brand-500 ring-1 ring-brand-500 bg-brand-50 dark:bg-brand-900/20' : 'border-gray-200 hover:border-brand-300 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800'"
                    class="relative p-4 rounded-2xl border transition-all text-left group">
                    <div class="flex items-center gap-4">
                        <div :class="activeTab === 'general' ? 'bg-brand-500 text-white' : 'bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-300 group-hover:bg-brand-100 group-hover:text-brand-600'"
                            class="w-12 h-12 rounded-xl flex items-center justify-center transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                                </path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-800 dark:text-white"
                                :class="activeTab === 'general' ? 'text-brand-700 dark:text-brand-400' : ''">Informasi Toko
                            </h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Logo, Nama, Alamat</p>
                        </div>
                    </div>
                </button>

                <button @click="activeTab = 'finance'"
                    :class="activeTab === 'finance' ? 'border-blue-500 ring-1 ring-blue-500 bg-blue-50 dark:bg-blue-900/20' : 'border-gray-200 hover:border-blue-300 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800'"
                    class="relative p-4 rounded-2xl border transition-all text-left group">
                    <div class="flex items-center gap-4">
                        <div :class="activeTab === 'finance' ? 'bg-blue-500 text-white' : 'bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-300 group-hover:bg-blue-100 group-hover:text-blue-600'"
                            class="w-12 h-12 rounded-xl flex items-center justify-center transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                </path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-800 dark:text-white"
                                :class="activeTab === 'finance' ? 'text-blue-700 dark:text-blue-400' : ''">Pajak & Mata Uang
                            </h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">PPN, Rupiah/USD</p>
                        </div>
                    </div>
                </button>

                <button @click="activeTab = 'hardware'"
                    :class="activeTab === 'hardware' ? 'border-orange-500 ring-1 ring-orange-500 bg-orange-50 dark:bg-orange-900/20' : 'border-gray-200 hover:border-orange-300 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800'"
                    class="relative p-4 rounded-2xl border transition-all text-left group">
                    <div class="flex items-center gap-4">
                        <div :class="activeTab === 'hardware' ? 'bg-orange-500 text-white' : 'bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-300 group-hover:bg-orange-100 group-hover:text-orange-600'"
                            class="w-12 h-12 rounded-xl flex items-center justify-center transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H3a2 2 0 00-2 2v4h18zM19 8h6v2h-6V8z">
                                </path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-800 dark:text-white"
                                :class="activeTab === 'hardware' ? 'text-orange-700 dark:text-orange-400' : ''">Hardware
                            </h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Printer & Scanner</p>
                        </div>
                    </div>
                </button>

                <button @click="activeTab = 'cashier'"
                    :class="activeTab === 'cashier' ? 'border-purple-500 ring-1 ring-purple-500 bg-purple-50 dark:bg-purple-900/20' : 'border-gray-200 hover:border-purple-300 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800'"
                    class="relative p-4 rounded-2xl border transition-all text-left group">
                    <div class="flex items-center gap-4">
                        <div :class="activeTab === 'cashier' ? 'bg-purple-500 text-white' : 'bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-300 group-hover:bg-purple-100 group-hover:text-purple-600'"
                            class="w-12 h-12 rounded-xl flex items-center justify-center transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z">
                                </path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-800 dark:text-white"
                                :class="activeTab === 'cashier' ? 'text-purple-700 dark:text-purple-400' : ''">Cashier
                            </h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">POS & Shifts</p>
                        </div>
                    </div>
                </button>

                <button @click="activeTab = 'backup'"
                    :class="activeTab === 'backup' ? 'border-green-500 ring-1 ring-green-500 bg-green-50 dark:bg-green-900/20' : 'border-gray-200 hover:border-green-300 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800'"
                    class="relative p-4 rounded-2xl border transition-all text-left group">
                    <div class="flex items-center gap-4">
                        <div :class="activeTab === 'backup' ? 'bg-green-500 text-white' : 'bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-300 group-hover:bg-green-100 group-hover:text-green-600'"
                            class="w-12 h-12 rounded-xl flex items-center justify-center transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4">
                                </path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-800 dark:text-white"
                                :class="activeTab === 'backup' ? 'text-green-700 dark:text-green-400' : ''">Backup & Data
                            </h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Export & Reset</p>
                        </div>
                    </div>
                </button>
            </div>

            <!-- Content Area -->
            <div
                class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm p-6 relative overflow-hidden">

                <!-- General Settings -->
                <div x-show="activeTab === 'general'" class="space-y-6"
                    x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Nama
                                Toko</label>
                            <input type="text" value="SAGA RETAIL"
                                class="w-full px-4 py-2.5 border border-gray-200 rounded-xl dark:bg-gray-900 dark:border-gray-600 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">No.
                                Telepon</label>
                            <input type="text" value="021-555-0123"
                                class="w-full px-4 py-2.5 border border-gray-200 rounded-xl dark:bg-gray-900 dark:border-gray-600 dark:text-white">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Alamat</label>
                            <textarea rows="3"
                                class="w-full px-4 py-2.5 border border-gray-200 rounded-xl dark:bg-gray-900 dark:border-gray-600 dark:text-white">Jl. Jendral Sudirman No. 1, Jakarta Pusat</textarea>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Logo
                                Struk</label>
                            <div class="flex items-center gap-4">
                                <div
                                    class="w-16 h-16 bg-gray-100 rounded-lg flex items-center justify-center border border-dashed border-gray-300 dark:bg-gray-700 dark:border-gray-600">
                                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                                        </path>
                                    </svg>
                                </div>
                                <button
                                    class="px-4 py-2 bg-white border border-gray-200 text-gray-600 rounded-lg text-sm font-medium hover:bg-gray-50 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300">Upload
                                    Logo</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Finance Settings -->
                <div x-show="activeTab === 'finance'" class="space-y-6" x-cloak
                    x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Mata
                                Uang</label>
                            <select
                                class="w-full px-4 py-2.5 border border-gray-200 rounded-xl dark:bg-gray-900 dark:border-gray-600 dark:text-white">
                                <option value="IDR">Rupiah (Rp)</option>
                                <option value="USD">Dollar ($)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">PPN (%)</label>
                            <input type="number" value="11"
                                class="w-full px-4 py-2.5 border border-gray-200 rounded-xl dark:bg-gray-900 dark:border-gray-600 dark:text-white">
                        </div>
                        <div class="flex items-center gap-3">
                            <input type="checkbox" id="tax_included"
                                class="w-5 h-5 text-brand-600 rounded border-gray-300 focus:ring-brand-500">
                            <label for="tax_included" class="text-sm text-gray-700 dark:text-gray-300">Harga sudah termasuk
                                pajak</label>
                        </div>
                    </div>
                </div>

                <!-- Hardware Settings -->
                <div x-show="activeTab === 'hardware'" class="space-y-8" x-cloak
                    x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100">

                    <!-- Printers Section -->
                    <div>
                        <h2
                            class="text-lg font-bold text-gray-800 dark:text-white mb-4 border-b border-gray-100 dark:border-gray-700 pb-3 flex items-center gap-2">
                            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H3a2 2 0 00-2 2v4h18zM19 8h6v2h-6V8z">
                                </path>
                            </svg>
                            Printer Kasir
                        </h2>

                        <div
                            class="bg-blue-50 text-blue-700 p-4 rounded-xl text-sm dark:bg-blue-900/30 dark:text-blue-400 mb-6 flex items-start gap-3">
                            <svg class="w-5 h-5 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <div>
                                <p class="font-bold">Info Koneksi</p>
                                <p>Pastikan printer terhubung via USB atau Bluetooth. Gunakan browser Google Chrome untuk
                                    dukungan WebUSB/WebBluetooth terbaik.</p>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <!-- Printer List -->
                            <div class="border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden">
                                <template x-for="printer in printers" :key="printer.id">
                                    <div
                                        class="p-4 flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors border-b last:border-0 border-gray-100 dark:border-gray-700">
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center text-gray-500 dark:bg-gray-700 dark:text-gray-300">
                                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H3a2 2 0 00-2 2v4h18zM19 8h6v2h-6V8z">
                                                    </path>
                                                </svg>
                                            </div>
                                            <div>
                                                <h4 class="font-bold text-gray-800 dark:text-white" x-text="printer.name">
                                                </h4>
                                                <p class="text-xs text-gray-500"
                                                    x-text="printer.type + ' • ' + printer.connection"></p>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <span x-show="printer.connected"
                                                class="text-xs font-bold text-green-600 bg-green-100 px-2 py-1 rounded-full dark:bg-green-900/30 dark:text-green-400">Terhubung</span>
                                            <button @click="testPrint(printer)"
                                                class="px-3 py-1.5 text-sm border border-gray-200 rounded-lg hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">Test
                                                Print</button>
                                            <button @click="connectPrinter(printer)" x-show="!printer.connected"
                                                class="px-3 py-1.5 text-sm bg-brand-600 text-white rounded-lg hover:bg-brand-700">Connect</button>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            <!-- Add Printer Button -->
                            <button
                                class="w-full py-3 border-2 border-dashed border-gray-200 rounded-xl text-gray-500 font-medium hover:border-brand-500 hover:text-brand-600 hover:bg-brand-50 dark:border-gray-700 dark:hover:border-brand-500 dark:hover:bg-brand-900/20 transition-all flex items-center justify-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4v16m8-8H4"></path>
                                </svg>
                                Tambah Printer Baru
                            </button>
                        </div>
                    </div>

                    <!-- Scanner Section -->
                    <div>
                        <h2
                            class="text-lg font-bold text-gray-800 dark:text-white mb-4 border-b border-gray-100 dark:border-gray-700 pb-3 flex items-center gap-2">
                            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z">
                                </path>
                            </svg>
                            Barcode Scanner
                        </h2>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Mode
                                    Scanner</label>
                                <select
                                    class="w-full px-4 py-2.5 border border-gray-200 rounded-xl dark:bg-gray-900 dark:border-gray-600 dark:text-white">
                                    <option value="hid">HID Mode (Keyboard Emulation)</option>
                                    <option value="serial">Serial Mode (WebSerial API)</option>
                                </select>
                                <p class="text-xs text-gray-500 mt-1">Gunakan mode HID untuk scanner USB plug-and-play
                                    standar.</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Auto
                                    Enter</label>
                                <div class="flex items-center gap-3 mt-2">
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" checked class="sr-only peer">
                                        <div
                                            class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-brand-300 dark:peer-focus:ring-brand-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-brand-600">
                                        </div>
                                        <span class="ml-3 text-sm font-medium text-gray-700 dark:text-gray-300">Aktif</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Test Area
                                Scanner</label>
                            <div class="relative">
                                <input type="text" placeholder="Scan barcode di sini untuk mengetes..."
                                    class="w-full pl-10 px-4 py-3 border-2 border-dashed border-gray-300 rounded-xl dark:bg-gray-900 dark:border-gray-600 dark:text-white focus:border-brand-500 focus:ring-0">
                                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z">
                                    </path>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Cashier Settings -->
                <div x-show="activeTab === 'cashier'" class="space-y-8" x-cloak
                    x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- POS Behavior -->
                        <div class="space-y-4">
                            <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-2">POS Behavior</h3>

                            <!-- Toggles -->
                            <div class="space-y-3">
                                <div
                                    class="flex items-center justify-between p-3 border border-gray-100 rounded-xl dark:border-gray-700">
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Auto Print
                                        Receipt</span>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" x-model="cashierSettings.auto_print" class="sr-only peer">
                                        <div
                                            class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-brand-300 dark:peer-focus:ring-brand-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-brand-600">
                                        </div>
                                    </label>
                                </div>
                                <div
                                    class="flex items-center justify-between p-3 border border-gray-100 rounded-xl dark:border-gray-700">
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Show Stock
                                        Quantity</span>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" x-model="cashierSettings.show_stock" class="sr-only peer">
                                        <div
                                            class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-brand-300 dark:peer-focus:ring-brand-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-brand-600">
                                        </div>
                                    </label>
                                </div>
                                <div
                                    class="flex items-center justify-between p-3 border border-gray-100 rounded-xl dark:border-gray-700">
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Allow Discount</span>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" x-model="cashierSettings.allow_discount"
                                            class="sr-only peer">
                                        <div
                                            class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-brand-300 dark:peer-focus:ring-brand-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-brand-600">
                                        </div>
                                    </label>
                                </div>
                                <div
                                    class="flex items-center justify-between p-3 border border-gray-100 rounded-xl dark:border-gray-700">
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Sound Effects</span>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" x-model="cashierSettings.sound_enabled" class="sr-only peer">
                                        <div
                                            class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-brand-300 dark:peer-focus:ring-brand-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-brand-600">
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Payment & Quick Amounts -->
                        <div class="space-y-4">
                            <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-2">Payment Settings</h3>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Default
                                    Payment Method</label>
                                <select x-model="cashierSettings.default_payment"
                                    class="w-full px-4 py-2.5 border border-gray-200 rounded-xl dark:bg-gray-900 dark:border-gray-600 dark:text-white">
                                    <option value="cash">Cash (Tunai)</option>
                                    <option value="qris">QRIS</option>
                                    <option value="transfer">Transfer Bank</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Quick
                                    Amounts (Pecahan Uang)</label>
                                <div class="grid grid-cols-2 gap-2">
                                    <template x-for="(amt, idx) in cashierSettings.quick_amounts" :key="idx">
                                        <div
                                            class="flex items-center gap-1 bg-gray-50 rounded-lg pl-3 border border-gray-200 dark:bg-gray-800 dark:border-gray-700">
                                            <span class="text-xs text-gray-500">Rp</span>
                                            <input type="number" x-model.number="cashierSettings.quick_amounts[idx]"
                                                class="w-full py-2 bg-transparent border-none text-sm focus:ring-0 dark:text-white">
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Backup & Export -->
                <div x-show="activeTab === 'backup'" class="space-y-8" x-cloak
                    x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Export Tools -->
                        <div class="space-y-4">
                            <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-2">Export Data</h3>
                            <button
                                class="w-full px-4 py-3 border border-gray-200 rounded-xl hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800 flex items-center gap-3 transition-colors">
                                <div
                                    class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center text-green-600 dark:bg-green-900/30 dark:text-green-400">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                    </svg>
                                </div>
                                <div class="text-left">
                                    <p class="font-medium text-gray-800 dark:text-white">Export Products</p>
                                    <p class="text-xs text-gray-500">Download .xlsx (Excel)</p>
                                </div>
                            </button>
                            <button
                                class="w-full px-4 py-3 border border-gray-200 rounded-xl hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800 flex items-center gap-3 transition-colors">
                                <div
                                    class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center text-blue-600 dark:bg-blue-900/30 dark:text-blue-400">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01">
                                        </path>
                                    </svg>
                                </div>
                                <div class="text-left">
                                    <p class="font-medium text-gray-800 dark:text-white">Export Transactions</p>
                                    <p class="text-xs text-gray-500">Download .xlsx (Excel)</p>
                                </div>
                            </button>
                        </div>

                        <!-- Database Backup -->
                        <div class="space-y-4">
                            <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-2">Database Backup</h3>
                            <div
                                class="p-4 bg-gray-50 rounded-xl dark:bg-gray-800 border border-gray-100 dark:border-gray-700">
                                <p class="text-sm text-gray-600 dark:text-gray-300 mb-4">Last backup: <span
                                        class="font-bold">Never</span></p>
                                <button
                                    class="w-full py-2.5 bg-brand-600 text-white rounded-lg hover:bg-brand-700 font-medium flex items-center justify-center gap-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                    </svg>
                                    Download SQL Backup
                                </button>
                            </div>

                            <div class="pt-4 border-t border-gray-100 dark:border-gray-700">
                                <h3 class="text-lg font-bold text-red-600 mb-2">Danger Zone</h3>
                                <button
                                    @click="Swal.fire('Fitur ini dibatasi', 'Hanya owner yang bisa melakukan reset data.', 'warning')"
                                    class="w-full py-2.5 border border-red-200 bg-red-50 text-red-600 rounded-lg hover:bg-red-100 font-medium flex items-center justify-center gap-2 dark:bg-red-900/10 dark:border-red-900 dark:text-red-400">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                        </path>
                                    </svg>
                                    Factory Reset Data
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Save Button -->
                <div class="mt-8 pt-6 border-t border-gray-100 dark:border-gray-700 flex justify-end">
                    <button @click="saveSettings()" :disabled="isLoading"
                        class="px-6 py-2.5 bg-brand-600 text-white rounded-xl hover:bg-brand-700 shadow-lg shadow-brand-500/30 flex items-center gap-2 font-medium transition-all transform active:scale-95 disabled:opacity-50">
                        <span x-show="isLoading"
                            class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                        <span x-text="isLoading ? 'Menyimpan...' : 'Simpan Perubahan'"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection