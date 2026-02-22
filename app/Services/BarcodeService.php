<?php

namespace App\Services;

use App\Models\ProductBarcode;
use Picqer\Barcode\BarcodeGeneratorPNG;
use Picqer\Barcode\BarcodeGeneratorHTML;
use Picqer\Barcode\BarcodeGeneratorSVG;

class BarcodeService
{
    /**
     * Generate barcode image
     */
    public function generateBarcode($barcode, $type = 'ean13', $format = 'png')
    {
        $generator = new BarcodeGeneratorPNG();
        
        switch ($type) {
            case 'ean13':
                $generator->setType($generator::TYPE_EAN_13);
                break;
            case 'upc':
                $generator->setType($generator::TYPE_UPC_A);
                break;
            case 'code128':
                $generator->setType($generator::TYPE_CODE_128);
                break;
            case 'qr':
                // QR code would need a different library
                return $this->generateQRCode($barcode);
            default:
                $generator->setType($generator::TYPE_CODE_128);
        }
        
        return $generator->getBarcode($barcode);
    }

    /**
     * Generate barcode as HTML
     */
    public function generateBarcodeHTML($barcode, $type = 'ean13')
    {
        $generator = new BarcodeGeneratorHTML();
        
        switch ($type) {
            case 'ean13':
                $generator->setType($generator::TYPE_EAN_13);
                break;
            case 'upc':
                $generator->setType($generator::TYPE_UPC_A);
                break;
            case 'code128':
                $generator->setType($generator::TYPE_CODE_128);
                break;
            default:
                $generator->setType($generator::TYPE_CODE_128);
        }
        
        return $generator->getBarcode($barcode, $generator::COLOR_BLACK, true);
    }

    /**
     * Generate barcode as SVG
     */
    public function generateBarcodeSVG($barcode, $type = 'ean13')
    {
        $generator = new BarcodeGeneratorSVG();
        
        switch ($type) {
            case 'ean13':
                $generator->setType($generator::TYPE_EAN_13);
                break;
            case 'upc':
                $generator->setType($generator::TYPE_UPC_A);
                break;
            case 'code128':
                $generator->setType($generator::TYPE_CODE_128);
                break;
            default:
                $generator->setType($generator::TYPE_CODE_128);
        }
        
        return $generator->getBarcode($barcode);
    }

    /**
     * Generate QR code (placeholder - would need QR library)
     */
    private function generateQRCode($data)
    {
        // Placeholder for QR code generation
        // Would need a library like endroid/qr-code
        return null;
    }

    /**
     * Generate barcode for product
     */
    public function generateForProduct($productId, $type = 'ean13', $setAsPrimary = false)
    {
        $product = \App\Models\Product::find($productId);
        
        if (!$product) {
            return null;
        }

        // Generate barcode based on type
        switch ($type) {
            case 'ean13':
                $barcode = ProductBarcode::generateEAN13(substr($product->sku ?? 'PROD', 0, 4));
                break;
            case 'upc':
                $barcode = ProductBarcode::generateUPCA(substr($product->sku ?? 'PROD', 0, 2));
                break;
            case 'code128':
                $barcode = ProductBarcode::generateCode128(12);
                break;
            default:
                $barcode = ProductBarcode::generateCode128(12);
        }

        // If setting as primary, unset other primaries
        if ($setAsPrimary) {
            ProductBarcode::where('product_id', $productId)
                ->update(['is_primary' => false]);
        }

        // Create barcode record
        $productBarcode = ProductBarcode::create([
            'product_id' => $productId,
            'barcode' => $barcode,
            'barcode_type' => $type,
            'is_primary' => $setAsPrimary,
        ]);

        return $productBarcode;
    }

    /**
     * Generate barcodes for multiple products
     */
    public function generateBulk($productIds, $type = 'ean13')
    {
        $results = [];
        
        foreach ($productIds as $productId) {
            $barcode = $this->generateForProduct($productId, $type, count($results) === 0);
            if ($barcode) {
                $results[] = $barcode;
            }
        }
        
        return $results;
    }

    /**
     * Get primary barcode for product
     */
    public function getPrimaryBarcode($productId)
    {
        return ProductBarcode::where('product_id', $productId)
            ->where('is_primary', true)
            ->first();
    }

    /**
     * Get all barcodes for product
     */
    public function getProductBarcodes($productId)
    {
        return ProductBarcode::where('product_id', $productId)->get();
    }

    /**
     * Find product by barcode
     */
    public function findProductByBarcode($barcode)
    {
        $productBarcode = ProductBarcode::where('barcode', $barcode)->with('product')->first();
        
        return $productBarcode ? $productBarcode->product : null;
    }

    /**
     * Validate barcode
     */
    public function validateBarcode($barcode, $type)
    {
        switch ($type) {
            case 'ean13':
                return ProductBarcode::validateEAN13($barcode);
            case 'upc':
                return ProductBarcode::validateUPCA($barcode);
            default:
                return true; // Can't validate code128 or qr
        }
    }
}
