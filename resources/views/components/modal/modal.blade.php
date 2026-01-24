@props([
    'title' => 'Modal Title',
    'size' => 'md', // sm, md, lg, xl
])

@php
    $sizeClasses = match($size) {
        'sm' => 'max-w-sm',
        'lg' => 'max-w-lg',
        'xl' => 'max-w-2xl',
        default => 'max-w-md',
    };
@endphp

<div x-data="{ open: false }" class="relative">
    <!-- Modal Trigger Button (via slot) -->
    
    <!-- Modal Backdrop -->
    <div 
        x-show="open"
        x-transition
        @click="open = false"
        class="fixed inset-0 z-40 bg-black/50 dark:bg-black/70"
        style="display: none;"
    ></div>
    
    <!-- Modal Content -->
    <div
        x-show="open"
        x-transition
        @keydown.escape="open = false"
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
        style="display: none;"
    >
        <div class="{{ $sizeClasses }} w-full rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
            <!-- Header -->
            <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4 dark:border-gray-800">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                    {{ $title }}
                </h3>
                <button
                    @click="open = false"
                    class="text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300"
                >
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            
            <!-- Body -->
            <div class="px-6 py-4">
                {{ $slot }}
            </div>
        </div>
    </div>
</div>
