<?php

namespace App\Http\Controllers\Api\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    /**
     * Display a listing of subscription plans
     */
    public function index()
    {
        $plans = SubscriptionPlan::ordered()->get();

        return response()->json([
            'success' => true,
            'data' => $plans
        ]);
    }

    /**
     * Store a newly created subscription plan
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:subscription_plans,code',
            'price_monthly' => 'required|numeric|min:0',
            'price_yearly' => 'required|numeric|min:0',
            'features' => 'nullable|array',
            'limits' => 'nullable|array',
            'trial_days' => 'integer|min:0|max:365',
            'is_active' => 'boolean',
            'priority' => 'integer',
        ]);

        $plan = SubscriptionPlan::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Subscription plan created successfully',
            'data' => $plan
        ], 201);
    }

    /**
     * Display the specified subscription plan
     */
    public function show(SubscriptionPlan $plan)
    {
        return response()->json([
            'success' => true,
            'data' => $plan
        ]);
    }

    /**
     * Update the specified subscription plan
     */
    public function update(Request $request, SubscriptionPlan $plan)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'code' => 'sometimes|string|unique:subscription_plans,code,' . $plan->id,
            'price_monthly' => 'sometimes|numeric|min:0',
            'price_yearly' => 'sometimes|numeric|min:0',
            'features' => 'nullable|array',
            'limits' => 'nullable|array',
            'trial_days' => 'sometimes|integer|min:0|max:365',
            'is_active' => 'sometimes|boolean',
            'priority' => 'sometimes|integer',
        ]);

        $plan->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Subscription plan updated successfully',
            'data' => $plan->fresh()
        ]);
    }

    /**
     * Remove the specified subscription plan
     */
    public function destroy(SubscriptionPlan $plan)
    {
        // Check if plan has active subscriptions
        $activeSubscriptions = $plan->tenantSubscriptions()
            ->whereIn('status', ['trial', 'active'])
            ->count();

        if ($activeSubscriptions > 0) {
            return response()->json([
                'success' => false,
                'message' => "Cannot delete plan with {$activeSubscriptions} active subscriptions"
            ], 400);
        }

        $plan->delete();

        return response()->json([
            'success' => true,
            'message' => 'Subscription plan deleted successfully'
        ]);
    }

    /**
     * Get available plans for signup
     */
    public function available()
    {
        $plans = SubscriptionPlan::active()
            ->ordered()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $plans
        ]);
    }
}
