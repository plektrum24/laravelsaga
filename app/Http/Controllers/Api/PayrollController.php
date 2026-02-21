<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Payroll;
use App\Services\SalaryService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PayrollController extends Controller
{
    protected $salaryService;

    public function __construct(SalaryService $salaryService)
    {
        $this->salaryService = $salaryService;
    }

    /**
     * Preview payroll for an employee.
     */
    public function preview(Request $request, Employee $employee)
    {
        $request->validate([
            'period' => 'required|date_format:Y-m',
        ]);

        $data = $this->salaryService->calculateMonthlySalary($employee, $request->period);

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * Store a new payroll record.
     */
    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:tenant.employees,id',
            'period' => 'required|date_format:Y-m',
            'payment_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $employee = Employee::findOrFail($request->employee_id);
        $calc = $this->salaryService->calculateMonthlySalary($employee, $request->period);

        $payroll = Payroll::updateOrCreate(
        [
            'employee_id' => $employee->id,
            'period' => $request->period,
        ],
        [
            'payment_date' => $request->payment_date ?? date('Y-m-d'),
            'basic_salary' => $calc['basic_salary'],
            'allowances' => $calc['allowances'],
            'deductions' => $calc['deductions'],
            'bonuses' => $calc['bonuses'],
            'total_amount' => $calc['total_amount'],
            'status' => 'paid',
            'notes' => $request->notes,
        ]
        );

        return response()->json([
            'success' => true,
            'data' => $payroll
        ]);
    }

    /**
     * List payroll records.
     */
    public function index(Request $request)
    {
        $query = Payroll::with('employee');

        if ($request->period) {
            $query->where('period', $request->period);
        }

        if ($request->employee_id) {
            $query->where('employee_id', $request->employee_id);
        }

        return response()->json([
            'success' => true,
            'data' => $query->paginate(20)
        ]);
    }

    /**
     * Preview total payroll for the organization.
     */
    public function bulkPreview(Request $request)
    {
        $request->validate([
            'period' => 'required|date_format:Y-m',
        ]);

        $data = $this->salaryService->calculateBulk($request->period);

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * Store payroll for all active employees.
     */
    public function bulkStore(Request $request)
    {
        $request->validate([
            'period' => 'required|date_format:Y-m',
            'payment_date' => 'nullable|date',
        ]);

        $batch = $this->salaryService->calculateBulk($request->period);
        $payrolls = [];

        foreach ($batch['items'] as $item) {
            $employee = Employee::find($item['employee_id']);
            if (!$employee)
                continue;

            $payrolls[] = Payroll::updateOrCreate(
            [
                'employee_id' => $employee->id,
                'period' => $request->period,
            ],
            [
                'payment_date' => $request->payment_date ?? date('Y-m-d'),
                'basic_salary' => $item['basic_salary'],
                'allowances' => $item['allowances'],
                'deductions' => $item['deductions'],
                'bonuses' => $item['bonuses'],
                'total_amount' => $item['total_amount'],
                'status' => 'paid',
            ]
            );
        }

        return response()->json([
            'success' => true,
            'count' => count($payrolls),
            'total_payout' => $batch['total_payout']
        ]);
    }
}
