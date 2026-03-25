<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    /**
     * Get users filtered by role (for tenant users)
     */
    public function index(Request $request)
    {
        try {
            $query = User::query();

            // Filter by role if provided
            if ($request->has('role') && $request->role) {
                $roles = is_array($request->role) ? $request->role : explode(',', $request->role);
                $query->whereIn('role', $roles);
            }

            // Filter by branch if provided
            if ($request->has('branch_id') && $request->branch_id) {
                $query->where('branch_id', $request->branch_id);
            }

            // Search by name
            if ($request->has('search') && $request->search) {
                $query->where('name', 'like', "%{$request->search}%");
            }

            $users = $query->orderBy('name', 'asc')->get();

            return response()->json([
                'success' => true,
                'data' => $users
            ]);
        } catch (\Exception $e) {
            Log::error('User index error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching users: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get cashier users specifically
     */
    public function cashiers(Request $request)
    {
        try {
            $query = User::where('role', 'cashier');

            // Filter by branch if provided
            if ($request->has('branch_id') && $request->branch_id) {
                $query->where('branch_id', $request->branch_id);
            }

            $cashiers = $query->orderBy('name', 'asc')->get();

            return response()->json([
                'success' => true,
                'data' => $cashiers
            ]);
        } catch (\Exception $e) {
            Log::error('Cashiers index error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching cashiers: ' . $e->getMessage()
            ], 500);
        }
    }
}
