<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ForecastTargetService;
use Illuminate\Http\Request;

class ForecastTargetController extends Controller
{
    protected $forecastTargetService;

    public function __construct(ForecastTargetService $forecastTargetService)
    {
        $this->forecastTargetService = $forecastTargetService;
    }

    /**
     * Calculate forecast from target
     * POST /api/forecast/calculate-target
     */
    public function calculateTarget(Request $request)
    {
        $validated = $request->validate([
            'target_revenue' => 'required|numeric|min:0',
            'duration_days' => 'required|integer|min:1',
        ]);

        $tenantId = auth()->user()->tenant_id;
        
        $result = $this->forecastTargetService->calculateFromTarget(
            $tenantId,
            $validated['target_revenue'],
            $validated['duration_days']
        );

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }

    /**
     * Save forecast target
     * POST /api/forecast/save-target
     */
    public function saveTarget(Request $request)
    {
        $validated = $request->validate([
            'target_revenue' => 'required|numeric|min:0',
            'duration_days' => 'required|integer|min:1',
            'current_trajectory' => 'numeric|min:0',
            'gap' => 'numeric',
            'product_mix' => 'array',
        ]);

        $tenantId = auth()->user()->tenant_id;
        
        $result = $this->forecastTargetService->saveTarget($tenantId, $validated);

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'data' => $result['target'],
                'message' => 'Forecast target saved successfully',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result['message'],
        ], 400);
    }

    /**
     * Get active target
     * GET /api/forecast/active-target
     */
    public function getActiveTarget()
    {
        $tenantId = auth()->user()->tenant_id;
        
        $target = $this->forecastTargetService->getActiveTarget($tenantId);

        if (!$target) {
            return response()->json([
                'success' => true,
                'data' => null,
                'message' => 'No active forecast target',
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $target,
        ]);
    }

    /**
     * Update target progress
     * POST /api/forecast/update-progress
     */
    public function updateProgress(Request $request)
    {
        $validated = $request->validate([
            'target_id' => 'required|exists:forecast_targets,id',
            'current_trajectory' => 'required|numeric|min:0',
        ]);

        $target = \App\Models\ForecastTarget::findOrFail($validated['target_id']);
        
        // Verify tenant ownership
        if ($target->tenant_id !== auth()->user()->tenant_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $target->update([
            'current_trajectory' => $validated['current_trajectory'],
            'gap' => $target->target_revenue - $validated['current_trajectory'],
        ]);

        // Check if achieved
        if ($target->current_trajectory >= $target->target_revenue) {
            $target->update([
                'status' => 'achieved',
                'achieved_at' => now(),
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $target->fresh(),
            'message' => 'Progress updated successfully',
        ]);
    }
}
