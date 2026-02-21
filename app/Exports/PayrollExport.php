<?php

namespace App\Exports;

use App\Models\Payroll;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PayrollExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $period;

    public function __construct($period)
    {
        $this->period = $period;
    }

    public function collection()
    {
        return Payroll::with(['employee.branch'])
            ->where('period', $this->period)
            ->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'NIK',
            'Nama Karyawan',
            'Jabatan',
            'Cabang',
            'Gaji Pokok',
            'Tunjangan',
            'Bonus',
            'Potongan',
            'Total Bersih',
            'Status',
            'Tanggal Bayar'
        ];
    }

    public function map($payroll): array
    {
        return [
            $payroll->id,
            $payroll->employee->nik,
            $payroll->employee->name,
            $payroll->employee->role,
            $payroll->employee->branch->name ?? '-',
            $payroll->basic_salary,
            $payroll->allowances,
            $payroll->bonuses,
            $payroll->deductions,
            $payroll->total_amount,
            $payroll->status,
            $payroll->payment_date
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
