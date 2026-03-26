<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'label' => null,
    'name' => null,
    'type' => 'text',
    'placeholder' => null,
    'value' => null,
    'error' => null,
    'required' => false,
    'disabled' => false,
]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter(([
    'label' => null,
    'name' => null,
    'type' => 'text',
    'placeholder' => null,
    'value' => null,
    'error' => null,
    'required' => false,
    'disabled' => false,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $inputClasses = 'w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-gray-900 placeholder-gray-500 transition outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white dark:placeholder-gray-400 dark:focus:border-brand-500';
    
    $errorClasses = $error ? 'border-error-500 focus:border-error-500 focus:ring-error-500' : '';
?>

<div class="mb-4">
    <?php if($label): ?>
        <label 
            for="<?php echo e($name); ?>" 
            class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300"
        >
            <?php echo e($label); ?>

            <?php if($required): ?>
                <span class="text-error-500">*</span>
            <?php endif; ?>
        </label>
    <?php endif; ?>
    
    <input 
        <?php echo e($attributes->merge([
            'class' => "$inputClasses $errorClasses",
            'type' => $type,
            'name' => $name,
            'id' => $name,
            'placeholder' => $placeholder,
            'value' => $value,
            'disabled' => $disabled,
            'required' => $required,
        ])); ?>

    />
    
    <?php if($error): ?>
        <p class="mt-1 text-xs text-error-500 dark:text-error-400"><?php echo e($error); ?></p>
    <?php endif; ?>
</div>
<?php /**PATH D:\Project Aplikasi\laravelsaga\resources\views/components/form/input.blade.php ENDPATH**/ ?>