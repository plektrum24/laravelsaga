<?php

namespace App\Services;

use App\Models\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class TenantDatabaseService
{
    /**
     * Create database for the tenant and run migrations.
     */
    public function createDatabase(Tenant $tenant)
    {
        try {
            $dbName = $tenant->database_name;

            // 1. Create Database using default connection
            // We use a raw statement. Warning: validate dbName to prevent injection if it came directly from input.
            // But here it comes from 'saga_tenant_' . code (alphanumeric), so it should be safe.
            DB::statement("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

            Log::info("Database {$dbName} created for tenant {$tenant->name}");

            // 2. Configure connection
            $this->configureTenantConnection($tenant);

            // 3. Migrate
            Log::info("Starting migration for tenant {$tenant->name}");
            Artisan::call('migrate', [
                '--database' => 'tenant',
                '--path' => 'database/migrations/tenant',
                '--force' => true,
            ]);
            Log::info("Migration completed for tenant {$tenant->name}");

        } catch (\Exception $e) {
            Log::error("Failed to create database for tenant {$tenant->id}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Configure the tenant connection dynamically.
     */
    public function configureTenantConnection(Tenant $tenant)
    {
        Config::set('database.connections.tenant.database', $tenant->database_name);
        DB::purge('tenant');
        DB::reconnect('tenant');
    }
}
