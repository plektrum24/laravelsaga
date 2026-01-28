<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Unit;

class UnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $units = [
            'Pcs',
            'Pack',
            'Dus',
            'Bal',
            'Rol',
            'Pail',
            'Kg',
            '1/2',
            '1/4',
            'Gram',
            'Ons',
            'Lembar',
            'Ikat'
        ];

        foreach ($units as $name) {
            Unit::firstOrCreate(
                ['name' => $name], // Search by name
                [
                    'name' => $name,
                    'abbreviation' => $name, // Use name as abbr for now
                    'tenant_id' => 1 // Default tenant
                ]
            );
        }
    }
}
