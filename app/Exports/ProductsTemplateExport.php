<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Collection;

class ProductsTemplateExport implements FromCollection, WithHeadings
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return new Collection([
            [
                'Indomie Goreng Original', // NAMAITEM
                '0123456789123',           // BARCODE
                'Makanan Instan',          // KATEGORI
                'Pcs',   // SATUAN1
                'Pack',  // SATUAN2
                'Dus',   // SATUAN3
                '-',     // SATUAN4
                '-',     // SATUAN5
                '1',     // KONVERSI1
                '10',    // KONVERSI2
                '40',    // KONVERSI3
                '-',     // KONVERSI4
                '-',     // KONVERSI5
                '2500',  // HARGABELI1
                '22000', // HARGABELI2
                '85000', // HARGABELI3
                '-',     // HARGABELI4
                '-',     // HARGABELI5
                '3500',  // HARGAJUAL1
                '30000', // HARGAJUAL2
                '115000',// HARGAJUAL3
                '-',     // HARGAJUAL4
                '-',     // HARGAJUAL5
                '100',   // STOK
                '10',    // STOKMIN
                '2026-12-31' // EXPIRY
            ]
        ]);
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
}
