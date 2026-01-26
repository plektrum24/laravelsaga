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
            CreateOperationalUsersSeeder::class,
        ]);

        // 2. Create Main Branch
        \Illuminate\Support\Facades\DB::table('branches')->insert([
            'name' => 'Toko Pusat',
            'code' => 'B001',
            'address' => 'Jl. Raya Utama No. 1',
            'is_main' => true,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 3. Create Units
        \Illuminate\Support\Facades\DB::table('units')->insert([
            ['name' => 'Pcs', 'sort_order' => 1],
            ['name' => 'Box', 'sort_order' => 2],
            ['name' => 'Pack', 'sort_order' => 3],
            ['name' => 'Kg', 'sort_order' => 4],
        ]);

        // 4. Create Categories
        \Illuminate\Support\Facades\DB::table('categories')->insert([
            ['name' => 'Umum', 'prefix' => 'GEN', 'is_active' => true],
            ['name' => 'Makanan', 'prefix' => 'FOOD', 'is_active' => true],
            ['name' => 'Minuman', 'prefix' => 'DRK', 'is_active' => true],
            ['name' => 'Sembako', 'prefix' => 'SEM', 'is_active' => true],
        ]);

        // 5. Create Dummy Suppliers
        \Illuminate\Support\Facades\DB::table('suppliers')->insert([
            ['name' => 'Agen Makmur Jaya', 'code' => 'SUP001', 'phone' => '08123456789', 'address' => 'Jakarta'],
            ['name' => 'PT. Distribusi Utama', 'code' => 'SUP002', 'phone' => '08987654321', 'address' => 'Surabaya'],
        ]);

        // 6. Create Dummy Customers
        \Illuminate\Support\Facades\DB::table('customers')->insert([
            ['name' => 'Pelanggan Umum', 'phone' => '-', 'address' => '-'],
            ['name' => 'Budi Santoso', 'phone' => '08111111111', 'address' => 'Jakarta'],
        ]);
    }
}
