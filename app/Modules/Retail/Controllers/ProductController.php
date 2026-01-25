<?php

namespace App\Modules\Retail\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Modules\Retail\Models\Product;
use App\Modules\Retail\Models\ProductUnit;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    /**
     * Display a listing of the products.
     */
    public function index(Request $request)
    {
        $tenantId = $request->user()->tenant_id;
        $query = Product::with(['category', 'units.unit'])
            ->where('tenant_id', $tenantId);

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            });
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
            'units' => 'required|array|min:1',
            'units.*.unit_id' => 'required|exists:units,id',
            'units.*.conversion_qty' => 'required|numeric|min:0',
            'units.*.buy_price' => 'numeric|min:0',
            'units.*.sell_price' => 'numeric|min:0',
            'units.*.weight' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();

            $baseUnitData = collect($request->units)->firstWhere('is_base_unit', true) ?? $request->units[0];

            // 1. Create Product
            $product = Product::create([
                'tenant_id' => $request->user()->tenant_id,
                'sku' => $request->sku,
                'name' => $request->name,
                'branch_id' => $request->branch_id,
                'category_id' => $request->category_id,
                'stock' => $request->stock ?? 0,
                'buy_price' => $baseUnitData['buy_price'] ?? 0,
                'sell_price' => $baseUnitData['sell_price'] ?? 0,
                'is_active' => true,
                'track_stock' => true
            ]);

            // 2. Create Product Units
            foreach ($request->units as $unitData) {
                ProductUnit::create([
                    'product_id' => $product->id,
                    'unit_id' => $unitData['unit_id'],
                    'conversion_qty' => $unitData['conversion_qty'],
                    'buy_price' => $unitData['buy_price'],
                    'sell_price' => $unitData['sell_price'],
                    'weight' => $unitData['weight'] ?? 0,
                    'is_base_unit' => $unitData['is_base_unit'] ?? false
                ]);
            }

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
        $product = Product::with(['category', 'units.unit'])
            ->where('tenant_id', request()->user()->tenant_id)
            ->find($id);

        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Product not found'], 404);
        }

        return response()->json(['success' => true, 'data' => $product]);
    }

    public function update(Request $request, string $id)
    {
        $product = Product::where('tenant_id', $request->user()->tenant_id)->find($id);

        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Product not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'sku' => Rule::unique('products')->ignore($product->id)->where(function ($query) use ($request) {
                return $query->where('tenant_id', $request->user()->tenant_id);
            }),
            'name' => 'sometimes|required|string|max:255',
            'units' => 'sometimes|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();

            $product->update($request->only([
                'sku',
                'name',
                'category_id',
                'description',
                'min_stock',
                'stock'
            ]));

            if ($request->has('units')) {
                $product->units()->delete();
                foreach ($request->units as $unitData) {
                    ProductUnit::create([
                        'product_id' => $product->id,
                        'unit_id' => $unitData['unit_id'],
                        'conversion_qty' => $unitData['conversion_qty'],
                        'buy_price' => $unitData['buy_price'],
                        'sell_price' => $unitData['sell_price'],
                        'weight' => $unitData['weight'] ?? 0,
                        'is_base_unit' => $unitData['is_base_unit'] ?? false
                    ]);
                }

                $baseUnit = $product->units()->where('is_base_unit', true)->first();
                if ($baseUnit) {
                    $product->update([
                        'buy_price' => $baseUnit->buy_price,
                        'sell_price' => $baseUnit->sell_price
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Product updated successfully',
                'data' => $product->load('units.unit')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Update failed: ' . $e->getMessage()], 500);
        }
    }

    public function destroy(string $id)
    {
        $product = Product::where('tenant_id', request()->user()->tenant_id)->find($id);

        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Product not found'], 404);
        }

        $product->delete();

        return response()->json(['success' => true, 'message' => 'Product deleted successfully']);
    }

    // Helper Methods for Frontend
    public function getCategories()
    {
        $categories = \App\Modules\Retail\Models\Category::where('is_active', true)->get();
        return response()->json(['success' => true, 'data' => $categories]);
    }

    public function getUnits()
    {
        $units = \App\Modules\Retail\Models\Unit::orderBy('sort_order')->get();
        return response()->json(['success' => true, 'data' => $units]);
    }

    public function generateSku($categoryId)
    {
        $category = \App\Modules\Retail\Models\Category::find($categoryId);
        if (!$category)
            return response()->json(['success' => false, 'message' => 'Category not found'], 404);

        // Logic: CAT-0001
        $prefix = $category->prefix ?? 'GEN';
        $lastProduct = Product::where('category_id', $categoryId)->latest()->first();

        $nextNumber = 1;
        if ($lastProduct && preg_match('/-(\d+)$/', $lastProduct->sku, $matches)) {
            $nextNumber = intval($matches[1]) + 1;
        }

        $sku = sprintf("%s-%04d", $prefix, $nextNumber);

        return response()->json(['success' => true, 'data' => ['sku' => $sku]]);
    }
}
