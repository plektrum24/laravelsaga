<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ProductsExport implements FromCollection, WithHeadings, WithMapping
{
    protected $tenantConnection;

    public function __construct($tenantConnection)
    {
        $this->tenantConnection = $tenantConnection;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Product::on($this->tenantConnection)
            ->with(['category', 'units.unit'])
            ->get();
    }

    public function headings(): array
    {
        return [
            'NAMAITEM',
            'BARCODE',
            'KATEGORI',
            'SATUAN1',
            'SATUAN2',
            'SATUAN3',
            'SATUAN4',
            'SATUAN5',
            'KONVERSI1',
            'KONVERSI2',
            'KONVERSI3',
            'KONVERSI4',
            'KONVERSI5',
            'HARGABELI1',
            'HARGABELI2',
            'HARGABELI3',
            'HARGABELI4',
            'HARGABELI5',
            'HARGAJUAL1',
            'HARGAJUAL2',
            'HARGAJUAL3',
            'HARGAJUAL4',
            'HARGAJUAL5',
            'STOK',
            'STOKMIN',
            'EXPIRY'
        ];
    }

    public function map($product): array
    {
        $units = $product->units->sortBy('conversion_qty');
        $u1 = $units->shift();
        $u2 = $units->shift();
        $u3 = $units->shift();
        $u4 = $units->shift();
        $u5 = $units->shift();

        return [
            $product->name,
            $product->barcode ?? '-',
            $product->category->name ?? '-',
            // SATUAN 1-5
            $u1 && $u1->unit ? $u1->unit->name : '-',
            $u2 && $u2->unit ? $u2->unit->name : '-',
            $u3 && $u3->unit ? $u3->unit->name : '-',
            $u4 && $u4->unit ? $u4->unit->name : '-',
            $u5 && $u5->unit ? $u5->unit->name : '-',
            // KONVERSI 1-5
            $u1 ? (float) $u1->conversion_qty : 1,
            $u2 ? (float) $u2->conversion_qty : '-',
            $u3 ? (float) $u3->conversion_qty : '-',
            $u4 ? (float) $u4->conversion_qty : '-',
            $u5 ? (float) $u5->conversion_qty : '-',
            // HARGABELI 1-5
            $u1 ? (float) $u1->buy_price : '-',
            $u2 ? (float) $u2->buy_price : '-',
            $u3 ? (float) $u3->buy_price : '-',
            $u4 ? (float) $u4->buy_price : '-',
            $u5 ? (float) $u5->buy_price : '-',
            // HARGAJUAL 1-5
            $u1 ? (float) $u1->sell_price : '-',
            $u2 ? (float) $u2->sell_price : '-',
            $u3 ? (float) $u3->sell_price : '-',
            $u4 ? (float) $u4->sell_price : '-',
            $u5 ? (float) $u5->sell_price : '-',

            (float) $product->stock,
            (float) $product->min_stock,
            $product->expired_date ? (is_string($product->expired_date) ? $product->expired_date : $product->expired_date->format('Y-m-d')) : '-'
        ];
    }
}
