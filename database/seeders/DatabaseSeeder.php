<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create User
        // 1. Create User (Manual instead of Factory to avoid Faker error in Prod)
        \App\Models\User::firstOrCreate(
        ['email' => 'admin@sagatoko.com'],
        [
            'name' => 'Demo Owner',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
        ]
        );

        // Call Operational Seeder
        $this->call([
            CreateOperationalUsersSeeder::class ,
        ]);

        // 2. Create Main Branch
        \Illuminate\Support\Facades\DB::table('branches')->updateOrInsert(
        ['code' => 'B001'],
        [
            'name' => 'Toko Pusat',
            'address' => 'Jl. Raya Utama No. 1',
            'is_main' => true,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]
        );

        // 3. Create Units
        $this->call([
            UnitSeeder::class ,
        ]);

        // 4. Create Categories
        foreach ([
        ['name' => 'Umum', 'prefix' => 'GEN', 'is_active' => true],
        ['name' => 'Makanan', 'prefix' => 'FOOD', 'is_active' => true],
        ['name' => 'Minuman', 'prefix' => 'DRK', 'is_active' => true],
        ['name' => 'Sembako', 'prefix' => 'SEM', 'is_active' => true],
        ] as $category) {
            \Illuminate\Support\Facades\DB::table('categories')->updateOrInsert(
            ['prefix' => $category['prefix']],
                $category + ['created_at' => now(), 'updated_at' => now()]
            );
        }

        // 5. Create Dummy Suppliers
        foreach ([
        ['name' => 'Agen Makmur Jaya', 'code' => 'SUP001', 'phone' => '08123456789', 'address' => 'Jakarta'],
        ['name' => 'PT. Distribusi Utama', 'code' => 'SUP002', 'phone' => '08987654321', 'address' => 'Surabaya'],
        ] as $supplier) {
            \Illuminate\Support\Facades\DB::table('suppliers')->updateOrInsert(
            ['code' => $supplier['code']],
                $supplier + ['created_at' => now(), 'updated_at' => now()]
            );
        }

        // 6. Create Dummy Customers
        foreach ([
        ['name' => 'Pelanggan Umum', 'phone' => '-', 'address' => '-'],
        ['name' => 'Budi Santoso', 'phone' => '08111111111', 'address' => 'Jakarta'],
        ] as $customer) {
            \Illuminate\Support\Facades\DB::table('customers')->updateOrInsert(
            ['name' => $customer['name'], 'phone' => $customer['phone']],
                $customer + ['created_at' => now(), 'updated_at' => now()]
            );
        }
    }
}
