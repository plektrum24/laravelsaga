<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $query = Supplier::query();

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('contact_person', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $limit = $request->limit ?? 50;
        $suppliers = $query->orderBy('name')->paginate($limit);

        return response()->json([
            'success' => true,
            'data' => $suppliers->items(),
            'pagination' => [
                'total' => $suppliers->total(),
                'per_page' => $suppliers->perPage(),
                'current_page' => $suppliers->currentPage(),
                'last_page' => $suppliers->lastPage(),
            ]
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
        ]);

        $supplier = Supplier::create($request->only([
            'name',
            'contact_person',
            'phone',
            'email',
            'address'
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Supplier berhasil ditambahkan',
            'data' => $supplier
        ], 201);
    }

    public function show($id)
    {
        $supplier = Supplier::with('purchases')->findOrFail($id);
        return response()->json(['success' => true, 'data' => $supplier]);
    }

    public function update(Request $request, $id)
    {
        $supplier = Supplier::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
        ]);

        $supplier->update($request->only([
            'name',
            'contact_person',
            'phone',
            'email',
            'address'
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Supplier berhasil diupdate',
            'data' => $supplier
        ]);
    }

    public function destroy($id)
    {
        $supplier = Supplier::findOrFail($id);

        // Check if supplier has purchases
        if ($supplier->purchases()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Supplier tidak bisa dihapus karena memiliki riwayat pembelian'
            ], 400);
        }

        $supplier->delete();

        return response()->json([
            'success' => true,
            'message' => 'Supplier berhasil dihapus'
        ]);
    }
}
