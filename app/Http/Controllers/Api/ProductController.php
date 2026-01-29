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
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ProductsExport;
use App\Exports\ProductsTemplateExport;
use Barryvdh\DomPDF\Facade\Pdf;

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
        $query->reorder();
        $sort = $request->sort ?? 'name_asc';

        switch ($sort) {
            case 'name_asc':
                $query->orderBy('name', 'asc');
                break;
            case 'name_desc':
                $query->orderBy('name', 'desc');
                break;
            case 'price_asc':
                $query->orderBy('sell_price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('sell_price', 'desc');
                break;
            case 'stock_asc':
                $query->orderByRaw('CAST(stock AS DECIMAL(15,2)) ASC');
                break;
            case 'stock_desc':
                $query->orderByRaw('CAST(stock AS DECIMAL(15,2)) DESC');
                break;
            default:
                $query->orderBy('name', 'asc');
        }

        $limit = $request->limit ?? 40;
        $products = $query->paginate($limit);

        $products->getCollection()->transform(function ($product) {
            $product->units = $product->units->map(function ($productUnit) {
                $productUnit->conversion_qty = (float) $productUnit->conversion_qty;
                return $productUnit;
            });
            $baseUnit = $product->units->where('is_base_unit', true)->first();
            if ($baseUnit) {
                $product->base_unit_name = $baseUnit->unit->name ?? '-';
                $product->buy_price = $baseUnit->buy_price;
                $product->sell_price = $baseUnit->sell_price;
            }
            return $product;
        });

        $products->setCollection($products->getCollection()->values());

        return response()->json([
            'success' => true,
            'data' => [
                'products' => $products->items(),
                'pagination' => [
                    'total' => $products->total(),
                    'per_page' => $products->perPage(),
                    'current_page' => $products->currentPage(),
                    'last_page' => $products->lastPage(),
                    'totalPages' => $products->lastPage(),
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
            'category_id' => ['required', \Illuminate\Validation\Rule::exists(Category::class, 'id')],
            'units' => 'required|array|min:1',
            'units.*.unit_id' => ['required', \Illuminate\Validation\Rule::exists(Unit::class, 'id')],
            'units.*.conversion_qty' => 'required|numeric',
            'units.*.buy_price' => 'required|numeric',
            'units.*.sell_price' => 'required|numeric',
        ]);

        try {
            DB::connection('tenant')->beginTransaction();

            $data = $request->only([
                'name',
                'category_id',
                'stock',
                'min_stock',
                'barcode',
                'track_stock',
                'branch_id'
            ]);

            if (empty($request->sku)) {
                $category = Category::find($request->category_id);
                $prefix = strtoupper(substr($category->name ?? 'GEN', 0, 3));
                $data['sku'] = $prefix . '-' . time();
            } else {
                $data['sku'] = $request->sku;
            }

            if ($request->hasFile('image')) {
                $path = $request->file('image')->store('products', 'public');
                $data['image_url'] = url('storage/' . $path);
            } elseif ($request->image_url) {
                $data['image_url'] = $request->image_url;
            }

            $mainUnit = $request->units[0];
            $data['buy_price'] = $mainUnit['buy_price'];
            $data['sell_price'] = $mainUnit['sell_price'];
            $data['track_stock'] = $request->track_stock ?? true;
            $data['is_active'] = true;

            $product = Product::create($data);

            foreach ($request->units as $index => $u) {
                $product->units()->create([
                    'unit_id' => $u['unit_id'],
                    'conversion_qty' => $u['conversion_qty'],
                    'buy_price' => $u['buy_price'],
                    'sell_price' => $u['sell_price'],
                    'weight' => $u['weight'] ?? 0,
                    'is_base_unit' => $index === 0,
                ]);
            }

            DB::connection('tenant')->commit();
            return response()->json(['success' => true, 'message' => 'Product saved', 'data' => $product]);

        } catch (\Exception $e) {
            DB::connection('tenant')->rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        try {
            DB::connection('tenant')->beginTransaction();

            $data = $request->only([
                'name',
                'category_id',
                'stock',
                'min_stock',
                'barcode',
                'branch_id'
            ]);

            if ($request->hasFile('image')) {
                $path = $request->file('image')->store('products', 'public');
                $data['image_url'] = url('storage/' . $path);
            } elseif ($request->image_url) {
                $data['image_url'] = $request->image_url;
            }

            if (!empty($request->units)) {
                $mainUnit = $request->units[0];
                $data['buy_price'] = $mainUnit['buy_price'];
                $data['sell_price'] = $mainUnit['sell_price'];
            }

            $product->update($data);

            if (!empty($request->units)) {
                $product->units()->delete();
                foreach ($request->units as $index => $u) {
                    $product->units()->create([
                        'unit_id' => $u['unit_id'],
                        'conversion_qty' => $u['conversion_qty'],
                        'buy_price' => $u['buy_price'],
                        'sell_price' => $u['sell_price'],
                        'weight' => $u['weight'] ?? 0,
                        'is_base_unit' => $index === 0,
                    ]);
                }
            }

            DB::connection('tenant')->commit();
            return response()->json(['success' => true, 'data' => $product]);

        } catch (\Exception $e) {
            DB::connection('tenant')->rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);

        if ($product->image_url && Storage::disk('public')->exists(str_replace('/storage/', '', $product->image_url))) {
            Storage::disk('public')->delete(str_replace('/storage/', '', $product->image_url));
        }

        $product->delete();
        return response()->json(['success' => true, 'message' => 'Product deleted']);
    }

    public function destroyAll()
    {
        $products = Product::all();
        foreach ($products as $product) {
            if ($product->image_url && Storage::disk('public')->exists(str_replace('/storage/', '', $product->image_url))) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $product->image_url));
            }
        }

        Product::query()->delete();

        return response()->json(['success' => true, 'message' => 'All products deleted']);
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

    public function storeUnit(Request $request)
    {
        $request->validate(['name' => 'required']);
        $unit = Unit::create([
            'name' => $request->name,
            'abbreviation' => substr($request->name, 0, 3)
        ]);
        return response()->json(['success' => true, 'data' => $unit]);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt,xlsx'
        ]);

        $file = $request->file('file');

        try {
            $arrays = Excel::toArray(new \stdClass(), $file);
            $data = $arrays[0];
            $headerRow = array_shift($data);

            // 1. Map header indices dynamically
            $cols = [];
            foreach ($headerRow as $i => $h) {
                if (empty($h))
                    continue;
                $cols[strtoupper(trim($h))] = $i;
            }

            // Identify all units present (SATUAN1, SATUAN2, ...)
            $unitSuffixes = [];
            foreach (array_keys($cols) as $key) {
                if (preg_match('/^SATUAN(\d+)$/', $key, $matches)) {
                    $unitSuffixes[] = $matches[1];
                }
            }
            sort($unitSuffixes, SORT_NUMERIC);

            $count = 0;
            DB::connection('tenant')->beginTransaction();
            foreach ($data as $index => $row) {
                $name = $row[$cols['NAMAITEM'] ?? -1] ?? null;
                if (empty($name))
                    continue;

                $barcode = $row[$cols['BARCODE'] ?? -1] ?? null;
                $categoryName = $row[$cols['KATEGORI'] ?? -1] ?? 'General';
                $stock = floatval(str_replace([',', ' '], '', $row[$cols['STOK'] ?? -1] ?? 0));
                $minStock = floatval(str_replace([',', ' '], '', $row[$cols['STOKMIN'] ?? -1] ?? 0));
                $expiryStr = $row[$cols['EXPIRY'] ?? -1] ?? null;
                $expiredDate = (!empty($expiryStr) && strtotime($expiryStr)) ? date('Y-m-d', strtotime($expiryStr)) : null;

                // Find by name since SKU is hidden in export but used internally
                $product = Product::on('tenant')->where('name', $name)->first();
                $sku = $product ? $product->sku : 'SKU-' . strtoupper(Str::random(6));

                // 1. Category
                $category = Category::firstOrCreate(['name' => $categoryName]);

                // 2. Product Base
                $product = Product::updateOrCreate(
                    ['name' => $name],
                    [
                        'sku' => $sku,
                        'barcode' => $barcode,
                        'category_id' => $category->id,
                        'stock' => $stock,
                        'min_stock' => $minStock,
                        'expired_date' => $expiredDate,
                        'is_active' => true,
                    ]
                );

                // 3. Units Sync (Dynamic Loop)
                $product->units()->delete();

                foreach ($unitSuffixes as $num) {
                    $uName = $row[$cols["SATUAN$num"] ?? -1] ?? null;
                    if (empty($uName) || $uName == '-')
                        continue;

                    $conv = floatval(str_replace([',', ' '], '', $row[$cols["KONVERSI$num"] ?? -1] ?? 1));
                    $bp = floatval(str_replace([',', ' '], '', $row[$cols["HARGABELI$num"] ?? -1] ?? 0));
                    $sp = floatval(str_replace([',', ' '], '', $row[$cols["HARGAJUAL$num"] ?? -1] ?? 0));

                    $unitModel = Unit::firstOrCreate(['name' => $uName]);

                    $product->units()->create([
                        'unit_id' => $unitModel->id,
                        'is_base_unit' => ($conv == 1),
                        'conversion_qty' => $conv,
                        'buy_price' => $bp,
                        'sell_price' => $sp,
                    ]);

                    if ($conv == 1) {
                        $product->update([
                            'buy_price' => $bp,
                            'sell_price' => $sp,
                        ]);
                    }
                }

                $count++;
            }
            DB::connection('tenant')->commit();

            return response()->json(['success' => true, 'message' => "Imported $count products with dynamic multi-unit support successfully."]);

        } catch (\Exception $e) {
            DB::connection('tenant')->rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Import failed on row ' . ($count + 1) . ': ' . $e->getMessage()
            ], 500);
        }
    }

    public function exportExcel()
    {
        Log::info('ProductController: exportExcel called');
        return Excel::download(new ProductsExport('tenant'), 'products_' . date('Y-m-d') . '.xlsx');
    }

    public function exportPdf()
    {
        Log::info('ProductController: exportPdf called');
        $products = Product::on('tenant')->with(['category', 'units.unit'])->get();
        $pdf = Pdf::loadView('exports.products_pdf', compact('products'));
        return $pdf->download('products_' . date('Y-m-d') . '.pdf');
    }

    public function downloadTemplate()
    {
        Log::info('ProductController: downloadTemplate called');
        return Excel::download(new ProductsTemplateExport, 'template_import_produk.xlsx');
    }
}
