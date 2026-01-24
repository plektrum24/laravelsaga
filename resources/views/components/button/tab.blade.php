@props([
    'type' => 'primary', // primary, secondary, success, error, warning
    'active' => false,
    'icon' => null,
])

@php
    $tabClasses = match($type) {
        'secondary' => 'text-gray-600 border-transparent hover:border-gray-300 dark:text-gray-400 dark:hover:border-gray-600',
        'success' => 'text-success-600 border-transparent hover:border-success-300 dark:text-success-500 dark:hover:border-success-600',
        'error' => 'text-error-600 border-transparent hover:border-error-300 dark:text-error-500 dark:hover:border-error-600',
        'warning' => 'text-warning-600 border-transparent hover:border-warning-300 dark:text-warning-500 dark:hover:border-warning-600',
        default => 'text-brand-600 border-transparent hover:border-brand-300 dark:text-brand-500 dark:hover:border-brand-600',
    };
    
    $activeClasses = $active 
        ? "border-b-2 border-{$type}-500 text-{$type}-600 dark:text-{$type}-500" 
        : $tabClasses;
@endphp

<button
    {{ $attributes->merge([
        'class' => "inline-flex items-center gap-2 border-b-2 px-4 py-3 font-medium transition $activeClasses",
        'role' => 'tab',
    ]) }}
    :aria-selected="active"
>
    @if($icon)
        <svg class="h-5 w-5">{{ $icon }}</svg>
    @endif
    {{ $slot }}
</button>
