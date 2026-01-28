<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function assets(Request $request)
    {
        // Calculate Total Asset Value (Stock * Buy Price)
        // Note: Ideally buy_price is dynamic (average cost), but here we use current buy_price

        $query = Product::query();

        if ($request->has('branch_id') && $request->branch_id) {
            $query->where('branch_id', $request->branch_id);
        }

        $totalAsset = $query->sum(DB::raw('stock * buy_price'));

        return response()->json([
            'success' => true,
            'data' => [
                'totalAsset' => $totalAsset
            ]
        ]);
    }
}
