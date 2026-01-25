<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use App\Models\Tenant;

class CreateOperationalUsersSeeder extends Seeder
{
    public function run()
    {
        // 1. Get Retail Tenant (Assume created by previous seeder)
        $tenant = Tenant::where('domain', 'testretail')->first();
        if (!$tenant) {
            $this->command->error("Retail Tenant not found! Run SetupTestingTenantsSeeder first.");
            return;
        }

        $guardName = 'web';

        // 2. Ensure Roles Exist
        $roleKasir = Role::firstOrCreate(['name' => 'Kasir', 'guard_name' => $guardName]);
        $roleGudang = Role::firstOrCreate(['name' => 'Gudang', 'guard_name' => $guardName]);

        // 3. Create Kasir User
        $kasir = User::firstOrCreate(
            ['email' => 'kasir@sagatoko.com'],
            [
                'name' => 'Kasir Retail',
                'password' => bcrypt('12345678'),
                'tenant_id' => $tenant->id,
                'role' => 'Kasir' // Legacy column fallback
            ]
        );
        $kasir->assignRole($roleKasir);
        $this->command->info("User Created: kasir@sagatoko.com / 12345678");

        // 4. Create Gudang User
        $gudang = User::firstOrCreate(
            ['email' => 'gudang@sagatoko.com'],
            [
                'name' => 'Staff Gudang',
                'password' => bcrypt('12345678'),
                'tenant_id' => $tenant->id,
                'role' => 'Gudang' // Legacy column fallback
            ]
        );
        $gudang->assignRole($roleGudang);
        $this->command->info("User Created: gudang@sagatoko.com / 12345678");
    }
}
