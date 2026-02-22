<?php

namespace App\Services;

use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoicePdfService
{
    /**
     * Generate PDF for invoice
     */
    public function generate(Invoice $invoice)
    {
        $data = [
            'invoice' => $invoice,
            'tenant' => $invoice->tenant,
            'plan' => $invoice->subscription,
        ];

        $pdf = Pdf::loadView('exports.invoice-pdf', $data);
        $pdf->setPaper('A4', 'portrait');

        // Save PDF
        $filename = 'invoice_' . $invoice->invoice_number . '.pdf';
        $path = storage_path('app/invoices/' . $filename);

        // Ensure directory exists
        if (!file_exists(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        $pdf->save($path);

        // Update invoice with PDF path
        $invoice->update(['pdf_path' => $path]);

        return $path;
    }

    /**
     * Download PDF
     */
    public function download(Invoice $invoice)
    {
        if (!$invoice->pdf_path || !file_exists($invoice->pdf_path)) {
            $this->generate($invoice);
        }

        return response()->download($invoice->pdf_path);
    }
}
