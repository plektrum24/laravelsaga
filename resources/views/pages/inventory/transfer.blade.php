@extends('layouts.app')

@section('title', 'Transfer Item | SAGA TOKO APP')

@section('content')
    <div x-data="{
                    page: 'transferItem',
                    activeTab: 'outgoing',
                    isLoading: false,
                    showModal: false,
                    viewDetailModal: false,
                    selectedTransfer: null,
                    selectedTransferItems: [],

                    // User info for branch locking
                    userRole: JSON.parse(localStorage.getItem('saga_user'))?.role || '',
                    userBranchId: JSON.parse(localStorage.getItem('saga_user'))?.branch_id || null,
                    isFromLocked: false,

                    // Transfer form
                    transferForm: {
                        fromLocation: '',
                        toLocation: '',
                        notes: '',
                        items: []
                    },

                    locations: [],
                    transfers: [],
                    products: [],
                    searchProduct: '',
                    showDropdown: false,

                    async init() {
                        const token = localStorage.getItem('saga_token');
                        // if (!token) return window.location.href = '/signin'; // Disabled for Dev

                        await Promise.all([
                            this.fetchLocations(token),
                            this.fetchTransfers(token)
                        ]);

                        // Poll for updates every 30s
                        setInterval(() => this.fetchTransfers(token), 30000);

                        // Auto-set fromLocation based on role
                        if (this.userRole === 'tenant_owner') {
                            const savedBranch = localStorage.getItem('saga_selected_branch');
                            if (savedBranch) {
                                this.transferForm.fromLocation = parseInt(savedBranch);
                                await this.fetchProducts(token, savedBranch);
                            }
                            this.isFromLocked = false;
                        } else if (this.userBranchId) {
                            this.transferForm.fromLocation = this.userBranchId;
                            this.isFromLocked = true;
                            await this.fetchProducts(token, this.userBranchId);
                        }

                        this.$watch('transferForm.fromLocation', async (value) => {
                            if (value) {
                                await this.fetchProducts(token, value);
                            }
                        });
                    },

                    async fetchLocations(token) {
                        try {
                            const res = await fetch('/api/branches', { headers: { 'Authorization': 'Bearer ' + token } });
                            const data = await res.json();
                            if (data.success) {
                                this.locations = data.data.map(b => ({
                                    id: b.id,
                                    name: b.name,
                                    type: b.is_main ? 'PUSAT' : 'CABANG',
                                    is_active: b.is_active
                                }));
                            }
                        } catch (e) {
                            console.error('Error fetching locations:', e);
                        }
                    },

                    async fetchProducts(token, branchId = null, searchQuery = '') {
                        try {
                            let url = '/api/products?limit=100';
                            if (branchId) url += '&branch_id=' + branchId;
                            if (searchQuery) url += '&search=' + encodeURIComponent(searchQuery);

                            const res = await fetch(url, { headers: { 'Authorization': 'Bearer ' + token } });
                            const data = await res.json();
                            if (data.success) this.products = data.data.products || [];
                        } catch (e) {
                            console.error(e);
                        }
                    },

                    async searchProducts() {
                        const token = localStorage.getItem('saga_token');
                        if (this.transferForm.fromLocation) {
                            await this.fetchProducts(token, this.transferForm.fromLocation, this.searchProduct);
                        }
                    },

                    async fetchTransfers(token) {
                        try {
                            const res = await fetch('/api/transfers', { headers: { 'Authorization': 'Bearer ' + token } });
                            const data = await res.json();
                            if (data.success) {
                                this.transfers = data.data.map(t => ({
                                    id: t.id,
                                    code: t.transfer_number,
                                    from: t.from_branch_name,
                                    to: t.to_branch_name,
                                    items: t.item_count || 0,
                                    total_qty: t.total_qty || 0,
                                    status: t.status,
                                    date: t.created_at,
                                    raw: t
                                }));
                            }
                        } catch (e) {
                            console.error('Error fetching transfers:', e);
                        }
                    },

                    get filteredProducts() {
                        return this.products.slice(0, 10);
                    },

                    get filteredTransfers() {
                        if (this.activeTab === 'outgoing') {
                            return this.transfers.filter(t => t.status === 'shipped' || t.status === 'pending');
                        } else {
                            return this.transfers.filter(t => t.status === 'received' || t.status === 'shipped');
                        }
                    },

                    openNewTransfer() {
                        const savedFrom = this.transferForm.fromLocation || localStorage.getItem('saga_selected_branch') || this
                            .userBranchId;
                        this.transferForm = {
                            fromLocation: savedFrom,
                            toLocation: '',
                            notes: '',
                            items: []
                        };
                        this.searchProduct = '';
                        this.showModal = true;

                        if (savedFrom) {
                            const token = localStorage.getItem('saga_token');
                            this.fetchProducts(token, savedFrom);
                        }
                    },

                    addItemToTransfer(product) {
                        const existing = this.transferForm.items.find(i => i.product_id === product.id);
                        if (existing) {
                            existing.quantity++;
                        } else {
                            const units = product.units || [{
                                unit_id: null,
                                unit_name: 'Pcs',
                                conversion_qty: 1,
                                is_base_unit: true
                            }];
                            const baseUnit = units.find(u => u.is_base_unit) || units[0];
                            this.transferForm.items.push({
                                product_id: product.id,
                                name: product.name,
                                sku: product.sku,
                                stock: product.stock,
                                units: units,
                                selectedUnit: baseUnit,
                                quantity: 1
                            });
                        }
                        this.searchProduct = '';
                    },

                    removeItemFromTransfer(index) {
                        this.transferForm.items.splice(index, 1);
                    },

                    async createTransfer() {
                        if (!this.transferForm.fromLocation || !this.transferForm.toLocation) {
                            Swal.fire('Error', 'Pilih lokasi asal dan tujuan', 'error');
                            return;
                        }
                        if (this.transferForm.items.length === 0) {
                            Swal.fire('Error', 'Tambahkan minimal 1 item', 'error');
                            return;
                        }
                        this.isLoading = true;

                        const payload = {
                            from_branch_id: this.transferForm.fromLocation,
                            to_branch_id: this.transferForm.toLocation,
                            notes: this.transferForm.notes,
                            items: this.transferForm.items.map(i => {
                                const conversionQty = parseFloat(i.selectedUnit?.conversion_qty) || 1;
                                return {
                                    product_id: i.product_id,
                                    quantity: i.quantity * conversionQty,
                                    unit_id: i.selectedUnit?.unit_id || null,
                                    notes: i.selectedUnit?.unit_name ?
                                        `(${i.quantity} ${i.selectedUnit.unit_name})` : ''
                                };
                            })
                        };

                        try {
                            const token = localStorage.getItem('saga_token');
                            const res = await fetch('/api/transfers', {
                                method: 'POST',
                                headers: {
                                    'Authorization': 'Bearer ' + token,
                                    'Content-Type': 'application/json'
                                },
                                body: JSON.stringify(payload)
                            });
                            const data = await res.json();

                            if (data.success) {
                                this.showModal = false;
                                await this.fetchTransfers(token);
                                Swal.fire('Berhasil', 'Transfer berhasil dibuat', 'success');
                                this.transferForm.items = [];
                                this.transferForm.notes = '';
                            } else {
                                Swal.fire('Gagal', data.message || 'Gagal membuat transfer', 'error');
                            }
                        } catch (e) {
                            console.error(e);
                            Swal.fire('Error', 'Terjadi kesalahan sistem', 'error');
                        } finally {
                            this.isLoading = false;
                        }
                    },

                    async shipTransfer(transfer) {
                        const result = await Swal.fire({
                            title: 'Konfirmasi Pengiriman',
                            text: 'Tandai sebagai dikirim (Shipped)? Stok akan dipotong dari cabang asal.',
                            icon: 'question',
                            showCancelButton: true,
                            confirmButtonColor: '#3085d6',
                            cancelButtonColor: '#d33',
                            confirmButtonText: 'Ya, Kirim!'
                        });

                        if (!result.isConfirmed) return;

                        const token = localStorage.getItem('saga_token');
                        try {
                            const res = await fetch(`/api/transfers/${transfer.id}/approve`, {
                                method: 'PATCH',
                                headers: { 'Authorization': 'Bearer ' + token }
                            });
                            const data = await res.json();
                            if (data.success) {
                                await this.fetchTransfers(token);
                                Swal.fire('Berhasil!', 'Status transfer diperbarui menjadi Shipped.', 'success');
                            } else {
                                Swal.fire('Gagal', data.message, 'error');
                            }
                        } catch (e) {
                            Swal.fire('Error', 'Terjadi kesalahan sistem', 'error');
                        }
                    },

                    async receiveTransfer(transfer) {
                        const result = await Swal.fire({
                            title: 'Konfirmasi Penerimaan',
                            text: 'Barang sudah diterima? Stok akan bertambah di cabang tujuan.',
                            icon: 'question',
                            showCancelButton: true,
                            confirmButtonColor: '#3085d6',
                            cancelButtonColor: '#d33',
                            confirmButtonText: 'Ya, Terima!'
                        });

                        if (!result.isConfirmed) return;

                        const token = localStorage.getItem('saga_token');
                        try {
                            const res = await fetch(`/api/transfers/${transfer.id}/receive`, {
                                method: 'PATCH',
                                headers: { 'Authorization': 'Bearer ' + token }
                            });
                            const data = await res.json();
                            if (data.success) {
                                await this.fetchTransfers(token);
                                Swal.fire('Berhasil!', 'Barang telah diterima.', 'success');
                            } else {
                                Swal.fire('Gagal', data.message, 'error');
                            }
                        } catch (e) {
                            Swal.fire('Error', 'Terjadi kesalahan sistem', 'error');
                        }
                    },

                    getStatusBadge(status) {
                        const badges = {
                            'pending': {
                                text: 'Pending',
                                class: 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400'
                            },
                            'shipped': {
                                text: 'Shipped / Delivery',
                                class: 'bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400'
                            },
                            'received': {
                                text: 'Received',
                                class: 'bg-green-100 text-green-600 dark:bg-green-900/30 dark:text-green-400'
                            }
                        };
                        return badges[status] || badges['pending'];
                    },

                    formatDate(dateStr) {
                        return new Date(dateStr).toLocaleDateString('id-ID', {
                            day: '2-digit',
                            month: 'short',
                            year: 'numeric'
                        });
                    },

                    async viewTransfer(transfer) {
                        this.isLoading = true;
                        try {
                            const token = localStorage.getItem('saga_token');
                            const res = await fetch(`/api/transfers/${transfer.id}`, {
                                headers: { 'Authorization': 'Bearer ' + token }
                            });
                            const data = await res.json();
                            if (data.success) {
                                this.selectedTransfer = data.data;
                                this.selectedTransferItems = data.data.items || [];
                                this.viewDetailModal = true;
                            } else {
                                Swal.fire('Error', data.message || 'Gagal mengambil detail', 'error');
                            }
                        } catch (e) {
                            console.error(e);
                            Swal.fire('Error', 'Gagal mengambil detail transfer', 'error');
                        } finally {
                            this.isLoading = false;
                        }
                    }
                }" x-init="init()">
        <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Transfer Item</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">Kelola perpindahan barang antar cabang/gudang</p>
            </div>
            <button @click="openNewTransfer()"
                class="px-4 py-2 bg-brand-500 text-white rounded-lg hover:bg-brand-600 flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6">
                    </path>
                </svg>
                New Transfer
            </button>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-yellow-100 rounded-xl flex items-center justify-center dark:bg-yellow-900/30">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Pending</p>
                        <p class="text-2xl font-bold text-yellow-600"
                            x-text="transfers.filter(t => t.status === 'pending').length">0</p>
                    </div>
                </div>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center dark:bg-blue-900/30">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">In Transit (Shipped)</p>
                        <p class="text-2xl font-bold text-blue-600"
                            x-text="transfers.filter(t => t.status === 'shipped').length">0</p>
                    </div>
                </div>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center dark:bg-green-900/30">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Received</p>
                        <p class="text-2xl font-bold text-green-600"
                            x-text="transfers.filter(t => t.status === 'received').length">0</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <div class="flex gap-2 mb-4">
            <button @click="activeTab = 'outgoing'" class="px-4 py-2 rounded-lg font-medium text-sm transition-colors"
                :class="activeTab === 'outgoing' ? 'bg-brand-500 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-400'">
                <span class="flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3">
                        </path>
                    </svg>
                    Outgoing (Kirim)
                </span>
            </button>
            <button @click="activeTab = 'incoming'" class="px-4 py-2 rounded-lg font-medium text-sm transition-colors"
                :class="activeTab === 'incoming' ? 'bg-brand-500 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-400'">
                <span class="flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M7 16l-4-4m0 0l4-4m-4 4h18"></path>
                    </svg>
                    Incoming (Terima)
                </span>
            </button>
        </div>

        <!-- Transfer List -->
        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-gray-800/50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Transfer Code</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">From → To</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Items</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Total Qty</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Date</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    <template x-for="transfer in filteredTransfers" :key="transfer.id">
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/30 transition-colors">
                            <td class="px-4 py-4">
                                <span class="font-mono font-medium text-gray-800 dark:text-white"
                                    x-text="transfer.code"></span>
                            </td>
                            <td class="px-4 py-4">
                                <div class="flex items-center gap-2">
                                    <span class="text-gray-600 dark:text-gray-300" x-text="transfer.from"></span>
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                                    </svg>
                                    <span class="text-gray-600 dark:text-gray-300" x-text="transfer.to"></span>
                                </div>
                            </td>
                            <td class="px-4 py-4 text-center font-medium text-gray-800 dark:text-white"
                                x-text="transfer.items"></td>
                            <td class="px-4 py-4 text-center font-medium text-gray-800 dark:text-white"
                                x-text="transfer.total_qty"></td>
                            <td class="px-4 py-4 text-center text-sm text-gray-500 dark:text-gray-400"
                                x-text="formatDate(transfer.date)"></td>
                            <td class="px-4 py-4 text-center">
                                <span class="px-2.5 py-1 rounded-full text-xs font-medium"
                                    :class="getStatusBadge(transfer.status).class"
                                    x-text="getStatusBadge(transfer.status).text"></span>
                            </td>
                            <td class="px-4 py-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <button @click="viewTransfer(transfer)"
                                        class="p-1.5 text-blue-500 hover:text-blue-700 hover:bg-blue-50 rounded-lg transition-colors"
                                        title="View Detail">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z">
                                            </path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                            </path>
                                        </svg>
                                    </button>
                                    <template x-if="activeTab === 'outgoing'">
                                        <div>
                                            <button x-show="transfer.status === 'pending'" @click="shipTransfer(transfer)"
                                                class="px-3 py-1.5 bg-blue-500 text-white text-xs font-medium rounded-lg hover:bg-blue-600">
                                                Mark as Shipped
                                            </button>
                                            <span x-show="transfer.status === 'shipped'"
                                                class="text-xs text-gray-400">Waiting
                                                for receiver</span>
                                        </div>
                                    </template>
                                    <template x-if="activeTab === 'incoming'">
                                        <div>
                                            <button x-show="transfer.status === 'shipped'"
                                                @click="receiveTransfer(transfer)"
                                                class="px-3 py-1.5 bg-green-500 text-white text-xs font-medium rounded-lg hover:bg-green-600">
                                                Confirm Received
                                            </button>
                                            <span x-show="transfer.status === 'received'" class="text-xs text-green-600">✓
                                                Completed</span>
                                        </div>
                                    </template>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
            <div x-show="filteredTransfers.length === 0" class="p-8 text-center text-gray-400">
                <svg class="w-12 h-12 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                </svg>
                No transfers found
            </div>
        </div>

        <!-- Info Card -->
        <div class="mt-6 rounded-2xl bg-blue-50 p-5 dark:bg-blue-900/20">
            <div class="flex gap-4">
                <svg class="w-6 h-6 text-blue-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                        clip-rule="evenodd" />
                </svg>
                <div>
                    <h4 class="font-medium text-blue-800 dark:text-blue-300">Cara Kerja Transfer</h4>
                    <ul class="text-sm text-blue-600 dark:text-blue-400 mt-1 list-disc list-inside space-y-1">
                        <li><strong>Pending:</strong> Transfer dibuat, menunggu konfirmasi pengiriman</li>
                        <li><strong>Shipped/Delivery:</strong> Barang sudah dikirim oleh cabang pengirim</li>
                        <li><strong>Received:</strong> Barang diterima dan dikonfirmasi oleh cabang tujuan</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- New Transfer Modal -->
        <div x-show="showModal"
            class="fixed inset-0 z-[9999] flex items-center justify-center bg-gray-900/80 backdrop-blur-sm p-4" x-cloak
            x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
            <div class="bg-white dark:bg-gray-800 rounded-2xl w-full max-w-2xl max-h-[90vh] overflow-hidden flex flex-col">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                    <h2 class="text-xl font-bold text-gray-800 dark:text-white">New Transfer</h2>
                    <button @click="showModal = false" class="text-gray-400 hover:text-gray-600"><svg class="w-6 h-6"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                            </path>
                        </svg></button>
                </div>

                <div class="flex-1 overflow-y-auto p-6 space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                                From (Asal)
                                <span x-show="isFromLocked" class="text-xs text-gray-500 ml-1">🔒 Locked</span>
                            </label>
                            <select x-model="transferForm.fromLocation" :disabled="isFromLocked"
                                class="w-full px-4 py-2.5 border border-gray-200 rounded-xl dark:bg-gray-900 dark:border-gray-700 dark:text-white"
                                :class="isFromLocked ? 'bg-gray-100 cursor-not-allowed dark:bg-gray-800' : ''">
                                <option value="">Select Location</option>
                                <template x-for="loc in locations" :key="loc.id">
                                    <option :value="loc.id" x-text="loc.name + ' (' + loc.type + ')'"></option>
                                </template>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">To
                                (Tujuan)</label>
                            <select x-model="transferForm.toLocation"
                                class="w-full px-4 py-2.5 border border-gray-200 rounded-xl dark:bg-gray-900 dark:border-gray-700 dark:text-white">
                                <option value="">Select Location</option>
                                <template x-for="loc in locations" :key="loc.id">
                                    <option :value="loc.id" :disabled="loc.id == transferForm.fromLocation"
                                        x-text="loc.name + ' (' + loc.type + ')'"></option>
                                </template>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Add
                            Products</label>
                        <div class="relative">
                            <input type="text" x-model="searchProduct" @input.debounce.300ms="searchProducts()"
                                @focus="showDropdown = true; if (!searchProduct && products.length === 0 && transferForm.fromLocation) { fetchProducts(localStorage.getItem('saga_token'), transferForm.fromLocation); }"
                                @blur="setTimeout(() => showDropdown = false, 200)"
                                placeholder="Ketik nama produk untuk mencari..."
                                class="w-full px-4 py-2.5 pl-10 border border-gray-200 rounded-xl dark:bg-gray-900 dark:border-gray-700 dark:text-white">
                            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>

                            <div x-show="showDropdown && (searchProduct || products.length > 0)"
                                class="absolute z-10 mt-1 w-full bg-white border border-gray-200 rounded-xl shadow-lg max-h-48 overflow-y-auto dark:bg-gray-900 dark:border-gray-700">
                                <template x-for="product in filteredProducts" :key="product.id">
                                    <button @click="addItemToTransfer(product)"
                                        class="w-full px-4 py-2.5 text-left hover:bg-gray-50 flex items-center justify-between dark:hover:bg-gray-800">
                                        <div>
                                            <p class="font-medium text-gray-800 dark:text-white text-sm"
                                                x-text="product.name"></p>
                                            <p class="text-xs text-gray-500" x-text="product.sku"></p>
                                        </div>
                                        <span class="text-xs text-gray-500">Stock: <span
                                                x-text="product.stock"></span></span>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Items to
                            Transfer</label>
                        <div class="border border-gray-200 rounded-xl overflow-hidden dark:border-gray-700">
                            <div x-show="transferForm.items.length === 0" class="p-6 text-center text-gray-400">
                                Search and add products above
                            </div>
                            <template x-for="(item, idx) in transferForm.items" :key="idx">
                                <div
                                    class="flex items-center justify-between px-4 py-3 border-b border-gray-100 last:border-b-0 dark:border-gray-700">
                                    <div class="flex-1">
                                        <p class="font-medium text-gray-800 dark:text-white text-sm" x-text="item.name">
                                        </p>
                                        <p class="text-xs text-gray-400">
                                            Stock:
                                            <span
                                                x-text="Math.floor(item.stock / (item.selectedUnit?.conversion_qty || 1))"></span>
                                            <span x-text="item.selectedUnit?.unit_name || 'pcs'"></span>
                                            <span x-show="(item.selectedUnit?.conversion_qty || 1) > 1"
                                                class="text-gray-300">
                                                (<span x-text="item.stock"></span> base)
                                            </span>
                                        </p>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <select @change="item.selectedUnit = item.units[$event.target.selectedIndex]"
                                            class="px-2 py-1.5 border border-gray-200 rounded-lg text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-white">
                                            <template x-for="(u, uIdx) in item.units" :key="u.unit_id">
                                                <option :value="uIdx" :selected="item.selectedUnit?.unit_id === u.unit_id"
                                                    x-text="u.unit_name + (u.conversion_qty > 1 ? ' (1:' + u.conversion_qty + ')' : '')">
                                                </option>
                                            </template>
                                        </select>
                                        <input type="number" x-model.number="item.quantity" min="1"
                                            class="w-16 text-center px-2 py-1.5 border border-gray-200 rounded-lg text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-white">
                                        <button @click="removeItemFromTransfer(idx)"
                                            class="text-red-500 hover:text-red-700"><svg class="w-5 h-5" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M6 18L18 6M6 6l12 12"></path>
                                            </svg></button>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Notes
                            (Optional)</label>
                        <textarea x-model="transferForm.notes" rows="2" placeholder="Add notes for this transfer..."
                            class="w-full px-4 py-2.5 border border-gray-200 rounded-xl dark:bg-gray-900 dark:border-gray-700 dark:text-white"></textarea>
                    </div>
                </div>

                <div
                    class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 flex justify-end gap-3 bg-gray-100 dark:bg-gray-800">
                    <button @click="showModal = false"
                        class="px-4 py-2 border border-gray-200 rounded-xl hover:bg-gray-50 dark:border-gray-600 dark:hover:bg-gray-700 dark:text-white">Cancel</button>
                    <button @click="createTransfer()" :disabled="isLoading"
                        class="px-4 py-2 bg-brand-500 text-white rounded-xl hover:bg-brand-600 disabled:opacity-50 flex items-center gap-2">
                        <svg x-show="isLoading" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                            </circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z">
                            </path>
                        </svg>
                        <span x-text="isLoading ? 'Creating...' : 'Create Transfer'">Create Transfer</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- View Detail Modal -->
        <div x-show="viewDetailModal"
            class="fixed inset-0 z-[9999] flex items-center justify-center bg-gray-900/80 backdrop-blur-sm p-4" x-cloak
            x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
            <div
                class="bg-white dark:bg-gray-800 rounded-2xl w-full max-w-3xl max-h-[90vh] overflow-hidden flex flex-col shadow-2xl">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                    <h2 class="text-xl font-bold text-gray-800 dark:text-white">Detail Transfer</h2>
                    <button @click="viewDetailModal = false"
                        class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                            </path>
                        </svg>
                    </button>
                </div>

                <div class="flex-1 overflow-y-auto p-6 bg-gray-50 dark:bg-gray-900/50" x-show="selectedTransfer">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                        <div class="bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-200 dark:border-gray-700">
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Transfer Code</p>
                            <p class="font-bold text-gray-800 dark:text-white" x-text="selectedTransfer?.transfer_number">
                            </p>
                        </div>
                        <div class="bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-200 dark:border-gray-700">
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">From</p>
                            <p class="font-bold text-gray-800 dark:text-white" x-text="selectedTransfer?.from_branch_name">
                            </p>
                        </div>
                        <div class="bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-200 dark:border-gray-700">
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">To</p>
                            <p class="font-bold text-gray-800 dark:text-white" x-text="selectedTransfer?.to_branch_name">
                            </p>
                        </div>
                        <div class="bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-200 dark:border-gray-700">
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Status</p>
                            <span class="px-2.5 py-1 rounded-full text-xs font-medium"
                                :class="getStatusBadge(selectedTransfer?.status).class"
                                x-text="getStatusBadge(selectedTransfer?.status).text"></span>
                        </div>
                    </div>

                    <div
                        class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                        <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-700">
                            <h3 class="font-semibold text-gray-800 dark:text-white">Daftar Barang</h3>
                        </div>
                        <table class="w-full text-sm text-left">
                            <thead class="bg-gray-50 dark:bg-gray-700 text-gray-500 dark:text-gray-400 uppercase text-xs">
                                <tr>
                                    <th class="px-4 py-3">Produk</th>
                                    <th class="px-4 py-3 text-center">Qty Requested</th>
                                    <th class="px-4 py-3 text-center">Qty Approved</th>
                                    <th class="px-4 py-3 text-center">Qty Received</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                <template x-for="item in selectedTransferItems" :key="item.id">
                                    <tr class="dark:text-gray-300">
                                        <td class="px-4 py-3">
                                            <p class="font-medium text-gray-800 dark:text-white" x-text="item.product_name">
                                            </p>
                                            <p class="text-xs text-gray-500 text-mono" x-text="item.sku"></p>
                                        </td>
                                        <td class="px-4 py-3 text-center" x-text="item.quantity_requested"></td>
                                        <td class="px-4 py-3 text-center" x-text="item.quantity_approved || '-'"></td>
                                        <td class="px-4 py-3 text-center" x-text="item.quantity_received || '-'"></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-6" x-show="selectedTransfer?.notes">
                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Notes:</p>
                        <div class="bg-yellow-50 dark:bg-yellow-900/20 p-3 rounded-lg text-sm text-gray-600 dark:text-gray-400 border border-yellow-100 dark:border-yellow-800/30"
                            x-text="selectedTransfer?.notes"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection