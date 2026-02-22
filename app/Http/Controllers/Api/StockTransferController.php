<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockTransferController extends Controller
{
    /**
     * List all stock transfers
     * GET /api/stock-transfers
     */
    public function index(Request $request)
    {
        $query = StockTransfer::with(['fromBranch', 'toBranch', 'requestedBy', 'approvedBy'])
            ->where('tenant_id', auth()->user()->tenant_id);

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by from_branch
        if ($request->has('from_branch_id')) {
            $query->where('from_branch_id', $request->from_branch_id);
        }

        // Filter by to_branch
        if ($request->has('to_branch_id')) {
            $query->where('to_branch_id', $request->to_branch_id);
        }

        // Search by transfer number
        if ($request->has('search')) {
            $query->where('transfer_number', 'like', '%' . $request->search . '%');
        }

        $transfers = $query->latest()->paginate($request->get('limit', 50));

        return response()->json([
            'success' => true,
            'data' => $transfers
        ]);
    }

    /**
     * Get stock transfer details
     * GET /api/stock-transfers/{id}
     */
    public function show($id)
    {
        $transfer = StockTransfer::with(['fromBranch', 'toBranch', 'requestedBy', 'approvedBy', 'items.product', 'items.unit'])
            ->where('tenant_id', auth()->user()->tenant_id)
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $transfer
        ]);
    }

    /**
     * Create stock transfer
     * POST /api/stock-transfers
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'from_branch_id' => 'required|exists:branches,id',
            'to_branch_id' => 'required|exists:branches,id|different:from_branch_id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.unit_id' => 'nullable|exists:units,id',
            'items.*.qty_requested' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string',
        ]);

        // Verify branches belong to same tenant
        $fromBranch = Branch::findOrFail($validated['from_branch_id']);
        $toBranch = Branch::findOrFail($validated['to_branch_id']);
        
        if ($fromBranch->tenant_id !== auth()->user()->tenant_id || 
            $toBranch->tenant_id !== auth()->user()->tenant_id) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid branches selected'
            ], 403);
        }

        DB::connection('tenant')->beginTransaction();

        try {
            // Create transfer
            $transfer = StockTransfer::create([
                'tenant_id' => auth()->user()->tenant_id,
                'transfer_number' => StockTransfer::generateTransferNumber(),
                'from_branch_id' => $validated['from_branch_id'],
                'to_branch_id' => $validated['to_branch_id'],
                'requested_by' => auth()->id(),
                'status' => StockTransfer::STATUS_DRAFT,
                'notes' => $validated['notes'] ?? null,
            ]);

            // Create transfer items
            foreach ($validated['items'] as $item) {
                StockTransferItem::create([
                    'transfer_id' => $transfer->id,
                    'product_id' => $item['product_id'],
                    'unit_id' => $item['unit_id'] ?? null,
                    'qty_requested' => $item['qty_requested'],
                ]);
            }

            $transfer->updateTotalItems();

            DB::connection('tenant')->commit();

            return response()->json([
                'success' => true,
                'message' => 'Stock transfer created successfully',
                'data' => $transfer->load('items.product')
            ], 201);

        } catch (\Exception $e) {
            DB::connection('tenant')->rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update stock transfer (draft only)
     * PUT /api/stock-transfers/{id}
     */
    public function update(Request $request, $id)
    {
        $transfer = StockTransfer::where('tenant_id', auth()->user()->tenant_id)
            ->findOrFail($id);

        if ($transfer->status !== StockTransfer::STATUS_DRAFT) {
            return response()->json([
                'success' => false,
                'message' => 'Can only update draft transfers'
            ], 400);
        }

        $validated = $request->validate([
            'from_branch_id' => 'required|exists:branches,id',
            'to_branch_id' => 'required|exists:branches,id|different:from_branch_id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.unit_id' => 'nullable|exists:units,id',
            'items.*.qty_requested' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string',
        ]);

        DB::connection('tenant')->beginTransaction();

        try {
            $transfer->update([
                'from_branch_id' => $validated['from_branch_id'],
                'to_branch_id' => $validated['to_branch_id'],
                'notes' => $validated['notes'] ?? null,
            ]);

            // Delete existing items
            $transfer->items()->delete();

            // Create new items
            foreach ($validated['items'] as $item) {
                StockTransferItem::create([
                    'transfer_id' => $transfer->id,
                    'product_id' => $item['product_id'],
                    'unit_id' => $item['unit_id'] ?? null,
                    'qty_requested' => $item['qty_requested'],
                ]);
            }

            $transfer->updateTotalItems();

            DB::connection('tenant')->commit();

            return response()->json([
                'success' => true,
                'message' => 'Stock transfer updated successfully',
                'data' => $transfer->load('items.product')
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
     * Submit transfer for approval
     * POST /api/stock-transfers/{id}/submit
     */
    public function submit($id)
    {
        $transfer = StockTransfer::where('tenant_id', auth()->user()->tenant_id)
            ->findOrFail($id);

        if (!$transfer->canSubmit()) {
            return response()->json([
                'success' => false,
                'message' => 'Transfer cannot be submitted'
            ], 400);
        }

        // Validate stock availability
        foreach ($transfer->items as $item) {
            $product = Product::where('id', $item->product_id)
                ->where('branch_id', $transfer->from_branch_id)
                ->first();

            if (!$product || $product->stock < $item->qty_requested) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient stock for product: ' . ($product ? $product->name : 'Unknown')
                ], 400);
            }
        }

        $transfer->submit();

        return response()->json([
            'success' => true,
            'message' => 'Transfer submitted for approval',
            'data' => $transfer->load('items.product')
        ]);
    }

    /**
     * Approve transfer
     * POST /api/stock-transfers/{id}/approve
     */
    public function approve(Request $request, $id)
    {
        $transfer = StockTransfer::where('tenant_id', auth()->user()->tenant_id)
            ->findOrFail($id);

        if (!$transfer->canApprove()) {
            return response()->json([
                'success' => false,
                'message' => 'Transfer cannot be approved'
            ], 400);
        }

        $validated = $request->validate([
            'items' => 'nullable|array',
            'items.*.id' => 'required|exists:stock_transfer_items,id',
            'items.*.qty_approved' => 'required|numeric|min:0.01',
        ]);

        DB::connection('tenant')->beginTransaction();

        try {
            // Update approved quantities
            if (isset($validated['items'])) {
                foreach ($validated['items'] as $itemData) {
                    $item = StockTransferItem::findOrFail($itemData['id']);
                    $item->update([
                        'qty_approved' => $itemData['qty_approved'],
                    ]);
                }
            } else {
                // Auto-approve all requested quantities
                foreach ($transfer->items as $item) {
                    $item->update([
                        'qty_approved' => $item->qty_requested,
                    ]);
                }
            }

            $transfer->approve(auth()->id());

            DB::connection('tenant')->commit();

            return response()->json([
                'success' => true,
                'message' => 'Transfer approved successfully',
                'data' => $transfer->load('items.product')
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
     * Reject transfer
     * POST /api/stock-transfers/{id}/reject
     */
    public function reject($id)
    {
        $transfer = StockTransfer::where('tenant_id', auth()->user()->tenant_id)
            ->findOrFail($id);

        if (!$transfer->reject()) {
            return response()->json([
                'success' => false,
                'message' => 'Transfer cannot be rejected'
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'Transfer rejected',
            'data' => $transfer
        ]);
    }

    /**
     * Ship transfer
     * POST /api/stock-transfers/{id}/ship
     */
    public function ship(Request $request, $id)
    {
        $transfer = StockTransfer::where('tenant_id', auth()->user()->tenant_id)
            ->findOrFail($id);

        if (!$transfer->canShip()) {
            return response()->json([
                'success' => false,
                'message' => 'Transfer cannot be shipped'
            ], 400);
        }

        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|exists:stock_transfer_items,id',
            'items.*.qty_shipped' => 'required|numeric|min:0.01',
        ]);

        DB::connection('tenant')->beginTransaction();

        try {
            // Update shipped quantities and create inventory movements
            foreach ($validated['items'] as $itemData) {
                $item = StockTransferItem::findOrFail($itemData['id']);
                $item->update([
                    'qty_shipped' => $itemData['qty_shipped'],
                ]);

                // Create inventory movement (transfer_out)
                InventoryMovement::create([
                    'tenant_id' => auth()->user()->tenant_id,
                    'product_id' => $item->product_id,
                    'branch_id' => $transfer->from_branch_id,
                    'user_id' => auth()->id(),
                    'reference_number' => $transfer->transfer_number,
                    'type' => 'transfer_out',
                    'qty' => -$itemData['qty_shipped'],
                    'current_stock' => Product::where('id', $item->product_id)
                        ->where('branch_id', $transfer->from_branch_id)
                        ->value('stock'),
                    'notes' => 'Transfer to ' . $transfer->toBranch->name,
                ]);

                // Deduct stock from source branch
                Product::where('id', $item->product_id)
                    ->where('branch_id', $transfer->from_branch_id)
                    ->decrement('stock', $itemData['qty_shipped']);
            }

            $transfer->ship(auth()->id());

            DB::connection('tenant')->commit();

            return response()->json([
                'success' => true,
                'message' => 'Transfer shipped successfully',
                'data' => $transfer->load('items.product')
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
     * Receive transfer
     * POST /api/stock-transfers/{id}/receive
     */
    public function receive(Request $request, $id)
    {
        $transfer = StockTransfer::where('tenant_id', auth()->user()->tenant_id)
            ->findOrFail($id);

        if (!$transfer->canReceive()) {
            return response()->json([
                'success' => false,
                'message' => 'Transfer cannot be received'
            ], 400);
        }

        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|exists:stock_transfer_items,id',
            'items.*.qty_received' => 'required|numeric|min:0',
        ]);

        DB::connection('tenant')->beginTransaction();

        try {
            // Update received quantities and calculate discrepancies
            foreach ($validated['items'] as $itemData) {
                $item = StockTransferItem::findOrFail($itemData['id']);
                $item->update([
                    'qty_received' => $itemData['qty_received'],
                    'qty_discrepancy' => $item->qty_shipped - $itemData['qty_received'],
                ]);

                // Create inventory movement (transfer_in)
                InventoryMovement::create([
                    'tenant_id' => auth()->user()->tenant_id,
                    'product_id' => $item->product_id,
                    'branch_id' => $transfer->to_branch_id,
                    'user_id' => auth()->id(),
                    'reference_number' => $transfer->transfer_number,
                    'type' => 'transfer_in',
                    'qty' => $itemData['qty_received'],
                    'current_stock' => Product::where('id', $item->product_id)
                        ->where('branch_id', $transfer->to_branch_id)
                        ->value('stock'),
                    'notes' => 'Transfer from ' . $transfer->fromBranch->name,
                ]);

                // Add stock to destination branch
                Product::where('id', $item->product_id)
                    ->where('branch_id', $transfer->to_branch_id)
                    ->increment('stock', $itemData['qty_received']);
            }

            $transfer->receive(auth()->id());

            DB::connection('tenant')->commit();

            return response()->json([
                'success' => true,
                'message' => 'Transfer received successfully',
                'data' => $transfer->load('items.product')
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
     * Cancel transfer
     * DELETE /api/stock-transfers/{id}
     */
    public function destroy($id)
    {
        $transfer = StockTransfer::where('tenant_id', auth()->user()->tenant_id)
            ->findOrFail($id);

        if (!$transfer->canCancel()) {
            return response()->json([
                'success' => false,
                'message' => 'Transfer cannot be cancelled'
            ], 400);
        }

        $transfer->cancel();

        return response()->json([
            'success' => true,
            'message' => 'Transfer cancelled successfully',
            'data' => $transfer
        ]);
    }

    /**
     * Get analytics dashboard
     * GET /api/stock-transfers/analytics/dashboard
     */
    public function dashboard()
    {
        $tenantId = auth()->user()->tenant_id;

        // Summary stats
        $totalTransfers = StockTransfer::where('tenant_id', $tenantId)->count();
        $inTransit = StockTransfer::where('tenant_id', $tenantId)
            ->where('status', 'in_transit')->count();
        $pending = StockTransfer::where('tenant_id', $tenantId)
            ->where('status', 'pending_approval')->count();
        $completed = StockTransfer::where('tenant_id', $tenantId)
            ->where('status', 'received')->count();

        // Recent transfers
        $recentTransfers = StockTransfer::with(['fromBranch', 'toBranch'])
            ->where('tenant_id', $tenantId)
            ->latest()
            ->limit(5)
            ->get();

        // Status distribution
        $statusDistribution = StockTransfer::where('tenant_id', $tenantId)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'summary' => [
                    'total' => $totalTransfers,
                    'in_transit' => $inTransit,
                    'pending' => $pending,
                    'completed' => $completed,
                ],
                'recent_transfers' => $recentTransfers,
                'status_distribution' => $statusDistribution,
            ]
        ]);
    }

    /**
     * Get in-transit report
     * GET /api/stock-transfers/reports/in-transit
     */
    public function inTransitReport(Request $request)
    {
        $query = StockTransfer::with(['fromBranch', 'toBranch', 'items.product', 'requestedBy'])
            ->where('tenant_id', auth()->user()->tenant_id)
            ->where('status', 'in_transit');

        if ($request->has('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->has('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $transfers = $query->latest()->get();

        return response()->json([
            'success' => true,
            'data' => $transfers
        ]);
    }

    /**
     * Get transfer history report
     * GET /api/stock-transfers/reports/history
     */
    public function historyReport(Request $request)
    {
        $query = StockTransfer::with(['fromBranch', 'toBranch', 'requestedBy', 'approvedBy', 'shippedBy', 'receivedBy'])
            ->where('tenant_id', auth()->user()->tenant_id);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->has('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        if ($request->has('from_branch_id')) {
            $query->where('from_branch_id', $request->from_branch_id);
        }

        if ($request->has('to_branch_id')) {
            $query->where('to_branch_id', $request->to_branch_id);
        }

        $transfers = $query->latest()->paginate($request->get('limit', 50));

        return response()->json([
            'success' => true,
            'data' => $transfers
        ]);
    }

    /**
     * Get branch comparison
     * GET /api/stock-transfers/reports/branch-comparison
     */
    public function branchComparison()
    {
        $tenantId = auth()->user()->tenant_id;

        // Get all branches
        $branches = \App\Models\Branch::where('tenant_id', $tenantId)->get();

        $comparison = [];

        foreach ($branches as $branch) {
            // Transfers from this branch
            $transfersFrom = StockTransfer::where('tenant_id', $tenantId)
                ->where('from_branch_id', $branch->id)
                ->where('status', 'received')
                ->count();

            // Transfers to this branch
            $transfersTo = StockTransfer::where('tenant_id', $tenantId)
                ->where('to_branch_id', $branch->id)
                ->where('status', 'received')
                ->count();

            // Total items transferred
            $itemsFrom = StockTransfer::where('tenant_id', $tenantId)
                ->where('from_branch_id', $branch->id)
                ->where('status', 'received')
                ->with('items')
                ->get()
                ->sum(function ($t) {
                    return $t->items->sum('qty_shipped');
                });

            $itemsTo = StockTransfer::where('tenant_id', $tenantId)
                ->where('to_branch_id', $branch->id)
                ->where('status', 'received')
                ->with('items')
                ->get()
                ->sum(function ($t) {
                    return $t->items->sum('qty_received');
                });

            $comparison[] = [
                'branch' => $branch,
                'transfers_from' => $transfersFrom,
                'transfers_to' => $transfersTo,
                'items_from' => $itemsFrom,
                'items_to' => $itemsTo,
                'net_flow' => $itemsTo - $itemsFrom,
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $comparison
        ]);
    }
}
