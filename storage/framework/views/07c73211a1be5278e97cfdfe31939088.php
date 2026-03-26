<!DOCTYPE html>
<html>

<head>
    <title>Product List</title>
    <style>
        body {
            font-family: sans-serif;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
            font-size: 12px;
        }

        th {
            bg-color: #f2f2f2;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .footer {
            text-align: right;
            font-size: 10px;
            margin-top: 20px;
        }
    </style>
</head>

<body>
    <div class="header">
        <h2>Product Inventory List</h2>
        <p>Generated on: <?php echo e(date('Y-m-d H:i')); ?></p>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 30px;">No</th>
                <th>Product Information</th>
                <th style="width: 80px;">Category</th>
                <th style="width: 150px;">Units & Pricing</th>
                <th style="width: 60px;">Stock</th>
            </tr>
        </thead>
        <tbody>
            <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $units = $product->units->sortBy('conversion_qty');
                ?>
                <tr>
                    <td><?php echo e($index + 1); ?></td>
                    <td>
                        <div style="font-weight: bold;"><?php echo e($product->name); ?></div>
                        <div style="font-size: 10px; color: #666;">SKU: <?php echo e($product->sku); ?> | Barcode:
                            <?php echo e($product->barcode ?? '-'); ?>

                        </div>
                    </td>
                    <td><?php echo e($product->category->name ?? '-'); ?></td>
                    <td>
                        <?php $__currentLoopData = $units; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $u): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div
                                style="margin-bottom: 4px; padding-bottom: 4px; border-bottom: 1px dashed #eee; last-child: border:0;">
                                <strong><?php echo e($u->unit->name ?? '-'); ?></strong><br>
                                <span style="font-size: 10px;">
                                    Beli: Rp <?php echo e(number_format($u->buy_price, 0, ',', '.')); ?><br>
                                    Jual: Rp <?php echo e(number_format($u->sell_price, 0, ',', '.')); ?>

                                    <?php if(!$u->is_base_unit): ?>
                                        <br>(Isi: <?php echo e((float) $u->conversion_qty); ?>)
                                    <?php endif; ?>
                                </span>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </td>
                    <td><?php echo e(number_format($product->stock, 2)); ?></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>

    <div class="footer">
        <p>Total Products: <?php echo e(count($products)); ?></p>
    </div>
</body>

</html><?php /**PATH D:\Project Aplikasi\laravelsaga\resources\views/exports/products_pdf.blade.php ENDPATH**/ ?>