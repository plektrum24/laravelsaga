@extends('layouts.app')

@section('title', 'Sales | SAGA POS')

@section('content')
    <div class="mb-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Sales Orders</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">Manage and track all sales transactions</p>
            </div>
            <a href="{{ route('sales.create') }}"
                class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-white hover:bg-brand-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                New Sale
            </a>
        </div>
    </div>

    <x-card.card>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="border-b border-gray-200 dark:border-gray-700">
                    <tr class="text-gray-600 dark:text-gray-400">
                        <th class="text-left py-3 px-4">Order #</th>
                        <th class="text-left py-3 px-4">Customer</th>
                        <th class="text-right py-3 px-4">Amount</th>
                        <th class="text-left py-3 px-4">Date</th>
                        <th class="text-center py-3 px-4">Status</th>
                        <th class="text-center py-3 px-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="border-b border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-800">
                        <td class="py-3 px-4 font-medium text-gray-900 dark:text-white">#ORD-001</td>
                        <td class="py-3 px-4 text-gray-700 dark:text-gray-300">John Doe</td>
                        <td class="py-3 px-4 text-right text-gray-900 dark:text-white font-semibold">Rp 750.000</td>
                        <td class="py-3 px-4 text-gray-700 dark:text-gray-300">2024-01-15</td>
                        <td class="py-3 px-4 text-center">
                            <x-badge.badge variant="success">Completed</x-badge.badge>
                        </td>
                        <td class="py-3 px-4 text-center">
                            <button class="text-brand-500 hover:text-brand-600">View</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </x-card.card>
@endsection
