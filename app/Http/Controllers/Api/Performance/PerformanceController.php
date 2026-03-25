<?php

namespace App\Http\Controllers\Api\Performance;

use App\Http\Controllers\Controller;
use App\Services\Performance\DatabaseOptimizationService;
use App\Services\Performance\CacheService;
use Illuminate\Http\Request;

/**
 * PerformanceController
 * 
 * API endpoints for performance monitoring and optimization
 */
class PerformanceController extends Controller
{
    protected $dbOptimization;
    protected $cacheService;

    public function __construct(
        DatabaseOptimizationService $dbOptimization,
        CacheService $cacheService
    ) {
        $this->dbOptimization = $dbOptimization;
        $this->cacheService = $cacheService;
    }

    /**
     * Get database statistics
     * 
     * GET /api/performance/database/stats
     */
    public function databaseStats()
    {
        try {
            $stats = $this->dbOptimization->getTableStatistics();

            return response()->json([
                'success' => true,
                'data' => $stats,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get database stats: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Analyze slow queries
     * 
     * GET /api/performance/database/slow-queries
     */
    public function slowQueries()
    {
        try {
            $analysis = $this->dbOptimization->analyzeSlowQueries();

            return response()->json([
                'success' => true,
                'data' => $analysis,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to analyze queries: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Check missing indexes
     * 
     * GET /api/performance/database/indexes
     */
    public function missingIndexes()
    {
        try {
            $recommendations = $this->dbOptimization->checkMissingIndexes();

            return response()->json([
                'success' => true,
                'data' => $recommendations,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to check indexes: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Optimize database tables
     * 
     * POST /api/performance/database/optimize
     */
    public function optimizeTables()
    {
        try {
            $result = $this->dbOptimization->optimizeTables();

            return response()->json([
                'success' => true,
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to optimize tables: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get cache statistics
     * 
     * GET /api/performance/cache/stats
     */
    public function cacheStats()
    {
        try {
            $stats = $this->cacheService->getStatistics();

            return response()->json([
                'success' => true,
                'data' => $stats,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get cache stats: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Warm up caches
     * 
     * POST /api/performance/cache/warmup
     */
    public function warmupCache()
    {
        try {
            $result = $this->cacheService->warmupCaches();

            return response()->json([
                'success' => true,
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to warmup cache: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Clear all cache
     * 
     * POST /api/performance/cache/clear
     */
    public function clearCache()
    {
        try {
            $result = $this->cacheService->clearAll();

            return response()->json([
                'success' => true,
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to clear cache: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get performance summary
     * 
     * GET /api/performance/summary
     */
    public function summary()
    {
        try {
            $dbStats = $this->dbOptimization->getTableStatistics();
            $cacheStats = $this->cacheService->getStatistics();

            return response()->json([
                'success' => true,
                'data' => [
                    'database' => [
                        'total_tables' => $dbStats['total_tables'],
                        'total_size' => $dbStats['total_size'],
                    ],
                    'cache' => $cacheStats,
                    'generated_at' => now()->format('Y-m-d H:i:s'),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get summary: ' . $e->getMessage(),
            ], 500);
        }
    }
}
