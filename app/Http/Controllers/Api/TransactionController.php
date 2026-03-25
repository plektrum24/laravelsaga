<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\LoyaltySetting;
use App\Models\CustomerPoint;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = Transaction::with(['items.product', 'customer', 'user']);

            // Date filter
            if ($request->has('date') && $request->date) {
                $query->whereDate('date', $request->date);
            }

            // Date range filter
            if ($request->has('start_date') && $request->start_date) {
                $query->whereDate('date', '>=', $request->start_date);
            }
            if ($request->has('end_date') && $request->end_date) {
                $query->whereDate('date', '<=', $request->end_date);
            }

            // Payment method filter
            if ($request->has('payment_method') && $request->payment_method) {
                $query->where('payment_method', $request->payment_method);
            }

            // Cashier filter
            if ($request->has('cashier_id') && $request->cashier_id) {
                $query->where('user_id', $request->cashier_id);
            }

            $perPage = $request->get('per_page', 20);
            $transactions = $query->latest()->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $transactions
            ]);
        } catch (\Exception $e) {
            Log::error('Transaction index error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching transactions: ' . $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            // Validate request
            $validated = $request->validate([
                'cart_items' => 'required|array|min:1',
                'cart_items.*.product_id' => 'required|exists:products,id',
                'cart_items.*.unit_id' => 'nullable|exists:units,id',
                'cart_items.*.qty' => 'required|numeric|min:1',
                'cart_items.*.price' => 'required|numeric|min:0',
                'payment_method' => 'required|in:cash,transfer,debit,credit,ewallet',
                'paid_amount' => 'required|numeric|min:0',
                'customer_id' => 'nullable|exists:customers,id',
                'notes' => 'nullable|string',
            ]);

            DB::connection('tenant')->beginTransaction();

            // Calculate Totals verification
            $subtotal = 0;
            $itemsToInsert = [];

            foreach ($request->cart_items as $item) {
                // Fetch product for secure price
                $product = Product::find($item['product_id']);
                if (!$product) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Produk tidak ditemukan: ID ' . $item['product_id']
                    ], 400);
                }

                $qty = $item['qty'];
                $price = $item['price'];
                $unitId = $item['unit_id'] ?? null;
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
                    if ($product->stock < $stockToDeduct) {
                        DB::connection('tenant')->rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => 'Stok tidak mencukupi untuk produk: ' . $product->name
                        ], 400);
                    }
                    $product->decrement('stock', $stockToDeduct);
                }
            }

            $grandTotal = $subtotal; // Add tax/discount later
            $change = $request->paid_amount - $grandTotal;

            // Get user branch
            $user = auth()->user();
            $branchId = $user->branch_id ?? $user->current_branch_id;
            
            if (!$branchId) {
                DB::connection('tenant')->rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Branch tidak ditemukan. Silakan pilih branch terlebih dahulu.'
                ], 400);
            }

            $transaction = Transaction::create([
                'tenant_id' => $user->tenant_id,
                'invoice_number' => 'INV/' . date('Ymd') . '/' . str_pad(
                    Transaction::whereDate('created_at', today())->count() + 1,
                    4,
                    '0',
                    STR_PAD_LEFT
                ),
                'branch_id' => $branchId,
                'customer_id' => $request->customer_id,
                'user_id' => $user->id,
                'date' => now(),
                'subtotal' => $subtotal,
                'grand_total' => $grandTotal,
                'paid_amount' => $request->paid_amount,
                'change_amount' => max(0, $change),
                'payment_method' => $request->payment_method,
                'status' => 'completed',
                'notes' => $request->notes,
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
                    InventoryMovement::create([
                        'tenant_id' => $user->tenant_id,
                        'product_id' => $product->id,
                        'branch_id' => $branchId,
                        'user_id' => $user->id,
                        'reference_number' => $transaction->invoice_number,
                        'type' => 'out',
                        'qty' => $item['qty'] * ($item['conversion_qty'] ?? 1),
                        'current_stock' => $product->stock,
                        'notes' => 'Sales: ' . $transaction->invoice_number,
                    ]);
                }
            }

            DB::connection('tenant')->commit();

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil',
                'data' => $transaction->load('items.product')
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::connection('tenant')->rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::connection('tenant')->rollBack();
            \Log::error('Transaction store error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
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
