@props([
    'type' => 'success', // success, error, warning, info
    'dismissible' => true,
    'title' => null,
])

@php
    $variantClasses = match($type) {
        'error' => 'border-error-200 bg-error-50 dark:border-error-500/20 dark:bg-error-500/10',
        'warning' => 'border-warning-200 bg-warning-50 dark:border-warning-500/20 dark:bg-warning-500/10',
        'info' => 'border-blue-light-200 bg-blue-light-50 dark:border-blue-light-500/20 dark:bg-blue-light-500/10',
        default => 'border-success-200 bg-success-50 dark:border-success-500/20 dark:bg-success-500/10',
    };
    
    $textClasses = match($type) {
        'error' => 'text-error-700 dark:text-error-200',
        'warning' => 'text-warning-700 dark:text-warning-200',
        'info' => 'text-blue-light-700 dark:text-blue-light-200',
        default => 'text-success-700 dark:text-success-200',
    };
@endphp

<div 
    @if($dismissible) 
        x-data="{ open: true }"
        x-show="open"
        x-transition
    @endif
    {{ $attributes->merge([
        'class' => "rounded-lg border $variantClasses p-4 sm:p-6"
    ]) }}
    role="alert"
>
    <div class="flex items-start justify-between">
        <div>
            @if($title)
                <h4 class="font-semibold {{ $textClasses }} mb-1">{{ $title }}</h4>
            @endif
            <p class="{{ $textClasses }} text-sm">
                {{ $slot }}
            </p>
        </div>
        @if($dismissible)
            <button 
                @click="open = false"
                class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
                type="button"
            >
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
            </button>
        @endif
    </div>
</div>
