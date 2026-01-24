@props([
    'label' => null,
    'name' => null,
    'checked' => false,
    'error' => null,
    'disabled' => false,
    'value' => null,
])

<div class="mb-4 flex items-center">
    <label class="relative flex cursor-pointer items-center gap-3">
        <input
            {{ $attributes->merge([
                'type' => 'radio',
                'name' => $name,
                'id' => $name,
                'value' => $value,
                'class' => 'h-5 w-5 cursor-pointer rounded-full border border-gray-300 checked:border-brand-500 checked:bg-brand-500 dark:border-gray-600 checked:dark:border-brand-500',
                'disabled' => $disabled,
            ]) }}
            @checked($checked)
        />
        @if($label)
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $label }}</span>
        @endif
    </label>
    @if($error)
        <p class="mt-1 text-xs text-error-500 dark:text-error-400">{{ $error }}</p>
    @endif
</div>
