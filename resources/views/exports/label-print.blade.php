<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Print Labels</title>
    <style>
        @page {
            size: {{ $width_mm }}mm {{ $height_mm }}mm;
            margin: 0;
        }
        
        @media print {
            body {
                margin: 0;
                padding: 0;
            }
            
            .label-container {
                page-break-after: always;
            }
        }
        
        .label-container {
            width: {{ $width_mm }}mm;
            height: {{ $height_mm }}mm;
            border: 1px solid #ccc;
            padding: 2mm;
            box-sizing: border-box;
            position: relative;
            font-family: Arial, sans-serif;
            overflow: hidden;
        }
        
        .label-field {
            position: absolute;
            box-sizing: border-box;
        }
        
        .label-field.bold {
            font-weight: bold;
        }
        
        .label-field.product-name {
            font-size: 14px;
        }
        
        .label-field.price {
            font-size: 18px;
            color: #d32f2f;
            font-weight: bold;
        }
        
        .label-field.barcode {
            font-size: 10px;
            font-family: 'Libre Barcode 39', cursive;
        }
        
        .label-field.sku {
            font-size: 10px;
        }
    </style>
</head>
<body>
    @foreach($labels as $label)
    <div class="label-container">
        @foreach($label['fields'] as $field)
        <div class="label-field {{ $field['type'] }} {{ $field['bold'] ? 'bold' : '' }}"
             style="left: {{ $field['x'] }}mm; top: {{ $field['y'] }}mm; width: {{ $field['width'] }}mm; height: {{ $field['height'] }}mm; font-size: {{ $field['font_size'] }}px; color: {{ $field['color'] }};">
            {{ $field['value'] }}
        </div>
        @endforeach
    </div>
    @endforeach
    
    <script>
        // Auto-print when page loads
        window.onload = function() {
            // Uncomment to enable auto-print
            // window.print();
        };
    </script>
</body>
</html>
