@props([
    'variant' => 'primary', // primary, success, error, warning, info
])

@php
    $variantClasses = match($variant) {
        'success' => 'inline-flex items-center justify-center gap-1 rounded-full bg-success-50 px-2.5 py-0.5 text-sm font-medium text-success-600 dark:bg-success-500/15 dark:text-success-500',
        'error' => 'inline-flex items-center justify-center gap-1 rounded-full bg-error-50 px-2.5 py-0.5 text-sm font-medium text-error-600 dark:bg-error-500/15 dark:text-error-500',
        'warning' => 'inline-flex items-center justify-center gap-1 rounded-full bg-warning-50 px-2.5 py-0.5 text-sm font-medium text-warning-600 dark:bg-warning-500/15 dark:text-warning-500',
        'info' => 'inline-flex items-center justify-center gap-1 rounded-full bg-blue-light-50 px-2.5 py-0.5 text-sm font-medium text-blue-light-600 dark:bg-blue-light-500/15 dark:text-blue-light-500',
        default => 'inline-flex items-center justify-center gap-1 rounded-full bg-brand-50 px-2.5 py-0.5 text-sm font-medium text-brand-500 dark:bg-brand-500/15 dark:text-brand-400',
    };
@endphp

<span {{ $attributes->merge(['class' => $variantClasses]) }}>
    {{ $slot }}
</span>
