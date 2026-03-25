<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            // Simple query without withCount to avoid potential relationship issues
            $categories = Category::orderBy('name')->get();
            
            // Manually add products count if needed
            foreach ($categories as $category) {
                $category->products_count = $category->products()->count();
            }
            
            return response()->json(['success' => true, 'data' => $categories]);
        } catch (\Exception $e) {
            \Log::error('Category index error: ' . $e->getMessage() . ' - Trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false, 
                'message' => 'Error fetching categories: ' . $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique(Category::class, 'name')],
        ]);

        $category = Category::create([
            'name' => $request->name,
            'prefix' => strtoupper(substr($request->name, 0, 3))
        ]);

        return response()->json(['success' => true, 'message' => 'Kategori berhasil dibuat', 'data' => $category], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $category = Category::withCount('products')->findOrFail($id);
        return response()->json(['success' => true, 'data' => $category]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);

        $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique(Category::class, 'name')->ignore($category->id)],
        ]);

        $category->update([
            'name' => $request->name,
        ]);

        return response()->json(['success' => true, 'message' => 'Kategori berhasil diupdate', 'data' => $category]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $category = Category::withCount('products')->findOrFail($id);

        if ($category->products_count > 0) {
            return response()->json(['success' => false, 'message' => 'Tidak dapat menghapus kategori yang memiliki produk'], 400);
        }

        $category->delete();

        return response()->json(['success' => true, 'message' => 'Kategori berhasil dihapus']);
    }
}
