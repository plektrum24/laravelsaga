@extends('layouts.app')

@section('title', 'Support Tickets | Tenant Portal')

@section('content')
<div x-data="supportTickets()" x-init="init()" class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Support Tickets</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Get help from our support team</p>
        </div>
        <div class="flex gap-2">
            <button @click="showCreateModal = true" class="btn-primary inline-flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                New Ticket
            </button>
            <a href="/tenant-portal" class="btn-secondary">← Back to Portal</a>
        </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Open</p>
            <h3 class="text-2xl font-bold text-blue-500 mt-1" x-text="stats.open">0</h3>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">In Progress</p>
            <h3 class="text-2xl font-bold text-purple-500 mt-1" x-text="stats.in_progress">0</h3>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Resolved</p>
            <h3 class="text-2xl font-bold text-green-500 mt-1" x-text="stats.resolved">0</h3>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total</p>
            <h3 class="text-2xl font-bold text-gray-800 dark:text-white mt-1" x-text="stats.total">0</h3>
        </div>
    </div>

    <!-- Filters -->
    <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="flex gap-4 flex-wrap">
            <select x-model="filters.status" @change="fetchTickets" class="rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                <option value="">All Status</option>
                <option value="open">Open</option>
                <option value="in_progress">In Progress</option>
                <option value="waiting_customer">Waiting Customer</option>
                <option value="resolved">Resolved</option>
            </select>
            <select x-model="filters.priority" @change="fetchTickets" class="rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                <option value="">All Priority</option>
                <option value="low">Low</option>
                <option value="medium">Medium</option>
                <option value="high">High</option>
                <option value="urgent">Urgent</option>
            </select>
        </div>
    </div>

    <!-- Tickets List -->
    <div class="grid grid-cols-1 gap-4">
        <template x-for="ticket in tickets.data" :key="ticket.id">
            <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03] hover:shadow-lg transition-shadow">
                <div class="flex items-start justify-between mb-4">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-2">
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-white" x-text="ticket.subject"></h3>
                            <span class="px-2 py-1 text-xs rounded-full capitalize"
                                  :class="{
                                      'bg-blue-100 text-blue-700': ticket.status === 'open',
                                      'bg-purple-100 text-purple-700': ticket.status === 'in_progress',
                                      'bg-orange-100 text-orange-700': ticket.status === 'waiting_customer',
                                      'bg-green-100 text-green-700': ticket.status === 'resolved'
                                  }"
                                  x-text="ticket.status">
                            </span>
                            <span class="px-2 py-1 text-xs rounded-full capitalize"
                                  :class="{
                                      'bg-gray-100 text-gray-700': ticket.priority === 'low',
                                      'bg-blue-100 text-blue-700': ticket.priority === 'medium',
                                      'bg-orange-100 text-orange-700': ticket.priority === 'high',
                                      'bg-red-100 text-red-700': ticket.priority === 'urgent'
                                  }"
                                  x-text="ticket.priority">
                            </span>
                        </div>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-2" x-text="ticket.message"></p>
                        <div class="flex items-center gap-4 text-xs text-gray-500">
                            <span>Created: <span x-text="formatDate(ticket.created_at)"></span></span>
                            <span x-show="ticket.assigned_admin">Assigned to: <span x-text="ticket.assigned_admin.name"></span></span>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <button @click="viewTicket(ticket)" class="text-brand-500 hover:text-brand-600 text-sm">View</button>
                        <button x-show="ticket.status === 'resolved'" @click="reopenTicket(ticket)" class="text-orange-500 hover:text-orange-600 text-sm">Reopen</button>
                    </div>
                </div>
                <!-- Latest Message Preview -->
                <div x-show="ticket.messages && ticket.messages.length > 0" class="border-t border-gray-200 dark:border-gray-800 pt-4 mt-4">
                    <p class="text-xs text-gray-500 mb-2">Latest Messages:</p>
                    <template x-for="message in ticket.messages.slice(-2)" :key="message.id">
                        <div class="flex items-start gap-2 mb-2 p-2 rounded-lg" :class="message.is_admin_message ? 'bg-blue-50 dark:bg-blue-900/20' : 'bg-gray-50 dark:bg-gray-800'">
                            <div class="flex-1">
                                <p class="text-sm text-gray-700 dark:text-gray-300" x-text="message.message"></p>
                                <p class="text-xs text-gray-500 mt-1" x-text="formatDate(message.created_at)"></p>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </template>
        <div x-show="tickets.data.length === 0" class="text-center py-12 rounded-2xl border border-gray-200 dark:border-gray-800">
            <p class="text-gray-500 dark:text-gray-400">No tickets found</p>
        </div>
    </div>
