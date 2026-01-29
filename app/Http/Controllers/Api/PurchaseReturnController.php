<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseReturnController extends Controller
{
    /**
     * List all purchase returns
     */
    public function index(Request $request)
    {
        $query = PurchaseReturn::with(['supplier', 'items.product', 'items.unit']);

        if ($request->has('supplier_id') && $request->supplier_id) {
            $query->where('supplier_id', $request->supplier_id);
        }

        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        $limit = $request->limit ?? 50;
        $returns = $query->orderBy('date', 'desc')->paginate($limit);

        // Transform for frontend
        $data = $returns->getCollection()->map(function ($r) {
            return [
                'id' => $r->id,
                'return_number' => $r->return_number,
                'date' => $r->date,
                'supplier_id' => $r->supplier_id,
                'supplier_name' => $r->supplier?->name,
                'reason' => $r->reason,
                'status' => $r->status,
                'total_amount' => $r->total_amount,
                'notes' => $r->notes,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Get available batches for a product by supplier
     */
    public function getBatches($productId, Request $request)
    {
        $supplierId = $request->supplier_id;

        // Find purchase items (batches) for this product from this supplier
        $batches = PurchaseItem::where('product_id', $productId)
            ->where('current_stock', '>', 0)
            ->whereHas('purchase', function ($q) use ($supplierId) {
                $q->where('supplier_id', $supplierId);
            })
            ->with(['purchase', 'unit'])
            ->orderBy('expiry_date', 'asc') // FEFO
            ->get()
            ->map(function ($item) {
                return [
                    'batch_id' => $item->id,
                    'invoice_number' => $item->purchase?->reference_number,
                    'purchase_date' => $item->purchase?->date,
                    'unit_id' => $item->unit_id,
                    'unit_name' => $item->unit?->name ?? 'Pcs',
                    'initial_qty' => $item->qty,
                    'current_stock' => $item->current_stock,
                    'unit_price' => $item->buy_price,
                    'expiry_date' => $item->expiry_date,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $batches
        ]);
    }

    /**
     * Create new purchase return
     */
    public function store(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'date' => 'required|date',
            'reason' => 'required|string',
            'items' => 'required|array|min:1',
            'items.*.purchase_item_id' => 'required|exists:purchase_items,id',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.0001',
            'items.*.unit_id' => 'required|exists:units,id',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        try {
            DB::connection('tenant')->beginTransaction();

            // Generate return number
            $returnNumber = 'RET-' . date('Ymd') . '-' . str_pad(
                PurchaseReturn::whereDate('date', today())->count() + 1,
                3,
                '0',
                STR_PAD_LEFT
            );

            $totalAmount = 0;

            // Create return header
            $return = PurchaseReturn::create([
                'return_number' => $returnNumber,
                'supplier_id' => $request->supplier_id,
                'branch_id' => $request->branch_id,
                'user_id' => auth()->id(),
                'date' => $request->date,
                'reason' => $request->reason,
                'status' => $request->status ?? 'draft',
                'notes' => $request->notes,
                'total_amount' => 0,
            ]);

            // Create return items
            foreach ($request->items as $item) {
                $subtotal = $item['quantity'] * $item['unit_price'];
                $totalAmount += $subtotal;

                PurchaseReturnItem::create([
                    'purchase_return_id' => $return->id,
                    'purchase_item_id' => $item['purchase_item_id'],
                    'product_id' => $item['product_id'],
                    'unit_id' => $item['unit_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $subtotal,
                ]);
            }

            // Update total
            $return->update(['total_amount' => $totalAmount]);

            // If status is completed, deduct from batches
            if ($request->status === 'completed') {
                $this->deductBatchStock($return);
            }

            DB::connection('tenant')->commit();

            return response()->json([
                'success' => true,
                'message' => 'Return berhasil dibuat',
                'data' => $return->load('items')
            ], 201);

        } catch (\Exception $e) {
            DB::connection('tenant')->rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat return: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show return detail
     */
    public function show($id)
    {
        $return = PurchaseReturn::with([
            'supplier',
            'items.product',
            'items.unit',
            'items.purchaseItem.purchase'
        ])->findOrFail($id);

        $data = [
            'id' => $return->id,
            'return_number' => $return->return_number,
            'date' => $return->date,
            'supplier_id' => $return->supplier_id,
            'supplier_name' => $return->supplier?->name,
            'reason' => $return->reason,
            'status' => $return->status,
            'total_amount' => $return->total_amount,
            'notes' => $return->notes,
            'items' => $return->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'product_name' => $item->product?->name,
                    'sku' => $item->product?->sku,
                    'unit_name' => $item->unit?->name,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'subtotal' => $item->subtotal,
                    'invoice_number' => $item->purchaseItem?->purchase?->reference_number,
                    'expiry_date' => $item->purchaseItem?->expiry_date,
                ];
            }),
        ];

        return response()->json(['success' => true, 'data' => $data]);
    }

    /**
     * Complete return - deduct stock from batches
     */
    public function complete($id)
    {
        $return = PurchaseReturn::findOrFail($id);

        if ($return->status !== 'draft') {
            return response()->json([
                'success' => false,
                'message' => 'Hanya return dengan status draft yang bisa diselesaikan'
            ], 400);
        }

        try {
            DB::connection('tenant')->beginTransaction();

            $this->deductBatchStock($return);
            $return->update(['status' => 'completed']);

            DB::connection('tenant')->commit();

            return response()->json([
                'success' => true,
                'message' => 'Return berhasil diselesaikan, stok batch telah dikurangi'
            ]);

        } catch (\Exception $e) {
            DB::connection('tenant')->rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancel return - restore stock if was completed
     */
    public function cancel($id)
    {
        $return = PurchaseReturn::with('items')->findOrFail($id);

        if ($return->status === 'cancelled') {
            return response()->json([
                'success' => false,
                'message' => 'Return sudah dibatalkan'
            ], 400);
        }

        try {
            DB::connection('tenant')->beginTransaction();

            // If was completed, restore batch stock
            if ($return->status === 'completed') {
                $this->restoreBatchStock($return);
            }

            $return->update(['status' => 'cancelled']);

            DB::connection('tenant')->commit();

            return response()->json([
                'success' => true,
                'message' => 'Return berhasil dibatalkan'
            ]);

        } catch (\Exception $e) {
            DB::connection('tenant')->rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete draft return
     */
    public function destroy($id)
    {
        $return = PurchaseReturn::findOrFail($id);

        if ($return->status !== 'draft') {
            return response()->json([
                'success' => false,
                'message' => 'Hanya return dengan status draft yang bisa dihapus'
            ], 400);
        }

        $return->items()->delete();
        $return->delete();

        return response()->json([
            'success' => true,
            'message' => 'Return berhasil dihapus'
        ]);
    }

    /**
     * Deduct stock from batches and product
     */
    private function deductBatchStock(PurchaseReturn $return)
    {
        foreach ($return->items as $item) {
            // Deduct from batch (PurchaseItem)
            $batch = PurchaseItem::find($item->purchase_item_id);
            if ($batch) {
                $batch->deductStock($item->quantity);
            }

            // Deduct from product stock (convert to base unit)
            $product = Product::find($item->product_id);
            if ($product && $product->track_stock) {
                $baseQty = $this->convertToBaseUnit($item->product_id, $item->unit_id, $item->quantity);
                $product->decrement('stock', $baseQty);
            }
        }
    }

    /**
     * Restore stock to batches (on cancel)
     */
    private function restoreBatchStock(PurchaseReturn $return)
    {
        foreach ($return->items as $item) {
            // Restore to batch
            $batch = PurchaseItem::find($item->purchase_item_id);
            if ($batch) {
                $batch->increment('current_stock', $item->quantity);
            }

            // Restore to product stock
            $product = Product::find($item->product_id);
            if ($product && $product->track_stock) {
                $baseQty = $this->convertToBaseUnit($item->product_id, $item->unit_id, $item->quantity);
                $product->increment('stock', $baseQty);
            }
        }
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
