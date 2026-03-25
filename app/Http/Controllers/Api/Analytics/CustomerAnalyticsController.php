<?php

namespace App\Http\Controllers\Api\Analytics;

use App\Http\Controllers\Controller;
use App\Services\Analytics\CustomerSegmentationService;
use Illuminate\Http\Request;

/**
 * CustomerAnalyticsController
 * 
 * API endpoints for customer segmentation and analytics
 */
class CustomerAnalyticsController extends Controller
{
    protected $customerSegmentation;

    public function __construct(CustomerSegmentationService $customerSegmentation)
    {
        $this->customerSegmentation = $customerSegmentation;
    }

    /**
     * Get RFM analysis
     * 
     * GET /api/customers/segmentation
     */
    public function segmentation()
    {
        try {
            $analysis = $this->customerSegmentation->rfmAnalysis();

            return response()->json([
                'success' => true,
                'data' => $analysis,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to perform segmentation: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get CLV analysis
     * 
     * GET /api/customers/lifetime-value
     */
    public function lifetimeValue()
    {
        try {
            $clv = $this->customerSegmentation->calculateCLV();

            return response()->json([
                'success' => true,
                'data' => $clv,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to calculate CLV: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get churn prediction
     * 
     * GET /api/customers/churn-risk
     */
    public function churnRisk()
    {
        try {
            $churn = $this->customerSegmentation->predictChurn();

            return response()->json([
                'success' => true,
                'data' => $churn,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to predict churn: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get customer journey analysis
     * 
     * GET /api/customers/journey
     */
    public function journey()
    {
        try {
            $journey = $this->customerSegmentation->getCustomerJourney();

            return response()->json([
                'success' => true,
                'data' => $journey,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get journey data: ' . $e->getMessage(),
            ], 500);
        }
    }
}
