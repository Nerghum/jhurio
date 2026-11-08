<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\Coupon;
use App\Models\DeliveryMethod;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderLog;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\TrackingEvent;
use App\Models\User;
use App\Services\AffiliateService;
use App\Services\FacebookCAPIService;
use App\Services\FirebasePushService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Order::with(['items.product.thumbnail', 'user'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('search')) {
            $s = $request->input('search');
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                    ->orWhere('phone', 'like', "%{$s}%")
                    ->orWhere('id', $s);
            });
        }

        $limit = (int) $request->input('limit', 20);
        $orders = $query->paginate($limit);

        $counts = Order::selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return response()->json([
            'total' => $orders->total(),
            'counts' => $counts,
            'orders' => $orders->items(),
            'current_page' => $orders->currentPage(),
            'last_page' => $orders->lastPage(),
        ]);
    }

    public function deliveryMethods(): JsonResponse
    {
        $methods = DeliveryMethod::where('is_active', true)->orderBy('cost')->get();

        return response()->json([
            'total' => $methods->count(),
            'delivery_methods' => $methods,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:25',
            'address' => 'required|string',
            'district' => 'required|string',
            'delivery_method_id' => 'required|exists:delivery_methods,id',
            'coupon_code' => 'nullable|string',
            'product_id' => 'nullable|required_without:items|exists:products,id',
            'variation_id' => 'nullable|exists:product_variations,id',
            'quantity' => 'nullable|integer|min:1',
            'bundle_info' => 'nullable|array',
            'order_bump_product_id' => 'nullable|exists:products,id',
            'items' => 'nullable|required_without:product_id|array|min:1',
            'items.*.product_id' => 'required_with:items|exists:products,id',
            'items.*.quantity' => 'required_with:items|integer|min:1',
            'items.*.variation_id' => 'nullable|exists:product_variations,id',
            'visitor_id' => 'nullable|string',
            'session_id' => 'nullable|string',
            'affiliate_code' => 'nullable|string',
        ]);

        $deliveryMethod = DeliveryMethod::findOrFail($validated['delivery_method_id']);
        $deliveryCost = (float) $deliveryMethod->cost;

        $orderItemsData = [];
        $subTotal = 0.0;
        $categoryPixels = [];
        $totalItemsCount = 0;
        $contentIds = [];

        if (! empty($validated['items'])) {
            foreach ($validated['items'] as $item) {
                $product = Product::with('categories')->find($item['product_id']);
                if (! $product) {
                    continue;
                }

                $qty = (int) $item['quantity'];
                $price = (float) ($product->sale_price ?? $product->price);
                $variationName = null;

                if (! empty($item['variation_id'])) {
                    $variation = ProductVariation::where('product_id', $product->id)->find($item['variation_id']);
                    if ($variation) {
                        $variationName = $variation->name;
                        if ($variation->price) {
                            $price = (float) $variation->price;
                        }
                    }
                }

                $orderItemsData[] = [
                    'product' => $product,
                    'product_id' => $product->id,
                    'variation_id' => $item['variation_id'] ?? null,
                    'product_name' => $product->name,
                    'variation_name' => $variationName,
                    'price' => $price,
                    'purchase_price' => (float) ($product->purchase_price ?? 0),
                    'quantity' => $qty,
                ];

                $subTotal += ($price * $qty);
                $totalItemsCount += $qty;
                $contentIds[] = $product->id;

                foreach ($product->categories as $category) {
                    if ($category->fb_pixel_id) {
                        $categoryPixels[] = [
                            'pixel_id' => $category->fb_pixel_id,
                            'capi_token' => $category->fb_capi_token,
                        ];
                    }
                }
            }
        } else {
            $product = Product::with('categories')->findOrFail($validated['product_id']);
            $qty = (int) ($validated['quantity'] ?? 1);
            $price = (float) ($product->sale_price ?? $product->price);
            $variationName = null;

            if (! empty($validated['variation_id'])) {
                $variation = ProductVariation::where('product_id', $product->id)->find($validated['variation_id']);
                if ($variation) {
                    $variationName = $variation->name;
                    if ($variation->price) {
                        $price = (float) $variation->price;
                    }
                }
            }

            $bundleInfo = $validated['bundle_info'] ?? null;
            $isBundle = ! empty($bundleInfo['is_bundle']);
            if ($isBundle) {
                $matchedTier = null;
                if (! empty($product->bundle_settings['enabled']) && ! empty($product->bundle_settings['tiers'])) {
                    if (isset($bundleInfo['tier_index']) && isset($product->bundle_settings['tiers'][$bundleInfo['tier_index']])) {
                        $matchedTier = $product->bundle_settings['tiers'][$bundleInfo['tier_index']];
                    } else {
                        foreach ($product->bundle_settings['tiers'] as $t) {
                            if (($t['quantity'] ?? 0) == $qty) {
                                $matchedTier = $t;
                                break;
                            }
                        }
                    }
                }

                if ($matchedTier) {
                    $tierQty = (int) $matchedTier['quantity'];
                    $baseTotal = $tierQty * $price;
                    $tierDiscount = ($matchedTier['discount_type'] === 'percentage')
                        ? ($baseTotal * (float) $matchedTier['discount_value']) / 100
                        : (float) $matchedTier['discount_value'];
                    $tierDiscount = min($tierDiscount, $baseTotal);
                    $price = ($baseTotal - $tierDiscount) / max(1, $tierQty);
                } elseif (isset($bundleInfo['unit_price'])) {
                    $price = (float) $bundleInfo['unit_price'];
                }
            }

            $orderItemsData[] = [
                'product' => $product,
                'product_id' => $product->id,
                'variation_id' => $validated['variation_id'] ?? null,
                'product_name' => $product->name,
                'variation_name' => $variationName,
                'price' => $price,
                'purchase_price' => (float) ($product->purchase_price ?? 0),
                'quantity' => $qty,
            ];

            $subTotal += ($price * $qty);
            $totalItemsCount += $qty;
            $contentIds[] = $product->id;

            foreach ($product->categories as $category) {
                if ($category->fb_pixel_id) {
                    $categoryPixels[] = [
                        'pixel_id' => $category->fb_pixel_id,
                        'capi_token' => $category->fb_capi_token,
                    ];
                }
            }
        }

        $bumpProduct = null;
        $bumpPrice = 0.0;
        if (! empty($validated['order_bump_product_id'])) {
            $bumpProduct = Product::find($validated['order_bump_product_id']);
            if ($bumpProduct) {
                $bumpPrice = (float) ($bumpProduct->sale_price ?? $bumpProduct->price);
            }
        }

        $discountAmount = 0.0;
        $appliedCoupon = null;
        if (! empty($validated['coupon_code'])) {
            $coupon = Coupon::where('code', strtoupper($validated['coupon_code']))
                ->where('is_active', true)
                ->first();

            if ($coupon && (! $coupon->expires_at || ! $coupon->expires_at->isPast()) && (! $coupon->usage_limit || $coupon->times_used < $coupon->usage_limit) && (! $coupon->min_order_value || $subTotal >= $coupon->min_order_value)) {
                $discountAmount = ($coupon->type === 'fixed')
                    ? (float) $coupon->value
                    : ($subTotal * (float) $coupon->value) / 100;

                $discountAmount = min($discountAmount, $subTotal);
                $appliedCoupon = $coupon->code;
                $coupon->increment('times_used');
            }
        }

        $totalAmount = max(0, $subTotal - $discountAmount + $deliveryCost + $bumpPrice);
        $fullAddress = trim($validated['address'].', '.$validated['district']);

        $user = User::where('phone', $validated['phone'])->first();
        if (! $user) {
            $user = User::create([
                'name' => $validated['name'],
                'phone' => $validated['phone'],
                'role' => 'customer',
                'password' => Hash::make(Str::random(16)),
            ]);
        } elseif ($user->name === 'Customer' || empty($user->name)) {
            $user->update(['name' => $validated['name']]);
        }

        Address::firstOrCreate([
            'user_id' => $user->id,
            'address' => $validated['address'],
            'district' => $validated['district'],
        ], [
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'label' => 'Home',
            'is_default' => Address::where('user_id', $user->id)->count() === 0,
        ]);

        $visitorId = $validated['visitor_id'] ?? $request->header('X-Visitor-Id');
        $sessionId = $validated['session_id'] ?? $request->header('X-Session-Id');
        $clientIp = $request->header('CF-Connecting-IP')
            ?? ($request->header('X-Forwarded-For') ? trim(explode(',', $request->header('X-Forwarded-For'))[0]) : null)
            ?? $request->ip();
        $userAgent = $request->userAgent() ?? 'Android App';

        $order = Order::create([
            'tracking_visitor_id' => $visitorId,
            'tracking_session_id' => $sessionId,
            'ip_address' => $clientIp,
            'user_agent' => $userAgent,
            'user_id' => $user->id,
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'shipping_address' => $fullAddress,
            'total_amount' => $totalAmount,
            'discount_amount' => $discountAmount,
            'coupon_code' => $appliedCoupon,
            'delivery_method_id' => $validated['delivery_method_id'],
            'delivery_cost' => $deliveryCost,
            'status' => 'pending',
        ]);

        foreach ($orderItemsData as $itemData) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $itemData['product_id'],
                'variation_id' => $itemData['variation_id'],
                'product_name' => $itemData['product_name'],
                'variation_name' => $itemData['variation_name'],
                'price' => $itemData['price'],
                'purchase_price' => $itemData['purchase_price'],
                'quantity' => $itemData['quantity'],
            ]);

            if (isset($itemData['product']) && $itemData['product']->stock_quantity !== null) {
                $itemData['product']->decrement('stock_quantity', $itemData['quantity']);
            }
        }

        if ($bumpProduct) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $bumpProduct->id,
                'variation_id' => null,
                'product_name' => $bumpProduct->name.' (Order Bump)',
                'variation_name' => null,
                'price' => $bumpPrice,
                'purchase_price' => (float) ($bumpProduct->purchase_price ?? 0),
                'quantity' => 1,
            ]);
        }

        OrderLog::create([
            'order_id' => $order->id,
            'status' => 'pending',
            'note' => 'Order placed via API / Mobile App',
        ]);

        // Record Affiliate Referral
        $affiliateCode = $validated['affiliate_code'] ?? $request->input('ref') ?? $request->input('affiliate_code');
        if ($affiliateCode) {
            AffiliateService::recordReferral($order, $affiliateCode);
        }

        if ($visitorId) {
            try {
                TrackingEvent::create([
                    'tracking_visitor_id' => $visitorId,
                    'tracking_session_id' => $sessionId,
                    'user_id' => $user->id,
                    'event_type' => 'purchase',
                    'page_url' => $request->fullUrl(),
                    'metadata' => [
                        'order_id' => $order->id,
                        'amount' => $order->total_amount,
                        'source' => 'android_app',
                    ],
                ]);
            } catch (\Throwable $e) {
            }
        }

        try {
            FacebookCAPIService::sendEvent('Purchase', [
                'content_name' => count($orderItemsData) === 1 ? $orderItemsData[0]['product_name'] : 'Android App Order',
                'content_ids' => $contentIds,
                'content_type' => 'product',
                'value' => $totalAmount,
                'currency' => 'BDT',
                'num_items' => $totalItemsCount,
            ], [
                'fn' => hash('sha256', $validated['name']),
                'ph' => hash('sha256', $validated['phone']),
                'ct' => hash('sha256', $validated['district']),
                'country' => hash('sha256', 'bd'),
            ], $order->id, $categoryPixels);
        } catch (\Throwable $e) {
        }

        try {
            $admins = User::whereNotNull('fcm_token')->get();
            foreach ($admins as $admin) {
                FirebasePushService::send(
                    $admin->fcm_token,
                    'New Mobile App Order! 📱🛍️',
                    "Order #{$order->id} from {$validated['name']} for ৳{$totalAmount}",
                    ['order_id' => (string) $order->id, 'url' => route('admin.orders.show', $order->id)]
                );
            }
        } catch (\Throwable $e) {
        }

        return response()->json([
            'success' => true,
            'message' => 'Order placed successfully',
            'order' => $order->load([
                'items.product.thumbnail',
                'items.variation',
                'deliveryMethod',
                'logs',
            ]),
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $order = Order::with([
            'items.product.thumbnail',
            'items.variation',
            'user',
            'logs',
            'deliveryMethod',
        ])->find($id);

        if (! $order) {
            return response()->json(['error' => 'Order not found'], 404);
        }

        return response()->json([
            'order' => $order,
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $order = Order::find($id);

        if (! $order) {
            return response()->json(['error' => 'Order not found'], 404);
        }

        $validated = $request->validate([
            'status' => 'nullable|in:pending,processing,shipped,delivered,completed,cancelled',
            'note' => 'nullable|string',
            'name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'shipping_address' => 'nullable|string',
            'subtotal' => 'nullable|numeric|min:0',
            'shipping_cost' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'total' => 'nullable|numeric|min:0',
            'delivery_method_id' => 'nullable|exists:delivery_methods,id',
        ]);

        $oldStatus = $order->status;
        $orderData = array_filter($validated, fn ($k) => $k !== 'note', ARRAY_FILTER_USE_KEY);

        $order->update(array_filter($orderData, fn ($v) => ! is_null($v)));

        // Create log if status changed or note provided
        if ((isset($validated['status']) && $validated['status'] !== $oldStatus) || ! empty($validated['note'])) {
            if (isset($validated['status']) && $validated['status'] !== $oldStatus) {
                AffiliateService::handleOrderStatusChanged($order, $oldStatus, $validated['status']);
            }

            OrderLog::create([
                'order_id' => $order->id,
                'status' => $validated['status'] ?? $order->status,
                'note' => $validated['note'] ?? 'Status updated via API to '.($validated['status'] ?? $order->status),
            ]);
        }

        return response()->json([
            'message' => 'Order updated successfully',
            'order' => $order->fresh()->load('items.product.thumbnail', 'logs'),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $order = Order::find($id);

        if (! $order) {
            return response()->json(['error' => 'Order not found'], 404);
        }

        $order->delete();

        return response()->json([
            'message' => 'Order deleted successfully',
        ]);
    }
}
