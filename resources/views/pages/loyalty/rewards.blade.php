@extends('layouts.app')

@section('title', 'Reward Catalog')

@section('content')
<div x-data="rewardCatalog()" x-init="init()">
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Reward Catalog</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Manage rewards for loyalty program</p>
        </div>
        <button @click="openAddModal()" class="px-4 py-2 bg-brand-600 text-white rounded-lg hover:bg-brand-700">
            + Add Reward
        </button>
    </div>
    
    <!-- Rewards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <template x-for="reward in rewards" :key="reward.id">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <!-- Reward Image -->
                <div class="h-48 bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                    <img v-if="reward.image_url" :src="reward.image_url" :alt="reward.name" class="w-full h-full object-cover">
                    <div v-else class="text-gray-400">
                        <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                        </svg>
                    </div>
                </div>
                
                <!-- Reward Info -->
                <div class="p-4">
                    <div class="flex justify-between items-start mb-2">
                        <h3 class="font-bold text-gray-800 dark:text-white" x-text="reward.name"></h3>
                        <span class="px-2 py-1 text-xs rounded-full font-semibold"
                            :class="{
                                'bg-green-100 text-green-700': reward.status === 'active',
                                'bg-yellow-100 text-yellow-700': reward.status === 'draft',
                                'bg-gray-100 text-gray-700': reward.status === 'inactive'
                            }"
                            x-text="reward.status"></span>
                    </div>
                    
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-3" x-text="reward.description"></p>
                    
                    <div class="flex justify-between items-center">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span class="font-bold text-brand-600" x-text="reward.points_cost + ' pts'"></span>
                        </div>
                        <span class="text-xs text-gray-500" x-show="reward.stock !== null" x-text="'Stock: ' + (reward.stock || '∞')"></span>
                    </div>
                    
                    <!-- Actions -->
                    <div class="flex gap-2 mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <button @click="openEditModal(reward)" class="flex-1 px-3 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm">
                            Edit
                        </button>
                        <button @click="deleteReward(reward.id)" class="flex-1 px-3 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 text-sm">
                            Delete
                        </button>
                    </div>
                </div>
            </div>
        </template>
    </div>
    
    <!-- Empty State -->
    <div x-show="rewards.length === 0 && !loading" class="text-center py-12">
        <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
        </svg>
        <p class="text-gray-500 dark:text-gray-400">No rewards yet. Create your first reward!</p>
    </div>
    
    <!-- Loading State -->
    <div x-show="loading" class="text-center py-12">
        <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-brand-600"></div>
        <p class="mt-4 text-gray-500">Loading rewards...</p>
    </div>
    
    <!-- Add/Edit Modal -->
    <div x-show="showModal" class="fixed inset-0 z-[99999] flex items-center justify-center bg-gray-900/70 backdrop-blur-sm p-4" x-cloak>
        <div class="bg-white dark:bg-gray-800 rounded-2xl w-full max-w-2xl shadow-2xl overflow-hidden max-h-[90vh] overflow-y-auto">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                <h3 class="text-xl font-bold text-gray-800 dark:text-white" x-text="editMode ? 'Edit Reward' : 'Add New Reward'"></h3>
                <button @click="showModal = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Reward Name</label>
                    <input type="text" x-model="rewardData.name" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Description</label>
                    <textarea x-model="rewardData.description" rows="3" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white"></textarea>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Points Cost</label>
                        <input type="number" x-model="rewardData.points_cost" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Stock (leave empty for infinite)</label>
                        <input type="number" x-model="rewardData.stock" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Image URL</label>
                    <input type="text" x-model="rewardData.image_url" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white" placeholder="https://...">
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Terms & Conditions</label>
                    <textarea x-model="rewardData.terms_conditions" rows="2" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white"></textarea>
                </div>
                
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Status</label>
                        <select x-model="rewardData.status" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                            <option value="draft">Draft</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Active From</label>
                        <input type="date" x-model="rewardData.active_from" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Active To</label>
                        <input type="date" x-model="rewardData.active_to" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                    </div>
                </div>
            </div>
            
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 flex justify-end gap-3">
                <button @click="showModal = false" class="px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">Cancel</button>
                <button @click="saveReward()" class="px-6 py-2 bg-brand-600 text-white rounded-lg hover:bg-brand-700">Save Reward</button>
            </div>
        </div>
    </div>
</div>

<script>
function rewardCatalog() {
    return {
        rewards: [],
        loading: false,
        showModal: false,
        editMode: false,
        rewardData: {
            name: '',
            description: '',
            points_cost: 100,
            stock: null,
            image_url: '',
            terms_conditions: '',
            status: 'draft',
            active_from: '',
            active_to: '',
        },
        
        async init() {
            await this.loadRewards();
        },
        
        async loadRewards() {
            this.loading = true;
            const token = localStorage.getItem('saga_token');
            try {
                const res = await fetch('/api/rewards', {
                    headers: { 'Authorization': 'Bearer ' + token }
                });
                const data = await res.json();
                if (data.success) {
                    this.rewards = data.data;
                }
            } catch (e) {
                console.error('Load rewards error:', e);
            } finally {
                this.loading = false;
            }
        },
        
        openAddModal() {
            this.editMode = false;
            this.rewardData = {
                name: '',
                description: '',
                points_cost: 100,
                stock: null,
                image_url: '',
                terms_conditions: '',
                status: 'draft',
                active_from: '',
                active_to: '',
            };
            this.showModal = true;
        },
        
        openEditModal(reward) {
            this.editMode = true;
            this.rewardData = { ...reward };
            this.showModal = true;
        },
        
        async saveReward() {
            const token = localStorage.getItem('saga_token');
            const url = this.editMode ? `/api/rewards/${this.rewardData.id}` : '/api/rewards';
            const method = this.editMode ? 'PUT' : 'POST';
            
            try {
                const res = await fetch(url, {
                    method,
                    headers: {
                        'Authorization': 'Bearer ' + token,
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(this.rewardData)
                });
                const data = await res.json();
                if (data.success) {
                    this.showModal = false;
                    await this.loadRewards();
                    Swal.fire({
                        icon: 'success',
                        title: 'Saved',
                        text: 'Reward saved successfully',
                        toast: true,
                        position: 'top-end',
                        timer: 2000,
                        showConfirmButton: false
                    });
                }
            } catch (e) {
                console.error('Save error:', e);
                Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to save reward' });
            }
        },
        
        async deleteReward(id) {
            const result = await Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            });
            
            if (result.isConfirmed) {
                const token = localStorage.getItem('saga_token');
                try {
                    await fetch(`/api/rewards/${id}`, {
                        method: 'DELETE',
                        headers: { 'Authorization': 'Bearer ' + token }
                    });
                    await this.loadRewards();
                    Swal.fire({
                        icon: 'success',
                        title: 'Deleted',
                        text: 'Reward has been deleted',
                        toast: true,
                        position: 'top-end',
                        timer: 2000,
                        showConfirmButton: false
                    });
                } catch (e) {
                    Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to delete reward' });
                }
            }
        }
    }
}
</script>
@endsection
