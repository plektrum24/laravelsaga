<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductUnit;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    /**
     * Display a listing of the products.
     */
    public function index(Request $request)
    {
        $query = Product::with(['category', 'units.unit']);

        if ($request->has('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('sku', 'like', "%{$search}%");
        }

        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $products = $query->latest()->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }

    /**
     * Store a newly created product in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sku' => 'required|unique:products,sku',
            'name' => 'required|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'base_unit_id' => 'required|exists:units,id', // Input for Base Unit
            'buy_price' => 'numeric|min:0',
            'sell_price' => 'numeric|min:0',
            'initial_stock' => 'numeric|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();

            // 1. Create Product
            $product = Product::create([
                'sku' => $request->sku,
                'name' => $request->name,
                'category_id' => $request->category_id,
                'stock' => $request->initial_stock ?? 0,
                'buy_price' => $request->buy_price ?? 0,
                'sell_price' => $request->sell_price ?? 0,
                'is_active' => true,
                'track_stock' => true
            ]);

            // 2. Create Base Product Unit
            ProductUnit::create([
                'product_id' => $product->id,
                'unit_id' => $request->base_unit_id,
                'conversion_qty' => 1,
                'buy_price' => $product->buy_price,
                'sell_price' => $product->sell_price,
                'is_base_unit' => true
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Product created successfully',
                'data' => $product->load('units.unit')
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Failed to create product: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified product.
     */
    public function show(string $id)
    {
        $product = Product::with(['category', 'units.unit'])->find($id);

        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Product not found'], 404);
        }

        return response()->json(['success' => true, 'data' => $product]);
    }

    /**
     * Update the specified product in storage.
     */
    public function update(Request $request, string $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Product not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'sku' => 'sometimes|required|unique:products,sku,' . $id,
            'name' => 'sometimes|required|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'buy_price' => 'numeric|min:0',
            'sell_price' => 'numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $product->update($request->only([
            'sku',
            'name',
            'category_id',
            'buy_price',
            'sell_price',
            'description',
            'min_stock'
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Product updated successfully',
            'data' => $product
        ]);
    }

    /**
     * Remove the specified product from storage.
     */
    public function destroy(string $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Product not found'], 404);
        }

        $product->delete();

        return response()->json(['success' => true, 'message' => 'Product deleted successfully']);
    }
}
