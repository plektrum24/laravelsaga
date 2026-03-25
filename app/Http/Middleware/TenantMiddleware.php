<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Tenant;

class TenantMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $tenantId = null;

        // 1. Check Header (for Owner switching branches)
        if ($request->hasHeader('X-Tenant-ID')) {
            $tenantId = $request->header('X-Tenant-ID');
            // Verify if user has access to this tenant (TODO: Implement Pivot Table check)
            // For now, allow if super_admin or matches user->tenant_id
            if ($user && $user->role !== 'super_admin' && $user->tenant_id != $tenantId) {
                // If we implement multi-branch strictly later, remove this check or adapt it.
                // For now strict:
                // return response()->json(['message' => 'Unauthorized tenant access'], 403);
            }
        }
        // 2. Fallback to User's Tenant
        elseif ($user && $user->tenant_id) {
            $tenantId = $user->tenant_id;
        }

        // If no tenant found, allow request to continue but without tenant DB connection
        // This prevents 403 errors for endpoints that don't require tenant context
        if (!$tenantId) {
            \Illuminate\Support\Facades\Log::warning("TenantMiddleware: No tenant ID found for user " . ($user?->id ?? 'anonymous'));
            return $next($request);
        }

        if ($tenantId) {
            $tenant = Tenant::find($tenantId);
            if ($tenant) {
                // Configure the tenant connection
                $dbName = $tenant->database_name;

                // Fallback to config database if tenant database_name is empty
                if (empty($dbName)) {
                    $dbName = Config::get('database.connections.tenant.database', Config::get('database.connections.mysql.database', 'tailadmin_laravel'));
                    \Illuminate\Support\Facades\Log::info("TenantMiddleware: Using fallback DB: " . $dbName);
                }

                \Illuminate\Support\Facades\Log::info("TenantMiddleware: Switching to tenant DB: " . $dbName);
                Config::set('database.connections.tenant.database', $dbName);

                try {
                    DB::purge('tenant');
                    DB::reconnect('tenant');

                    // Test the connection
                    DB::connection('tenant')->select('SELECT 1');

                    \Illuminate\Support\Facades\Log::info("TenantMiddleware: Reconnected successfully.");
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error("TenantMiddleware: Connection failed: " . $e->getMessage());
                    return response()->json([
                        'success' => false,
                        'message' => 'Database Connection Error (Middleware)',
                        'detail' => $e->getMessage(),
                        'db_name' => $dbName ?? 'unknown'
                    ], 500);
                }
            } else {
                \Illuminate\Support\Facades\Log::warning("TenantMiddleware: Tenant ID $tenantId not found.");
                return response()->json(['success' => false, 'message' => 'Tenant Not Found in Middleware referenced by ID: ' . $tenantId], 404);
            }
        }

        return $next($request);
    }
}
