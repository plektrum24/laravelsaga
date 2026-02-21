<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Attendance;
use Carbon\Carbon;

class SalaryService
{
    /**
     * Calculate net salary for an employee for a specific period.
     *
     * @param Employee $employee
     * @param string $period (YYYY-MM)
     * @return array
     */
    public function calculateMonthlySalary(Employee $employee, string $period)
    {
        $startDate = Carbon::parse($period . '-01')->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        // 1. Fetch Attendance Stats
        $attendanceStats = Attendance::where('employee_id', $employee->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $absentCount = $attendanceStats['absent'] ?? 0;
        $lateCount = $attendanceStats['late'] ?? 0;

        // 2. Logic for Deductions (Fines)
        $finePerAbsence = 50000;
        $finePerLateness = 10000;

        $deductions = ($absentCount * $finePerAbsence) + ($lateCount * $finePerLateness);

        // 3. Basic Components
        $basicSalary = $employee->basic_salary;
        $allowances = $employee->allowance +
            $employee->transport_allowance +
            $employee->meal_allowance +
            $employee->position_allowance;

        $bonus = $employee->performance_bonus;

        $totalAmount = ($basicSalary + $allowances + $bonus) - $deductions;

        return [
            'employee_id' => $employee->id,
            'period' => $period,
            'attendance_summary' => [
                'present' => $attendanceStats['present'] ?? 0,
                'absent' => $absentCount,
                'late' => $lateCount,
                'leave' => $attendanceStats['leave'] ?? 0,
            ],
            'basic_salary' => $basicSalary,
            'allowances' => $allowances,
            'bonuses' => $bonus,
            'deductions' => $deductions,
            'total_amount' => max(0, $totalAmount), // Salary cannot be negative
        ];
    }

    /**
     * Calculate salary for all active employees for a given period.
     */
    public function calculateBulk(string $period)
    {
        $employees = Employee::where('is_active', true)->get();
        $results = [];

        foreach ($employees as $employee) {
            $results[] = $this->calculateMonthlySalary($employee, $period);
        }

        return [
            'period' => $period,
            'total_employees' => count($results),
            'total_payout' => array_sum(array_column($results, 'total_amount')),
            'items' => $results
        ];
    }
}
