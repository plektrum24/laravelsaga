<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\CustomerTier;
use App\Models\MembershipTier;

class TierAssessmentService
{
    /**
     * Assess all customers and update tiers
     */
    public function assessAllCustomers($tenantId): int
    {
        $customers = Customer::where('tenant_id', $tenantId)->get();
        $updated = 0;
        
        foreach ($customers as $customer) {
            $result = $customer->assessAndUpdateTier();
            if ($result) {
                $updated++;
            }
        }
        
        return $updated;
    }
    
    /**
     * Assess customer after transaction
     */
    public function assessAfterTransaction(Customer $customer): ?CustomerTier
    {
        return $customer->assessAndUpdateTier();
    }
    
    /**
     * Get tier progression for customer
     */
    public function getTierProgression(Customer $customer): array
    {
        $currentTier = $customer->currentTier;
        $totalSpend = $customer->calculateLastYearSpend();
        $totalVisits = $customer->calculateLastYearVisits();
        
        $nextTier = MembershipTier::where('tenant_id', $customer->tenant_id)
            ->where('active', true)
            ->where(function ($q) use ($totalSpend, $totalVisits) {
                $q->where('min_spend', '>', $totalSpend)
                  ->orWhere('min_visits', '>', $totalVisits);
            })
            ->orderBy('priority', 'asc')
            ->first();
        
        $progress = [];
        
        if ($nextTier) {
            $spendNeeded = max(0, $nextTier->min_spend - $totalSpend);
            $visitsNeeded = max(0, $nextTier->min_visits - $totalVisits);
            
            $spendProgress = $nextTier->min_spend > 0 
                ? min(100, ($totalSpend / $nextTier->min_spend) * 100) 
                : 100;
            
            $visitsProgress = $nextTier->min_visits > 0 
                ? min(100, ($totalVisits / $nextTier->min_visits) * 100) 
                : 100;
            
            $progress = [
                'next_tier' => $nextTier->name,
                'next_tier_id' => $nextTier->id,
                'spend_needed' => $spendNeeded,
                'visits_needed' => $visitsNeeded,
                'spend_progress' => round($spendProgress, 1),
                'visits_progress' => round($visitsProgress, 1),
                'overall_progress' => round(min($spendProgress, $visitsProgress), 1),
            ];
        } else {
            $progress = [
                'next_tier' => null,
                'message' => 'Highest tier reached!',
                'overall_progress' => 100,
            ];
        }
        
        return [
            'current_tier' => $currentTier?->tier,
            'current_tier_name' => $currentTier?->tier->name,
            'total_spend' => $totalSpend,
            'total_visits' => $totalVisits,
            'progress' => $progress,
        ];
    }
}
