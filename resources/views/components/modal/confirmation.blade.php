@props([
    'title' => 'Confirm',
    'message' => 'Are you sure?',
    'confirmText' => 'Confirm',
    'cancelText' => 'Cancel',
    'variant' => 'warning', // warning, error, success, info
])

@php
    $variantClasses = match($variant) {
        'error' => 'text-error-600 dark:text-error-500',
        'success' => 'text-success-600 dark:text-success-500',
        'info' => 'text-blue-light-600 dark:text-blue-light-500',
        default => 'text-warning-600 dark:text-warning-500',
    };
@endphp

<div x-data="{ open: false }" class="relative">
    <!-- Confirmation Dialog -->
    <div 
        x-show="open"
        x-transition
        @click="open = false"
        class="fixed inset-0 z-40 bg-black/50 dark:bg-black/70"
        style="display: none;"
    ></div>
    
    <div
        x-show="open"
        x-transition
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
        style="display: none;"
    >
        <div class="max-w-sm w-full rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900 shadow-lg">
            <!-- Icon -->
            <div class="flex justify-center pt-6">
                <div class="rounded-full {{ $variantClasses }} bg-opacity-10 p-4 {{ str_replace('text-', 'bg-', $variantClasses) }}/10">
                    <svg class="h-8 w-8 {{ $variantClasses }}" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                </div>
            </div>
            
            <!-- Content -->
            <div class="px-6 py-4 text-center">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $title }}</h3>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">{{ $message }}</p>
            </div>
            
            <!-- Actions -->
            <div class="flex gap-3 border-t border-gray-100 bg-gray-50 px-6 py-4 dark:border-gray-800 dark:bg-gray-800/50">
                <button
                    @click="open = false"
                    class="flex-1 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600"
                >
                    {{ $cancelText }}
                </button>
                <button
                    @click="$emit('confirm'); open = false"
                    class="flex-1 rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white transition hover:bg-brand-600"
                >
                    {{ $confirmText }}
                </button>
            </div>
        </div>
    </div>
</div>
