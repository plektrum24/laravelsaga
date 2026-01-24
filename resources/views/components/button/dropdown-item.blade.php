@props([
    'href' => '#',
    'icon' => null,
])

<a
    href="{{ $href }}"
    {{ $attributes->merge([
        'class' => 'menu-dropdown-item menu-dropdown-item-inactive flex items-center gap-3',
    ]) }}
>
    @if($icon)
        <div class="text-gray-500 group-hover:text-gray-700 dark:text-gray-400 dark:group-hover:text-gray-300">
            {{ $icon }}
        </div>
    @endif
    {{ $slot }}
</a>
