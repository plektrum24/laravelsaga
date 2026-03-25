@extends('layouts.app')

@section('title', 'Label Designer')

@section('content')
<div x-data="labelDesigner()" x-init="init()" class="space-y-6">
    <!-- Header -->
    <div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-2xl p-6 shadow-lg">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-white flex items-center gap-3">
                    <span class="p-2 bg-white/20 rounded-lg">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                        </svg>
                    </span>
                    Label Designer
                </h1>
                <p class="text-blue-100 text-sm mt-2">Create and customize product labels with barcode support</p>
            </div>
            <button @click="openDesigner()" class="px-6 py-3 bg-white text-blue-600 rounded-xl hover:bg-blue-50 shadow-lg font-semibold transition-all transform hover:-translate-y-0.5 flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                New Template
            </button>
        </div>
    </div>

    <!-- Tabs -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="border-b border-gray-200 dark:border-gray-700">
            <nav class="flex">
                <button @click="activeTab = 'templates'"
                    :class="activeTab === 'templates' ? 'border-blue-500 text-blue-600 bg-blue-50 dark:bg-blue-900/20' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400'"
                    class="py-4 px-6 border-b-2 font-medium text-sm transition-all flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                    </svg>
                    Templates
                </button>
                <button @click="activeTab = 'designer'"
                    :class="activeTab === 'designer' ? 'border-blue-500 text-blue-600 bg-blue-50 dark:bg-blue-900/20' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400'"
                    class="py-4 px-6 border-b-2 font-medium text-sm transition-all flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    Designer
                </button>
                <button @click="activeTab = 'history'"
                    :class="activeTab === 'history' ? 'border-blue-500 text-blue-600 bg-blue-50 dark:bg-blue-900/20' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400'"
                    class="py-4 px-6 border-b-2 font-medium text-sm transition-all flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Print History
                </button>
            </nav>
        </div>

        <!-- Templates Tab -->
        <div x-show="activeTab === 'templates'" class="p-6" x-transition.opacity>
            <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
                <div class="flex flex-wrap gap-2">
                    <select x-model="filters.type" @change="loadTemplates()" class="px-4 py-2 rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-900 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                        <option value="">All Types</option>
                        <option value="price_tag">Price Tag</option>
                        <option value="shelf_label">Shelf Label</option>
                        <option value="barcode_label">Barcode Label</option>
                        <option value="custom">Custom</option>
                    </select>
                    <input type="text" x-model="filters.search" @input="loadTemplates()" placeholder="Search templates..." class="px-4 py-2 rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-900 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <template x-for="template in templates" :key="template.id">
                    <div class="border border-gray-200 dark:border-gray-700 rounded-xl p-5 hover:shadow-lg hover:border-blue-300 dark:hover:border-blue-700 transition-all group">
                        <div class="flex justify-between items-start mb-3">
                            <div class="flex items-center gap-2">
                                <span class="p-2 bg-blue-100 dark:bg-blue-900/30 rounded-lg text-blue-600 dark:text-blue-400">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                    </svg>
                                </span>
                                <h3 class="font-bold text-gray-800 dark:text-white" x-text="template.name"></h3>
                            </div>
                            <span class="px-3 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400" x-text="template.template_type.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())"></span>
                        </div>
                        
                        <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-3 mb-3">
                            <div class="flex items-center justify-center text-gray-400" style="height: 80px;">
                                <div class="text-center">
                                    <svg class="w-12 h-12 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                    </svg>
                                    <span class="text-xs" x-text="template.width_mm + ' × ' + template.height_mm + ' mm'"></span>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-between text-xs text-gray-500 mb-4">
                            <span x-show="template.is_default" class="flex items-center gap-1 text-green-600 dark:text-green-400">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                Default
                            </span>
                        </div>

                        <div class="flex gap-2">
                            <button @click="editTemplate(template.id)" class="flex-1 px-3 py-2 bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900/30 text-sm font-medium transition-colors flex items-center justify-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                                Edit
                            </button>
                            <button @click="printLabels(template.id)" class="flex-1 px-3 py-2 bg-green-50 dark:bg-green-900/20 text-green-600 dark:text-green-400 rounded-lg hover:bg-green-100 dark:hover:bg-green-900/30 text-sm font-medium transition-colors flex items-center justify-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                                </svg>
                                Print
                            </button>
                            <button @click="deleteTemplate(template.id)" class="px-3 py-2 bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 rounded-lg hover:bg-red-100 dark:hover:bg-red-900/30 text-sm font-medium transition-colors" x-show="!template.is_default">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </template>
            </div>

            <div x-show="templates.length === 0" class="text-center py-12">
                <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                </svg>
                <p class="text-gray-500">No templates found. Create your first template!</p>
            </div>
        </div>

        <!-- Designer Tab -->
        <div x-show="activeTab === 'designer'" class="p-6" x-transition.opacity>
            <div class="bg-gradient-to-r from-purple-50 to-blue-50 dark:from-purple-900/20 dark:to-blue-900/20 rounded-xl p-4 mb-6">
                <div class="flex items-center gap-3">
                    <span class="p-2 bg-purple-100 dark:bg-purple-900/30 rounded-lg text-purple-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </span>
                    <div>
                        <h3 class="font-semibold text-gray-800 dark:text-white">Design Your Label</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Configure template settings and preview in real-time</p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Settings Panel -->
                <div class="bg-white dark:bg-gray-900 rounded-xl p-5 border border-gray-200 dark:border-gray-700 shadow-sm">
                    <h4 class="font-bold text-gray-800 dark:text-white mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        Settings
                    </h4>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Template Name</label>
                            <input type="text" x-model="designerData.name" placeholder="e.g., Product Price Tag" class="w-full px-4 py-2.5 rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-800 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Type</label>
                            <select x-model="designerData.template_type" class="w-full px-4 py-2.5 rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-800 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                                <option value="price_tag">🏷️ Price Tag</option>
                                <option value="shelf_label">📦 Shelf Label</option>
                                <option value="barcode_label">📊 Barcode Label</option>
                                <option value="custom">⚙️ Custom</option>
                            </select>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Width (mm)</label>
                                <input type="number" x-model="designerData.width_mm" class="w-full px-4 py-2.5 rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-800 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Height (mm)</label>
                                <input type="number" x-model="designerData.height_mm" class="w-full px-4 py-2.5 rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-800 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                            </div>
                        </div>
                        
                        <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
                            <label class="flex items-center gap-2 mb-3">
                                <input type="checkbox" x-model="designerData.include_barcode" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <span class="text-sm text-gray-700 dark:text-gray-300">Include Barcode</span>
                            </label>
                            <label class="flex items-center gap-2 mb-3">
                                <input type="checkbox" x-model="designerData.include_qr" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <span class="text-sm text-gray-700 dark:text-gray-300">Include QR Code</span>
                            </label>
                            <label class="flex items-center gap-2">
                                <input type="checkbox" x-model="designerData.include_logo" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <span class="text-sm text-gray-700 dark:text-gray-300">Include Logo</span>
                            </label>
                        </div>

                        <div class="flex gap-2 pt-4">
                            <button @click="saveTemplate()" class="flex-1 px-4 py-2.5 bg-blue-600 text-white rounded-xl hover:bg-blue-700 shadow-lg shadow-blue-500/30 font-medium transition-all transform hover:-translate-y-0.5">
                                Save Template
                            </button>
                            <button @click="resetDesigner()" class="px-4 py-2.5 bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 rounded-xl hover:bg-gray-200 dark:hover:bg-gray-700 font-medium transition-colors">
                                Reset
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Preview Panel -->
                <div class="lg:col-span-2">
                    <div class="bg-white dark:bg-gray-900 rounded-xl p-5 border border-gray-200 dark:border-gray-700 shadow-sm h-full">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="font-bold text-gray-800 dark:text-white flex items-center gap-2">
                                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                                Live Preview
                            </h4>
                            <span class="text-xs text-gray-500" x-text="designerData.width_mm + ' × ' + designerData.height_mm + ' mm'"></span>
                        </div>
                        
                        <div class="flex items-center justify-center p-8 bg-gray-50 dark:bg-gray-800 rounded-xl" style="min-height: 400px;">
                            <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 shadow-lg"
                                 :style="'width: ' + Math.max(designerData.width_mm * 6, 200) + 'px; height: ' + Math.max(designerData.height_mm * 6, 150) + 'px; position: relative;'">
                                <!-- Preview header -->
                                <div class="absolute top-2 left-2 right-2 text-center">
                                    <span class="text-xs font-bold text-gray-700 dark:text-gray-300" x-text="designerData.name || 'Template Name'"></span>
                                </div>
                                
                                <!-- Preview fields -->
                                <template x-for="(field, index) in designerData.layout_json.fields" :key="index">
                                    <div class="absolute border border-dashed border-blue-400 bg-blue-50 dark:bg-blue-900/20 p-1 text-xs rounded"
                                         :style="'left: ' + field.x * 4 + 'px; top: ' + field.y * 4 + 'px; width: ' + field.width * 4 + 'px; height: ' + field.height * 4 + 'px;'">
                                        <span x-text="field.type"></span>
                                    </div>
                                </template>
                                
                                <!-- Default preview content -->
                                <div class="absolute inset-0 flex flex-col items-center justify-center p-4" x-show="designerData.layout_json.fields.length === 0">
                                    <div class="text-center space-y-2">
                                        <div class="text-lg font-bold text-gray-800 dark:text-white">Product Name</div>
                                        <div class="text-2xl font-bold text-blue-600">Rp 10.000</div>
                                        <div class="flex gap-1 justify-center">
                                            <div class="w-16 h-8 bg-gray-800"></div>
                                        </div>
                                        <div class="text-xs text-gray-500">SKU: PROD-001</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-xl border border-blue-100 dark:border-blue-800">
                            <div class="flex items-start gap-3">
                                <svg class="w-5 h-5 text-blue-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <div class="text-sm text-blue-800 dark:text-blue-200">
                                    <p class="font-semibold mb-1">Preview Mode</p>
                                    <p class="text-blue-600 dark:text-blue-300">This is a live preview. Adjust settings on the left to customize your label design.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Print History Tab -->
        <div x-show="activeTab === 'history'" class="p-6" x-transition.opacity>
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="text-xl font-bold text-gray-800 dark:text-white">Print History</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Track all label printing jobs</p>
                </div>
                <button @click="loadPrintHistory()" class="px-4 py-2 bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 rounded-xl hover:bg-blue-100 dark:hover:bg-blue-900/30 text-sm font-medium transition-colors flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    Refresh
                </button>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Date & Time</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Template</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Products</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Quantity</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            <template x-for="job in printHistory" :key="job.id">
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                    <td class="px-6 py-4 text-gray-600 dark:text-gray-300" x-text="formatDate(job.created_at)"></td>
                                    <td class="px-6 py-4">
                                        <span class="font-medium text-gray-800 dark:text-white" x-text="job.template?.name || '-'"></span>
                                    </td>
                                    <td class="px-6 py-4 text-gray-600 dark:text-gray-300" x-text="(job.product_ids?.length || 0) + ' items'"></td>
                                    <td class="px-6 py-4">
                                        <span class="font-semibold text-gray-800 dark:text-white" x-text="job.quantity"></span>
                                        <span class="text-xs text-gray-500">labels</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="px-3 py-1.5 text-xs font-semibold rounded-full"
                                            :class="{
                                                'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400': job.status === 'pending',
                                                'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400': job.status === 'printing',
                                                'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400': job.status === 'completed',
                                                'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400': job.status === 'failed'
                                            }"
                                            x-text="job.status.charAt(0).toUpperCase() + job.status.slice(1)">
                                        </span>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

            <div x-show="printHistory.length === 0" class="text-center py-12">
                <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="text-gray-500">No print history yet</p>
            </div>
        </div>
    </div>
