<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class TransactionExportController extends Controller
{
    public function downloadReceipt(Transaction $transaction)
    {
        $transaction->load(['items.product', 'customer', 'user', 'branch']);

        $data = [
            'transaction' => $transaction,
            'tenant' => auth()->user()->tenant ?? (object)['name' => 'SAGA TOKO APP'],
            'branch' => $transaction->branch ?? (object)['name' => 'Main Branch', 'address' => 'Jl. Example No. 123'],
        ];

        // Custom paper size for thermal receipt (approx 80mm wide, auto height or fixed long)
        // 80mm is about 226pt. Height 200mm is 567pt.
        $pdf = Pdf::loadView('exports.receipt-thermal', $data)
            ->setPaper([0, 0, 226, 600], 'portrait');

        $filename = 'Receipt_' . $transaction->invoice_number . '.pdf';

        return $pdf->stream($filename);
    }
}
