<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\MembershipTier;
use App\Services\TierAssessmentService;
use Illuminate\Http\Request;

class TierController extends Controller
{
    private TierAssessmentService $tierService;
    
    public function __construct(TierAssessmentService $tierService)
    {
        $this->tierService = $tierService;
    }
    
    /**
     * Get all tiers for current tenant
     * GET /api/tiers
     */
    public function index()
    {
        $tiers = MembershipTier::where('tenant_id', auth()->user()->tenant_id)
            ->where('active', true)
            ->orderBy('priority', 'asc')
            ->get();
        
        return response()->json([
            'success' => true,
            'data' => $tiers
        ]);
    }
    
    /**
     * Get customer's current tier and progression
     * GET /api/customers/{id}/tier
     */
    public function customerTier($customerId)
    {
        $customer = Customer::findOrFail($customerId);
        $progression = $this->tierService->getTierProgression($customer);
        
        return response()->json([
            'success' => true,
            'data' => $progression
        ]);
    }
    
    /**
     * Calculate tier progress
     * POST /api/tiers/calculate-progress
     */
    public function calculateProgress(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
        ]);
        
        $customer = Customer::findOrFail($validated['customer_id']);
        $progression = $this->tierService->getTierProgression($customer);
        
        return response()->json([
            'success' => true,
            'data' => $progression
        ]);
    }
    
    /**
     * Manually assess customer tier
     * POST /api/customers/{id}/assess-tier
     */
    public function assessCustomer($customerId)
    {
        $customer = Customer::findOrFail($customerId);
        $tier = $customer->assessAndUpdateTier();
        
        return response()->json([
            'success' => true,
            'message' => 'Tier assessment completed',
            'data' => [
                'tier' => $tier?->tier,
                'qualified_at' => $tier?->qualified_at,
            ]
        ]);
    }
    
    /**
     * Create/Update tier (Admin)
     * POST /api/tiers
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'min_spend' => 'required|numeric|min:0',
            'min_visits' => 'required|integer|min:0',
            'benefits' => 'required|array',
            'badge_color' => 'required|string',
            'priority' => 'required|integer',
            'active' => 'boolean',
        ]);
        
        $validated['tenant_id'] = auth()->user()->tenant_id;
        
        $tier = MembershipTier::updateOrCreate(
            ['tenant_id' => auth()->user()->tenant_id, 'name' => $validated['name']],
            $validated
        );
        
        return response()->json([
            'success' => true,
            'message' => 'Tier saved successfully',
            'data' => $tier
        ]);
    }
}
