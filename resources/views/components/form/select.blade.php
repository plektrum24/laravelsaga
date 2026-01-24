@props([
    'label' => null,
    'name' => null,
    'options' => [],
    'value' => null,
    'error' => null,
    'required' => false,
    'disabled' => false,
    'placeholder' => 'Select an option',
])

@php
    $selectClasses = 'w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-gray-900 transition outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white dark:focus:border-brand-500';
    
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
    
    <select
        {{ $attributes->merge([
            'class' => "$selectClasses $errorClasses",
            'name' => $name,
            'id' => $name,
            'disabled' => $disabled,
            'required' => $required,
        ]) }}
    >
        <option value="">{{ $placeholder }}</option>
        @foreach($options as $optionValue => $optionLabel)
            <option value="{{ $optionValue }}" @selected($optionValue == $value)>
                {{ $optionLabel }}
            </option>
        @endforeach
    </select>
    
    @if($error)
        <p class="mt-1 text-xs text-error-500 dark:text-error-400">{{ $error }}</p>
    @endif
</div>
