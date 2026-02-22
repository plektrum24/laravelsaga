<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StockTransfer;
use Barryvdh\DomPDF\Facade\Pdf;

class StockTransferExportController extends Controller
{
    /**
     * Generate PDF for Stock Transfer Order
     * GET /api/stock-transfers/{id}/print
     */
    public function printTransferOrder($id)
    {
        $transfer = StockTransfer::with([
            'fromBranch',
            'toBranch',
            'requestedBy',
            'approvedBy',
            'shippedBy',
            'receivedBy',
            'items.product',
            'items.unit'
        ])->where('tenant_id', auth()->user()->tenant_id)->findOrFail($id);

        $pdf = Pdf::loadView('exports.stock-transfer-order', compact('transfer'));
        $pdf->setPaper('A4', 'portrait');
        
        return $pdf->download('TO-' . $transfer->transfer_number . '.pdf');
    }

    /**
     * Generate PDF for Stock Transfer Receipt
     * GET /api/stock-transfers/{id}/print-receipt
     */
    public function printReceipt($id)
    {
        $transfer = StockTransfer::with([
            'fromBranch',
            'toBranch',
            'requestedBy',
            'approvedBy',
            'shippedBy',
            'receivedBy',
            'items.product',
            'items.unit'
        ])->where('tenant_id', auth()->user()->tenant_id)->findOrFail($id);

        if ($transfer->status !== 'received') {
            return response()->json([
                'success' => false,
                'message' => 'Can only print receipt for received transfers'
            ], 400);
        }

        $pdf = Pdf::loadView('exports.stock-transfer-receipt', compact('transfer'));
        $pdf->setPaper('A4', 'portrait');
        
        return $pdf->download('Receipt-' . $transfer->transfer_number . '.pdf');
    }
}
