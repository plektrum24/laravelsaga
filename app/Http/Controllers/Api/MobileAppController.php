<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Category;
use App\Models\WebOrder;
use App\Models\WebCart;
use App\Models\LoyaltySetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class MobileAppController extends Controller
{
    /**
     * Mobile login
     * POST /api/mobile/login
     */
    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (!Auth::attempt($validated)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials'
            ], 401);
        }

        $user = Auth::user();
        $token = $user->createToken('mobile-app')->plainTextToken;

        // Get or create customer
        $customer = Customer::where('email', $user->email)->first();

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user,
                'token' => $token,
                'customer' => $customer,
                'token_type' => 'Bearer',
            ]
        ]);
    }

    /**
     * Mobile register
     * POST /api/mobile/register
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'phone' => 'nullable|string',
        ]);

        $user = \App\Models\User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'phone' => $validated['phone'] ?? null,
        ]);

        // Create customer record
        $customer = Customer::create([
            'tenant_id' => 1, // Default tenant, should be configured
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
        ]);

        $token = $user->createToken('mobile-app')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Registration successful',
            'data' => [
                'user' => $user,
                'token' => $token,
                'customer' => $customer,
                'token_type' => 'Bearer',
            ]
        ], 201);
    }

    /**
     * Mobile logout
     * POST /api/mobile/logout
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully'
        ]);
    }

    /**
     * Get mobile home data
     * GET /api/mobile/home
     */
    public function home()
    {
        $tenantId = auth()->user()->tenant_id ?? 1;

        // Featured products
        $featuredProducts = Product::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->where('stock', '>', 0)
            ->inRandomOrder()
            ->limit(10)
            ->get();

        // Categories
        $categories = Category::where('tenant_id', $tenantId)
            ->withCount('products')
            ->get();

        // Banners (placeholder - would need banners table)
        $banners = [
            ['title' => 'Welcome', 'image' => null, 'action' => 'products'],
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'featured_products' => $featuredProducts,
                'categories' => $categories,
                'banners' => $banners,
            ]
        ]);
    }

    /**
     * Get product detail for mobile
     * GET /api/mobile/products/{id}
     */
    public function productDetail($id)
    {
        $product = Product::where('is_active', true)
            ->where('tenant_id', auth()->user()->tenant_id ?? 1)
            ->with(['category', 'units.unit'])
            ->findOrFail($id);

        // Related products
        $relatedProducts = Product::where('tenant_id', auth()->user()->tenant_id ?? 1)
            ->where('is_active', true)
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->limit(4)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'product' => $product,
                'related_products' => $relatedProducts,
            ]
        ]);
    }

    /**
     * Mobile product search
     * GET /api/mobile/products
     */
    public function products(Request $request)
    {
        $query = Product::where('is_active', true)
            ->where('tenant_id', auth()->user()->tenant_id ?? 1);

        // Search
        if ($request->has('q')) {
            $search = $request->q;
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

        // Price range
        if ($request->has('min_price')) {
            $query->where('sell_price', '>=', $request->min_price);
        }
        if ($request->has('max_price')) {
            $query->where('sell_price', '<=', $request->max_price);
        }

        // Sort
        $sort = $request->get('sort', 'relevance');
        switch ($sort) {
            case 'price_low':
                $query->orderBy('sell_price', 'asc');
                break;
            case 'price_high':
                $query->orderBy('sell_price', 'desc');
                break;
            case 'newest':
                $query->latest();
                break;
            case 'popular':
                // Would need popularity metric
                $query->orderBy('stock', 'desc');
                break;
        }

        $products = $query->paginate($request->get('limit', 20));

        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }

    /**
     * Get cart summary
     * GET /api/mobile/cart/summary
     */
    public function cartSummary(Request $request)
    {
        $tenantId = auth()->user()->tenant_id ?? 1;
        $customerId = auth()->user()->customer_id ?? null;

        $cart = WebCart::getOrCreate($tenantId, $customerId);

        return response()->json([
            'success' => true,
            'data' => [
                'item_count' => $cart->item_count,
                'total' => $cart->total,
            ]
        ]);
    }

    /**
     * Get loyalty summary
     * GET /api/mobile/loyalty/summary
     */
    public function loyaltySummary(Request $request)
    {
        $customerId = auth()->user()->customer_id ?? null;

        if (!$customerId) {
            return response()->json([
                'success' => false,
                'message' => 'Customer not found'
            ], 404);
        }

        $customer = Customer::findOrFail($customerId);
        
        // Get points balance
        $pointsData = \App\Models\CustomerPoint::getBalanceWithBreakdown($customerId);
        
        // Get tier
        $tier = $customer->currentTier;

        return response()->json([
            'success' => true,
            'data' => [
                'points_balance' => $pointsData['balance'],
                'total_earned' => $pointsData['total_earned'],
                'expiring_soon' => $pointsData['expiring_soon'],
                'tier' => $tier?->tier,
                'tier_name' => $tier?->tier->name,
                'tier_benefits' => $tier?->getBenefits(),
            ]
        ]);
    }

    /**
     * Get QR membership code
     * GET /api/mobile/loyalty/qr-code
     */
    public function qrCode()
    {
        $customerId = auth()->user()->customer_id ?? null;

        if (!$customerId) {
            return response()->json([
                'success' => false,
                'message' => 'Customer not found'
            ], 404);
        }

        $customer = Customer::findOrFail($customerId);
        
        // Generate QR code data (would need QR library)
        $qrData = base64_encode(json_encode([
            'customer_id' => $customer->id,
            'email' => $customer->email,
            'phone' => $customer->phone,
        ]));

        return response()->json([
            'success' => true,
            'data' => [
                'qr_code' => $qrData,
                'customer' => [
                    'name' => $customer->name,
                    'email' => $customer->email,
                    'phone' => $customer->phone,
                ],
            ]
        ]);
    }

    /**
     * Get order history
     * GET /api/mobile/orders
     */
    public function orders(Request $request)
    {
        $customerId = auth()->user()->customer_id ?? null;

        if (!$customerId) {
            return response()->json([
                'success' => false,
                'message' => 'Customer not found'
            ], 404);
        }

        $query = WebOrder::where('customer_id', $customerId)
            ->with('items.product')
            ->latest();

        $orders = $query->paginate($request->get('limit', 20));

        return response()->json([
            'success' => true,
            'data' => $orders
        ]);
    }

    /**
     * Get order detail
     * GET /api/mobile/orders/{orderNumber}
     */
    public function orderDetail($orderNumber)
    {
        $order = WebOrder::where('order_number', $orderNumber)
            ->where('customer_id', auth()->user()->customer_id ?? null)
            ->with('items.product')
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $order
        ]);
    }

    /**
     * Get app settings
     * GET /api/mobile/settings
     */
    public function settings()
    {
        $loyaltySettings = LoyaltySetting::forTenant(auth()->user()->tenant_id ?? 1);

        return response()->json([
            'success' => true,
            'data' => [
                'loyalty' => [
                    'enabled' => $loyaltySettings?->enabled ?? false,
                    'earn_rate' => $loyaltySettings?->earn_rate ?? 10000,
                    'point_value' => $loyaltySettings?->point_value ?? 100,
                ],
                'payment_methods' => [
                    ['code' => 'cod', 'name' => 'Cash on Delivery', 'enabled' => true],
                    ['code' => 'bank_transfer', 'name' => 'Bank Transfer', 'enabled' => true],
                ],
                'app_version' => '1.0.0',
                'min_app_version' => '1.0.0',
            ]
        ]);
    }

    /**
     * Scan barcode
     * POST /api/mobile/scan
     */
    public function scan(Request $request)
    {
        $validated = $request->validate([
            'barcode' => 'required|string',
        ]);

        $product = Product::where('barcode', $validated['barcode'])
            ->orWhere('sku', $validated['barcode'])
            ->where('is_active', true)
            ->where('tenant_id', auth()->user()->tenant_id ?? 1)
            ->first();

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $product
        ]);
    }

    /**
     * Add to cart (mobile)
     * POST /api/mobile/cart/add
     */
    public function addToCart(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'qty' => 'required|integer|min:1',
        ]);

        $tenantId = auth()->user()->tenant_id ?? 1;
        $customerId = auth()->user()->customer_id ?? null;

        $cart = WebCart::getOrCreate($tenantId, $customerId);
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
                'item_count' => $cart->item_count,
                'total' => $cart->total,
                'item' => $item,
            ]
        ]);
    }

    /**
     * Get full cart details
     * GET /api/mobile/cart
     */
    public function cart(Request $request)
    {
        $tenantId = auth()->user()->tenant_id ?? 1;
        $customerId = auth()->user()->customer_id ?? null;

        $cart = WebCart::getOrCreate($tenantId, $customerId);
        $cart->load(['items.product.category']);

        return response()->json([
            'success' => true,
            'data' => [
                'cart' => $cart,
                'items' => $cart->items,
                'total' => $cart->total,
                'item_count' => $cart->item_count,
            ]
        ]);
    }

    /**
     * Update cart item quantity
     * PUT /api/mobile/cart/items/{id}
     */
    public function updateCartItem(Request $request, $id)
    {
        $validated = $request->validate([
            'qty' => 'required|integer|min:1',
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
     * DELETE /api/mobile/cart/items/{id}
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
     * DELETE /api/mobile/cart/clear
     */
    public function clearCart()
    {
        $tenantId = auth()->user()->tenant_id ?? 1;
        $customerId = auth()->user()->customer_id ?? null;

        $cart = WebCart::getOrCreate($tenantId, $customerId);
        $cart->clear();

        return response()->json([
            'success' => true,
            'message' => 'Cart cleared'
        ]);
    }

    /**
     * Mobile checkout
     * POST /api/mobile/checkout
     */
    public function checkout(Request $request)
    {
        $validated = $request->validate([
            'shipping_address' => 'required|array',
            'billing_address' => 'required|array',
            'payment_method' => 'required|string',
            'notes' => 'nullable|string',
            'use_points' => 'boolean',
        ]);

        $tenantId = auth()->user()->tenant_id ?? 1;
        $customerId = auth()->user()->customer_id ?? null;

        $cart = WebCart::getOrCreate($tenantId, $customerId);
        $cart->load('items.product');

        if ($cart->items->count() === 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cart is empty'
            ], 400);
        }

        $user = auth()->user();

        DB::connection('tenant')->beginTransaction();

        try {
            // Create order
            $order = WebOrder::create([
                'tenant_id' => $tenantId,
                'customer_id' => $customerId,
                'order_number' => WebOrder::generateOrderNumber(),
                'status' => WebOrder::STATUS_PENDING,
                'payment_method' => $validated['payment_method'],
                'payment_status' => 'pending',
                'shipping_address' => $validated['shipping_address'],
                'billing_address' => $validated['billing_address'],
                'notes' => $validated['notes'] ?? null,
                'customer_name' => $user->name,
                'customer_email' => $user->email,
                'customer_phone' => $user->phone ?? $user->customer->phone ?? null,
            ]);

            // Create order items
            foreach ($cart->items as $item) {
                WebOrderItem::create([
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
     * Redeem loyalty points
     * POST /api/mobile/loyalty/redeem
     */
    public function redeemPoints(Request $request)
    {
        $validated = $request->validate([
            'points' => 'required|integer|min:1',
        ]);

        $customerId = auth()->user()->customer_id ?? null;

        if (!$customerId) {
            return response()->json([
                'success' => false,
                'message' => 'Customer not found'
            ], 404);
        }

        $customer = Customer::findOrFail($customerId);
        $pointsData = \App\Models\CustomerPoint::getBalanceWithBreakdown($customerId);

        if ($validated['points'] > $pointsData['balance']) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient points'
            ], 400);
        }

        $loyaltySettings = LoyaltySetting::forTenant(auth()->user()->tenant_id ?? 1);

        if ($validated['points'] < $loyaltySettings->min_redemption_points) {
            return response()->json([
                'success' => false,
                'message' => 'Minimum redemption is ' . $loyaltySettings->min_redemption_points . ' points'
            ], 400);
        }

        DB::connection('tenant')->beginTransaction();

        try {
            $newBalance = $pointsData['balance'] - $validated['points'];
            $discountValue = $validated['points'] * $loyaltySettings->point_value;

            \App\Models\CustomerPoint::create([
                'customer_id' => $customerId,
                'tenant_id' => auth()->user()->tenant_id ?? 1,
                'points' => -$validated['points'],
                'type' => \App\Models\CustomerPoint::TYPE_REDEEM,
                'reference_type' => 'mobile_app',
                'reference_id' => null,
                'expiry_date' => null,
                'balance_after' => $newBalance,
                'notes' => 'Redeemed via mobile app',
            ]);

            DB::connection('tenant')->commit();

            return response()->json([
                'success' => true,
                'message' => 'Points redeemed successfully',
                'data' => [
                    'points_redeemed' => $validated['points'],
                    'discount_value' => $discountValue,
                    'new_balance' => $newBalance,
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
     * Get loyalty rewards catalog
     * GET /api/mobile/loyalty/rewards
     */
    public function rewards()
    {
        $rewards = \App\Models\Reward::where('tenant_id', auth()->user()->tenant_id ?? 1)
            ->active()
            ->available()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $rewards
        ]);
    }

    /**
     * Get nearby stores
     * GET /api/mobile/stores/nearby?lat=-6.2088&lng=106.8456&radius=10
     */
    public function nearbyStores(Request $request)
    {
        $validated = $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'radius' => 'numeric|min:1|max:100',
        ]);

        $stores = \App\Models\Store::where('tenant_id', auth()->user()->tenant_id ?? 1)
            ->active()
            ->near($validated['latitude'], $validated['longitude'], $validated['radius'] ?? 10)
            ->get()
            ->map(function ($store) use ($validated) {
                return [
                    'id' => $store->id,
                    'name' => $store->name,
                    'address' => $store->address,
                    'city' => $store->city,
                    'phone' => $store->phone,
                    'latitude' => $store->latitude,
                    'longitude' => $store->longitude,
                    'distance' => round($store->getDistanceFromAttribute($validated['latitude'], $validated['longitude']), 2),
                    'opening_hours' => $store->opening_hours,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $stores
        ]);
    }

    /**
     * Get all stores
     * GET /api/mobile/stores
     */
    public function stores()
    {
        $stores = \App\Models\Store::where('tenant_id', auth()->user()->tenant_id ?? 1)
            ->active()
            ->get()
            ->map(function ($store) {
                return [
                    'id' => $store->id,
                    'name' => $store->name,
                    'address' => $store->address,
                    'city' => $store->city,
                    'phone' => $store->phone,
                    'email' => $store->email,
                    'latitude' => $store->latitude,
                    'longitude' => $store->longitude,
                    'opening_hours' => $store->opening_hours,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $stores
        ]);
    }

    /**
     * Get digital receipt
     * GET /api/mobile/orders/{orderNumber}/receipt
     */
    public function receipt($orderNumber)
    {
        $order = WebOrder::where('order_number', $orderNumber)
            ->where('customer_id', auth()->user()->customer_id ?? null)
            ->with('items.product')
            ->firstOrFail();

        $receipt = [
            'order_number' => $order->order_number,
            'date' => $order->created_at->format('d M Y, H:i'),
            'items' => $order->items->map(function ($item) {
                return [
                    'name' => $item->product->name,
                    'qty' => $item->qty,
                    'price' => $item->price,
                    'subtotal' => $item->subtotal,
                ];
            }),
            'subtotal' => $order->subtotal,
            'shipping' => $order->shipping_cost,
            'tax' => $order->tax,
            'discount' => $order->discount,
            'total' => $order->total,
            'payment_method' => $order->payment_method,
            'payment_status' => $order->payment_status,
        ];

        return response()->json([
            'success' => true,
            'data' => $receipt
        ]);
    }

    /**
     * Get wishlist
     * GET /api/mobile/wishlist
     */
    public function getWishlist()
    {
        $customerId = auth()->user()->customer_id ?? null;

        if (!$customerId) {
            return response()->json([
                'success' => false,
                'message' => 'Customer not found'
            ], 404);
        }

        $customer = Customer::findOrFail($customerId);
        $wishlist = $customer->wishlist ?? [];

        // Get product details
        $products = Product::whereIn('id', $wishlist)
            ->where('is_active', true)
            ->with('category')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }

    /**
     * Add to wishlist
     * POST /api/mobile/wishlist/add
     */
    public function addToWishlist(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        $customerId = auth()->user()->customer_id ?? null;

        if (!$customerId) {
            return response()->json([
                'success' => false,
                'message' => 'Customer not found'
            ], 404);
        }

        $customer = Customer::findOrFail($customerId);
        $wishlist = $customer->wishlist ?? [];

        if (!in_array($validated['product_id'], $wishlist)) {
            $wishlist[] = $validated['product_id'];
            $customer->update(['wishlist' => $wishlist]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Added to wishlist',
            'data' => [
                'wishlist_count' => count($wishlist),
            ]
        ]);
    }

    /**
     * Remove from wishlist
     * DELETE /api/mobile/wishlist/remove/{productId}
     */
    public function removeFromWishlist($productId)
    {
        $customerId = auth()->user()->customer_id ?? null;

        if (!$customerId) {
            return response()->json([
                'success' => false,
                'message' => 'Customer not found'
            ], 404);
        }

        $customer = Customer::findOrFail($customerId);
        $wishlist = $customer->wishlist ?? [];

        $wishlist = array_diff($wishlist, [$productId]);
        $customer->update(['wishlist' => array_values($wishlist)]);

        return response()->json([
            'success' => true,
            'message' => 'Removed from wishlist',
            'data' => [
                'wishlist_count' => count($wishlist),
            ]
        ]);
    }

    /**
     * Submit product review
     * POST /api/mobile/products/{productId}/review
     */
    public function submitReview(Request $request, $productId)
    {
        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
            'order_id' => 'nullable|exists:web_orders,id',
        ]);

        $customerId = auth()->user()->customer_id ?? null;

        if (!$customerId) {
            return response()->json([
                'success' => false,
                'message' => 'Customer not found'
            ], 404);
        }

        // Create review
        $review = \App\Models\ProductReview::create([
            'product_id' => $productId,
            'customer_id' => $customerId,
            'rating' => $validated['rating'],
            'comment' => $validated['comment'] ?? null,
            'order_id' => $validated['order_id'] ?? null,
            'status' => 'pending', // pending, approved, rejected
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Review submitted successfully',
            'data' => $review
        ]);
    }

    /**
     * Get product reviews
     * GET /api/mobile/products/{productId}/reviews
     */
    public function getProductReviews($productId)
    {
        $reviews = \App\Models\ProductReview::where('product_id', $productId)
            ->where('status', 'approved')
            ->with('customer')
            ->latest()
            ->paginate(20);

        // Calculate average rating
        $averageRating = \App\Models\ProductReview::where('product_id', $productId)
            ->where('status', 'approved')
            ->avg('rating') ?? 0;

        return response()->json([
            'success' => true,
            'data' => [
                'reviews' => $reviews,
                'average_rating' => round($averageRating, 1),
                'total_reviews' => $reviews->total(),
            ]
        ]);
    }

    /**
     * Share product
     * POST /api/mobile/products/{productId}/share
     */
    public function shareProduct(Request $request, $productId)
    {
        $validated = $request->validate([
            'platform' => 'required|in:whatsapp,facebook,twitter,email,sms',
            'recipient' => 'nullable|string',
        ]);

        $product = Product::findOrFail($productId);

        // Generate share link
        $shareLink = url('/products/' . $productId);

        // Track share (for analytics)
        \App\Models\ProductShare::create([
            'product_id' => $productId,
            'customer_id' => auth()->user()->customer_id ?? null,
            'platform' => $validated['platform'],
            'shared_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Product shared successfully',
            'data' => [
                'share_link' => $shareLink,
                'product_name' => $product->name,
            ]
        ]);
    }

    /**
     * Get scan & go session
     * GET /api/mobile/scan-and-go/session
     */
    public function getScanAndGoSession()
    {
        $customerId = auth()->user()->customer_id ?? null;

        if (!$customerId) {
            return response()->json([
                'success' => false,
                'message' => 'Customer not found'
            ], 404);
        }

        // Get or create scan & go session
        $session = \App\Models\ScanAndGoSession::firstOrCreate(
            ['customer_id' => $customerId, 'status' => 'active'],
            ['status' => 'active', 'started_at' => now()]
        );

        $session->load('items.product');

        return response()->json([
            'success' => true,
            'data' => [
                'session' => $session,
                'items' => $session->items,
                'total' => $session->items->sum(function ($item) {
                    return $item->qty * $item->price;
                }),
            ]
        ]);
    }

    /**
     * Add item to scan & go session
     * POST /api/mobile/scan-and-go/scan
     */
    public function scanAndGoScan(Request $request)
    {
        $validated = $request->validate([
            'barcode' => 'required|string',
            'qty' => 'integer|min:1',
        ]);

        $product = Product::where('barcode', $validated['barcode'])
            ->orWhere('sku', $validated['barcode'])
            ->first();

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        }

        $customerId = auth()->user()->customer_id ?? null;
        $session = \App\Models\ScanAndGoSession::where('customer_id', $customerId)
            ->where('status', 'active')
            ->first();

        if (!$session) {
            $session = \App\Models\ScanAndGoSession::create([
                'customer_id' => $customerId,
                'status' => 'active',
                'started_at' => now(),
            ]);
        }

        // Add or update item in session
        $sessionItem = \App\Models\ScanAndGoItem::where('session_id', $session->id)
            ->where('product_id', $product->id)
            ->first();

        if ($sessionItem) {
            $sessionItem->qty += $validated['qty'] ?? 1;
            $sessionItem->save();
        } else {
            \App\Models\ScanAndGoItem::create([
                'session_id' => $session->id,
                'product_id' => $product->id,
                'qty' => $validated['qty'] ?? 1,
                'price' => $product->sell_price,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Product scanned successfully',
            'data' => [
                'product' => $product,
                'qty' => $validated['qty'] ?? 1,
            ]
        ]);
    }

    /**
     * Complete scan & go session (checkout)
     * POST /api/mobile/scan-and-go/checkout
     */
    public function scanAndGoCheckout(Request $request)
    {
        $customerId = auth()->user()->customer_id ?? null;

        $session = \App\Models\ScanAndGoSession::where('customer_id', $customerId)
            ->where('status', 'active')
            ->with('items.product')
            ->first();

        if (!$session || $session->items->count() === 0) {
            return response()->json([
                'success' => false,
                'message' => 'No items in session'
            ], 400);
        }

        // Convert to order
        $order = WebOrder::create([
            'tenant_id' => auth()->user()->tenant_id ?? 1,
            'customer_id' => $customerId,
            'order_number' => WebOrder::generateOrderNumber(),
            'status' => WebOrder::STATUS_CONFIRMED,
            'payment_status' => 'pending',
            'payment_method' => 'scan_and_go',
        ]);

        foreach ($session->items as $item) {
            WebOrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item->product_id,
                'qty' => $item->qty,
                'price' => $item->price,
                'subtotal' => $item->qty * $item->price,
            ]);

            // Deduct stock
            $item->product->decrement('stock', $item->qty);
        }

        $order->updateTotals();

        // Mark session as completed
        $session->update([
            'status' => 'completed',
            'completed_at' => now(),
            'order_id' => $order->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Scan & Go checkout completed',
            'data' => [
                'order' => $order,
                'order_number' => $order->order_number,
            ]
        ]);
    }
}
