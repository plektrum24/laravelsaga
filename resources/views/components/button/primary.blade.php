@props([
    'variant' => 'primary', // primary, secondary, success, error, warning, info
    'size' => 'md', // sm, md, lg
    'disabled' => false,
    'icon' => null,
    'iconPosition' => 'left', // left, right
])

@php
    $baseClasses = 'inline-flex items-center gap-2 font-medium rounded-lg transition shadow-theme-xs focus:outline-none focus:ring-2 focus:ring-offset-2';
    
    $sizeClasses = match($size) {
        'sm' => 'px-3 py-1.5 text-xs',
        'lg' => 'px-6 py-3.5 text-base',
        default => 'px-4 py-2.5 text-sm',
    };
    
    $variantClasses = match($variant) {
        'secondary' => 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 focus:ring-gray-300',
        'success' => 'bg-success-500 text-white hover:bg-success-600 dark:hover:bg-success-600 focus:ring-success-300',
        'error' => 'bg-error-500 text-white hover:bg-error-600 dark:hover:bg-error-600 focus:ring-error-300',
        'warning' => 'bg-warning-500 text-white hover:bg-warning-600 dark:hover:bg-warning-600 focus:ring-warning-300',
        'info' => 'bg-blue-light-500 text-white hover:bg-blue-light-600 dark:hover:bg-blue-light-600 focus:ring-blue-light-300',
        default => 'bg-brand-500 text-white hover:bg-brand-600 dark:hover:bg-brand-600 focus:ring-brand-300',
    };
    
    $disabledClasses = $disabled ? 'opacity-50 cursor-not-allowed' : '';
@endphp

<button 
    {{ $attributes->merge([
        'class' => "$baseClasses $sizeClasses $variantClasses $disabledClasses",
        'disabled' => $disabled,
    ]) }}
>
    @if($icon && $iconPosition === 'left')
        <svg class="w-4 h-4">{{ $icon }}</svg>
    @endif
    
    {{ $slot }}
    
    @if($icon && $iconPosition === 'right')
        <svg class="w-4 h-4">{{ $icon }}</svg>
    @endif
</button>
