<?php $__env->startSection('title', 'Label Designer'); ?>

<?php $__env->startSection('content'); ?>
<div x-data="labelDesigner()" x-init="init()">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Label Designer</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400">Create and manage label templates</p>
    </div>

    <!-- Tabs -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm mb-6">
        <div class="border-b border-gray-200 dark:border-gray-700">
            <nav class="flex -mb-px">
                <button @click="activeTab = 'templates'"
                    :class="activeTab === 'templates' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                    class="py-4 px-6 border-b-2 font-medium text-sm">
                    Templates
                </button>
                <button @click="activeTab = 'designer'"
                    :class="activeTab === 'designer' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                    class="py-4 px-6 border-b-2 font-medium text-sm">
                    Designer
                </button>
                <button @click="activeTab = 'history'"
                    :class="activeTab === 'history' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                    class="py-4 px-6 border-b-2 font-medium text-sm">
                    Print History
                </button>
            </nav>
        </div>

        <!-- Templates Tab -->
        <div x-show="activeTab === 'templates'" class="p-6">
            <div class="flex justify-between mb-4">
                <div class="flex gap-2">
                    <select x-model="filters.type" @change="loadTemplates()" class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900">
                        <option value="">All Types</option>
                        <option value="price_tag">Price Tag</option>
                        <option value="shelf_label">Shelf Label</option>
                        <option value="barcode_label">Barcode Label</option>
                        <option value="custom">Custom</option>
                    </select>
                </div>
                <button @click="openDesigner()" class="px-4 py-2 bg-brand-600 text-white rounded-lg hover:bg-brand-700">
                    + New Template
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <template x-for="template in templates" :key="template.id">
                    <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:shadow-md transition-shadow">
                        <div class="flex justify-between items-start mb-2">
                            <h3 class="font-bold text-gray-800 dark:text-white" x-text="template.name"></h3>
                            <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-700" x-text="template.template_type"></span>
                        </div>
                        <p class="text-sm text-gray-500 mb-2" x-text="template.width_mm + 'mm x ' + template.height_mm + 'mm'"></p>
                        <div class="flex gap-2">
                            <button @click="editTemplate(template.id)" class="text-blue-600 hover:text-blue-800 text-sm">Edit</button>
                            <button @click="printLabels(template.id)" class="text-green-600 hover:text-green-800 text-sm">Print</button>
                            <button @click="deleteTemplate(template.id)" class="text-red-600 hover:text-red-800 text-sm" x-show="!template.is_default">Delete</button>
                        </div>
                        <div x-show="template.is_default" class="mt-2 text-xs text-gray-500">✓ Default Template</div>
                    </div>
                </template>
            </div>
        </div>

        <!-- Designer Tab -->
        <div x-show="activeTab === 'designer'" class="p-6">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Settings Panel -->
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Template Name</label>
                        <input type="text" x-model="designerData.name" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Type</label>
                        <select x-model="designerData.template_type" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900">
                            <option value="price_tag">Price Tag</option>
                            <option value="shelf_label">Shelf Label</option>
                            <option value="barcode_label">Barcode Label</option>
                            <option value="custom">Custom</option>
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Width (mm)</label>
                            <input type="number" x-model="designerData.width_mm" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Height (mm)</label>
                            <input type="number" x-model="designerData.height_mm" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900">
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <button @click="saveTemplate()" class="flex-1 px-4 py-2 bg-brand-600 text-white rounded-lg hover:bg-brand-700">Save Template</button>
                        <button @click="resetDesigner()" class="px-4 py-2 bg-gray-200 rounded-lg hover:bg-gray-300">Reset</button>
                    </div>
                </div>

                <!-- Preview Panel -->
                <div class="lg:col-span-2">
                    <div class="border border-gray-300 dark:border-gray-600 rounded-lg p-4" style="min-height: 300px;">
                        <h4 class="font-bold mb-2">Preview</h4>
                        <div class="border border-gray-200 dark:border-gray-700 bg-white" 
                             :style="'width: ' + designerData.width_mm * 4 + 'px; height: ' + designerData.height_mm * 4 + 'px; position: relative;'">
                            <!-- Preview fields will be rendered here -->
                            <template x-for="(field, index) in designerData.layout_json.fields" :key="index">
                                <div class="absolute border border-dashed border-gray-400 p-1 text-xs"
                                     :style="'left: ' + field.x * 4 + 'px; top: ' + field.y * 4 + 'px; width: ' + field.width * 4 + 'px; height: ' + field.height * 4 + 'px;'">
                                    <span x-text="field.type"></span>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Print History Tab -->
        <div x-show="activeTab === 'history'" class="p-6">
            <h3 class="font-bold text-lg mb-4">Print History</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-4 py-2 text-left">Date</th>
                            <th class="px-4 py-2 text-left">Template</th>
                            <th class="px-4 py-2 text-left">Products</th>
                            <th class="px-4 py-2 text-left">Qty</th>
                            <th class="px-4 py-2 text-left">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="job in printHistory" :key="job.id">
                            <tr class="border-b dark:border-gray-700">
                                <td class="px-4 py-2" x-text="formatDate(job.created_at)"></td>
                                <td class="px-4 py-2" x-text="job.template?.name || '-'"></td>
                                <td class="px-4 py-2" x-text="job.product_ids?.length || 0"></td>
                                <td class="px-4 py-2" x-text="job.quantity"></td>
                                <td class="px-4 py-2">
                                    <span class="px-2 py-1 text-xs rounded-full"
                                        :class="{
                                            'bg-yellow-100 text-yellow-700': job.status === 'pending',
                                            'bg-blue-100 text-blue-700': job.status === 'printing',
                                            'bg-green-100 text-green-700': job.status === 'completed',
                                            'bg-red-100 text-red-700': job.status === 'failed'
                                        }"
                                        x-text="job.status">
                                    </span>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
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
        filters: { type: '' },
        designerData: {
            name: '',
            template_type: 'price_tag',
            width_mm: 50,
            height_mm: 30,
            layout_json: { fields: [] }
        },

        async init() {
            await this.loadTemplates();
        },

        async loadTemplates() {
            const token = localStorage.getItem('saga_token');
            let url = '/api/label-templates?';
            if (this.filters.type) url += `type=${this.filters.type}`;
            
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
                if (data.success) this.printHistory = data.data.data;
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
                layout_json: { fields: [] }
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
                    Swal.fire({ icon: 'success', title: 'Saved', text: 'Template saved', toast: true, position: 'top-end', timer: 2000 });
                }
            } catch (e) { Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to save' }); }
        },

        async deleteTemplate(id) {
            const result = await Swal.fire({ title: 'Delete?', icon: 'warning', showCancelButton: true, confirmButtonText: 'Yes' });
            if (result.isConfirmed) {
                const token = localStorage.getItem('saga_token');
                await fetch(`/api/label-templates/${id}`, { method: 'DELETE', headers: { 'Authorization': 'Bearer ' + token } });
                await this.loadTemplates();
                Swal.fire({ icon: 'success', title: 'Deleted' });
            }
        },

        editTemplate(id) {
            // Load template and open designer
            this.activeTab = 'designer';
        },

        printLabels(templateId) {
            // Open print dialog
            Swal.fire({ icon: 'info', title: 'Print', text: 'Print functionality - Wave 3' });
        },

        formatDate(date) {
            if (!date) return '-';
            return new Date(date).toLocaleDateString('id-ID');
        }
    }
}
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\Project App\laravelsaga\resources\views/pages/inventory/label-designer.blade.php ENDPATH**/ ?>