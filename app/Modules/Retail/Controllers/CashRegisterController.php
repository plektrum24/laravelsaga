<?php

namespace App\Modules\Retail\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Modules\Retail\Models\CashRegister;
use App\Modules\Retail\Models\CashExpense;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CashRegisterController extends Controller
{
    // Get current open register for authenticated user
    public function current(Request $request)
    {
        $register = CashRegister::where('user_id', $request->user()->id)
            ->where('status', 'open')
            ->latest()
            ->first();

        if (!$register) {
            return response()->json(['success' => true, 'data' => null]);
        }

        // Calculate totals dynamically (or rely on stored columns if updated strictly)
        // Ideally we sum up transactions on the fly or keep updating the column.
        // For now, let's assume we rely on the column being updated by Sales Logic.
        // But for expenses, we can sum specific relation.
        $register->total_expenses = $register->expenses()->sum('amount');

        return response()->json(['success' => true, 'data' => $register]);
    }

    public function open(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'start_cash' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        // Check if already open
        $existing = CashRegister::where('user_id', $request->user()->id)
            ->where('status', 'open')
            ->first();
        if ($existing) {
            return response()->json(['success' => false, 'message' => 'Register already open'], 400);
        }

        $register = CashRegister::create([
            'tenant_id' => $request->user()->tenant_id,
            'branch_id' => $request->user()->branch_id,
            'user_id' => $request->user()->id,
            'start_cash' => $request->start_cash,
            'opened_at' => now(),
            'status' => 'open'
        ]);

        return response()->json(['success' => true, 'data' => $register]);
    }

    public function close(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'end_cash' => 'required|numeric|min:0',
            'notes' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $register = CashRegister::where('user_id', $request->user()->id)
            ->where('status', 'open')
            ->latest()
            ->first();

        if (!$register) {
            return response()->json(['success' => false, 'message' => 'No open register found'], 404);
        }

        // Calculate Expected Cash
        // Start + Cash Sales - Expenses
        $expected = $register->start_cash + $register->total_cash_sales - $register->total_expenses;
        $diff = $request->end_cash - $expected;

        $register->update([
            'end_cash' => $request->end_cash,
            'diff_amount' => $diff,
            'closed_at' => now(),
            'status' => 'closed',
            'notes' => $request->notes
        ]);

        return response()->json(['success' => true, 'data' => $register]);
    }

    public function addExpense(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:1',
            'note' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $register = CashRegister::where('user_id', $request->user()->id)
            ->where('status', 'open')
            ->latest()
            ->first();

        if (!$register) {
            return response()->json(['success' => false, 'message' => 'Register not open'], 400);
        }

        try {
            DB::beginTransaction();

            CashExpense::create([
                'tenant_id' => $request->user()->tenant_id,
                'cash_register_id' => $register->id,
                'user_id' => $request->user()->id,
                'amount' => $request->amount,
                'note' => $request->note
            ]);

            // Update register total
            $register->increment('total_expenses', $request->amount);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Expense recorded']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
