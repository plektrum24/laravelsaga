<?php

namespace App\Http\Controllers\Api\Analytics;

use App\Http\Controllers\Controller;
use App\Services\Analytics\ReportBuilderService;
use App\Services\Analytics\ExportService;
use Illuminate\Http\Request;

/**
 * ReportBuilderController
 * 
 * API endpoints for custom report generation
 */
class ReportBuilderController extends Controller
{
    protected $reportBuilder;

    public function __construct(ReportBuilderService $reportBuilder)
    {
        $this->reportBuilder = $reportBuilder;
    }

    /**
     * Generate sales report
     * 
     * GET /api/reports/sales
     */
    public function salesReport(Request $request)
    {
        try {
            $filters = [
                'date_from' => $request->input('date_from'),
                'date_to' => $request->input('date_to'),
                'branch_id' => $request->input('branch_id'),
                'category_id' => $request->input('category_id'),
            ];

            $report = $this->reportBuilder->generateSalesReport($filters);

            return response()->json([
                'success' => true,
                'data' => $report,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate sales report: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Generate inventory report
     * 
     * GET /api/reports/inventory
     */
    public function inventoryReport(Request $request)
    {
        try {
            $filters = [
                'branch_id' => $request->input('branch_id'),
            ];

            $report = $this->reportBuilder->generateInventoryReport($filters);

            return response()->json([
                'success' => true,
                'data' => $report,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate inventory report: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Generate customer report
     * 
     * GET /api/reports/customers
     */
    public function customerReport(Request $request)
    {
        try {
            $filters = [
                'date_from' => $request->input('date_from'),
                'date_to' => $request->input('date_to'),
            ];

            $report = $this->reportBuilder->generateCustomerReport($filters);

            return response()->json([
                'success' => true,
                'data' => $report,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate customer report: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Export report to Excel
     * 
     * POST /api/reports/export/excel
     */
    public function exportExcel(Request $request)
    {
        try {
            $reportType = $request->input('type'); // sales, inventory, customers
            $filters = $request->only(['date_from', 'date_to', 'branch_id', 'category_id']);

            // Generate report
            switch ($reportType) {
                case 'sales':
                    $data = $this->reportBuilder->generateSalesReport($filters);
                    break;
                case 'inventory':
                    $data = $this->reportBuilder->generateInventoryReport($filters);
                    break;
                case 'customers':
                    $data = $this->reportBuilder->generateCustomerReport($filters);
                    break;
                default:
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid report type',
                    ], 400);
            }

            // Export to array format
            $exportData = $this->reportBuilder->exportToArray($reportType, $data);

            return response()->json([
                'success' => true,
                'data' => $exportData,
                'message' => 'Report ready for export',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to export report: ' . $e->getMessage(),
            ], 500);
        }
    }
}
