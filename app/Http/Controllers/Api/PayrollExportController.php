<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payroll;
use App\Models\Employee;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\PayrollExport;
use Carbon\Carbon;

class PayrollExportController extends Controller
{
    /**
     * Download individual salary slip as PDF.
     */
    public function downloadPdf(Payroll $payroll)
    {
        $payroll->load(['employee.branch', 'employee.user']);

        $data = [
            'payroll' => $payroll,
            'tenant' => auth()->user()->tenant ?? (object)['name' => 'SAGA TOKO APP'],
            'date' => Carbon::now()->format('d/m/Y'),
        ];

        $pdf = Pdf::loadView('exports.payroll-slip', $data);

        $filename = 'Slip_Gaji_' . str_replace(' ', '_', $payroll->employee->name) . '_' . $payroll->period . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Export monthly payroll to Excel.
     */
    public function exportExcel(Request $request)
    {
        $request->validate([
            'period' => 'required|date_format:Y-m',
        ]);

        $period = $request->period;

        // We'll create the Excel export class next
        return Excel::download(new PayrollExport($period), "Payroll_Report_$period.xlsx");
    }
}