</div>

<script>
function labelDesigner() {
    return {
        activeTab: 'templates',
        templates: [],
        printHistory: [],
        filters: { type: '', search: '' },
        designerData: {
            name: '',
            template_type: 'price_tag',
            width_mm: 50,
            height_mm: 30,
            layout_json: { fields: [] },
            include_barcode: true,
            include_qr: false,
            include_logo: false
        },

        async init() {
            await this.loadTemplates();
        },

        async loadTemplates() {
            const token = localStorage.getItem('saga_token');
            let url = '/api/label-templates?';
            if (this.filters.type) url += `type=${this.filters.type}`;
            if (this.filters.search) url += `&search=${this.filters.search}`;

            try {
                const res = await fetch(url, { headers: { 'Authorization': 'Bearer ' + token } });
                const data = await res.json();
                if (data.success) this.templates = data.data;
            } catch (e) { console.error('Load templates error:', e); }
        },

        async loadPrintHistory() {
            const token = localStorage.getItem('saga_token');
            try {
                const res = await fetch('/api/label-templates/print-history', { headers: { 'Authorization': 'Bearer ' + token } });
                const data = await res.json();
                if (data.success) this.printHistory = data.data.data || [];
            } catch (e) { console.error('Load history error:', e); }
        },

        openDesigner() {
            this.activeTab = 'designer';
            this.resetDesigner();
        },

        resetDesigner() {
            this.designerData = {
                name: '',
                template_type: 'price_tag',
                width_mm: 50,
                height_mm: 30,
                layout_json: { fields: [] },
                include_barcode: true,
                include_qr: false,
                include_logo: false
            };
        },

        async saveTemplate() {
            const token = localStorage.getItem('saga_token');
            try {
                const res = await fetch('/api/label-templates', {
                    method: 'POST',
                    headers: {
                        'Authorization': 'Bearer ' + token,
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(this.designerData)
                });
                const data = await res.json();
                if (data.success) {
                    await this.loadTemplates();
                    Swal.fire({ 
                        icon: 'success', 
                        title: 'Template Saved', 
                        text: 'Your label template has been saved successfully',
                        toast: true, 
                        position: 'top-end', 
                        timer: 2000,
                        showConfirmButton: false
                    });
                }
            } catch (e) { 
                Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to save template' }); 
            }
        },

        async deleteTemplate(id) {
            const result = await Swal.fire({ 
                title: 'Delete Template?', 
                text: 'Are you sure you want to delete this template?',
                icon: 'warning', 
                showCancelButton: true, 
                confirmButtonText: 'Yes, Delete',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#EF4444'
            });
            if (result.isConfirmed) {
                const token = localStorage.getItem('saga_token');
                await fetch(`/api/label-templates/${id}`, { method: 'DELETE', headers: { 'Authorization': 'Bearer ' + token } });
                await this.loadTemplates();
                Swal.fire({ icon: 'success', title: 'Deleted', text: 'Template has been deleted' });
            }
        },

        async editTemplate(id) {
            const token = localStorage.getItem('saga_token');
            try {
                const res = await fetch(`/api/label-templates/${id}`, { headers: { 'Authorization': 'Bearer ' + token } });
                const data = await res.json();
                if (data.success) {
                    const template = data.data;
                    this.designerData = {
                        name: template.name,
                        template_type: template.template_type,
                        width_mm: template.width_mm,
                        height_mm: template.height_mm,
                        layout_json: template.layout_json || { fields: [] },
                        include_barcode: template.include_barcode ?? true,
                        include_qr: template.include_qr ?? false,
                        include_logo: template.include_logo ?? false
                    };
                    this.activeTab = 'designer';
                }
            } catch (e) { 
                Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to load template' }); 
            }
        },

        printLabels(templateId) {
            Swal.fire({ 
                icon: 'info', 
                title: 'Print Labels', 
                text: 'Select products to print labels for',
                showCancelButton: true,
                confirmButtonText: 'Select Products',
                cancelButtonText: 'Cancel'
            });
        },

        formatDate(date) {
            if (!date) return '-';
            return new Date(date).toLocaleDateString('id-ID', { 
                year: 'numeric', 
                month: 'short', 
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }
    }
}
</script>
@endsection
