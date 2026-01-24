@props([
    'title' => null,
    'variant' => 'default', // default, compact
])

@php
    $cardClasses = 'rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]';
    $paddingClasses = $variant === 'compact' ? 'px-4 py-3' : 'px-6 py-5';
@endphp

<div {{ $attributes->merge(['class' => $cardClasses]) }}>
    @if($title)
        <div class="{{ $paddingClasses }}">
            <h3 class="text-base font-medium text-gray-800 dark:text-white/90">
                {{ $title }}
            </h3>
        </div>
        <div class="border-t border-gray-100 {{ $paddingClasses }} dark:border-gray-800">
            {{ $slot }}
        </div>
    @else
        <div class="{{ $paddingClasses }}">
            {{ $slot }}
        </div>
    @endif
</div>
