<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Product;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{
    /**
     * List all purchases (goods-in)
     */
    public function index(Request $request)
    {
        $query = Purchase::with(['supplier', 'items.product', 'items.unit', 'branch']);

        if ($request->has('supplier_id') && $request->supplier_id) {
            $query->where('supplier_id', $request->supplier_id);
        }

        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        if ($request->has('search') && $request->search) {
            $query->where('reference_number', 'like', '%' . $request->search . '%');
        }

        $limit = $request->limit ?? 50;
        $purchases = $query->orderBy('date', 'desc')->paginate($limit);

        return response()->json([
            'success' => true,
            'data' => $purchases->items(),
            'pagination' => [
                'total' => $purchases->total(),
                'per_page' => $purchases->perPage(),
                'current_page' => $purchases->currentPage(),
                'last_page' => $purchases->lastPage(),
            ]
        ]);
    }

    /**
     * Create new purchase (goods-in) with batch tracking
     */
    public function store(Request $request)
    {
        $request->validate([
            'supplier_id' => 'nullable|exists:suppliers,id',
            'date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.unit_id' => 'required|exists:units,id',
            'items.*.qty' => 'required|numeric|min:0.0001',
            'items.*.buy_price' => 'required|numeric|min:0',
            'items.*.expiry_date' => 'nullable|date',
        ]);

        try {
            DB::connection('tenant')->beginTransaction();

            // Generate reference number
            $refNumber = 'GRN-' . date('Ymd') . '-' . str_pad(
                Purchase::whereDate('date', today())->count() + 1,
                3,
                '0',
                STR_PAD_LEFT
            );

            // Create Purchase header
            $purchase = Purchase::create([
                'supplier_id' => $request->supplier_id,
                'branch_id' => $request->branch_id,
                'user_id' => auth()->id(),
                'reference_number' => $refNumber,
                'date' => $request->date,
                'status' => $request->status ?? 'completed',
                'notes' => $request->notes,
                'total_amount' => 0,
            ]);

            $totalAmount = 0;

            // Create PurchaseItems (Batch records)
            foreach ($request->items as $item) {
                $subtotal = $item['qty'] * $item['buy_price'];
                $totalAmount += $subtotal;

                // Create batch record
                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $item['product_id'],
                    'unit_id' => $item['unit_id'],
                    'qty' => $item['qty'],
                    'current_stock' => $item['qty'], // Initialize current_stock = qty
                    'buy_price' => $item['buy_price'],
                    'expiry_date' => $item['expiry_date'] ?? null,
                    'subtotal' => $subtotal,
                ]);

                // Update product stock
                $product = Product::find($item['product_id']);
                if ($product && $product->track_stock) {
                    // Convert to base unit if needed
                    $baseQty = $this->convertToBaseUnit($item['product_id'], $item['unit_id'], $item['qty']);
                    $product->increment('stock', $baseQty);
                }
            }

            // Update total
            $purchase->update(['total_amount' => $totalAmount]);

            DB::connection('tenant')->commit();

            return response()->json([
                'success' => true,
                'message' => 'Penerimaan barang berhasil dicatat',
                'data' => $purchase->load('items.product', 'items.unit', 'supplier')
            ], 201);

        } catch (\Exception $e) {
            DB::connection('tenant')->rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show purchase detail
     */
    public function show($id)
    {
        $purchase = Purchase::with(['supplier', 'items.product', 'items.unit', 'branch', 'user'])
            ->findOrFail($id);

        return response()->json(['success' => true, 'data' => $purchase]);
    }

    /**
     * Delete purchase (only if draft/cancelled)
     */
    public function destroy($id)
    {
        $purchase = Purchase::findOrFail($id);

        if ($purchase->status === 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'Tidak bisa menghapus purchase yang sudah completed'
            ], 400);
        }

        $purchase->items()->delete();
        $purchase->delete();

        return response()->json([
            'success' => true,
            'message' => 'Purchase berhasil dihapus'
        ]);
    }

    /**
     * Convert quantity to base unit
     */
    private function convertToBaseUnit($productId, $unitId, $qty)
    {
        $productUnit = \App\Models\ProductUnit::where('product_id', $productId)
            ->where('unit_id', $unitId)
            ->first();

        if ($productUnit) {
            return $qty * $productUnit->conversion_qty;
        }

        return $qty;
    }
}
