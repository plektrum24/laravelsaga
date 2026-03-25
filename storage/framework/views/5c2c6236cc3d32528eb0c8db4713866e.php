<?php $__env->startSection('title', 'Stock Transfer Analytics'); ?>

<?php $__env->startSection('content'); ?>
<div x-data="transferAnalytics()" x-init="init()">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Stock Transfer Analytics</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400">Monitor and analyze stock transfers across branches</p>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 border-l-4 border-blue-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Total Transfers</p>
                    <p class="text-3xl font-bold text-gray-800 dark:text-white" x-text="stats.total"></p>
                </div>
                <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 border-l-4 border-purple-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">In Transit</p>
                    <p class="text-3xl font-bold text-purple-600" x-text="stats.in_transit"></p>
                </div>
                <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 border-l-4 border-yellow-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Pending Approval</p>
                    <p class="text-3xl font-bold text-yellow-600" x-text="stats.pending"></p>
                </div>
                <div class="w-12 h-12 bg-yellow-100 dark:bg-yellow-900/30 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 border-l-4 border-green-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Completed</p>
                    <p class="text-3xl font-bold text-green-600" x-text="stats.completed"></p>
                </div>
                <div class="w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm mb-6">
        <div class="border-b border-gray-200 dark:border-gray-700">
            <nav class="flex -mb-px">
                <button @click="activeTab = 'dashboard'"
                    :class="activeTab === 'dashboard' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="w-1/4 py-4 px-1 text-center border-b-2 font-medium text-sm">
                    Dashboard
                </button>
                <button @click="activeTab = 'in-transit'"
                    :class="activeTab === 'in-transit' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="w-1/4 py-4 px-1 text-center border-b-2 font-medium text-sm">
                    In Transit
                </button>
                <button @click="activeTab = 'history'"
                    :class="activeTab === 'history' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="w-1/4 py-4 px-1 text-center border-b-2 font-medium text-sm">
                    History
                </button>
                <button @click="activeTab = 'comparison'"
                    :class="activeTab === 'comparison' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="w-1/4 py-4 px-1 text-center border-b-2 font-medium text-sm">
                    Branch Comparison
                </button>
            </nav>
        </div>

        <!-- Dashboard Tab -->
        <div x-show="activeTab === 'dashboard'" class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Status Distribution -->
                <div>
                    <h3 class="font-bold text-lg mb-4">Status Distribution</h3>
                    <div class="space-y-3">
                        <template x-for="item in statusDistribution" :key="item.status">
                            <div class="flex items-center justify-between">
                                <span class="px-2 py-1 text-xs rounded-full font-semibold capitalize"
                                    :class="{
                                        'bg-gray-100 text-gray-700': item.status === 'draft',
                                        'bg-yellow-100 text-yellow-700': item.status === 'pending_approval',
                                        'bg-blue-100 text-blue-700': item.status === 'approved',
                                        'bg-purple-100 text-purple-700': item.status === 'in_transit',
                                        'bg-green-100 text-green-700': item.status === 'received',
                                        'bg-red-100 text-red-700': item.status === 'cancelled'
                                    }"
                                    x-text="formatStatus(item.status) + ' (' + item.count + ')'">
                                </span>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Recent Transfers -->
                <div>
                    <h3 class="font-bold text-lg mb-4">Recent Transfers</h3>
                    <div class="space-y-2">
                        <template x-for="transfer in recentTransfers" :key="transfer.id">
                            <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                                <div>
                                    <p class="font-semibold text-brand-600" x-text="transfer.transfer_number"></p>
                                    <p class="text-xs text-gray-500" x-text="transfer.from_branch?.name + ' → ' + transfer.to_branch?.name"></p>
                                </div>
                                <span class="px-2 py-1 text-xs rounded-full font-semibold capitalize"
                                    :class="{
                                        'bg-gray-100 text-gray-700': transfer.status === 'draft',
                                        'bg-yellow-100 text-yellow-700': transfer.status === 'pending_approval',
                                        'bg-blue-100 text-blue-700': transfer.status === 'approved',
                                        'bg-purple-100 text-purple-700': transfer.status === 'in_transit',
                                        'bg-green-100 text-green-700': transfer.status === 'received',
                                        'bg-red-100 text-red-700': transfer.status === 'cancelled'
                                    }"
                                    x-text="formatStatus(transfer.status)">
                                </span>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <!-- In Transit Tab -->
        <div x-show="activeTab === 'in-transit'" class="p-6">
            <h3 class="font-bold text-lg mb-4">In-Transit Shipments</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-4 py-2 text-left">Transfer #</th>
                            <th class="px-4 py-2 text-left">From</th>
                            <th class="px-4 py-2 text-left">To</th>
                            <th class="px-4 py-2 text-left">Items</th>
                            <th class="px-4 py-2 text-left">Shipped Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="transfer in inTransitData" :key="transfer.id">
                            <tr class="border-b dark:border-gray-700">
                                <td class="px-4 py-2 font-semibold text-brand-600" x-text="transfer.transfer_number"></td>
                                <td class="px-4 py-2" x-text="transfer.from_branch?.name"></td>
                                <td class="px-4 py-2" x-text="transfer.to_branch?.name"></td>
                                <td class="px-4 py-2" x-text="transfer.total_items"></td>
                                <td class="px-4 py-2" x-text="formatDate(transfer.shipped_date)"></td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- History Tab -->
        <div x-show="activeTab === 'history'" class="p-6">
            <h3 class="font-bold text-lg mb-4">Transfer History</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-4 py-2 text-left">Transfer #</th>
                            <th class="px-4 py-2 text-left">From → To</th>
                            <th class="px-4 py-2 text-left">Status</th>
                            <th class="px-4 py-2 text-left">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="transfer in historyData" :key="transfer.id">
                            <tr class="border-b dark:border-gray-700">
                                <td class="px-4 py-2 font-semibold text-brand-600" x-text="transfer.transfer_number"></td>
                                <td class="px-4 py-2">
                                    <div x-text="transfer.from_branch?.name + ' → ' + transfer.to_branch?.name"></div>
                                </td>
                                <td class="px-4 py-2">
                                    <span class="px-2 py-1 text-xs rounded-full font-semibold capitalize"
                                        :class="{
                                            'bg-gray-100 text-gray-700': transfer.status === 'draft',
                                            'bg-yellow-100 text-yellow-700': transfer.status === 'pending_approval',
                                            'bg-blue-100 text-blue-700': transfer.status === 'approved',
                                            'bg-purple-100 text-purple-700': transfer.status === 'in_transit',
                                            'bg-green-100 text-green-700': transfer.status === 'received',
                                            'bg-red-100 text-red-700': transfer.status === 'cancelled'
                                        }"
                                        x-text="formatStatus(transfer.status)">
                                    </span>
                                </td>
                                <td class="px-4 py-2" x-text="formatDate(transfer.created_at)"></td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Branch Comparison Tab -->
        <div x-show="activeTab === 'comparison'" class="p-6">
            <h3 class="font-bold text-lg mb-4">Branch Stock Flow Comparison</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-4 py-2 text-left">Branch</th>
                            <th class="px-4 py-2 text-right">Transfers From</th>
                            <th class="px-4 py-2 text-right">Transfers To</th>
                            <th class="px-4 py-2 text-right">Items From</th>
                            <th class="px-4 py-2 text-right">Items To</th>
                            <th class="px-4 py-2 text-right">Net Flow</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="branch in branchComparison" :key="branch.branch.id">
                            <tr class="border-b dark:border-gray-700">
                                <td class="px-4 py-2 font-semibold" x-text="branch.branch.name"></td>
                                <td class="px-4 py-2 text-right" x-text="branch.transfers_from"></td>
                                <td class="px-4 py-2 text-right" x-text="branch.transfers_to"></td>
                                <td class="px-4 py-2 text-right" x-text="formatNumber(branch.items_from)"></td>
                                <td class="px-4 py-2 text-right" x-text="formatNumber(branch.items_to)"></td>
                                <td class="px-4 py-2 text-right font-bold"
                                    :class="branch.net_flow >= 0 ? 'text-green-600' : 'text-red-600'"
                                    x-text="(branch.net_flow >= 0 ? '+' : '') + formatNumber(branch.net_flow)"></td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function transferAnalytics() {
    return {
        activeTab: 'dashboard',
        stats: {
            total: 0,
            in_transit: 0,
            pending: 0,
            completed: 0
        },
        statusDistribution: [],
        recentTransfers: [],
        inTransitData: [],
        historyData: [],
        branchComparison: [],

        async init() {
            await this.loadDashboard();
        },

        async loadDashboard() {
            const token = localStorage.getItem('saga_token');
            try {
                const res = await fetch('/api/stock-transfers/analytics/dashboard', {
                    headers: { 'Authorization': 'Bearer ' + token }
                });
                const data = await res.json();
                if (data.success) {
                    this.stats = data.data.summary;
                    this.statusDistribution = data.data.status_distribution;
                    this.recentTransfers = data.data.recent_transfers;
                }
            } catch (e) {
                console.error('Load dashboard error:', e);
            }
        },

        async loadInTransit() {
            const token = localStorage.getItem('saga_token');
            try {
                const res = await fetch('/api/stock-transfers/reports/in-transit', {
                    headers: { 'Authorization': 'Bearer ' + token }
                });
                const data = await res.json();
                if (data.success) {
                    this.inTransitData = data.data;
                }
            } catch (e) {
                console.error('Load in-transit error:', e);
            }
        },

        async loadHistory() {
            const token = localStorage.getItem('saga_token');
            try {
                const res = await fetch('/api/stock-transfers/reports/history', {
                    headers: { 'Authorization': 'Bearer ' + token }
                });
                const data = await res.json();
                if (data.success) {
                    this.historyData = data.data.data;
                }
            } catch (e) {
                console.error('Load history error:', e);
            }
        },

        async loadBranchComparison() {
            const token = localStorage.getItem('saga_token');
            try {
                const res = await fetch('/api/stock-transfers/reports/branch-comparison', {
                    headers: { 'Authorization': 'Bearer ' + token }
                });
                const data = await res.json();
                if (data.success) {
                    this.branchComparison = data.data;
                }
            } catch (e) {
                console.error('Load branch comparison error:', e);
            }
        },

        formatDate(date) {
            if (!date) return '-';
            return new Date(date).toLocaleDateString('id-ID', { year: 'numeric', month: 'short', day: 'numeric' });
        },

        formatStatus(status) {
            return status.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase());
        },

        formatNumber(num) {
            return new Intl.NumberFormat('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 }).format(num);
        }
    }
}
</script>

<!-- Load data when tab changes -->
<script>
document.addEventListener('alpine:init', () => {
    const observer = new MutationObserver(() => {
        const inTransitTab = document.querySelector('[x-show="activeTab === \'in-transit\'"]');
        const historyTab = document.querySelector('[x-show="activeTab === \'history\'"]');
        const comparisonTab = document.querySelector('[x-show="activeTab === \'comparison\'"]');
        
        if (inTransitTab && !inTransitTab.style.display) {
            // Tab is visible, load data
        }
    });
    
    observer.observe(document.body, { childList: true, subtree: true });
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\Project App\laravelsaga\resources\views/pages/inventory/stock-transfer-analytics.blade.php ENDPATH**/ ?>