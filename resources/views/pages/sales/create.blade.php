@extends('layouts.app')

@section('title', 'New Sale | SAGA POS')

@section('content')
    <div class="mb-6">
        <a href="{{ route('sales.index') }}" class="text-brand-500 hover:text-brand-600 flex items-center gap-1 mb-4">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Back to Sales
        </a>
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Create New Sale</h1>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2">
            <x-card.card title="Sale Items">
                <div class="space-y-4">
                    <x-form.input label="Search Product" placeholder="Type product name or SKU..." />
                    <!-- Items will be added here -->
                    <div class="text-center text-gray-400 py-8">
                        <p>No items added yet</p>
                    </div>
                </div>
            </x-card.card>
        </div>
        <div>
            <x-card.card title="Sale Summary">
                <div class="space-y-4">
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Subtotal:</span>
                        <span class="font-semibold">Rp 0</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Discount:</span>
                        <span class="font-semibold">Rp 0</span>
                    </div>
                    <div class="border-t border-gray-200 dark:border-gray-700 pt-4 flex justify-between text-lg font-bold">
                        <span>Total:</span>
                        <span>Rp 0</span>
                    </div>
                    <button
                        class="w-full rounded-lg bg-brand-500 px-4 py-2.5 text-white font-semibold hover:bg-brand-600 mt-6">
                        Complete Sale
                    </button>
                </div>
            </x-card.card>
        </div>
    </div>
@endsection
