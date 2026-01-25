<?php

namespace App\Listeners;

use App\Events\TenantCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class BootTenantPermissions
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(TenantCreated $event): void
    {
        $tenant = $event->tenant; // Assuming TenantCreated passes the tenant

        // In Spatie, permissions are global unless we use 'team_id' or similar tenant scope.
        // For Single DB Multi-Tenant WITHOUT 'team_id' (simplest start), we just create roles.
        // Ideally, Role name should be unique OR scoped. 
        // Here we assume simple Global Roles for now (e.g. 'retail-cashier') OR we scope it later.

        // Strategy: Create standard roles if they don't exist.
        // Since many tenants share the same role names (e.g. 'cashier'), 
        // usually we just seed them ONCE globally, OR we use Spatie's team_id feature.
        // Assuming user wants Tenant-Specific Roles? 
        // Let's implement the logic requested: IF business_type == X, Create Role Y.

        $guardName = 'web'; // or api

        if ($tenant->business_type === 'barber') {
            $this->createRoleIfNotExists('Kapster', ['manage_booking'], $guardName);
            $this->createRoleIfNotExists('Owner', ['manage_booking', 'manage_staff'], $guardName);
        } elseif ($tenant->business_type === 'retail') {
            $this->createRoleIfNotExists('Owner', ['manage_stock', 'view_inventory', 'pos_access', 'manage_settings'], $guardName);
            $this->createRoleIfNotExists('Kasir', ['pos_access'], $guardName);
            $this->createRoleIfNotExists('Gudang', ['manage_stock', 'view_inventory'], $guardName);
        } elseif ($tenant->business_type === 'laundry') {
            $this->createRoleIfNotExists('Staff Cuci', ['update_status'], $guardName);
        }
    }

    private function createRoleIfNotExists($roleName, $permissions, $guard = 'web')
    {
        // Check if role exists
        $role = \Spatie\Permission\Models\Role::firstOrCreate(
            ['name' => $roleName, 'guard_name' => $guard]
        );

        foreach ($permissions as $permName) {
            $permission = \Spatie\Permission\Models\Permission::firstOrCreate(
                ['name' => $permName, 'guard_name' => $guard]
            );
            $role->givePermissionTo($permission);
        }
    }
}
