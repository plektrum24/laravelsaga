<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['category', 'units.unit', 'branch']);

        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        // Filter Category
        if ($request->has('category_id') && $request->category_id) {
            $query->where('category_id', $request->category_id);
        }

        // Filter Low Stock
        if ($request->has('low_stock') && $request->low_stock === 'true') {
            $query->whereColumn('stock', '<=', 'min_stock');
        }

        // Sort
        $sort = $request->sort ?? 'name_asc';
        switch ($sort) {
            case 'name_desc': $query->orderBy('name', 'desc'); break;
            case 'price_asc': $query->orderBy('sell_price', 'asc'); break; // Base sell price
            case 'price_desc': $query->orderBy('sell_price', 'desc'); break;
            case 'stock_asc': $query->orderBy('stock', 'asc'); break;
            case 'stock_desc': $query->orderBy('stock', 'desc'); break;
            default: $query->orderBy('name', 'asc');
        }

        $limit = $request->limit ?? 20;
        $products = $query->paginate($limit);

        return response()->json([
            'success' => true,
            'data' => [
                'products' => $products->items(),
                'pagination' => [
                    'total' => $products->total(),
                    'per_page' => $products->perPage(),
                    'current_page' => $products->currentPage(),
                    'last_page' => $products->lastPage(),
                    'totalPages' => $products->lastPage(), // Frontend alias
                ]
            ]
        ]);
    }

    public function show($id)
    {
        $product = Product::with(['category', 'units.unit', 'branch'])->findOrFail($id);
        return response()->json(['success' => true, 'data' => $product]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'units' => 'required|array|min:1',
            'units.*.unit_id' => 'required|exists:units,id',
            'units.*.conversion_qty' => 'required|numeric',
            'units.*.buy_price' => 'required|numeric',
            'units.*.sell_price' => 'required|numeric',
        ]);

        try {
            DB::beginTransaction();

            $data = $request->only([
                'name', 'category_id', 'stock', 'min_stock', 'barcode', 'track_stock', 'branch_id'
            ]);
            
            // Generate SKU if empty
            if (empty($request->sku)) {
                $category = Category::find($request->category_id);
                $prefix = strtoupper(substr($category->name ?? 'GEN', 0, 3));
                $data['sku'] = $prefix . '-' . time();
            } else {
                $data['sku'] = $request->sku;
            }

            // Image Upload
            if ($request->hasFile('image')) {
                $path = $request->file('image')->store('products', 'public');
                $data['image_url'] = url('storage/' . $path);
            } elseif ($request->image_url) {
                // Keep existing url if passed (edit mode mostly, but store generally new)
                $data['image_url'] = $request->image_url;
            }

            // Base Unit (Largest/First) logic
            // Frontend sends largest unit last usually, or we pick first? 
            // The Logic in View: stock is input in largest unit. 
            // We need to determine base unit pricing for the main product table.
            
            // Simplify: Take the first unit as "Base" for main table display? 
            // Or the one marked is_base_unit?
            // Frontend logic seems to force first unit in array as base.
            
            $mainUnit = $request->units[0]; 
            $data['buy_price'] = $mainUnit['buy_price'];
            $data['sell_price'] = $mainUnit['sell_price'];
            $data['track_stock'] = $request->track_stock ?? true;
            $data['is_active'] = true;

            $product = Product::create($data);

            // Sync Units
            foreach ($request->units as $index => $u) {
                $product->units()->create([
                    'unit_id' => $u['unit_id'],
                    'conversion_qty' => $u['conversion_qty'],
                    'buy_price' => $u['buy_price'],
                    'sell_price' => $u['sell_price'],
                    'is_base_unit' => $index === 0, // Assume first is base
                ]);
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Product saved', 'data' => $product]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        
        // Similar validation and logic as store, but updating.
        // For brevity in this turn, assuming standard update logic.
        // Implementing full update logic:

        try {
            DB::beginTransaction();
            
            $data = $request->only([
                'name', 'category_id', 'stock', 'min_stock', 'barcode', 'branch_id'
            ]);

            if ($request->hasFile('image')) {
                $path = $request->file('image')->store('products', 'public');
                $data['image_url'] = url('storage/' . $path);
            }

             // Update Pricing from first unit
            if (!empty($request->units)) {
                $mainUnit = $request->units[0];
                $data['buy_price'] = $mainUnit['buy_price'];
                $data['sell_price'] = $mainUnit['sell_price'];
            }

            $product->update($data);

            // Re-create units
            if (!empty($request->units)) {
                $product->units()->delete();
                foreach ($request->units as $index => $u) {
                    $product->units()->create([
                        'unit_id' => $u['unit_id'],
                        'conversion_qty' => $u['conversion_qty'],
                        'buy_price' => $u['buy_price'],
                        'sell_price' => $u['sell_price'],
                        'is_base_unit' => $index === 0,
                    ]);
                }
            }

            DB::commit();
            return response()->json(['success' => true, 'data' => $product]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        Product::findOrFail($id)->delete();
        return response()->json(['success' => true, 'message' => 'Product deleted']);
    }

    public function categories()
    {
        return response()->json(['success' => true, 'data' => Category::all()]);
    }

    public function units()
    {
        return response()->json(['success' => true, 'data' => Unit::all()]);
    }
    
    public function generateSku($categoryId)
    {
        $category = Category::findOrFail($categoryId);
        $prefix = strtoupper(substr($category->name, 0, 3));
        $sku = $prefix . '-' . mt_rand(10000, 99999);
        return response()->json(['success' => true, 'data' => ['sku' => $sku]]);
    }
    
    // Support creating units on the fly
    public function storeUnit(Request $request) {
        $request->validate(['name' => 'required']);
        $unit = Unit::create([
            'name' => $request->name, 
            'abbreviation' => substr($request->name, 0, 3)
        ]);
        return response()->json(['success' => true, 'data' => $unit]);
    }
}
