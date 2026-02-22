<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerPoint;
use App\Models\CustomerReward;
use App\Models\Reward;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RewardController extends Controller
{
    /**
     * Get all rewards for current tenant
     * GET /api/rewards
     */
    public function index(Request $request)
    {
        $query = Reward::where('tenant_id', auth()->user()->tenant_id);
        
        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        // Only active rewards by default
        if (!$request->has('status')) {
            $query->where('status', Reward::STATUS_ACTIVE);
        }
        
        $rewards = $query->orderBy('points_cost', 'asc')->get();
        
        return response()->json([
            'success' => true,
            'data' => $rewards
        ]);
    }
    
    /**
     * Get reward by ID
     * GET /api/rewards/{id}
     */
    public function show($id)
    {
        $reward = Reward::where('tenant_id', auth()->user()->tenant_id)->findOrFail($id);
        
        return response()->json([
            'success' => true,
            'data' => $reward
        ]);
    }
    
    /**
     * Create new reward
     * POST /api/rewards
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'points_cost' => 'required|integer|min:1',
            'stock' => 'nullable|integer|min:0',
            'image_url' => 'nullable|string',
            'terms_conditions' => 'nullable|string',
            'active_from' => 'nullable|date',
            'active_to' => 'nullable|date|after:active_from',
            'status' => 'required|in:draft,active,inactive',
        ]);
        
        $validated['tenant_id'] = auth()->user()->tenant_id;
        
        $reward = Reward::create($validated);
        
        return response()->json([
            'success' => true,
            'message' => 'Reward created successfully',
            'data' => $reward
        ], 201);
    }
    
    /**
     * Update reward
     * PUT /api/rewards/{id}
     */
    public function update(Request $request, $id)
    {
        $reward = Reward::where('tenant_id', auth()->user()->tenant_id)->findOrFail($id);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'points_cost' => 'required|integer|min:1',
            'stock' => 'nullable|integer|min:0',
            'image_url' => 'nullable|string',
            'terms_conditions' => 'nullable|string',
            'active_from' => 'nullable|date',
            'active_to' => 'nullable|date|after:active_from',
            'status' => 'required|in:draft,active,inactive',
        ]);
        
        $reward->update($validated);
        
        return response()->json([
            'success' => true,
            'message' => 'Reward updated successfully',
            'data' => $reward
        ]);
    }
    
    /**
     * Delete reward
     * DELETE /api/rewards/{id}
     */
    public function destroy($id)
    {
        $reward = Reward::where('tenant_id', auth()->user()->tenant_id)->findOrFail($id);
        $reward->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Reward deleted successfully'
        ]);
    }
    
    /**
     * Redeem reward
     * POST /api/rewards/redeem
     */
    public function redeem(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'reward_id' => 'required|exists:reward_catalog,id',
        ]);

        DB::connection('tenant')->beginTransaction();
        
        try {
            $customer = Customer::findOrFail($validated['customer_id']);
            $reward = Reward::findOrFail($validated['reward_id']);
            
            // Check if reward is available
            if (!$reward->isAvailable()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Reward is not available'
                ], 400);
            }
            
            // Check customer points balance
            $balanceData = CustomerPoint::getBalanceWithBreakdown($validated['customer_id']);
            $balance = $balanceData['balance'];
            
            if ($balance < $reward->points_cost) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient points. Required: ' . $reward->points_cost . ', Available: ' . $balance
                ], 400);
            }
            
            // Deduct points
            $newBalance = $balance - $reward->points_cost;
            
            CustomerPoint::create([
                'customer_id' => $validated['customer_id'],
                'tenant_id' => auth()->user()->tenant_id,
                'points' => -$reward->points_cost,
                'type' => CustomerPoint::TYPE_REDEEM,
                'reference_type' => 'reward',
                'reference_id' => $reward->id,
                'expiry_date' => null,
                'balance_after' => $newBalance,
                'notes' => 'Redeemed reward: ' . $reward->name,
            ]);
            
            // Create customer reward record
            $customerReward = CustomerReward::create([
                'customer_id' => $validated['customer_id'],
                'reward_id' => $reward->id,
                'points_redeemed' => $reward->points_cost,
                'status' => CustomerReward::STATUS_PENDING,
                'expiry_date' => now()->addMonths(3), // 3 months to claim
            ]);
            
            // Decrement reward stock
            $reward->decrementStock();
            
            DB::connection('tenant')->commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Reward redeemed successfully',
                'data' => [
                    'customer_reward' => $customerReward,
                    'points_redeemed' => $reward->points_cost,
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
    
    /**
     * Get customer's rewards
     * GET /api/customers/{id}/rewards
     */
    public function customerRewards($customerId)
    {
        $rewards = CustomerReward::where('customer_id', $customerId)
            ->with('reward')
            ->latest()
            ->get();
        
        return response()->json([
            'success' => true,
            'data' => $rewards
        ]);
    }
    
    /**
     * Fulfill customer reward
     * POST /api/customer-rewards/{id}/fulfill
     */
    public function fulfillReward($id)
    {
        $customerReward = CustomerReward::findOrFail($id);
        
        if ($customerReward->status !== CustomerReward::STATUS_PENDING) {
            return response()->json([
                'success' => false,
                'message' => 'Reward cannot be fulfilled'
            ], 400);
        }
        
        $customerReward->markAsFulfilled();
        
        return response()->json([
            'success' => true,
            'message' => 'Reward fulfilled successfully',
            'data' => $customerReward
        ]);
    }
}
