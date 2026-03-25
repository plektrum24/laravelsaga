<?php

namespace App\Services\Performance;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

/**
 * DatabaseOptimizationService
 * 
 * Provides database query optimization and analysis
 */
class DatabaseOptimizationService
{
    /**
     * Analyze slow queries (requires query log enabled)
     */
    public function analyzeSlowQueries()
    {
        // Enable query log temporarily
        DB::enableQueryLog();
        
        // Execute some common queries
        $queries = [
            'products' => DB::table('products')->count(),
            'transactions' => DB::table('transactions')->whereDate('created_at', Carbon::today())->count(),
            'customers' => DB::table('customers')->count(),
        ];
        
        $queryLog = DB::getQueryLog();
        DB::disableQueryLog();
        
        // Analyze queries
        $analysis = [];
        foreach ($queryLog as $query) {
            $analysis[] = [
                'sql' => $query['query'],
                'bindings' => $query['bindings'],
                'time_ms' => $query['time'],
                'is_slow' => $query['time'] > 100, // > 100ms is slow
            ];
        }
        
        return [
            'queries' => $analysis,
            'total_queries' => count($queryLog),
            'slow_queries' => collect($analysis)->where('is_slow', true)->count(),
            'avg_time_ms' => collect($analysis)->avg('time_ms') ?? 0,
        ];
    }

    /**
     * Check missing indexes on key tables
     */
    public function checkMissingIndexes()
    {
        $recommendations = [];
        
        // Check common tables for indexing
        $tables = [
            'transactions' => ['branch_id', 'customer_id', 'status', 'created_at'],
            'transaction_items' => ['transaction_id', 'product_id'],
            'products' => ['category_id', 'branch_id', 'sku'],
            'customers' => ['phone', 'email'],
            'inventory_movements' => ['product_id', 'branch_id', 'type'],
        ];
        
        foreach ($tables as $table => $columns) {
            foreach ($columns as $column) {
                // Check if index exists (simplified check)
                $indexExists = DB::select("
                    SELECT COUNT(*) as count 
                    FROM information_schema.statistics 
                    WHERE table_schema = DATABASE() 
                    AND table_name = ? 
                    AND column_name = ?
                ", [$table, $column]);
                
                if ($indexExists[0]->count == 0) {
                    $recommendations[] = [
                        'table' => $table,
                        'column' => $column,
                        'recommendation' => "Add index on {$table}.{$column}",
                        'sql' => "ALTER TABLE {$table} ADD INDEX idx_{$column} ({$column});",
                        'priority' => in_array($column, ['created_at', 'status', 'product_id', 'transaction_id']) ? 'High' : 'Medium',
                    ];
                }
            }
        }
        
        return [
            'recommendations' => $recommendations,
            'high_priority' => collect($recommendations)->where('priority', 'High')->count(),
            'medium_priority' => collect($recommendations)->where('priority', 'Medium')->count(),
        ];
    }

    /**
     * Optimize tables
     */
    public function optimizeTables()
    {
        $tables = DB::select('SHOW TABLES');
        $dbName = DB::getDatabaseName();
        $tableKey = 'Tables_in_' . $dbName;
        
        $results = [];
        foreach ($tables as $table) {
            $tableName = $table->$tableKey;
            
            // Optimize table
            DB::statement("OPTIMIZE TABLE {$tableName}");
            
            // Get table stats
            $stats = DB::select("
                SELECT 
                    table_name,
                    table_rows,
                    data_length,
                    index_length,
                    data_free
                FROM information_schema.tables 
                WHERE table_schema = DATABASE() 
                AND table_name = ?
            ", [$tableName]);
            
            if ($stats) {
                $results[] = [
                    'table' => $tableName,
                    'rows' => $stats[0]->table_rows,
                    'data_size' => $this->formatBytes($stats[0]->data_length),
                    'index_size' => $this->formatBytes($stats[0]->index_length),
                    'free_space' => $this->formatBytes($stats[0]->data_free),
                ];
            }
        }
        
        return [
            'tables' => $results,
            'total_tables' => count($results),
            'optimized_at' => Carbon::now()->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Get table statistics
     */
    public function getTableStatistics()
    {
        $tables = DB::select('SHOW TABLE STATUS');
        
        $stats = [];
        $totalSize = 0;
        
        foreach ($tables as $table) {
            $dataSize = $table->Data_length + $table->Index_length;
            $totalSize += $dataSize;
            
            $stats[] = [
                'table' => $table->Name,
                'rows' => $table->Rows,
                'data_size' => $this->formatBytes($table->Data_length),
                'index_size' => $this->formatBytes($table->Index_length),
                'total_size' => $this->formatBytes($dataSize),
                'avg_row_length' => $this->formatBytes($table->Avg_row_length),
                'auto_increment' => $table->Auto_increment,
                'engine' => $table->Engine,
                'collation' => $table->Collation,
            ];
        }
        
        return [
            'tables' => $stats,
            'total_tables' => count($stats),
            'total_size' => $this->formatBytes($totalSize),
            'database' => DB::getDatabaseName(),
        ];
    }

    /**
     * Clear old data (archive strategy)
     */
    public function clearOldData($daysToKeep = 365)
    {
        $cutoffDate = Carbon::now()->subDays($daysToKeep);
        
        // Count records to be archived
        $oldTransactions = DB::table('transactions')
            ->where('created_at', '<', $cutoffDate)
            ->where('status', 'completed')
            ->count();
        
        $oldLogs = DB::table('activity_logs')
            ->where('created_at', '<', $cutoffDate)
            ->count();
        
        return [
            'cutoff_date' => $cutoffDate->format('Y-m-d'),
            'records_to_archive' => [
                'transactions' => $oldTransactions,
                'activity_logs' => $oldLogs,
            ],
            'warning' => 'Review before executing archive operation',
        ];
    }

    /**
     * Format bytes to human readable
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
