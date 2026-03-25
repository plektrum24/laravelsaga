<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'title' => null,
    'variant' => 'default', // default, compact
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
    'title' => null,
    'variant' => 'default', // default, compact
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $cardClasses = 'rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]';
    $paddingClasses = $variant === 'compact' ? 'px-4 py-3' : 'px-6 py-5';
?>

<div <?php echo e($attributes->merge(['class' => $cardClasses])); ?>>
    <?php if($title): ?>
        <div class="<?php echo e($paddingClasses); ?>">
            <h3 class="text-base font-medium text-gray-800 dark:text-white/90">
                <?php echo e($title); ?>

            </h3>
        </div>
        <div class="border-t border-gray-100 <?php echo e($paddingClasses); ?> dark:border-gray-800">
            <?php echo e($slot); ?>

        </div>
    <?php else: ?>
        <div class="<?php echo e($paddingClasses); ?>">
            <?php echo e($slot); ?>

        </div>
    <?php endif; ?>
</div>
<?php /**PATH D:\Project App\laravelsaga\resources\views/components/card/card.blade.php ENDPATH**/ ?>