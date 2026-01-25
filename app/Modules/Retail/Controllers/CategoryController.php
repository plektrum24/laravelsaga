<?php

namespace App\Modules\Retail\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Modules\Retail\Models\Category;
use Illuminate\Support\Facades\Validator;

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
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100|unique:categories,name'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        // Auto generate prefix from name (e.g. "Makanan Ringan" -> "MR")
        $words = explode(' ', strtoupper($request->name));
        $prefix = '';
        foreach ($words as $w)
            $prefix .= substr($w, 0, 1);
        $prefix = substr($prefix, 0, 5); // Limit 5 chars

        $category = Category::create([
            'name' => $request->name,
            'prefix' => $prefix,
            'is_active' => true
        ]);

        return response()->json(['success' => true, 'message' => 'Category created', 'data' => $category]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $category = Category::find($id);
        if (!$category)
            return response()->json(['success' => false, 'message' => 'Category not found'], 404);
        return response()->json(['success' => true, 'data' => $category]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $category = Category::find($id);
        if (!$category)
            return response()->json(['success' => false, 'message' => 'Category not found'], 404);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100|unique:categories,name,' . $id
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $category->update(['name' => $request->name]);

        return response()->json(['success' => true, 'message' => 'Category updated', 'data' => $category]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $category = Category::find($id);
        if (!$category)
            return response()->json(['success' => false, 'message' => 'Category not found'], 404);

        if ($category->products()->exists()) {
            return response()->json(['success' => false, 'message' => 'Cannot delete: Category has products'], 400);
        }

        $category->delete();
        return response()->json(['success' => true, 'message' => 'Category deleted']);
    }
}
