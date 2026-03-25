<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BranchController extends Controller
{
    /**
     * Get all branches
     * GET /api/branches
     */
    public function index()
    {
        try {
            $branches = Branch::withCount(['users as employees' => function ($query) {
                $query->where('role', '!=', 'Owner');
            }])
                ->latest()
                ->get();

            return response()->json([
                'success' => true,
                'data' => $branches
            ]);
        } catch (\Exception $e) {
            \Log::error('Branch index error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get branch by ID
     * GET /api/branches/{id}
     */
    public function show($id)
    {
        try {
            $branch = Branch::withCount(['users as employees'])
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $branch
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Branch not found'
            ], 404);
        }
    }

    /**
     * Create new branch
     * POST /api/branches
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:100',
                'code' => 'nullable|string|max:20|unique:branches,code',
                'address' => 'nullable|string',
                'city' => 'nullable|string|max:100',
                'province' => 'nullable|string|max:100',
                'postal_code' => 'nullable|string|max:20',
                'phone' => 'nullable|string|max:20',
                'email' => 'nullable|email|max:100',
                'status' => 'nullable|in:active,inactive',
                'manager_name' => 'nullable|string|max:100',
                'manager_phone' => 'nullable|string|max:20',
            ]);

            // Generate code if not provided
            if (empty($validated['code'])) {
                $validated['code'] = $this->generateCode($validated['name']);
            }

            $validated['status'] = $validated['status'] ?? 'active';

            $branch = Branch::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Branch created successfully',
                'data' => $branch
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Branch store error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update branch
     * PUT /api/branches/{id}
     */
    public function update(Request $request, $id)
    {
        try {
            $branch = Branch::findOrFail($id);

            $validated = $request->validate([
                'name' => 'sometimes|required|string|max:100',
                'code' => 'nullable|string|max:20|unique:branches,code,' . $id,
                'address' => 'nullable|string',
                'city' => 'nullable|string|max:100',
                'province' => 'nullable|string|max:100',
                'postal_code' => 'nullable|string|max:20',
                'phone' => 'nullable|string|max:20',
                'email' => 'nullable|email|max:100',
                'status' => 'nullable|in:active,inactive',
                'manager_name' => 'nullable|string|max:100',
                'manager_phone' => 'nullable|string|max:20',
            ]);

            $branch->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Branch updated successfully',
                'data' => $branch
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Branch update error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete branch
     * DELETE /api/branches/{id}
     */
    public function destroy($id)
    {
        try {
            $branch = Branch::findOrFail($id);

            // Check if branch has users
            $userCount = User::where('branch_id', $id)->count();
            if ($userCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete branch with ' . $userCount . ' user(s). Reassign or delete users first.'
                ], 400);
            }

            $branch->delete();

            return response()->json([
                'success' => true,
                'message' => 'Branch deleted successfully'
            ]);

        } catch (\Exception $e) {
            \Log::error('Branch delete error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get branch statistics
     * GET /api/branches/statistics
     */
    public function statistics()
    {
        try {
            $totalBranches = Branch::count();
            $activeBranches = Branch::where('status', 'active')->count();
            $inactiveBranches = Branch::where('status', 'inactive')->count();
            $totalEmployees = User::where('role', '!=', 'Owner')->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'total_branches' => $totalBranches,
                    'active_branches' => $activeBranches,
                    'inactive_branches' => $inactiveBranches,
                    'total_employees' => $totalEmployees,
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Branch statistics error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate unique branch code
     */
    private function generateCode($name)
    {
        $prefix = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $name), 0, 3));
        $random = rand(100, 999);
        
        $code = 'BR-' . $prefix . '-' . $random;
        
        // Ensure uniqueness
        while (Branch::where('code', $code)->exists()) {
            $random = rand(100, 999);
            $code = 'BR-' . $prefix . '-' . $random;
        }
        
        return $code;
    }
}
