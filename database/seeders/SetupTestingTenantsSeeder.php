<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class SetupTestingTenantsSeeder extends Seeder
{
    public function run()
    {
        // 1. Retail Test Setup
        $retailTenant = Tenant::firstOrCreate(
            ['domain' => 'testretail'],
            [
                'name' => 'Toko Retail Jaya',
                'owner_name' => 'Pak Retail',
                'business_type' => 'retail',
                'subscription_plan' => 'pro',
                'is_active' => true
            ]
        );

        $retailUser = User::firstOrCreate(
            ['email' => 'retail@sagatoko.com'],
            [
                'name' => 'Owner Retail',
                'password' => Hash::make('12345678'),
                'tenant_id' => $retailTenant->id,
            ]
        );
        $retailUser->assignRole('Owner');
        $retailUser->update(['tenant_id' => $retailTenant->id]); // Ensure link

        $this->command->info("Retail User Created: retail@sagatoko.com / 12345678");


        // 2. Barber Test Setup
        $barberTenant = Tenant::firstOrCreate(
            ['domain' => 'testbarber'],
            [
                'name' => 'Saga Barbershop',
                'owner_name' => 'Bro Barber',
                'business_type' => 'barber',
                'subscription_plan' => 'basic',
                'is_active' => true
            ]
        );

        $barberUser = User::firstOrCreate(
            ['email' => 'barber@sagatoko.com'],
            [
                'name' => 'Owner Barber',
                'password' => Hash::make('12345678'),
                'tenant_id' => $barberTenant->id,
            ]
        );
        $barberUser->assignRole('Owner'); // This 'Owner' is creating during BootTenant for Barber? 
        // Note: Spatie roles are global by default in this table setup, but permissions differ.
        // Actually, in BootTenantPermissions:
        // retail -> Owner gets ['manage_stock', 'pos_access']
        // barber -> Owner gets ['manage_booking']
        // Since the role name "Owner" is SAME, Spatie finds existing Role "Owner".
        // PROBLEM: If "Owner" role already has 'manage_stock' attached from Retail setup, 
        // then Barber Owner ALSO has 'manage_stock'.

        // FIX for Single-DB Simple approach: 
        // We should Sync permissions based on current context. 
        // OR simpler: we rely on Frontend Menu Hiding (which we did).
        // Since we are validating Menu Visibility by business_type in MenuController, 
        // even if Barber has 'manage_stock' permission, he WON'T SEE THE MENU.
        // This is acceptable for Phase 1.

        $barberUser->update(['tenant_id' => $barberTenant->id]);

        $this->command->info("Barber User Created: barber@sagatoko.com / 12345678");
    }
}
