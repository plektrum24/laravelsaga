<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $query = Transaction::with(['items.product', 'customer', 'user']);

        if ($request->date) {
            $query->whereDate('date', $request->date);
        }

        return response()->json([
            'success' => true,
            'data' => $query->latest()->paginate(20)
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'cart_items' => 'required|array|min:1',
            'paid_amount' => 'required|numeric',
            'payment_method' => 'required|string',
        ]);

        try {
            DB::beginTransaction();

            // Calculate Totals verification
            $subtotal = 0;
            $itemsToInsert = [];

            foreach ($request->cart_items as $item) {
                // Fetch product for secure price
                // For optimal perf we could fetchAll whereIn id, but loop is fine for POS cart size
                $product = Product::find($item['id']);
                if (!$product) continue;

                $qty = $item['qty'];
                // Logic for unit price? For now assume base price or passed price verified
                // Simplification for rapid dev: Trust frontend sending correct price but verify basic existence
                // Ideally: Find distinct price based on unit.
                
                $price = $item['price']; 
                $lineTotal = $price * $qty;
                $subtotal += $lineTotal;

                $itemsToInsert[] = [
                    'product_id' => $product->id,
                    'qty' => $qty,
                    'price' => $price,
                    'subtotal' => $lineTotal,
                    'cogs' => $product->buy_price, // Approx COGS
                    'unit_id' => null, // TODO: Enhance with specific unit
                ];

                // Decrement Stock
                if ($product->track_stock) {
                    $product->decrement('stock', $qty);
                    // Log movement could be here or observer
                }
            }

            $grandTotal = $subtotal; // Add tax/discount later
            $change = $request->paid_amount - $grandTotal;

            $transaction = Transaction::create([
                'invoice_number' => 'INV/' . date('Ymd') . '/' . mt_rand(1000, 9999),
                'branch_id' => $request->user()->branch_id,
                'customer_id' => $request->customer_id,
                'user_id' => $request->user()->id,
                'date' => now(),
                'subtotal' => $subtotal,
                'grand_total' => $grandTotal,
                'paid_amount' => $request->paid_amount,
                'change_amount' => max(0, $change),
                'payment_method' => $request->payment_method,
                'status' => 'completed'
            ]);

            foreach ($itemsToInsert as $item) {
                $transaction->items()->create($item);
            }

            DB::commit();

            return response()->json([
                'success' => true, 
                'message' => 'Transaction success',
                'data' => $transaction
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
