<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\Customer;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SalesController extends Controller
{
    /**
     * Display a listing of sales orders.
     */
    public function index(Request $request)
    {
        $query = SalesOrder::with(['customer', 'user', 'items'])
            ->latest();

        // Filters
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        if ($request->has('customer_id') && $request->customer_id) {
            $query->where('customer_id', $request->customer_id);
        }

        if ($request->has('date_from') && $request->date_from) {
            $query->where('order_date', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to) {
            $query->where('order_date', '<=', $request->date_to);
        }

        if ($request->has('search') && $request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('order_number', 'like', '%' . $request->search . '%')
                    ->orWhereHas('customer', function ($q) use ($request) {
                        $q->where('name', 'like', '%' . $request->search . '%');
                    });
            });
        }

        $perPage = $request->get('per_page', 15);
        $salesOrders = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $salesOrders,
        ]);
    }

    /**
     * Store a newly created sales order.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'order_date' => 'required|date',
            'status' => 'required|in:pending,confirmed,processing,completed,cancelled',
            'notes' => 'nullable|string',
            'payment_method' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.discount' => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {
            // Generate order number
            $orderNumber = $this->generateOrderNumber();

            // Calculate totals
            $subtotal = 0;
            $discount = 0;

            foreach ($validated['items'] as $item) {
                $itemSubtotal = $item['qty'] * $item['price'];
                $itemDiscount = $item['discount'] ?? 0;
                $subtotal += $itemSubtotal - $itemDiscount;
                $discount += $itemDiscount;
            }

            $tax = $subtotal * 0.11; // 11% tax
            $shippingCost = $request->get('shipping_cost', 0);
            $grandTotal = $subtotal + $tax + $shippingCost;

            // Create sales order
            $salesOrder = SalesOrder::create([
                'tenant_id' => auth()->user()->tenant_id,
                'branch_id' => auth()->user()->branch_id,
                'customer_id' => $validated['customer_id'],
                'user_id' => auth()->id(),
                'order_number' => $orderNumber,
                'order_date' => $validated['order_date'],
                'status' => $validated['status'],
                'subtotal' => $subtotal,
                'discount' => $discount,
                'tax' => $tax,
                'shipping_cost' => $shippingCost,
                'grand_total' => $grandTotal,
                'notes' => $validated['notes'],
                'payment_method' => $validated['payment_method'],
                'payment_status' => 'unpaid',
            ]);

            // Create sales order items
            foreach ($validated['items'] as $item) {
                SalesOrderItem::create([
                    'sales_order_id' => $salesOrder->id,
                    'product_id' => $item['product_id'],
                    'product_name' => Product::find($item['product_id'])->name,
                    'product_sku' => Product::find($item['product_id'])->sku,
                    'qty' => $item['qty'],
                    'price' => $item['price'],
                    'subtotal' => ($item['qty'] * $item['price']) - ($item['discount'] ?? 0),
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Sales order created successfully',
                'data' => $salesOrder->load(['customer', 'user', 'items']),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create sales order',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified sales order.
     */
    public function show($id)
    {
        $salesOrder = SalesOrder::with(['customer', 'user', 'items.product'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $salesOrder,
        ]);
    }

    /**
     * Update the specified sales order.
     */
    public function update(Request $request, $id)
    {
        $salesOrder = SalesOrder::findOrFail($id);

        $validated = $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'order_date' => 'sometimes|required|date',
            'status' => 'sometimes|required|in:pending,confirmed,processing,completed,cancelled',
            'notes' => 'nullable|string',
            'payment_method' => 'nullable|string',
            'payment_status' => 'sometimes|required|in:unpaid,partial,paid',
        ]);

        DB::beginTransaction();

        try {
            $salesOrder->update($validated);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Sales order updated successfully',
                'data' => $salesOrder->load(['customer', 'user', 'items']),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update sales order',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified sales order.
     */
    public function destroy($id)
    {
        $salesOrder = SalesOrder::findOrFail($id);
        $salesOrder->delete();

        return response()->json([
            'success' => true,
            'message' => 'Sales order deleted successfully',
        ]);
    }

    /**
     * Get sales order statistics.
     */
    public function statistics(Request $request)
    {
        $dateFrom = $request->get('date_from', Carbon::now()->startOfMonth());
        $dateTo = $request->get('date_to', Carbon::now()->endOfMonth());

        $statistics = [
            'total_orders' => SalesOrder::whereBetween('order_date', [$dateFrom, $dateTo])->count(),
            'total_revenue' => SalesOrder::whereBetween('order_date', [$dateFrom, $dateTo])
                ->where('status', 'completed')
                ->sum('grand_total'),
            'pending_orders' => SalesOrder::where('status', 'pending')
                ->whereBetween('order_date', [$dateFrom, $dateTo])
                ->count(),
            'completed_orders' => SalesOrder::where('status', 'completed')
                ->whereBetween('order_date', [$dateFrom, $dateTo])
                ->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $statistics,
        ]);
    }

    /**
     * Generate unique order number.
     */
    private function generateOrderNumber()
    {
        $date = Carbon::now();
        $prefix = 'SO-' . $date->format('Ymd') . '-';

        $lastOrder = SalesOrder::where('order_number', 'like', $prefix . '%')
            ->orderBy('order_number', 'desc')
            ->first();

        if ($lastOrder) {
            $lastNumber = (int) substr($lastOrder->order_number, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }
}
