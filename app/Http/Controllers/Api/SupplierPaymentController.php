<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use App\Models\SupplierPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SupplierPaymentController extends Controller
{
    public function index(Request $request, $supplierId)
    {
        $tenantId = auth()->user()->tenant_id;
        $payments = SupplierPayment::with('user:id,name')
            ->where('tenant_id', $tenantId)
            ->where('supplier_id', $supplierId)
            ->latest('date')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $payments
        ]);
    }

    public function store(Request $request, $supplierId)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'payment_method' => 'required|string',
            'date' => 'required|date',
            'notes' => 'nullable|string'
        ]);

        $tenantId = auth()->user()->tenant_id;
        $supplier = Supplier::where('tenant_id', $tenantId)->findOrFail($supplierId);

        try {
            DB::beginTransaction();

            // Create payment record
            $payment = SupplierPayment::create([
                'tenant_id' => $tenantId,
                'supplier_id' => $supplier->id,
                'amount' => $request->amount,
                'payment_method' => $request->payment_method,
                'date' => $request->date,
                'notes' => $request->notes,
                'created_by' => auth()->id()
            ]);

            // Deduct from supplier debt
            $supplier->decrement('debt_balance', $request->amount);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pembayaran berhasil dicatat',
                'data' => $payment,
                'new_balance' => $supplier->fresh()->debt_balance
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal mencatat pembayaran: ' . $e->getMessage()
            ], 500);
        }
    }
}
