<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = Category::withCount('products')->orderBy('name')->get();
        return response()->json(['success' => true, 'data' => $categories]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
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
            'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
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
