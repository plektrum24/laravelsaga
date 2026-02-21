<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $query = Employee::with(['branch', 'user']);

        if ($request->search) {
            $query->where('name', 'like', "%{$request->search}%")
                ->orWhere('nik', 'like', "%{$request->search}%");
        }

        if ($request->branch_id) {
            $query->where('branch_id', $request->branch_id);
        }

        $employees = $query->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $employees
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'role' => 'required|string',
            'branch_id' => 'nullable|exists:tenant.branches,id',
            'user_id' => 'nullable|exists:users,id',
            'nik' => 'nullable|string|unique:tenant.employees,nik',
            'basic_salary' => 'nullable|numeric|min:0',
            'allowance' => 'nullable|numeric|min:0',
            'transport_allowance' => 'nullable|numeric|min:0',
            'meal_allowance' => 'nullable|numeric|min:0',
            'position_allowance' => 'nullable|numeric|min:0',
            'performance_bonus' => 'nullable|numeric|min:0',
            'bank_name' => 'nullable|string|max:100',
            'bank_account_number' => 'nullable|string|max:50',
            'bank_account_holder' => 'nullable|string|max:100',
        ]);

        $employee = Employee::create($request->all());

        return response()->json([
            'success' => true,
            'data' => $employee
        ]);
    }

    public function update(Request $request, Employee $employee)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'role' => 'required|string',
            'branch_id' => 'nullable|exists:tenant.branches,id',
            'user_id' => 'nullable|exists:users,id',
            'nik' => 'nullable|string|unique:tenant.employees,nik,' . $employee->id,
            'basic_salary' => 'nullable|numeric|min:0',
            'allowance' => 'nullable|numeric|min:0',
            'transport_allowance' => 'nullable|numeric|min:0',
            'meal_allowance' => 'nullable|numeric|min:0',
            'position_allowance' => 'nullable|numeric|min:0',
            'performance_bonus' => 'nullable|numeric|min:0',
            'bank_name' => 'nullable|string|max:100',
            'bank_account_number' => 'nullable|string|max:50',
            'bank_account_holder' => 'nullable|string|max:100',
        ]);

        $employee->update($request->all());

        return response()->json([
            'success' => true,
            'data' => $employee
        ]);
    }

    public function destroy(Employee $employee)
    {
        $employee->delete();

        return response()->json([
            'success' => true,
            'message' => 'Employee deleted successfully'
        ]);
    }
}
