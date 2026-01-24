@extends('layouts.app')

@section('title', 'Add Inventory | SAGA POS')

@section('content')
    <div class="mb-6">
        <a href="{{ route('inventory') }}" class="text-brand-500 hover:text-brand-600 flex items-center gap-1 mb-4">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Back to Inventory
        </a>
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Add New Product</h1>
    </div>

    <x-card.card>
        <form class="space-y-6">
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <x-form.input name="product_name" label="Product Name" placeholder="Enter product name" />
                <x-form.input name="sku" label="SKU" placeholder="Enter SKU" />
                <x-form.input name="category" label="Category" placeholder="Select category" />
                <x-form.input name="price" label="Price" type="number" placeholder="Enter price" />
                <x-form.input name="cost" label="Cost Price" type="number" placeholder="Enter cost price" />
                <x-form.input name="stock" label="Initial Stock" type="number" placeholder="Enter stock quantity" />
            </div>

            <x-form.textarea name="description" label="Description" placeholder="Enter product description" rows="4" />

            <div class="flex gap-4">
                <button type="submit"
                    class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-6 py-2.5 text-white hover:bg-brand-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Save Product
                </button>
                <a href="{{ route('inventory') }}"
                    class="inline-flex items-center gap-2 rounded-lg border border-gray-200 px-6 py-2.5 text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">
                    Cancel
                </a>
            </div>
        </form>
    </x-card.card>
@endsection
