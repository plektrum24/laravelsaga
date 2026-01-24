@props([
    'active' => false,
    'badge' => null,
    'icon' => null,
])

@php
    $itemClasses = $active ? 'menu-item-active' : 'menu-item-inactive';
@endphp

<div class="group menu-item {{ $itemClasses }}">
    @if($icon)
        <div class="menu-item-icon" :class="{ 'menu-item-icon-active': $active, 'menu-item-icon-inactive': !$active }">
            {{ $icon }}
        </div>
    @endif
    
    <span class="flex-1">
        {{ $slot }}
    </span>
    
    @if($badge)
        <x-badge.badge variant="primary">
            {{ $badge }}
        </x-badge.badge>
    @endif
    
    <svg class="menu-item-arrow" :class="{ 'menu-item-arrow-active': $active, 'menu-item-arrow-inactive': !$active }" width="20" height="20" viewBox="0 0 20 20" fill="none">
        <path d="M7.5 7.5L10 10L12.5 7.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
    </svg>
</div>
