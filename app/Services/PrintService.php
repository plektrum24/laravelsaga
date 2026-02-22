<?php

namespace App\Services;

use App\Models\LabelTemplate;
use App\Models\PrintJob;
use App\Models\Product;
use App\Models\ProductBarcode;
use Illuminate\Support\Facades\Http;

class PrintService
{
    /**
     * Process print job
     */
    public function processPrintJob(PrintJob $printJob)
    {
        $printJob->update(['status' => 'printing']);

        try {
            $template = $printJob->template;
            $products = $printJob->getProducts();

            // Generate labels for each product
            $labels = [];
            foreach ($products as $product) {
                for ($i = 0; $i < $printJob->quantity; $i++) {
                    $labels[] = $this->generateLabel($template, $product);
                }
            }

            // Send to printer
            if ($printJob->printer_name) {
                $this->sendToPrinter($labels, $printJob->printer_name);
            }

            $printJob->markAsCompleted();

            return [
                'success' => true,
                'labels' => $labels,
                'count' => count($labels)
            ];

        } catch (\Exception $e) {
            $printJob->markAsFailed($e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Generate label content
     */
    public function generateLabel(LabelTemplate $template, Product $product)
    {
        $layout = $template->layout_json;
        $barcode = ProductBarcode::where('product_id', $product->id)
            ->where('is_primary', true)
            ->first();

        $label = [
            'template_id' => $template->id,
            'product_id' => $product->id,
            'fields' => []
        ];

        foreach ($layout['fields'] ?? [] as $field) {
            $value = $this->getFieldValue($field, $product, $barcode);
            $label['fields'][] = [
                'type' => $field['type'],
                'value' => $value,
                'x' => $field['x'],
                'y' => $field['y'],
                'width' => $field['width'],
                'height' => $field['height'],
                'font_size' => $field['font_size'] ?? 10,
                'bold' => $field['bold'] ?? false,
                'color' => $field['color'] ?? '#000000',
            ];
        }

        return $label;
    }

    /**
     * Get field value based on type
     */
    private function getFieldValue($field, $product, $barcode)
    {
        switch ($field['type']) {
            case 'product_name':
                return $product->name;
            case 'price':
                return 'Rp ' . number_format($product->sell_price, 0, ',', '.');
            case 'barcode':
                return $barcode ? $barcode->barcode : $product->barcode;
            case 'barcode_number':
                return $barcode ? $barcode->barcode : $product->barcode;
            case 'sku':
                return $product->sku;
            case 'category':
                return $product->category?->name ?? '-';
            default:
                return '';
        }
    }

    /**
     * Send to thermal printer
     */
    public function sendToPrinter($labels, $printerName)
    {
        // This would integrate with actual printer
        // Options:
        // 1. Browser Print API (client-side)
        // 2. Network printer (IPP/LPD)
        // 3. Print server service
        
        // For now, return labels data for client-side printing
        return [
            'printer' => $printerName,
            'labels' => $labels,
            'timestamp' => now()
        ];
    }

    /**
     * Generate print-ready HTML
     */
    public function generatePrintHTML($labels, LabelTemplate $template)
    {
        $html = view('exports.label-print', [
            'labels' => $labels,
            'template' => $template,
            'width_mm' => $template->width_mm,
            'height_mm' => $template->height_mm
        ])->render();

        return $html;
    }

    /**
     * Batch print products
     */
    public function batchPrint($productIds, $templateId, $quantity = 1, $printerName = null)
    {
        $template = LabelTemplate::find($templateId);
        
        if (!$template) {
            return ['success' => false, 'message' => 'Template not found'];
        }

        $printJob = PrintJob::create([
            'tenant_id' => auth()->user()->tenant_id,
            'template_id' => $templateId,
            'product_ids' => $productIds,
            'quantity' => $quantity,
            'status' => 'pending',
            'printer_name' => $printerName,
            'created_by' => auth()->id(),
        ]);

        return $this->processPrintJob($printJob);
    }

    /**
     * Quick print barcode label
     */
    public function quickPrintBarcode($productId, $quantity = 1)
    {
        // Get default barcode label template
        $template = LabelTemplate::getDefaultForType(
            auth()->user()->tenant_id,
            'barcode_label'
        );

        if (!$template) {
            return ['success' => false, 'message' => 'No default barcode template'];
        }

        return $this->batchPrint([$productId], $template->id, $quantity);
    }

    /**
     * Quick print price tag
     */
    public function quickPrintPriceTag($productId, $quantity = 1)
    {
        // Get default price tag template
        $template = LabelTemplate::getDefaultForType(
            auth()->user()->tenant_id,
            'price_tag'
        );

        if (!$template) {
            return ['success' => false, 'message' => 'No default price tag template'];
        }

        return $this->batchPrint([$productId], $template->id, $quantity);
    }

    /**
     * Get printer list (for network printers)
     */
    public function getAvailablePrinters()
    {
        // This would query network printers
        // For now, return mock data
        return [
            ['name' => 'Browser Default', 'type' => 'browser', 'default' => true],
            ['name' => 'Thermal Printer (USB)', 'type' => 'usb', 'default' => false],
            ['name' => 'Network Printer', 'type' => 'network', 'default' => false],
        ];
    }

    /**
     * Test printer connection
     */
    public function testPrinter($printerName)
    {
        // Send test label to printer
        $testLabel = [
            'fields' => [
                ['type' => 'text', 'value' => 'Printer Test', 'x' => 10, 'y' => 10]
            ]
        ];

        $result = $this->sendToPrinter([$testLabel], $printerName);

        return ['success' => true, 'message' => 'Test print sent'];
    }
}
