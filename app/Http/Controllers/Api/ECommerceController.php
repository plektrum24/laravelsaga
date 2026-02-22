<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\WebCart;
use App\Models\WebCartItem;
use App\Models\WebOrder;
use App\Models\WebOrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ECommerceController extends Controller
{
    /**
     * Get product catalog
     * GET /api/web/products
     */
    public function products(Request $request)
    {
        $query = Product::where('is_active', true)
            ->where('tenant_id', auth()->user()->tenant_id ?? 1);

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        // Category filter
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // In stock only
        if ($request->has('in_stock') && $request->in_stock === 'true') {
            $query->where('stock', '>', 0);
        }

        // Sort
        $sort = $request->get('sort', 'name_asc');
        switch ($sort) {
            case 'price_asc':
                $query->orderBy('sell_price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('sell_price', 'desc');
                break;
            case 'newest':
                $query->latest();
                break;
        }

        $products = $query->with('category')->paginate($request->get('limit', 20));

        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }

    /**
     * Get product detail
     * GET /api/web/products/{id}
     */
    public function productDetail($id)
    {
        $product = Product::where('is_active', true)
            ->where('tenant_id', auth()->user()->tenant_id ?? 1)
            ->with(['category', 'units.unit'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $product
        ]);
    }

    /**
     * Get categories
     * GET /api/web/categories
     */
    public function categories()
    {
        $categories = Category::where('tenant_id', auth()->user()->tenant_id ?? 1)
            ->withCount('products')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $categories
        ]);
    }

    /**
     * Get cart
     * GET /api/web/cart
     */
    public function getCart(Request $request)
    {
        $tenantId = auth()->user()->tenant_id ?? 1;
        $customerId = $request->get('customer_id');
        $sessionId = $request->get('session_id');

        $cart = WebCart::getOrCreate($tenantId, $customerId, $sessionId);
        $cart->load(['items.product']);

        return response()->json([
            'success' => true,
            'data' => [
                'cart' => $cart,
                'total' => $cart->total,
                'item_count' => $cart->item_count,
            ]
        ]);
    }

    /**
     * Add to cart
     * POST /api/web/cart/add
     */
    public function addToCart(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'qty' => 'required|integer|min:1',
            'customer_id' => 'nullable|exists:customers,id',
            'session_id' => 'nullable|string',
        ]);

        $tenantId = auth()->user()->tenant_id ?? 1;
        $cart = WebCart::getOrCreate($tenantId, $validated['customer_id'] ?? null, $validated['session_id'] ?? null);

        $product = Product::find($validated['product_id']);
        
        $item = $cart->addItem(
            $validated['product_id'],
            $validated['qty'],
            $product->sell_price
        );

        return response()->json([
            'success' => true,
            'message' => 'Added to cart',
            'data' => [
                'cart' => $cart->load('items.product'),
                'total' => $cart->total,
                'item_count' => $cart->item_count,
            ]
        ]);
    }

    /**
     * Update cart item
     * PUT /api/web/cart/items/{id}
     */
    public function updateCartItem(Request $request, $id)
    {
        $validated = $request->validate([
            'qty' => 'required|integer|min:0',
        ]);

        $item = WebCartItem::findOrFail($id);
        $item->updateQuantity($validated['qty']);

        $cart = $item->cart;
        $cart->load('items.product');

        return response()->json([
            'success' => true,
            'message' => 'Cart updated',
            'data' => [
                'cart' => $cart,
                'total' => $cart->total,
                'item_count' => $cart->item_count,
            ]
        ]);
    }

    /**
     * Remove from cart
     * DELETE /api/web/cart/items/{id}
     */
    public function removeFromCart($id)
    {
        $item = WebCartItem::findOrFail($id);
        $cart = $item->cart;
        
        $item->delete();

        $cart->load('items.product');

        return response()->json([
            'success' => true,
            'message' => 'Removed from cart',
            'data' => [
                'cart' => $cart,
                'total' => $cart->total,
                'item_count' => $cart->item_count,
            ]
        ]);
    }

    /**
     * Clear cart
     * DELETE /api/web/cart/clear
     */
    public function clearCart(Request $request)
    {
        $tenantId = auth()->user()->tenant_id ?? 1;
        $customerId = $request->get('customer_id');
        $sessionId = $request->get('session_id');

        $cart = WebCart::getOrCreate($tenantId, $customerId, $sessionId);
        $cart->clear();

        return response()->json([
            'success' => true,
            'message' => 'Cart cleared'
        ]);
    }

    /**
     * Checkout
     * POST /api/web/checkout
     */
    public function checkout(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'session_id' => 'nullable|string',
            'shipping_address' => 'required|array',
            'billing_address' => 'required|array',
            'payment_method' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        $tenantId = auth()->user()->tenant_id ?? 1;
        $cart = WebCart::getOrCreate($tenantId, $validated['customer_id'] ?? null, $validated['session_id'] ?? null);
        $cart->load('items.product');

        if ($cart->items->count() === 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cart is empty'
            ], 400);
        }

        DB::connection('tenant')->beginTransaction();

        try {
            // Create order
            $order = WebOrder::create([
                'tenant_id' => $tenantId,
                'customer_id' => $validated['customer_id'] ?? null,
                'order_number' => WebOrder::generateOrderNumber(),
                'status' => WebOrder::STATUS_PENDING,
                'payment_method' => $validated['payment_method'],
                'payment_status' => 'pending',
                'shipping_address' => $validated['shipping_address'],
                'billing_address' => $validated['billing_address'],
                'notes' => $validated['notes'] ?? null,
                'customer_name' => $request->get('customer_name'),
                'customer_email' => $request->get('customer_email'),
                'customer_phone' => $request->get('customer_phone'),
            ]);

            // Create order items
            foreach ($cart->items as $item) {
                $orderItem = WebOrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'qty' => $item->qty,
                    'price' => $item->price,
                    'subtotal' => $item->qty * $item->price,
                ]);

                // Deduct stock
                $item->product->decrement('stock', $item->qty);
            }

            // Update order totals
            $order->updateTotals();

            // Clear cart
            $cart->clear();

            DB::connection('tenant')->commit();

            return response()->json([
                'success' => true,
                'message' => 'Order placed successfully',
                'data' => [
                    'order' => $order,
                    'order_number' => $order->order_number,
                ]
            ]);

        } catch (\Exception $e) {
            DB::connection('tenant')->rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get order by number
     * GET /api/web/orders/{orderNumber}
     */
    public function getOrder($orderNumber)
    {
        $order = WebOrder::where('order_number', $orderNumber)
            ->where('tenant_id', auth()->user()->tenant_id ?? 1)
            ->with(['items.product', 'customer'])
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $order
        ]);
    }
}
