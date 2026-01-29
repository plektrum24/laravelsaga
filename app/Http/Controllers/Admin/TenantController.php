<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\Request;

use App\Services\TenantDatabaseService;

class TenantController extends Controller
{
    public function __construct(protected TenantDatabaseService $tenantDatabaseService)
    {
    }
    /**
     * Display a listing of tenants.
     */
    public function index(Request $request)
    {
        if ($request->expectsJson() || $request->ajax()) {
            $tenants = Tenant::withCount('users')->get();
            return response()->json([
                'success' => true,
                'data' => $tenants
            ]);
        }

        return view('pages.admin.tenants.index');
    }

    /**
     * Store a newly created tenant.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:tenants,code',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'owner_email' => 'required|email|unique:users,email',
            'owner_name' => 'required|string|max:255',
            'owner_password' => 'required|string|min:6',
            'business_type' => 'required|in:retail,barber,laundry,car_wash,cafe',
            'subscription_plan' => 'required|in:basic,pro,enterprise',
            'subscription_duration' => 'required|string',
            'custom_valid_until' => 'nullable|date|after:today',
        ]);

        // Calculate valid_until
        $validUntil = now()->addMonth(); // Default

        switch ($request->subscription_duration) {
            case '1_month':
                $validUntil = now()->addMonth();
                break;
            case '6_months':
                $validUntil = now()->addMonths(6);
                break;
            case '1_year':
                $validUntil = now()->addYear();
                break;
            case 'custom':
                if ($request->custom_valid_until) {
                    $validUntil = \Carbon\Carbon::parse($request->custom_valid_until);
                }
                break;
        }

        // Create tenant
        $tenant = Tenant::create([
            'name' => $validated['name'],
            'code' => $validated['code'],
            'owner_name' => $validated['owner_name'],
            'address' => $validated['address'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'status' => 'active',
            'is_active' => true,
            'business_type' => $validated['business_type'],
            'subscription_plan' => $validated['subscription_plan'],
            'valid_until' => $validUntil,
            'database_name' => 'laravel_saga_tenant_' . strtolower($validated['code']),
        ]);

        // Create Physical Database
        try {
            $this->tenantDatabaseService->createDatabase($tenant);
        } catch (\Exception $e) {
            $tenant->delete();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create tenant database: ' . $e->getMessage()
            ], 500);
        }

        // Create owner user
        $user = \App\Models\User::create([
            'name' => $validated['owner_name'],
            'email' => $validated['owner_email'],
            'password' => bcrypt($validated['owner_password']),
            'role' => 'tenant_owner',
            'tenant_id' => $tenant->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tenant created successfully',
            'data' => $tenant
        ]);
    }

    /**
     * Update the specified tenant.
     */
    public function update(Request $request, $id)
    {
        $tenant = Tenant::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
        ]);

        $tenant->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Tenant updated successfully',
            'data' => $tenant
        ]);
    }

    /**
     * Update tenant status.
     */
    public function updateStatus(Request $request, $id)
    {
        $tenant = Tenant::findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|in:active,suspended,inactive',
        ]);

        $tenant->update(['status' => $validated['status']]);

        return response()->json([
            'success' => true,
            'message' => 'Tenant status updated to ' . $validated['status'],
            'data' => $tenant
        ]);
    }

    /**
     * Extend tenant subscription.
     */
    public function extendSubscription(Request $request, $id)
    {
        $tenant = Tenant::findOrFail($id);

        $validated = $request->validate([
            'subscription_duration' => 'required|string',
            'custom_valid_until' => 'nullable|date|after:today',
        ]);

        $currentExpiry = $tenant->valid_until ? \Carbon\Carbon::parse($tenant->valid_until) : now();
        // If expired, start from now. If active, add to current expiry.
        if ($currentExpiry->isPast()) {
            $currentExpiry = now();
        }

        $newExpiry = $currentExpiry->copy();

        switch ($request->subscription_duration) {
            case '1_month':
                $newExpiry->addMonth();
                break;
            case '6_months':
                $newExpiry->addMonths(6);
                break;
            case '1_year':
                $newExpiry->addYear();
                break;
            case 'custom':
                if ($request->custom_valid_until) {
                    $newExpiry = \Carbon\Carbon::parse($request->custom_valid_until);
                }
                break;
        }

        $tenant->update(['valid_until' => $newExpiry]);

        return response()->json([
            'success' => true,
            'message' => 'Subscription extended until ' . $newExpiry->format('Y-m-d'),
            'data' => $tenant
        ]);
    }

    /**
     * Remove the specified tenant.
     */
    public function destroy($id)
    {
        $tenant = Tenant::findOrFail($id);
        $tenant->update(['status' => 'inactive']); // Soft delete

        return response()->json([
            'success' => true,
            'message' => 'Tenant deleted successfully'
        ]);
    }
}
