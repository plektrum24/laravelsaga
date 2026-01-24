@props([
    'size' => 'md', // xs, sm, md, lg, xl, 2xl
    'src' => null,
    'alt' => 'avatar',
])

@php
    $sizeClasses = match($size) {
        'xs' => 'h-6 w-6 max-w-6',
        'sm' => 'h-8 w-8 max-w-8',
        'lg' => 'h-12 w-12 max-w-12',
        'xl' => 'h-14 w-14 max-w-14',
        '2xl' => 'h-16 w-16 max-w-16',
        default => 'h-10 w-10 max-w-10',
    };
@endphp

<div {{ $attributes->merge(['class' => "relative $sizeClasses rounded-full overflow-hidden"]) }}>
    @if($src)
        <img src="{{ $src }}" alt="{{ $alt }}" class="w-full h-full object-cover">
    @else
        <div class="w-full h-full bg-brand-100 dark:bg-brand-900 flex items-center justify-center">
            <span class="text-brand-700 dark:text-brand-200 font-semibold">
                {{ substr($alt, 0, 1)|upper }}
            </span>
        </div>
    @endif
</div>
