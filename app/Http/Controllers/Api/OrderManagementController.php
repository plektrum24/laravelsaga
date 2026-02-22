<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WebOrder;
use App\Models\WebOrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderManagementController extends Controller
{
    /**
     * Get all orders (Admin)
     * GET /api/admin/orders
     */
    public function index(Request $request)
    {
        $query = WebOrder::where('tenant_id', auth()->user()->tenant_id)
            ->with(['customer', 'items.product']);

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by payment status
        if ($request->has('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        // Filter by date range
        if ($request->has('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->has('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        // Search by order number
        if ($request->has('search')) {
            $query->where('order_number', 'like', '%' . $request->search . '%');
        }

        $orders = $query->latest()->paginate($request->get('limit', 50));

        return response()->json([
            'success' => true,
            'data' => $orders
        ]);
    }

    /**
     * Get order detail (Admin)
     * GET /api/admin/orders/{id}
     */
    public function show($id)
    {
        $order = WebOrder::where('tenant_id', auth()->user()->tenant_id)
            ->with(['customer', 'items.product'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $order
        ]);
    }

    /**
     * Confirm order
     * POST /api/admin/orders/{id}/confirm
     */
    public function confirm($id)
    {
        $order = WebOrder::where('tenant_id', auth()->user()->tenant_id)
            ->findOrFail($id);

        if ($order->status !== WebOrder::STATUS_PENDING) {
            return response()->json([
                'success' => false,
                'message' => 'Order cannot be confirmed'
            ], 400);
        }

        $order->confirm();

        return response()->json([
            'success' => true,
            'message' => 'Order confirmed',
            'data' => $order
        ]);
    }

    /**
     * Mark order as processing
     * POST /api/admin/orders/{id}/process
     */
    public function process($id)
    {
        $order = WebOrder::where('tenant_id', auth()->user()->tenant_id)
            ->findOrFail($id);

        if ($order->status !== WebOrder::STATUS_CONFIRMED) {
            return response()->json([
                'success' => false,
                'message' => 'Order cannot be processed'
            ], 400);
        }

        $order->markAsProcessing();

        return response()->json([
            'success' => true,
            'message' => 'Order is being processed',
            'data' => $order
        ]);
    }

    /**
     * Mark order as shipped
     * POST /api/admin/orders/{id}/ship
     */
    public function ship(Request $request, $id)
    {
        $order = WebOrder::where('tenant_id', auth()->user()->tenant_id)
            ->findOrFail($id);

        if ($order->status !== WebOrder::STATUS_PROCESSING) {
            return response()->json([
                'success' => false,
                'message' => 'Order cannot be shipped'
            ], 400);
        }

        $validated = $request->validate([
            'tracking_number' => 'nullable|string',
            'shipping_carrier' => 'nullable|string',
        ]);

        $order->markAsShipped();

        // Send shipping notification
        // This would trigger email/SMS notification

        return response()->json([
            'success' => true,
            'message' => 'Order shipped',
            'data' => $order
        ]);
    }

    /**
     * Mark order as delivered
     * POST /api/admin/orders/{id}/deliver
     */
    public function deliver($id)
    {
        $order = WebOrder::where('tenant_id', auth()->user()->tenant_id)
            ->findOrFail($id);

        if (!in_array($order->status, [WebOrder::STATUS_PROCESSING, WebOrder::STATUS_SHIPPED])) {
            return response()->json([
                'success' => false,
                'message' => 'Order cannot be delivered'
            ], 400);
        }

        $order->markAsDelivered();
        $order->update(['payment_status' => 'paid']);

        return response()->json([
            'success' => true,
            'message' => 'Order delivered',
            'data' => $order
        ]);
    }

    /**
     * Cancel order
     * POST /api/admin/orders/{id}/cancel
     */
    public function cancel(Request $request, $id)
    {
        $order = WebOrder::where('tenant_id', auth()->user()->tenant_id)
            ->findOrFail($id);

        if (!in_array($order->status, [WebOrder::STATUS_PENDING, WebOrder::STATUS_CONFIRMED])) {
            return response()->json([
                'success' => false,
                'message' => 'Order cannot be cancelled'
            ], 400);
        }

        $validated = $request->validate([
            'cancel_reason' => 'nullable|string',
        ]);

        DB::connection('tenant')->beginTransaction();

        try {
            $order->cancel();
            
            // Restore stock
            foreach ($order->items as $item) {
                $item->product->increment('stock', $item->qty);
            }

            DB::connection('tenant')->commit();

            return response()->json([
                'success' => true,
                'message' => 'Order cancelled',
                'data' => $order
            ]);

        } catch (\Exception $e) {
            DB::connection('tenant')->rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get order statistics
     * GET /api/admin/orders/statistics
     */
    public function statistics(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;

        // Today's orders
        $todayOrders = WebOrder::where('tenant_id', $tenantId)
            ->whereDate('created_at', today())
            ->count();

        // Pending orders
        $pendingOrders = WebOrder::where('tenant_id', $tenantId)
            ->where('status', WebOrder::STATUS_PENDING)
            ->count();

        // Processing orders
        $processingOrders = WebOrder::where('tenant_id', $tenantId)
            ->whereIn('status', [WebOrder::STATUS_CONFIRMED, WebOrder::STATUS_PROCESSING])
            ->count();

        // Revenue (paid orders today)
        $todayRevenue = WebOrder::where('tenant_id', $tenantId)
            ->whereDate('created_at', today())
            ->where('payment_status', 'paid')
            ->sum('total');

        // Order status distribution
        $statusDistribution = WebOrder::where('tenant_id', $tenantId)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'today_orders' => $todayOrders,
                'pending_orders' => $pendingOrders,
                'processing_orders' => $processingOrders,
                'today_revenue' => $todayRevenue,
                'status_distribution' => $statusDistribution,
            ]
        ]);
    }

    /**
     * Export orders
     * GET /api/admin/orders/export
     */
    public function export(Request $request)
    {
        $query = WebOrder::where('tenant_id', auth()->user()->tenant_id)
            ->with(['customer', 'items.product']);

        if ($request->has('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->has('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $orders = $query->get();

        // Convert to array for export
        $exportData = $orders->map(function ($order) {
            return [
                'Order Number' => $order->order_number,
                'Date' => $order->created_at->format('Y-m-d H:i'),
                'Customer' => $order->customer_name,
                'Email' => $order->customer_email,
                'Phone' => $order->customer_phone,
                'Status' => $order->status,
                'Payment Status' => $order->payment_status,
                'Total' => $order->total,
                'Items' => $order->items->map(function ($item) {
                    return $item->product->name . ' (x' . $item->qty . ')';
                })->join(', '),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $exportData
        ]);
    }

    /**
     * Get customer orders
     * GET /api/customers/{customerId}/orders
     */
    public function customerOrders($customerId, Request $request)
    {
        $query = WebOrder::where('tenant_id', auth()->user()->tenant_id)
            ->where('customer_id', $customerId)
            ->with('items.product')
            ->latest();

        $orders = $query->paginate($request->get('limit', 20));

        return response()->json([
            'success' => true,
            'data' => $orders
        ]);
    }
}
