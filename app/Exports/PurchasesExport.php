<?php

namespace App\Exports;

use App\Models\Purchase;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PurchasesExport implements FromQuery, WithMapping, WithHeadings, WithStyles, WithTitle
{
    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function query()
    {
        $query = Purchase::with(['supplier', 'items.product', 'items.unit', 'branch']);

        if (isset($this->filters['supplier_id'])) {
            $query->where('supplier_id', $this->filters['supplier_id']);
        }

        if (isset($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }

        if (isset($this->filters['date_from'])) {
            $query->whereDate('date', '>=', $this->filters['date_from']);
        }

        if (isset($this->filters['date_to'])) {
            $query->whereDate('date', '<=', $this->filters['date_to']);
        }

        return $query->orderBy('date', 'desc');
    }

    public function map($purchase): array
    {
        $rows = [];
        
        foreach ($purchase->items as $item) {
            $rows[] = [
                date('d/m/Y', strtotime($purchase->date)),
                $purchase->reference_number,
                $purchase->supplier ? $purchase->supplier->name : '-',
                $purchase->branch ? $purchase->branch->name : '-',
                $item->product ? $item->product->name : '-',
                $item->product ? $item->product->sku : '-',
                $item->qty,
                $item->unit ? $item->unit->name : '-',
                $item->buy_price,
                $item->subtotal,
                ucfirst($purchase->status),
            ];
        }

        return $rows;
    }

    public function headings(): array
    {
        return [
            'Date',
            'Reference No',
            'Supplier',
            'Branch',
            'Product',
            'SKU',
            'Qty',
            'Unit',
            'Buy Price',
            'Subtotal',
            'Status'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }

    public function title(): string
    {
        return 'Purchases';
    }
}
