<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tenant;
use App\Models\User;

class SetupRetailTenantSeeder extends Seeder
{
    public function run()
    {
        // 1. Create Tenant (Trigger Event -> Create Roles)
        $tenant = Tenant::firstOrCreate(
            ['domain' => 'sagaretail'], // unique key
            [
                'name' => 'Saga Retail Store',
                'owner_name' => 'Owner Saga',
                'business_type' => 'retail',
                'subscription_plan' => 'pro',
                'is_active' => true
            ]
        );
        $this->command->info("Tenant Created: {$tenant->name} (Type: {$tenant->business_type})");

        // 2. Find Owner User
        $user = User::where('email', 'owner@sagatoko.com')->first();
        if ($user) {
            // 3. Link to Tenant
            $user->tenant_id = $tenant->id;
            $user->save();
            $this->command->info("User Assign to Tenant ID: {$tenant->id}");

            // 4. Assign Spatie Role
            $user->assignRole('Owner'); // Created by BootTenantPermissions
            $this->command->info("Role 'Owner' assigned to user.");
        } else {
            $this->command->error("User owner@sagatoko.com not found!");
        }
    }
}
