<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Product;
use App\Models\Unit;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Traits\Exportable;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class PurchaseController extends Controller
{
    use Exportable;
    /**
     * List all purchases (goods-in)
     */
    public function index(Request $request)
    {
        $query = Purchase::with(['supplier', 'items.product', 'items.unit', 'branch']);

        if ($request->has('supplier_id') && $request->supplier_id) {
            $query->where('supplier_id', $request->supplier_id);
        }

        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        if ($request->has('search') && $request->search) {
            $query->where('reference_number', 'like', '%' . $request->search . '%');
        }

        $limit = $request->limit ?? 50;
        $purchases = $query->orderBy('date', 'desc')->paginate($limit);

        return response()->json([
            'success' => true,
            'data' => $purchases->items(),
            'pagination' => [
                'total' => $purchases->total(),
                'per_page' => $purchases->perPage(),
                'current_page' => $purchases->currentPage(),
                'last_page' => $purchases->lastPage(),
            ]
        ]);
    }

    /**
     * Create new purchase (goods-in) with batch tracking
     */
    public function store(Request $request)
    {
        $request->validate([
            'supplier_id' => 'nullable|exists:suppliers,id',
            'date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.unit_id' => 'required|exists:units,id',
            'items.*.qty' => 'required|numeric|min:0.0001',
            'items.*.buy_price' => 'required|numeric|min:0',
            'items.*.expiry_date' => 'nullable|date',
        ]);

        try {
            DB::connection('tenant')->beginTransaction();

            // Generate reference number
            $refNumber = 'GRN-' . date('Ymd') . '-' . str_pad(
                Purchase::whereDate('date', today())->count() + 1,
                3,
                '0',
                STR_PAD_LEFT
            );

            // Create Purchase header
            $purchase = Purchase::create([
                'supplier_id' => $request->supplier_id,
                'branch_id' => $request->branch_id,
                'user_id' => auth()->id(),
                'reference_number' => $refNumber,
                'date' => $request->date,
                'status' => $request->status ?? 'completed',
                'notes' => $request->notes,
                'total_amount' => 0,
            ]);

            $totalAmount = 0;

            // Create PurchaseItems (Batch records)
            foreach ($request->items as $item) {
                $subtotal = $item['qty'] * $item['buy_price'];
                $totalAmount += $subtotal;

                // Create batch record
                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $item['product_id'],
                    'unit_id' => $item['unit_id'],
                    'qty' => $item['qty'],
                    'current_stock' => $item['qty'], // Initialize current_stock = qty
                    'buy_price' => $item['buy_price'],
                    'expiry_date' => $item['expiry_date'] ?? null,
                    'subtotal' => $subtotal,
                ]);

                // Update product stock
                $product = Product::find($item['product_id']);
                if ($product && $product->track_stock) {
                    // Convert to base unit if needed
                    $baseQty = $this->convertToBaseUnit($item['product_id'], $item['unit_id'], $item['qty']);
                    $product->increment('stock', $baseQty);
                }
            }

            // Update total
            $purchase->update(['total_amount' => $totalAmount]);

            DB::connection('tenant')->commit();

            return response()->json([
                'success' => true,
                'message' => 'Penerimaan barang berhasil dicatat',
                'data' => $purchase->load('items.product', 'items.unit', 'supplier')
            ], 201);

        } catch (\Exception $e) {
            DB::connection('tenant')->rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show purchase detail
     */
    public function show($id)
    {
        $purchase = Purchase::with(['supplier', 'items.product', 'items.unit', 'branch', 'user'])
            ->findOrFail($id);

        return response()->json(['success' => true, 'data' => $purchase]);
    }

    /**
     * Delete purchase (only if draft/cancelled)
     */
    public function destroy($id)
    {
        $purchase = Purchase::findOrFail($id);

        if ($purchase->status === 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'Tidak bisa menghapus purchase yang sudah completed'
            ], 400);
        }

        $purchase->items()->delete();
        $purchase->delete();

        return response()->json([
            'success' => true,
            'message' => 'Purchase berhasil dihapus'
        ]);
    }

    /**
     * Update purchase
     */
    public function update(Request $request, $id)
    {
        $purchase = Purchase::findOrFail($id);

        if ($purchase->status !== 'draft') {
            return response()->json([
                'success' => false,
                'message' => 'Hanya purchase dengan status draft yang bisa diubah'
            ], 400);
        }

        $request->validate([
            'supplier_id' => 'nullable|exists:suppliers,id',
            'date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.unit_id' => 'required|exists:units,id',
            'items.*.qty' => 'required|numeric|min:0.0001',
            'items.*.buy_price' => 'required|numeric|min:0',
        ]);

        try {
            DB::connection('tenant')->beginTransaction();

            // Delete old items
            $purchase->items()->delete();

            // Update header
            $purchase->update([
                'supplier_id' => $request->supplier_id,
                'date' => $request->date,
                'notes' => $request->notes,
            ]);

            $totalAmount = 0;

            // Create new items
            foreach ($request->items as $item) {
                $subtotal = $item['qty'] * $item['buy_price'];
                $totalAmount += $subtotal;

                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $item['product_id'],
                    'unit_id' => $item['unit_id'],
                    'qty' => $item['qty'],
                    'current_stock' => $item['qty'],
                    'buy_price' => $item['buy_price'],
                    'subtotal' => $subtotal,
                ]);
            }

            $purchase->update(['total_amount' => $totalAmount]);

            DB::connection('tenant')->commit();

            return response()->json([
                'success' => true,
                'message' => 'Purchase berhasil diupdate',
                'data' => $purchase->load('items.product', 'items.unit')
            ]);

        } catch (\Exception $e) {
            DB::connection('tenant')->rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Print GRN receipt
     */
    public function printReceipt($id)
    {
        $purchase = Purchase::with(['supplier', 'items.product', 'items.unit'])
            ->findOrFail($id);

        // For now, return JSON data for receipt
        // Can be enhanced to generate PDF
        return response()->json([
            'success' => true,
            'data' => [
                'receipt' => $purchase,
                'print_url' => '/api/purchases/' . $id . '/receipt/pdf'
            ]
        ]);
    }

    /**
     * Export purchases to Excel
     */
    public function exportExcel(Request $request)
    {
        try {
            $query = Purchase::with(['supplier', 'items.product', 'items.unit', 'branch']);

            // Apply filters
            if ($request->has('supplier_id') && $request->supplier_id) {
                $query->where('supplier_id', $request->supplier_id);
            }

            if ($request->has('status') && $request->status) {
                $query->where('status', $request->status);
            }

            if ($request->has('date_from') && $request->date_from) {
                $query->whereDate('date', '>=', $request->date_from);
            }

            if ($request->has('date_to') && $request->date_to) {
                $query->whereDate('date', '<=', $request->date_to);
            }

            $purchases = $query->orderBy('date', 'desc')->get();

            $data = [];
            foreach ($purchases as $purchase) {
                foreach ($purchase->items as $item) {
                    $data[] = [
                        'Tanggal' => date('d/m/Y', strtotime($purchase->date)),
                        'No. Referensi' => $purchase->reference_number,
                        'Supplier' => $purchase->supplier ? $purchase->supplier->name : '-',
                        'Branch' => $purchase->branch ? $purchase->branch->name : '-',
                        'Produk' => $item->product ? $item->product->name : '-',
                        'SKU' => $item->product ? $item->product->sku : '-',
                        'Qty' => $item->qty,
                        'Unit' => $item->unit ? $item->unit->name : '-',
                        'Harga Beli' => 'Rp ' . number_format($item->buy_price, 0, ',', '.'),
                        'Subtotal' => 'Rp ' . number_format($item->subtotal, 0, ',', '.'),
                        'Status' => ucfirst($purchase->status),
                    ];
                }
            }

            $headers = [
                'Tanggal',
                'No. Referensi',
                'Supplier',
                'Branch',
                'Produk',
                'SKU',
                'Qty',
                'Unit',
                'Harga Beli',
                'Subtotal',
                'Status'
            ];

            return $this->exportToExcel($data, $headers, 'Purchases');
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Export failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export purchases to PDF
     */
    public function exportPdf(Request $request)
    {
        try {
            $query = Purchase::with(['supplier', 'items.product', 'items.unit', 'branch']);

            // Apply filters
            if ($request->has('supplier_id') && $request->supplier_id) {
                $query->where('supplier_id', $request->supplier_id);
            }

            if ($request->has('status') && $request->status) {
                $query->where('status', $request->status);
            }

            if ($request->has('date_from') && $request->date_from) {
                $query->whereDate('date', '>=', $request->date_from);
            }

            if ($request->has('date_to') && $request->date_to) {
                $query->whereDate('date', '<=', $request->date_to);
            }

            $purchases = $query->orderBy('date', 'desc')->get();

            $html = view('exports.purchases.pdf', compact('purchases'))->render();
            
            return $this->renderPdf($html, 'Purchases', ['paper' => 'a4', 'orientation' => 'landscape']);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Export failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Convert quantity to base unit
     */
    private function convertToBaseUnit($productId, $unitId, $qty)
    {
        $productUnit = \App\Models\ProductUnit::where('product_id', $productId)
            ->where('unit_id', $unitId)
            ->first();

        if ($productUnit) {
            return $qty * $productUnit->conversion_qty;
        }

        return $qty;
    }
}
