<?php

namespace App\Services;

use App\Models\Tenant;
use App\Models\TenantSubscription;
use App\Models\SubscriptionPlan;
use App\Models\Invoice;
use App\Models\TenantUsage;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SubscriptionService
{
    /**
     * Create subscription for tenant
     */
    public function createSubscription(Tenant $tenant, SubscriptionPlan $plan, string $billingCycle = 'monthly'): TenantSubscription
    {
        return DB::transaction(function () use ($tenant, $plan, $billingCycle) {
            $subscription = TenantSubscription::create([
                'tenant_id' => $tenant->id,
                'plan_id' => $plan->id,
                'status' => $plan->trial_days > 0 ? 'trial' : 'active',
                'trial_ends_at' => $plan->trial_days > 0 ? Carbon::now()->addDays($plan->trial_days) : null,
                'billing_cycle' => $billingCycle,
                'started_at' => Carbon::now(),
                'expires_at' => $billingCycle === 'yearly' 
                    ? Carbon::now()->addYear() 
                    : Carbon::now()->addMonth(),
                'auto_renew' => true,
            ]);

            // Create initial invoice if not in trial
            if ($subscription->status !== 'trial') {
                $this->createInvoice($subscription, $billingCycle);
            }

            // Initialize usage tracking
            $this->initializeUsage($tenant, $plan);

            Log::info("Subscription created for tenant {$tenant->name}", [
                'subscription_id' => $subscription->id,
                'plan' => $plan->name,
            ]);

            return $subscription;
        });
    }

    /**
     * Upgrade subscription plan
     */
    public function upgradePlan(TenantSubscription $subscription, SubscriptionPlan $newPlan, string $billingCycle): TenantSubscription
    {
        return DB::transaction(function () use ($subscription, $newPlan, $billingCycle) {
            $oldPlan = $subscription->plan;

            // Calculate prorated amount (if needed)
            $proratedAmount = $this->calculateProration($subscription, $newPlan, $billingCycle);

            // Update subscription
            $subscription->update([
                'plan_id' => $newPlan->id,
                'billing_cycle' => $billingCycle,
                'expires_at' => $billingCycle === 'yearly'
                    ? Carbon::now()->addYear()
                    : Carbon::now()->addMonth(),
            ]);

            // Create invoice for upgrade
            if ($proratedAmount > 0) {
                Invoice::create([
                    'tenant_id' => $subscription->tenant_id,
                    'subscription_id' => $subscription->id,
                    'invoice_number' => Invoice::generateInvoiceNumber(),
                    'amount' => $proratedAmount,
                    'tax' => 0,
                    'discount' => 0,
                    'total' => $proratedAmount,
                    'status' => 'sent',
                    'due_date' => Carbon::now()->addDays(14),
                    'notes' => "Plan upgrade from {$oldPlan->name} to {$newPlan->name}",
                ]);
            }

            // Update usage limits
            $this->updateUsageLimits($subscription->tenant, $newPlan);

            Log::info("Subscription upgraded", [
                'subscription_id' => $subscription->id,
                'old_plan' => $oldPlan->name,
                'new_plan' => $newPlan->name,
            ]);

            return $subscription->fresh();
        });
    }

    /**
     * Downgrade subscription plan
     */
    public function downgradePlan(TenantSubscription $subscription, SubscriptionPlan $newPlan, string $billingCycle): TenantSubscription
    {
        // Check if downgrade is allowed
        if (!$subscription->canDowngrade()) {
            throw new \Exception('Downgrade not allowed for this subscription');
        }

        return DB::transaction(function () use ($subscription, $newPlan, $billingCycle) {
            $oldPlan = $subscription->plan;

            // Update subscription at end of period
            $subscription->update([
                'plan_id' => $newPlan->id,
                'billing_cycle' => $billingCycle,
            ]);

            Log::info("Subscription downgrade scheduled", [
                'subscription_id' => $subscription->id,
                'old_plan' => $oldPlan->name,
                'new_plan' => $newPlan->name,
            ]);

            return $subscription->fresh();
        });
    }

    /**
     * Cancel subscription
     */
    public function cancelSubscription(TenantSubscription $subscription, bool $immediate = false): void
    {
        DB::transaction(function () use ($subscription, $immediate) {
            if ($immediate) {
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

            Log::info("Subscription cancelled", [
                'subscription_id' => $subscription->id,
                'immediate' => $immediate,
            ]);
        });
    }

    /**
     * Renew subscription
     */
    public function renewSubscription(TenantSubscription $subscription): void
    {
        DB::transaction(function () use ($subscription) {
            $plan = $subscription->plan;

            // Extend subscription
            $subscription->extend($subscription->billing_cycle === 'yearly' ? 365 : 30);

            // Create renewal invoice
            $this->createInvoice($subscription, $subscription->billing_cycle);

            // Update usage limits
            $this->updateUsageLimits($subscription->tenant, $plan);

            Log::info("Subscription renewed", [
                'subscription_id' => $subscription->id,
                'new_expiry' => $subscription->expires_at,
            ]);
        });
    }

    /**
     * Check and process expiring subscriptions
     */
    public function processExpiringSubscriptions(): int
    {
        $expiring = TenantSubscription::expiringSoon(3)
            ->where('auto_renew', true)
            ->whereIn('status', ['trial', 'active'])
            ->get();

        $renewed = 0;
        foreach ($expiring as $subscription) {
            try {
                $this->renewSubscription($subscription);
                $renewed++;
            } catch (\Exception $e) {
                Log::error("Failed to renew subscription {$subscription->id}", [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $renewed;
    }

    /**
     * Check and process expired trials
     */
    public function processExpiredTrials(): int
    {
        $expiredTrials = TenantSubscription::where('status', 'trial')
            ->where('trial_ends_at', '<=', Carbon::now())
            ->get();

        $expired = 0;
        foreach ($expiredTrials as $subscription) {
            $subscription->update([
                'status' => 'expired',
            ]);
            $expired++;
        }

        return $expired;
    }

    /**
     * Calculate prorated amount for plan change
     */
    private function calculateProration(TenantSubscription $subscription, SubscriptionPlan $newPlan, string $billingCycle): float
    {
        $oldPlan = $subscription->plan;
        $daysRemaining = $subscription->expires_at->diffInDays(Carbon::now());
        $totalDays = $subscription->billing_cycle === 'yearly' ? 365 : 30;

        // Calculate unused credit from old plan
        $oldPrice = $subscription->billing_cycle === 'yearly' ? $oldPlan->price_yearly : $oldPlan->price_monthly;
        $credit = ($daysRemaining / $totalDays) * $oldPrice;

        // Calculate cost of new plan
        $newPrice = $billingCycle === 'yearly' ? $newPlan->price_yearly : $newPlan->price_monthly;

        // Prorated amount
        $proratedAmount = $newPrice - $credit;

        return max(0, $proratedAmount);
    }

    /**
     * Create invoice for subscription
     */
    private function createInvoice(TenantSubscription $subscription, string $billingCycle): Invoice
    {
        $plan = $subscription->plan;
        $amount = $billingCycle === 'yearly' ? $plan->price_yearly : $plan->price_monthly;

        return Invoice::create([
            'tenant_id' => $subscription->tenant_id,
            'subscription_id' => $subscription->id,
            'invoice_number' => Invoice::generateInvoiceNumber(),
            'amount' => $amount,
            'tax' => 0,
            'discount' => 0,
            'total' => $amount,
            'status' => 'sent',
            'due_date' => Carbon::now()->addDays(14),
            'notes' => "Subscription renewal - {$plan->name} ({$billingCycle})",
        ]);
    }

    /**
     * Initialize usage tracking for tenant
     */
    private function initializeUsage(Tenant $tenant, SubscriptionPlan $plan): void
    {
        $limits = $plan->limits ?? [];

        foreach ($limits as $metric => $limit) {
            TenantUsage::getOrCreate($tenant->id, $metric, $limit);
        }
    }

    /**
     * Update usage limits for tenant
     */
    private function updateUsageLimits(Tenant $tenant, SubscriptionPlan $plan): void
    {
        $limits = $plan->limits ?? [];

        foreach ($limits as $metric => $limit) {
            $usage = TenantUsage::forTenant($tenant->id)
                ->metric($metric)
                ->currentPeriod()
                ->first();

            if ($usage) {
                $usage->update(['limit_value' => $limit]);
            } else {
                TenantUsage::getOrCreate($tenant->id, $metric, $limit);
            }
        }
    }

    /**
     * Get subscription status for tenant
     */
    public function getSubscriptionStatus(Tenant $tenant): array
    {
        $subscription = $tenant->subscription;

        if (!$subscription) {
            return [
                'has_subscription' => false,
                'status' => null,
                'plan' => null,
            ];
        }

        return [
            'has_subscription' => true,
            'status' => $subscription->status,
            'plan' => $subscription->plan,
            'expires_at' => $subscription->expires_at,
            'trial_ends_at' => $subscription->trial_ends_at,
            'auto_renew' => $subscription->auto_renew,
            'days_until_expiry' => $subscription->days_until_expiry,
        ];
    }
}
