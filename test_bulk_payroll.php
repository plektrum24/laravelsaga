<?php

use App\Models\Employee;
use App\Models\Tenant;
use App\Services\SalaryService;
use Illuminate\Support\Facades\DB;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$tenant = Tenant::first();
if (!$tenant) {
    die("No tenant found. Seed database first.\n");
}

// Manually set tenant for testing
DB::purge('tenant');
config(['database.connections.tenant.database' => database_path('tenant_base.sqlite')]);
DB::reconnect('tenant');

$service = app(SalaryService::class);
$period = '2023-10';

echo "Testing Bulk Calculation for Period: $period\n";
$results = $service->calculateBulk($period);

echo "Total Employees: " . $results['total_employees'] . "\n";
echo "Total Payout: " . $results['total_payout'] . "\n";

foreach ($results['items'] as $item) {
    $emp = Employee::find($item['employee_id']);
    echo "- " . $emp->name . ": " . $item['total_amount'] . " (Att: " . $item['attendance_summary']['absent'] . " abs)\n";
}

echo "Verification Complete.\n";
