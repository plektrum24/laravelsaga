<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\InventoryMovement;
use App\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Traits\Exportable;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class InventoryController extends Controller
{
    use Exportable;
    public function adjustStock(Request $request, $id)
    {
        $user = auth()->user();

        // Get branch ID with multiple fallbacks
        $branchId = $request->branch_id
            ?? $user->current_branch_id
            ?? $user->branch_id;

        // If still no branch, try to get from tenant's branches
        if (!$branchId && $user->tenant_id) {
            $branchId = \App\Models\Branch::where('tenant_id', $user->tenant_id)
                ->orderBy('id')
                ->first()?->id;
        }

        // Validate branch_id exists
        if (!$branchId) {
            return response()->json([
                'success' => false,
                'message' => 'Branch tidak ditemukan. Silakan pilih branch terlebih dahulu.'
            ], 400);
        }

        $validated = $request->validate([
            'type' => 'required|in:add,subtract',
            'quantity' => 'required|numeric|min:0.01',
            'reason' => 'nullable|string',
            'branch_id' => ['nullable', Rule::exists('branches', 'id')]
        ]);

        try {
            DB::connection('tenant')->beginTransaction();
            $product = Product::findOrFail($id);

            // Ensure product belongs to the branch or tenant
            if ($product->branch_id && $product->branch_id != $branchId) {
                // Allow if user is adjusting stock for their branch
                $product->branch_id = $branchId;
                $product->save();
            }

            $oldStock = $product->stock;
            $qty = $validated['quantity'];

            if ($validated['type'] === 'subtract') {
                if ($product->stock < $qty) {
                    return response()->json(['success' => false, 'message' => 'Stock tidak mencukupi'], 400);
                }
                $product->decrement('stock', $qty);
                $newStock = $product->stock;
            } else {
                $product->increment('stock', $qty);
                $newStock = $product->stock;
            }

            // Log Movement
            InventoryMovement::create([
                'tenant_id' => $user->tenant_id,
                'product_id' => $product->id,
                'branch_id' => $branchId,
                'user_id' => $user->id,
                'reference_number' => 'ADJ-' . date('Ymd') . '-' . mt_rand(1000, 9999),
                'type' => 'adjustment',
                'qty' => $validated['type'] === 'add' ? $qty : -$qty,
                'current_stock' => $newStock,
                'notes' => $validated['reason'] ?? 'Manual Adjustment',
            ]);

            DB::connection('tenant')->commit();

            return response()->json([
                'success' => true,
                'message' => 'Stock berhasil diupdate',
                'data' => [
                    'old_stock' => $oldStock,
                    'new_stock' => $newStock,
                    'difference' => $validated['type'] === 'add' ? $qty : -$qty
                ]
            ]);

        } catch (\Exception $e) {
            DB::connection('tenant')->rollBack();
            return response()->json([
                'success' => false, 
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
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

    /**
     * Export inventory movements to Excel
     */
    public function exportExcel(Request $request)
    {
        try {
            $query = InventoryMovement::with(['product', 'branch', 'user']);

            // Apply filters
            if ($request->has('product_id') && $request->product_id) {
                $query->where('product_id', $request->product_id);
            }

            if ($request->has('type') && $request->type) {
                $query->where('type', $request->type);
            }

            if ($request->has('date_from') && $request->date_from) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }

            if ($request->has('date_to') && $request->date_to) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            $movements = $query->orderBy('created_at', 'desc')->get();

            $data = [];
            foreach ($movements as $movement) {
                $data[] = [
                    'Tanggal' => date('d/m/Y H:i', strtotime($movement->created_at)),
                    'Reference' => $movement->reference_number,
                    'Produk' => $movement->product ? $movement->product->name : '-',
                    'SKU' => $movement->product ? $movement->product->sku : '-',
                    'Branch' => $movement->branch ? $movement->branch->name : '-',
                    'Tipe' => ucfirst($movement->type),
                    'Qty' => $movement->qty,
                    'Stock Sebelum' => $movement->current_stock - $movement->qty,
                    'Stock Sesudah' => $movement->current_stock,
                    'User' => $movement->user ? $movement->user->name : '-',
                    'Notes' => $movement->notes ?? '-',
                ];
            }

            $headers = [
                'Tanggal',
                'Reference',
                'Produk',
                'SKU',
                'Branch',
                'Tipe',
                'Qty',
                'Stock Sebelum',
                'Stock Sesudah',
                'User',
                'Notes'
            ];

            return $this->exportToExcel($data, $headers, 'Inventory_Movements');
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Export failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export inventory movements to PDF
     */
    public function exportPdf(Request $request)
    {
        try {
            $query = InventoryMovement::with(['product', 'branch', 'user']);

            // Apply filters
            if ($request->has('product_id') && $request->product_id) {
                $query->where('product_id', $request->product_id);
            }

            if ($request->has('type') && $request->type) {
                $query->where('type', $request->type);
            }

            if ($request->has('date_from') && $request->date_from) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }

            if ($request->has('date_to') && $request->date_to) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            $movements = $query->orderBy('created_at', 'desc')->get();

            $html = view('exports.inventory-movements-pdf', compact('movements'))->render();
            
            return $this->renderPdf($html, 'Inventory_Movements', ['paper' => 'a4', 'orientation' => 'landscape']);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Export failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export stock adjustment history
     */
    public function exportAdjustments(Request $request)
    {
        try {
            $query = InventoryMovement::where('type', 'adjustment')
                ->with(['product', 'branch', 'user']);

            if ($request->has('date_from') && $request->date_from) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }

            if ($request->has('date_to') && $request->date_to) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            $adjustments = $query->orderBy('created_at', 'desc')->get();

            $data = [];
            foreach ($adjustments as $adj) {
                $data[] = [
                    'Tanggal' => date('d/m/Y H:i', strtotime($adj->created_at)),
                    'Reference' => $adj->reference_number,
                    'Produk' => $adj->product ? $adj->product->name : '-',
                    'Branch' => $adj->branch ? $adj->branch->name : '-',
                    'Adjustment' => ($adj->qty > 0 ? '+' : '') . $adj->qty,
                    'Stock After' => $adj->current_stock,
                    'Reason' => $adj->notes ?? '-',
                    'User' => $adj->user ? $adj->user->name : '-',
                ];
            }

            $headers = [
                'Tanggal',
                'Reference',
                'Produk',
                'Branch',
                'Adjustment',
                'Stock After',
                'Reason',
                'User'
            ];

            return $this->exportToExcel($data, $headers, 'Stock_Adjustments');
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Export failed: ' . $e->getMessage()
            ], 500);
        }
    }
}
