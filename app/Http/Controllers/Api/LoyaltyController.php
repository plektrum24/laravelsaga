<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerPoint;
use App\Models\LoyaltySetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LoyaltyController extends Controller
{
    /**
     * Get loyalty settings for current tenant
     * GET /api/loyalty/settings
     */
    public function settings()
    {
        $tenantId = auth()->user()->tenant_id;
        $settings = LoyaltySetting::getOrCreateForTenant($tenantId);
        
        return response()->json([
            'success' => true,
            'data' => $settings
        ]);
    }

    /**
     * Update loyalty settings
     * POST /api/loyalty/settings
     */
    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'earn_rate' => 'required|numeric|min:1',
            'point_value' => 'required|numeric|min:0.01',
            'min_redemption_points' => 'required|integer|min:0',
            'max_redemption_percent' => 'required|numeric|between:0,100',
            'points_expiry_months' => 'required|integer|min:0',
            'enabled' => 'boolean',
        ]);

        $tenantId = auth()->user()->tenant_id;
        $settings = LoyaltySetting::where('tenant_id', $tenantId)->first();
        
        if (!$settings) {
            $settings = LoyaltySetting::create([
                'tenant_id' => $tenantId,
                ...$validated
            ]);
        } else {
            $settings->update($validated);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Loyalty settings updated',
            'data' => $settings
        ]);
    }

    /**
     * Get customer points balance
     * GET /api/customers/{id}/points
     */
    public function customerPoints($customerId)
    {
        $customer = Customer::findOrFail($customerId);
        $balanceData = CustomerPoint::getBalanceWithBreakdown($customerId);
        
        return response()->json([
            'success' => true,
            'data' => [
                'customer' => $customer,
                'balance' => $balanceData['balance'],
                'total_earned' => $balanceData['total_earned'],
                'total_redeemed' => $balanceData['total_redeemed'],
                'total_expired' => $balanceData['total_expired'],
                'expiring_soon' => $balanceData['expiring_soon'],
            ]
        ]);
    }

    /**
     * Get customer points history
     * GET /api/customers/{id}/points/history
     */
    public function pointsHistory($customerId, Request $request)
    {
        $limit = $request->get('limit', 50);
        
        $history = CustomerPoint::where('customer_id', $customerId)
            ->with('reference')
            ->latest()
            ->paginate($limit);
        
        return response()->json([
            'success' => true,
            'data' => $history
        ]);
    }

    /**
     * Calculate points for a transaction
     * POST /api/loyalty/calculate
     */
    public function calculate(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'total_amount' => 'required|numeric|min:0',
        ]);

        $tenantId = auth()->user()->tenant_id;
        $settings = LoyaltySetting::forTenant($tenantId);
        
        if (!$settings || !$settings->enabled) {
            return response()->json([
                'success' => true,
                'data' => [
                    'points' => 0,
                    'message' => 'Loyalty program disabled'
                ]
            ]);
        }
        
        $points = $settings->calculatePoints($validated['total_amount']);
        
        return response()->json([
            'success' => true,
            'data' => [
                'points' => $points,
                'earn_rate' => $settings->earn_rate,
                'total_amount' => $validated['total_amount'],
                'estimated_value' => $settings->calculateValue($points),
            ]
        ]);
    }

    /**
     * Redeem points
     * POST /api/loyalty/redeem
     */
    public function redeem(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'points' => 'required|integer|min:1',
            'transaction_id' => 'nullable|exists:transactions,id',
        ]);

        DB::connection('tenant')->beginTransaction();
        
        try {
            $customer = Customer::findOrFail($validated['customer_id']);
            $tenantId = auth()->user()->tenant_id;
            $settings = LoyaltySetting::getOrCreateForTenant($tenantId);
            
            // Check if program is enabled
            if (!$settings->enabled) {
                return response()->json([
                    'success' => false,
                    'message' => 'Loyalty program is disabled'
                ], 400);
            }
            
            // Check minimum redemption
            if ($validated['points'] < $settings->min_redemption_points) {
                return response()->json([
                    'success' => false,
                    'message' => 'Minimum redemption is ' . $settings->min_redemption_points . ' points'
                ], 400);
            }
            
            // Calculate current balance
            $balanceData = CustomerPoint::getBalanceWithBreakdown($validated['customer_id']);
            $balance = $balanceData['balance'];
            
            // Check sufficient balance
            if ($validated['points'] > $balance) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient points balance. Available: ' . $balance
                ], 400);
            }
            
            // Calculate discount value
            $discountValue = $settings->calculateValue($validated['points']);
            
            // Create redemption record
            $newBalance = $balance - $validated['points'];
            
            CustomerPoint::create([
                'customer_id' => $validated['customer_id'],
                'tenant_id' => $tenantId,
                'points' => -$validated['points'],
                'type' => CustomerPoint::TYPE_REDEEM,
                'reference_type' => 'transaction',
                'reference_id' => $validated['transaction_id'] ?? null,
                'expiry_date' => null,
                'balance_after' => $newBalance,
                'notes' => 'Redeemed for discount on transaction',
            ]);
            
            DB::connection('tenant')->commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Points redeemed successfully',
                'data' => [
                    'points_redeemed' => $validated['points'],
                    'discount_value' => $discountValue,
                    'new_balance' => $newBalance,
                ]
            ]);
            
        } catch (\Exception $e) {
            DB::connection('tenant')->rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
