@props([
    'href' => '#',
    'active' => false,
])

@php
    $activeClasses = $active ? 'menu-item-active' : 'menu-item-inactive';
@endphp

<a 
    href="{{ $href }}"
    {{ $attributes->merge([
        'class' => "menu-item $activeClasses"
    ]) }}
>
    {{ $slot }}
</a>
