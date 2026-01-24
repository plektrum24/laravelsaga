@props([
    'label' => null,
    'name' => null,
    'type' => 'text',
    'placeholder' => null,
    'value' => null,
    'error' => null,
    'required' => false,
    'disabled' => false,
])

@php
    $inputClasses = 'w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-gray-900 placeholder-gray-500 transition outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white dark:placeholder-gray-400 dark:focus:border-brand-500';
    
    $errorClasses = $error ? 'border-error-500 focus:border-error-500 focus:ring-error-500' : '';
@endphp

<div class="mb-4">
    @if($label)
        <label 
            for="{{ $name }}" 
            class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300"
        >
            {{ $label }}
            @if($required)
                <span class="text-error-500">*</span>
            @endif
        </label>
    @endif
    
    <input 
        {{ $attributes->merge([
            'class' => "$inputClasses $errorClasses",
            'type' => $type,
            'name' => $name,
            'id' => $name,
            'placeholder' => $placeholder,
            'value' => $value,
            'disabled' => $disabled,
            'required' => $required,
        ]) }}
    />
    
    @if($error)
        <p class="mt-1 text-xs text-error-500 dark:text-error-400">{{ $error }}</p>
    @endif
</div>
