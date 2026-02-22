<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Http\Controllers\Controller;
use App\Models\TenantSubscription;
use App\Models\SubscriptionPlan;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class SubscriptionController extends Controller
{
    /**
     * Get current tenant subscription
     */
    public function current()
    {
        $tenant = Auth::user()->tenant;

        if (!$tenant) {
            return response()->json([
                'success' => false,
                'message' => 'Tenant not found'
            ], 404);
        }

        $subscription = $tenant->subscription()
            ->with('plan')
            ->first();

        // Get available plans for upgrade
        $availablePlans = SubscriptionPlan::active()
            ->ordered()
            ->get()
            ->map(function ($plan) use ($subscription) {
                $plan->can_upgrade = !$subscription || $plan->id !== $subscription->plan_id;
                $plan->is_current = $subscription && $plan->id === $subscription->plan_id;
                return $plan;
            });

        return response()->json([
            'success' => true,
            'data' => [
                'subscription' => $subscription,
                'available_plans' => $availablePlans,
            ]
        ]);
    }

    /**
     * Change subscription plan
     */
    public function change(Request $request)
    {
        $request->validate([
            'plan_id' => 'required|exists:subscription_plans,id',
            'billing_cycle' => 'required|in:monthly,yearly',
        ]);

        $tenant = Auth::user()->tenant;

        if (!$tenant) {
            return response()->json([
                'success' => false,
                'message' => 'Tenant not found'
            ], 404);
        }

        $newPlan = SubscriptionPlan::find($request->plan_id);
        $subscription = $tenant->subscription;

        // Calculate new expiry based on billing cycle
        $newExpiry = Carbon::now();
        if ($request->billing_cycle === 'yearly') {
            $newExpiry->addYear();
        } else {
            $newExpiry->addMonth();
        }

        if ($subscription) {
            // Update existing subscription
            $subscription->update([
                'plan_id' => $newPlan->id,
                'billing_cycle' => $request->billing_cycle,
                'expires_at' => $newExpiry,
                'status' => 'active',
            ]);

            // Create invoice for plan change
            $invoice = Invoice::create([
                'tenant_id' => $tenant->id,
                'subscription_id' => $subscription->id,
                'amount' => $request->billing_cycle === 'yearly' ? $newPlan->price_yearly : $newPlan->price_monthly,
                'tax' => 0,
                'discount' => 0,
                'total' => $request->billing_cycle === 'yearly' ? $newPlan->price_yearly : $newPlan->price_monthly,
                'status' => 'sent',
                'due_date' => Carbon::now()->addDays(14),
                'notes' => 'Plan change from ' . ($subscription->plan->name ?? 'previous plan') . ' to ' . $newPlan->name,
            ]);

            $subscription = $subscription->fresh();
        } else {
            // Create new subscription
            $subscription = TenantSubscription::create([
                'tenant_id' => $tenant->id,
                'plan_id' => $newPlan->id,
                'status' => 'trial',
                'trial_ends_at' => Carbon::now()->addDays($newPlan->trial_days),
                'billing_cycle' => $request->billing_cycle,
                'expires_at' => $newExpiry,
                'started_at' => Carbon::now(),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Subscription plan changed successfully',
            'data' => [
                'subscription' => $subscription,
                'new_plan' => $newPlan,
                'expires_at' => $subscription->expires_at,
            ]
        ]);
    }

    /**
     * Cancel subscription
     */
    public function cancel(Request $request)
    {
        $tenant = Auth::user()->tenant;

        if (!$tenant) {
            return response()->json([
                'success' => false,
                'message' => 'Tenant not found'
            ], 404);
        }

        $subscription = $tenant->subscription;

        if (!$subscription) {
            return response()->json([
                'success' => false,
                'message' => 'No subscription found'
            ], 404);
        }

        $request->validate([
            'reason' => 'nullable|string|max:500',
            'cancel_immediately' => 'boolean',
        ]);

        if ($request->cancel_immediately) {
            $subscription->update([
                'status' => 'cancelled',
                'cancelled_at' => Carbon::now(),
                'auto_renew' => false,
            ]);
        } else {
            // Cancel at end of period
            $subscription->update([
                'auto_renew' => false,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Subscription cancellation scheduled',
            'data' => [
                'subscription' => $subscription->fresh(),
                'access_until' => $subscription->expires_at,
            ]
        ]);
    }

    /**
     * Get subscription usage
     */
    public function usage()
    {
        $tenant = Auth::user()->tenant;

        if (!$tenant) {
            return response()->json([
                'success' => false,
                'message' => 'Tenant not found'
            ], 404);
        }

        $subscription = $tenant->subscription;
        
        if (!$subscription) {
            return response()->json([
                'success' => false,
                'message' => 'No subscription found'
            ], 404);
        }

        // Get current period usage
        $usage = $tenant->usage()
            ->where('period_end', '>=', Carbon::now())
            ->get();

        // Get plan limits
        $limits = $subscription->plan->limits ?? [];

        return response()->json([
            'success' => true,
            'data' => [
                'usage' => $usage,
                'limits' => $limits,
                'subscription' => [
                    'plan' => $subscription->plan,
                    'status' => $subscription->status,
                    'expires_at' => $subscription->expires_at,
                ]
            ]
        ]);
    }
}
