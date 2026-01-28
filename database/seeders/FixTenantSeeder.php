<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class FixTenantSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Default Tenant
        $tenant = Tenant::firstOrCreate(
            ['id' => 1],
            [
                'name' => 'Saga Default',
                'owner_name' => 'Admin',
                'business_type' => 'Retail',
                'is_active' => true
            ]
        );
        $this->command->info('Tenant ensured: ' . $tenant->name);

        // 2. Update Admin User
        $user = User::where('email', 'admin@sagatoko.com')->first();
        if ($user) {
            $user->tenant_id = $tenant->id;
            $user->save();
            $this->command->info('User linked to Tenant');
        }

        // 3. Update related tables
        DB::table('branches')->whereNull('tenant_id')->update(['tenant_id' => $tenant->id]);
        DB::table('categories')->whereNull('tenant_id')->update(['tenant_id' => $tenant->id]);
        DB::table('units')->whereNull('tenant_id')->update(['tenant_id' => $tenant->id]);

        // Inventory Tables (New)
        DB::table('products')->whereNull('tenant_id')->update(['tenant_id' => $tenant->id]);
        DB::table('product_units')->whereNull('tenant_id')->update(['tenant_id' => $tenant->id]);
        DB::table('inventory_movements')->whereNull('tenant_id')->update(['tenant_id' => $tenant->id]);

        $this->command->info('Fixed Tenant Links for All Tables (Branches, Cats, Units, Products, Movements).');
    }
}
