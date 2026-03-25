@extends('layouts.app')
@section('title', 'Settings | SAGA TOKO APP')
@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-gray-900 p-6" x-data="settingsPage()">
    <div class="max-w-5xl mx-auto">
        <div class="flex items-center gap-3 mb-8">
            <div class="w-12 h-12 bg-gradient-to-br from-amber-500 to-orange-600 rounded-2xl flex items-center justify-center shadow-lg">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
            </div>
            <div><h1 class="text-3xl font-bold text-gray-800 dark:text-white">Settings</h1><p class="text-sm text-gray-500 dark:text-gray-400">Konfigurasi sistem aplikasi</p></div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <!-- Tabs -->
            <div class="flex border-b border-gray-200 dark:border-gray-700 overflow-x-auto">
                <button @click="activeTab='general'" :class="activeTab==='general'?'border-brand-500 text-brand-600':'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400'" class="flex-1 px-6 py-4 text-sm font-semibold border-b-2 transition-all whitespace-nowrap">General Settings</button>
                <button @click="activeTab='store'" :class="activeTab==='store'?'border-brand-500 text-brand-600':'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400'" class="flex-1 px-6 py-4 text-sm font-semibold border-b-2 transition-all whitespace-nowrap">Store Settings</button>
                <button @click="activeTab='pos'" :class="activeTab==='pos'?'border-brand-500 text-brand-600':'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400'" class="flex-1 px-6 py-4 text-sm font-semibold border-b-2 transition-all whitespace-nowrap">POS Settings</button>
                <button @click="activeTab='invoice'" :class="activeTab==='invoice'?'border-brand-500 text-brand-600':'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400'" class="flex-1 px-6 py-4 text-sm font-semibold border-b-2 transition-all whitespace-nowrap">Invoice & Receipt</button>
                <button @click="activeTab='system'" :class="activeTab==='system'?'border-brand-500 text-brand-600':'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400'" class="flex-1 px-6 py-4 text-sm font-semibold border-b-2 transition-all whitespace-nowrap">System</button>
            </div>

            <!-- Tab Content -->
            <div class="p-6">
                <!-- General Settings -->
                <div x-show="activeTab==='general'" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div><label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Store Name</label><input type="text" x-model="settings.store_name" class="w-full px-4 py-3 border-2 border-gray-200 dark:border-gray-700 rounded-xl bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500"></div>
                        <div><label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Business Type</label><select x-model="settings.business_type" class="w-full px-4 py-3 border-2 border-gray-200 dark:border-gray-700 rounded-xl bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500"><option value="retail">Retail</option><option value="barber">Barber</option><option value="cafe">Cafe</option></select></div>
                        <div><label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Email</label><input type="email" x-model="settings.email" class="w-full px-4 py-3 border-2 border-gray-200 dark:border-gray-700 rounded-xl bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500"></div>
                        <div><label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Phone</label><input type="tel" x-model="settings.phone" class="w-full px-4 py-3 border-2 border-gray-200 dark:border-gray-700 rounded-xl bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500"></div>
                        <div class="md:col-span-2"><label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Address</label><textarea x-model="settings.address" rows="3" class="w-full px-4 py-3 border-2 border-gray-200 dark:border-gray-700 rounded-xl bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500"></textarea></div>
                        <div class="md:col-span-2"><label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Logo</label><div class="flex items-center gap-4"><div class="w-20 h-20 bg-gray-100 dark:bg-gray-700 rounded-xl flex items-center justify-center"><svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg></div><button class="px-4 py-2 border-2 border-gray-200 dark:border-gray-700 rounded-xl text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-900 transition-colors">Upload Logo</button></div></div>
                    </div>
                </div>

                <!-- Store Settings -->
                <div x-show="activeTab==='store'" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div><label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Currency</label><select x-model="settings.currency" class="w-full px-4 py-3 border-2 border-gray-200 dark:border-gray-700 rounded-xl bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500"><option value="IDR">IDR - Indonesian Rupiah</option><option value="USD">USD - US Dollar</option></select></div>
                        <div><label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Tax Rate (%)</label><input type="number" x-model="settings.tax_rate" class="w-full px-4 py-3 border-2 border-gray-200 dark:border-gray-700 rounded-xl bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500"></div>
                        <div><label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Opening Hours</label><input type="time" x-model="settings.opening_hours" class="w-full px-4 py-3 border-2 border-gray-200 dark:border-gray-700 rounded-xl bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500"></div>
                        <div><label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Closing Hours</label><input type="time" x-model="settings.closing_hours" class="w-full px-4 py-3 border-2 border-gray-200 dark:border-gray-700 rounded-xl bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500"></div>
                    </div>
                </div>

                <!-- POS Settings -->
                <div x-show="activeTab==='pos'" class="space-y-6">
                    <div class="space-y-4">
                        <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-900 rounded-xl"><div><p class="font-semibold text-gray-800 dark:text-white">Default Customer</p><p class="text-sm text-gray-500 dark:text-gray-400">Use walk-in customer for general sales</p></div><label class="relative inline-flex items-center cursor-pointer"><input type="checkbox" x-model="settings.pos_default_customer" class="sr-only peer"><div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-brand-300 dark:peer-focus:ring-brand-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-brand-600"></div></label></div>
                        <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-900 rounded-xl"><div><p class="font-semibold text-gray-800 dark:text-white">Auto Print Receipt</p><p class="text-sm text-gray-500 dark:text-gray-400">Automatically print receipt after transaction</p></div><label class="relative inline-flex items-center cursor-pointer"><input type="checkbox" x-model="settings.pos_auto_print" class="sr-only peer"><div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-brand-300 dark:peer-focus:ring-brand-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-brand-600"></div></label></div>
                    </div>
                </div>

                <!-- Invoice & Receipt -->
                <div x-show="activeTab==='invoice'" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div><label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Invoice Prefix</label><input type="text" x-model="settings.invoice_prefix" class="w-full px-4 py-3 border-2 border-gray-200 dark:border-gray-700 rounded-xl bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500"></div>
                        <div><label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Receipt Footer</label><input type="text" x-model="settings.receipt_footer" class="w-full px-4 py-3 border-2 border-gray-200 dark:border-gray-700 rounded-xl bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500"></div>
                    </div>
                </div>

                <!-- System -->
                <div x-show="activeTab==='system'" class="space-y-6">
                    <div class="space-y-4">
                        <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-900 rounded-xl"><div><p class="font-semibold text-gray-800 dark:text-white">Maintenance Mode</p><p class="text-sm text-gray-500 dark:text-gray-400">Disable access for non-admin users</p></div><label class="relative inline-flex items-center cursor-pointer"><input type="checkbox" x-model="settings.maintenance_mode" class="sr-only peer"><div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-brand-300 dark:peer-focus:ring-brand-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-brand-600"></div></label></div>
                        <div class="p-4 bg-red-50 dark:bg-red-900/20 rounded-xl border border-red-200 dark:border-red-800"><h4 class="font-bold text-red-800 dark:text-red-400 mb-2">Danger Zone</h4><p class="text-sm text-red-600 dark:text-red-400 mb-4">Clear all cache and reset application settings</p><button class="px-6 py-3 bg-red-600 text-white rounded-xl font-semibold hover:bg-red-700 transition-all">Clear Cache</button></div>
                    </div>
                </div>
            </div>

            <!-- Save Button -->
            <div class="p-6 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 flex items-center justify-end gap-3">
                <button class="px-6 py-3 border-2 border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 rounded-xl font-semibold hover:bg-gray-100 dark:hover:bg-gray-900 transition-colors">Cancel</button>
                <button @click="saveSettings()" class="px-6 py-3 bg-gradient-to-r from-brand-600 to-indigo-600 text-white rounded-xl font-semibold hover:from-brand-700 hover:to-indigo-700 transition-all shadow-lg shadow-brand-500/30">Save Settings</button>
            </div>
        </div>
    </div>
</div>
@push('scripts')
<script>
function settingsPage(){return{activeTab:'general',settings:{store_name:'SAGA TOKO',business_type:'retail',email:'info@saga.com',phone:'021-1234567',address:'Jl. Sudirman No. 123',currency:'IDR',tax_rate:10,opening_hours:'08:00',closing_hours:'22:00',pos_default_customer:true,pos_auto_print:false,invoice_prefix:'INV',receipt_footer:'Thank you for shopping!',maintenance_mode:false},saveSettings(){Swal.fire({icon:'success',title:'Settings Saved',text:'Your settings have been saved successfully',timer:2000,showConfirmButton:false})}}}
</script>
@endpush
@endsection
