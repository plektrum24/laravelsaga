<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\InventoryMovement; // Need to create this model
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    public function adjustStock(Request $request, $id)
    {
        $request->validate([
            'type' => 'required|in:add,subtract',
            'quantity' => 'required|numeric|min:0.01',
            'reason' => 'nullable|string',
            'branch_id' => ['nullable', \Illuminate\Validation\Rule::exists(\App\Models\Branch::class, 'id')]
        ]);

        try {
            DB::connection('tenant')->beginTransaction();
            $product = Product::findOrFail($id);

            $oldStock = $product->stock;
            $qty = $request->quantity;

            if ($request->type === 'subtract') {
                if ($product->stock < $qty) {
                    return response()->json(['success' => false, 'message' => 'Stock tidak mencukupi'], 400);
                }
                $product->decrement('stock', $qty);
                $newStock = $product->stock; // Laravel decrement doesn't auto upd model instance sometimes
            } else {
                $product->increment('stock', $qty);
                $newStock = $product->stock;
            }

            // Log Movement
            DB::table('inventory_movements')->insert([
                'product_id' => $product->id,
                'branch_id' => $request->branch_id ?? 1, // Default to 1 if null, or handle error
                'user_id' => auth()->id(),
                'type' => 'adjustment', // Enum: in, out, adjustment, transfer
                'qty' => $request->type === 'add' ? $qty : -$qty,
                'current_stock' => $newStock,
                'notes' => $request->reason ?? 'Manual Adjustment',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::connection('tenant')->commit();

            return response()->json(['success' => true, 'message' => 'Stock berhasil diupdate']);

        } catch (\Exception $e) {
            DB::connection('tenant')->rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function resetAllStock()
    {
        try {
            // Dangerous!
            DB::table('products')->update(['stock' => 0]);

            // Optional: Log this massive change?

            return response()->json(['success' => true, 'message' => 'Semua stock berhasil di-reset menjadi 0']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function expiry(Request $request)
    {
        // Placeholder until Batch System is active
        return response()->json([
            'success' => true,
            'data' => []
        ]);
    }
}
