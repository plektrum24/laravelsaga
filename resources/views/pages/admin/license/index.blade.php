@extends('layouts.app')

@section('title', 'License Generator')

@push('head')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush

@section('content')
    <div x-data="licenseGenerator()" x-init="init()">
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">License Generator</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Generate offline activation licenses for clients</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Generator Form -->
            <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
                <h2 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">Generate New License</h2>

                <form @submit.prevent="generateLicense()" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Machine ID</label>
                        <input type="text" x-model="form.machine_id" required placeholder="Enter client's Machine ID"
                            class="w-full px-4 py-3 border border-gray-200 rounded-xl dark:bg-gray-800 dark:border-gray-700 dark:text-white focus:ring-2 focus:ring-brand-500">
                        <p class="text-xs text-gray-400 mt-1">Machine ID can be obtained from client's Settings → License
                            menu</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Duration
                            (Days)</label>
                        <div class="grid grid-cols-4 gap-2 mb-3">
                            <button type="button" @click="form.duration_days = 30"
                                :class="form.duration_days === 30 ? 'bg-brand-500 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-white'"
                                class="py-2 rounded-lg font-medium transition">30 Days</button>
                            <button type="button" @click="form.duration_days = 90"
                                :class="form.duration_days === 90 ? 'bg-brand-500 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-white'"
                                class="py-2 rounded-lg font-medium transition">3 Months</button>
                            <button type="button" @click="form.duration_days = 180"
                                :class="form.duration_days === 180 ? 'bg-brand-500 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-white'"
                                class="py-2 rounded-lg font-medium transition">6 Months</button>
                            <button type="button" @click="form.duration_days = 365"
                                :class="form.duration_days === 365 ? 'bg-brand-500 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-white'"
                                class="py-2 rounded-lg font-medium transition">1 Year</button>
                        </div>
                        <input type="number" x-model.number="form.duration_days" min="1" required
                            class="w-full px-4 py-3 border border-gray-200 rounded-xl dark:bg-gray-800 dark:border-gray-700 dark:text-white">
                    </div>

                    <button type="submit" :disabled="isGenerating"
                        class="w-full py-3 bg-brand-500 text-white rounded-xl hover:bg-brand-600 font-medium disabled:opacity-50 flex items-center justify-center gap-2">
                        <svg x-show="isGenerating" class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                            </circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                        <span x-text="isGenerating ? 'Generating...' : 'Generate License'"></span>
                    </button>
                </form>
            </div>

            <!-- Generated License -->
            <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
                <h2 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">Generated License</h2>

                <div x-show="!generatedLicense" class="flex flex-col items-center justify-center py-12 text-gray-400">
                    <svg class="w-16 h-16 mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z">
                        </path>
                    </svg>
                    <p>Generate a license to see it here</p>
                </div>

                <div x-show="generatedLicense" class="space-y-4">
                    <div
                        class="p-4 bg-green-50 border border-green-200 rounded-xl dark:bg-green-900/20 dark:border-green-800">
                        <p class="text-sm text-green-600 dark:text-green-400 font-medium mb-2">License Key</p>
                        <div class="flex items-center gap-2">
                            <code
                                class="flex-1 px-4 py-3 bg-white rounded-lg text-lg font-mono font-bold text-gray-800 dark:bg-gray-800 dark:text-white select-all"
                                x-text="generatedLicense?.license_key"></code>
                            <button @click="copyToClipboard(generatedLicense?.license_key)"
                                class="p-3 bg-green-500 text-white rounded-lg hover:bg-green-600" title="Copy">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z">
                                    </path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="p-4 bg-gray-50 rounded-xl dark:bg-gray-800">
                            <p class="text-xs text-gray-500 mb-1">Machine ID</p>
                            <p class="font-medium text-gray-800 dark:text-white truncate"
                                x-text="generatedLicense?.machine_id"></p>
                        </div>
                        <div class="p-4 bg-gray-50 rounded-xl dark:bg-gray-800">
                            <p class="text-xs text-gray-500 mb-1">Expiry Date</p>
                            <p class="font-medium text-gray-800 dark:text-white" x-text="generatedLicense?.expiry_date"></p>
                        </div>
                        <div class="p-4 bg-gray-50 rounded-xl dark:bg-gray-800">
                            <p class="text-xs text-gray-500 mb-1">Duration</p>
                            <p class="font-medium text-gray-800 dark:text-white"
                                x-text="generatedLicense?.duration_days + ' days'"></p>
                        </div>
                        <div class="p-4 bg-gray-50 rounded-xl dark:bg-gray-800">
                            <p class="text-xs text-gray-500 mb-1">Generated</p>
                            <p class="font-medium text-gray-800 dark:text-white"
                                x-text="new Date().toLocaleDateString('id-ID')"></p>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
                        <p class="text-sm text-gray-500 mb-3">Send this license key to the client. They can activate it in
                            Settings → License.</p>
                        <button @click="copyAllDetails()"
                            class="w-full py-2.5 border border-gray-200 rounded-xl hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800 font-medium text-gray-700 dark:text-gray-300">
                            Copy All Details
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- History -->
        <div class="mt-6 rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">Recent Licenses</h2>
            <div x-show="history.length === 0" class="text-center py-8 text-gray-400">
                No licenses generated yet
            </div>
            <div x-show="history.length > 0" class="space-y-3">
                <template x-for="(item, index) in history" :key="index">
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl dark:bg-gray-800">
                        <div>
                            <code class="font-mono font-semibold text-gray-800 dark:text-white"
                                x-text="item.license_key"></code>
                            <p class="text-sm text-gray-500 mt-1">Machine: <span x-text="item.machine_id"></span> • Expires:
                                <span x-text="item.expiry_date"></span></p>
                        </div>
                        <button @click="copyToClipboard(item.license_key)" class="p-2 text-gray-400 hover:text-brand-500">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z">
                                </path>
                            </svg>
                        </button>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <script>
        function licenseGenerator() {
            return {
                form: { machine_id: '', duration_days: 30 },
                isGenerating: false,
                generatedLicense: null,
                history: [],

                init() {
                    // Load history from localStorage
                    const saved = localStorage.getItem('saga_license_history');
                    if (saved) this.history = JSON.parse(saved).slice(0, 10);
                },

                async generateLicense() {
                    if (!this.form.machine_id) {
                        Swal.fire({ icon: 'warning', title: 'Machine ID Required', text: 'Please enter the client Machine ID' });
                        return;
                    }

                    this.isGenerating = true;
                    try {
                        const token = localStorage.getItem('saga_token');
                        const response = await fetch('/api/admin/license/generate', {
                            method: 'POST',
                            headers: { 'Authorization': 'Bearer ' + token, 'Content-Type': 'application/json', 'Accept': 'application/json' },
                            body: JSON.stringify(this.form)
                        });
                        const data = await response.json();
                        if (data.success) {
                            this.generatedLicense = data.data;
                            // Add to history
                            this.history.unshift(data.data);
                            this.history = this.history.slice(0, 10);
                            localStorage.setItem('saga_license_history', JSON.stringify(this.history));

                            Swal.fire({ icon: 'success', title: 'License Generated!', timer: 1500, showConfirmButton: false });
                        } else {
                            Swal.fire({ icon: 'error', title: 'Failed', text: data.message });
                        }
                    } catch (error) {
                        console.error('Generate error:', error);
                        Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to generate license' });
                    } finally {
                        this.isGenerating = false;
                    }
                },

                copyToClipboard(text) {
                    navigator.clipboard.writeText(text);
                    Swal.fire({ icon: 'success', title: 'Copied!', timer: 1000, showConfirmButton: false, toast: true, position: 'top-end' });
                },

                copyAllDetails() {
                    if (!this.generatedLicense) return;
                    const text = `License Key: ${this.generatedLicense.license_key}\nMachine ID: ${this.generatedLicense.machine_id}\nExpiry: ${this.generatedLicense.expiry_date}\nDuration: ${this.generatedLicense.duration_days} days`;
                    navigator.clipboard.writeText(text);
                    Swal.fire({ icon: 'success', title: 'All Details Copied!', timer: 1000, showConfirmButton: false });
                }
            }
        }
    </script>
@endsection