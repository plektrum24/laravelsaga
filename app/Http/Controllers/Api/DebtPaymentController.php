<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use App\Models\Purchase;
use App\Models\DebtPayment;
use App\Models\DebtPaymentItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DebtPaymentController extends Controller
{
    /**
     * Get all supplier debts
     * GET /api/debts
     */
    public function index(Request $request)
    {
        $query = Purchase::with(['supplier'])
            ->where('tenant_id', auth()->user()->tenant_id)
            ->where('payment_status', '!=', 'paid')
            ->whereNotNull('supplier_id');

        if ($request->has('status')) {
            $query->where('payment_status', $request->status);
        }

        if ($request->has('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        $debts = $query->orderBy('due_date', 'asc')->paginate($request->get('limit', 50));

        return response()->json([
            'success' => true,
            'data' => $debts->items(),
            'pagination' => [
                'total' => $debts->total(),
                'per_page' => $debts->perPage(),
                'current_page' => $debts->currentPage(),
                'last_page' => $debts->lastPage(),
            ]
        ]);
    }

    /**
     * Get debt details
     * GET /api/debts/{id}
     */
    public function show($id)
    {
        $debt = Purchase::with(['supplier', 'payments'])
            ->where('tenant_id', auth()->user()->tenant_id)
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $debt
        ]);
    }

    /**
     * Make debt payment
     * POST /api/debts/{id}/pay
     */
    public function pay(Request $request, $id)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'payment_method' => 'required|in:cash,transfer,check',
            'notes' => 'nullable|string',
            'reference_number' => 'nullable|string',
        ]);

        try {
            DB::connection('tenant')->beginTransaction();

            $purchase = Purchase::where('tenant_id', auth()->user()->tenant_id)
                ->findOrFail($id);

            $remaining = $purchase->total_amount - $purchase->paid_amount;

            if ($validated['amount'] > $remaining) {
                return response()->json([
                    'success' => false,
                    'message' => 'Jumlah pembayaran melebihi sisa hutang'
                ], 400);
            }

            // Create payment record
            $payment = DebtPayment::create([
                'tenant_id' => auth()->user()->tenant_id,
                'purchase_id' => $purchase->id,
                'supplier_id' => $purchase->supplier_id,
                'amount' => $validated['amount'],
                'payment_date' => $validated['payment_date'],
                'payment_method' => $validated['payment_method'],
                'notes' => $validated['notes'] ?? null,
                'reference_number' => $validated['reference_number'] ?? null,
                'user_id' => auth()->id(),
            ]);

            // Update purchase paid amount
            $purchase->increment('paid_amount', $validated['amount']);

            // Update payment status
            if ($purchase->paid_amount >= $purchase->total_amount) {
                $purchase->update(['payment_status' => 'paid']);
            } elseif ($purchase->paid_amount > 0) {
                $purchase->update(['payment_status' => 'partial']);
            }

            DB::connection('tenant')->commit();

            return response()->json([
                'success' => true,
                'message' => 'Pembayaran hutang berhasil',
                'data' => $payment
            ]);

        } catch (\Exception $e) {
            DB::connection('tenant')->rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get payment history
     * GET /api/debts/payments/history
     */
    public function paymentHistory(Request $request)
    {
        $query = DebtPayment::with(['supplier', 'purchase', 'user'])
            ->where('tenant_id', auth()->user()->tenant_id);

        if ($request->has('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        if ($request->has('date_from')) {
            $query->where('payment_date', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->where('payment_date', '<=', $request->date_to);
        }

        $payments = $query->orderBy('payment_date', 'desc')->paginate($request->get('limit', 50));

        return response()->json([
            'success' => true,
            'data' => $payments->items(),
            'pagination' => [
                'total' => $payments->total(),
                'per_page' => $payments->perPage(),
                'current_page' => $payments->currentPage(),
                'last_page' => $payments->lastPage(),
            ]
        ]);
    }

    /**
     * Get debt statistics
     * GET /api/debts/statistics
     */
    public function statistics()
    {
        try {
            $user = auth()->user();
            if (!$user || !$user->tenant_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated or no tenant'
                ], 401);
            }

            $tenantId = $user->tenant_id;

            $totalDebt = Purchase::where('tenant_id', $tenantId)
                ->where('payment_status', '!=', 'paid')
                ->sum(DB::raw('total_amount - paid_amount'));

            $paidThisMonth = DebtPayment::where('tenant_id', $tenantId)
                ->whereMonth('payment_date', now()->month)
                ->whereYear('payment_date', now()->year)
                ->sum('amount');

            $dueThisMonth = Purchase::where('tenant_id', $tenantId)
                ->whereMonth('due_date', now()->month)
                ->whereYear('due_date', now()->year)
                ->sum(DB::raw('total_amount - paid_amount'));

            $overdue = Purchase::where('tenant_id', $tenantId)
                ->where('payment_status', '!=', 'paid')
                ->where('due_date', '<', now())
                ->sum(DB::raw('total_amount - paid_amount'));

            return response()->json([
                'success' => true,
                'data' => [
                    'total_debt' => $totalDebt ?? 0,
                    'paid_this_month' => $paidThisMonth ?? 0,
                    'due_this_month' => $dueThisMonth ?? 0,
                    'overdue' => $overdue ?? 0,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch statistics: ' . $e->getMessage()
            ], 500);
        }
    }
}
