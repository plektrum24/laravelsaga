<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductBarcode;
use App\Services\BarcodeService;
use Illuminate\Http\Request;

class BarcodeController extends Controller
{
    private BarcodeService $barcodeService;

    public function __construct(BarcodeService $barcodeService)
    {
        $this->barcodeService = $barcodeService;
    }

    /**
     * Get product barcodes
     * GET /api/barcodes/products/{productId}
     */
    public function getProductBarcodes($productId)
    {
        $barcodes = $this->barcodeService->getProductBarcodes($productId);
        
        return response()->json([
            'success' => true,
            'data' => $barcodes
        ]);
    }

    /**
     * Generate barcode for product
     * POST /api/barcodes/products/{productId}/generate
     */
    public function generateForProduct(Request $request, $productId)
    {
        $validated = $request->validate([
            'type' => 'required|in:ean13,upc,code128,qr',
            'set_as_primary' => 'boolean',
        ]);

        $barcode = $this->barcodeService->generateForProduct(
            $productId,
            $validated['type'],
            $validated['set_as_primary'] ?? false
        );

        if (!$barcode) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Barcode generated successfully',
            'data' => $barcode
        ]);
    }

    /**
     * Generate barcodes for multiple products
     * POST /api/barcodes/generate-bulk
     */
    public function generateBulk(Request $request)
    {
        $validated = $request->validate([
            'product_ids' => 'required|array',
            'product_ids.*' => 'required|exists:products,id',
            'type' => 'required|in:ean13,upc,code128,qr',
        ]);

        $barcodes = $this->barcodeService->generateBulk(
            $validated['product_ids'],
            $validated['type']
        );

        return response()->json([
            'success' => true,
            'message' => 'Barcodes generated successfully',
            'data' => [
                'generated' => count($barcodes),
                'barcodes' => $barcodes
            ]
        ]);
    }

    /**
     * Get barcode image
     * GET /api/barcodes/{barcode}/image
     */
    public function getImage($barcode)
    {
        $productBarcode = ProductBarcode::where('barcode', $barcode)->first();
        
        if (!$productBarcode) {
            return response()->json([
                'success' => false,
                'message' => 'Barcode not found'
            ], 404);
        }

        $image = $this->barcodeService->generateBarcode(
            $barcode,
            $productBarcode->barcode_type
        );

        return response($image)
            ->header('Content-Type', 'image/png')
            ->header('Content-Disposition', 'attachment; filename="barcode-' . $barcode . '.png"');
    }

    /**
     * Get barcode as HTML
     * GET /api/barcodes/{barcode}/html
     */
    public function getHTML($barcode)
    {
        $productBarcode = ProductBarcode::where('barcode', $barcode)->first();
        
        if (!$productBarcode) {
            return response()->json([
                'success' => false,
                'message' => 'Barcode not found'
            ], 404);
        }

        $html = $this->barcodeService->generateBarcodeHTML(
            $barcode,
            $productBarcode->barcode_type
        );

        return response()->json([
            'success' => true,
            'data' => [
                'barcode' => $barcode,
                'type' => $productBarcode->barcode_type,
                'html' => $html
            ]
        ]);
    }

    /**
     * Find product by barcode
     * GET /api/barcodes/lookup?barcode=123456789
     */
    public function lookup(Request $request)
    {
        $validated = $request->validate([
            'barcode' => 'required|string',
        ]);

        $product = $this->barcodeService->findProductByBarcode($validated['barcode']);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found for barcode'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $product
        ]);
    }

    /**
     * Set primary barcode
     * POST /api/barcodes/{id}/set-primary
     */
    public function setPrimary($id)
    {
        $barcode = ProductBarcode::findOrFail($id);
        
        // Unset other primaries
        ProductBarcode::where('product_id', $barcode->product_id)
            ->where('id', '!=', $id)
            ->update(['is_primary' => false]);
        
        // Set this as primary
        $barcode->update(['is_primary' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Primary barcode set successfully',
            'data' => $barcode
        ]);
    }

    /**
     * Delete barcode
     * DELETE /api/barcodes/{id}
     */
    public function destroy($id)
    {
        $barcode = ProductBarcode::findOrFail($id);
        
        // Can't delete primary barcode if it's the only one
        $count = ProductBarcode::where('product_id', $barcode->product_id)->count();
        if ($barcode->is_primary && $count === 1) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete the only barcode for this product'
            ], 400);
        }
        
        $barcode->delete();

        return response()->json([
            'success' => true,
            'message' => 'Barcode deleted successfully'
        ]);
    }
}