</div>

<!-- Create Ticket Modal -->
<div x-show="showCreateModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75" @click="showCreateModal = false"></div>
        <div class="relative bg-white dark:bg-gray-900 rounded-2xl p-6 max-w-lg w-full shadow-xl">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">Create Support Ticket</h3>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Subject</label>
                    <input type="text" x-model="newTicket.subject" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white" placeholder="Brief description of your issue">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Category</label>
                    <select x-model="newTicket.category" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        <option value="Technical">Technical Issue</option>
                        <option value="Billing">Billing Question</option>
                        <option value="Feature Request">Feature Request</option>
                        <option value="General">General Inquiry</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Priority</label>
                    <select x-model="newTicket.priority" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        <option value="low">Low</option>
                        <option value="medium" selected>Medium</option>
                        <option value="high">High</option>
                        <option value="urgent">Urgent</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Message</label>
                    <textarea x-model="newTicket.message" rows="4" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white" placeholder="Describe your issue in detail..."></textarea>
                </div>
            </div>
            <div class="flex justify-end gap-2 mt-6">
                <button @click="showCreateModal = false" class="btn-secondary">Cancel</button>
                <button @click="createTicket" class="btn-primary">Create Ticket</button>
            </div>
        </div>
    </div>
</div>

<!-- View Ticket Modal -->
<div x-show="showViewModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75" @click="showViewModal = false"></div>
        <div class="relative bg-white dark:bg-gray-900 rounded-2xl p-6 max-w-2xl w-full max-h-[80vh] overflow-y-auto shadow-xl">
            <div x-show="selectedTicket">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white" x-text="selectedTicket?.subject"></h3>
                    <button @click="showViewModal = false" class="text-gray-500 hover:text-gray-700">✕</button>
                </div>
                
                <!-- Ticket Info -->
                <div class="flex gap-2 mb-4">
                    <span class="px-2 py-1 text-xs rounded-full capitalize"
                          :class="{
                              'bg-blue-100 text-blue-700': selectedTicket?.status === 'open',
                              'bg-purple-100 text-purple-700': selectedTicket?.status === 'in_progress',
                              'bg-green-100 text-green-700': selectedTicket?.status === 'resolved'
                          }"
                          x-text="selectedTicket?.status">
                    </span>
                    <span class="px-2 py-1 text-xs rounded-full capitalize"
                          :class="{
                              'bg-gray-100 text-gray-700': selectedTicket?.priority === 'low',
                              'bg-blue-100 text-blue-700': selectedTicket?.priority === 'medium',
                              'bg-red-100 text-red-700': selectedTicket?.priority === 'urgent'
                          }"
                          x-text="selectedTicket?.priority">
                    </span>
                </div>

                <!-- Messages -->
                <div class="space-y-4 mb-4 max-h-96 overflow-y-auto">
                    <template x-for="message in selectedTicket?.messages" :key="message.id">
                        <div class="flex items-start gap-3 p-3 rounded-lg" :class="message.is_admin_message ? 'bg-blue-50 dark:bg-blue-900/20' : 'bg-gray-50 dark:bg-gray-800'">
                            <div class="w-8 h-8 rounded-full bg-brand-500 flex items-center justify-center text-white text-sm font-bold" x-text="message.is_admin_message ? 'S' : 'M'"></div>
                            <div class="flex-1">
                                <p class="text-sm text-gray-700 dark:text-gray-300" x-text="message.message"></p>
                                <p class="text-xs text-gray-500 mt-1" x-text="formatDate(message.created_at)"></p>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Reply Input -->
                <div class="border-t border-gray-200 dark:border-gray-800 pt-4">
                    <textarea x-model="replyMessage" rows="3" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white mb-2" placeholder="Type your reply..."></textarea>
                    <button @click="sendReply" class="btn-primary">Send Reply</button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function supportTickets() {
    return {
        tickets: { data: [], total: 0 },
        stats: { open: 0, in_progress: 0, resolved: 0, total: 0 },
        filters: { status: '', priority: '' },
        showCreateModal: false,
        showViewModal: false,
        selectedTicket: null,
        newTicket: { subject: '', category: 'Technical', priority: 'medium', message: '' },
        replyMessage: '',

        async init() {
            await this.fetchTickets();
        },

        async fetchTickets() {
            try {
                const token = localStorage.getItem('saga_token');
                const params = new URLSearchParams({ status: this.filters.status, priority: this.filters.priority });
                const response = await fetch(`/api/tenant/tickets?${params}`, {
                    headers: { 'Authorization': 'Bearer ' + token }
                });
                const result = await response.json();
                if (result.success) {
                    this.tickets = result.data;
                    this.calculateStats();
                }
            } catch (error) {
                console.error('Error fetching tickets:', error);
            }
        },

        calculateStats() {
            this.stats.total = this.tickets.total || 0;
            this.stats.open = this.tickets.data.filter(t => t.status === 'open').length;
            this.stats.in_progress = this.tickets.data.filter(t => t.status === 'in_progress').length;
            this.stats.resolved = this.tickets.data.filter(t => t.status === 'resolved').length;
        },

        async createTicket() {
            try {
                const token = localStorage.getItem('saga_token');
                const response = await fetch('/api/tenant/tickets', {
                    method: 'POST',
                    headers: {
                        'Authorization': 'Bearer ' + token,
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(this.newTicket)
                });
                const result = await response.json();
                if (result.success) {
                    alert('Ticket created successfully');
                    this.showCreateModal = false;
                    this.newTicket = { subject: '', category: 'Technical', priority: 'medium', message: '' };
                    await this.fetchTickets();
                } else {
                    alert(result.message || 'Failed to create ticket');
                }
            } catch (error) {
                console.error('Error creating ticket:', error);
                alert('An error occurred. Please try again.');
            }
        },

        viewTicket(ticket) {
            this.selectedTicket = ticket;
            this.showViewModal = true;
        },

        async sendReply() {
            if (!this.replyMessage.trim()) return;
            try {
                const token = localStorage.getItem('saga_token');
                const response = await fetch(`/api/tenant/tickets/${this.selectedTicket.id}/reply`, {
                    method: 'POST',
                    headers: {
                        'Authorization': 'Bearer ' + token,
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ message: this.replyMessage })
                });
                const result = await response.json();
                if (result.success) {
                    this.replyMessage = '';
                    await this.fetchTickets();
                    this.selectedTicket = this.tickets.data.find(t => t.id === this.selectedTicket.id);
                } else {
                    alert(result.message || 'Failed to send reply');
                }
            } catch (error) {
                console.error('Error sending reply:', error);
                alert('An error occurred. Please try again.');
            }
        },

        async reopenTicket(ticket) {
            if (!confirm('Are you sure you want to reopen this ticket?')) return;
            try {
                const token = localStorage.getItem('saga_token');
                const response = await fetch(`/api/tenant/tickets/${ticket.id}/reopen`, {
                    method: 'POST',
                    headers: { 'Authorization': 'Bearer ' + token }
                });
                const result = await response.json();
                if (result.success) {
                    alert('Ticket reopened');
                    await this.fetchTickets();
                }
            } catch (error) {
                console.error('Error reopening ticket:', error);
            }
        },

        formatDate(dateString) {
            if (!dateString) return 'N/A';
            return new Date(dateString).toLocaleDateString('id-ID', {
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
@endpush
@endsection
