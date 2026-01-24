@props([
    'variant' => 'primary', // primary, secondary
    'isOpen' => false,
])

<div class="relative">
    <!-- Trigger (usually a button or link) -->
    <button
        @click="open = !open"
        {{ $attributes->merge(['class' => 'inline-flex items-center gap-2']) }}
    >
        {{ $slot }}
    </button>
    
    <!-- Dropdown Menu -->
    <div
        x-show="open"
        @click.outside="open = false"
        x-transition
        class="absolute right-0 mt-2 w-48 rounded-lg border border-gray-200 bg-white shadow-lg dark:border-gray-800 dark:bg-gray-900"
        style="display: none; z-index: 50;"
    >
        {{ $slot }}
    </div>
</div>
