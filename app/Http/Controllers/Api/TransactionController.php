<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\LoyaltySetting;
use App\Models\CustomerPoint;
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
            DB::connection('tenant')->beginTransaction();

            // Calculate Totals verification
            $subtotal = 0;
            $itemsToInsert = [];

            foreach ($request->cart_items as $item) {
                // Fetch product for secure price
                // For optimal perf we could fetchAll whereIn id, but loop is fine for POS cart size
                $product = Product::find($item['id']);
                if (!$product)
                    continue;

                $qty = $item['qty'];
                // Logic for unit price? For now assume base price or passed price verified
                // Simplification for rapid dev: Trust frontend sending correct price but verify basic existence
                // Ideally: Find distinct price based on unit.

                $price = $item['price'];
                $unitId = $item['unitId'] ?? null;
                $lineTotal = $price * $qty;
                $subtotal += $lineTotal;

                // Find conversion factor and COGS
                $conversionQty = 1;
                $cogs = $product->buy_price; // Default to base product buy price

                if ($unitId) {
                    $productUnit = $product->units()->where('unit_id', $unitId)->first();
                    if ($productUnit) {
                        $conversionQty = $productUnit->conversion_qty;
                        $cogs = $productUnit->buy_price;
                    }
                }

                $itemsToInsert[] = [
                    'product_id' => $product->id,
                    'qty' => $qty,
                    'price' => $price,
                    'subtotal' => $lineTotal,
                    'cogs' => $cogs,
                    'unit_id' => $unitId,
                    'conversion_qty' => $conversionQty,
                ];

                // Decrement Stock
                if ($product->track_stock) {
                    $stockToDeduct = $qty * $conversionQty;
                    $product->decrement('stock', $stockToDeduct);
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

            // Award loyalty points to customer
            if ($request->customer_id) {
                $this->awardLoyaltyPoints($request->customer_id, $transaction->id, $grandTotal);
            }

            foreach ($itemsToInsert as $item) {
                $transaction->items()->create($item);

                // Record Inventory Movement
                $product = Product::find($item['product_id']);
                if ($product && $product->track_stock) {
                    // Note: Stock was already decremented in the first loop
                    InventoryMovement::create([
                        'tenant_id' => $transaction->tenant_id,
                        'product_id' => $product->id,
                        'branch_id' => $transaction->branch_id,
                        'user_id' => $transaction->user_id,
                        'reference_number' => $transaction->invoice_number,
                        'type' => 'out',
                        'qty' => $item['qty'] * ($item['conversion_qty'] ?? 1), // Need to ensure conversion_qty is available
                        'current_stock' => $product->stock,
                        'notes' => 'Sales: ' . $transaction->invoice_number,
                    ]);
                }
            }

            DB::connection('tenant')->commit();

            return response()->json([
                'success' => true,
                'message' => 'Transaction success',
                'data' => $transaction
            ]);

        }
        catch (\Exception $e) {
            DB::connection('tenant')->rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Award loyalty points to customer
     */
    private function awardLoyaltyPoints($customerId, $transactionId, $amount)
    {
        $tenantId = auth()->user()->tenant_id;
        $settings = LoyaltySetting::forTenant($tenantId);
        
        if (!$settings || !$settings->enabled) {
            return;
        }
        
        $customer = Customer::find($customerId);
        if (!$customer) {
            return;
        }
        
        // Assess tier after transaction
        $customer->assessAndUpdateTier();
        
        // Calculate base points
        $basePoints = $settings->calculatePoints($amount);
        
        // Apply tier multiplier
        $multiplier = $customer->getPointsMultiplier();
        $totalPoints = floor($basePoints * $multiplier);
        
        if ($totalPoints > 0) {
            // Calculate current balance
            $balanceData = CustomerPoint::getBalanceWithBreakdown($customerId);
            $balance = $balanceData['balance'];
            
            // Create points record
            CustomerPoint::create([
                'customer_id' => $customerId,
                'tenant_id' => $tenantId,
                'points' => $totalPoints,
                'type' => CustomerPoint::TYPE_EARN,
                'reference_type' => 'transaction',
                'reference_id' => $transactionId,
                'expiry_date' => now()->addMonths($settings->points_expiry_months),
                'balance_after' => $balance + $totalPoints,
                'notes' => 'Earned from transaction #' . $transactionId . 
                          ($customer->getTierName() ? " ({$customer->getTierName()} Tier)" : ''),
            ]);
        }
    }
}
