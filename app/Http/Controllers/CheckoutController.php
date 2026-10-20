<?php

namespace App\Http\Controllers;

use App\Models\AbandonedCart;
use App\Models\Address;
use App\Models\Coupon;
use App\Models\DeliveryMethod;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\User;
use App\Services\FacebookCAPIService;
use App\Services\FirebasePushService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    public function express(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'required|string',
            'district' => 'required|string',
            'product_id' => 'required|exists:products,id',
            'variation_id' => 'nullable|exists:product_variations,id',
            'quantity' => 'required|integer|min:1',
            'coupon_code' => 'nullable|string',
            'delivery_method_id' => 'required|exists:delivery_methods,id',
            'bundle_info' => 'nullable|array',
        ]);

        $product = Product::findOrFail($request->product_id);
        $price = $product->sale_price ?? $product->price;
        $variationName = null;

        if ($request->variation_id) {
            $variation = ProductVariation::where('product_id', $product->id)->find($request->variation_id);
            if ($variation) {
                $variationName = $variation->name;
                if ($variation->price) {
                    $price = $variation->price;
                }
            }
        }

        $bundleInfo = $request->bundle_info;
        $isBundle = ! empty($bundleInfo['is_bundle']);
        if ($isBundle) {
            $matchedTier = null;
            if (! empty($product->bundle_settings['enabled']) && ! empty($product->bundle_settings['tiers'])) {
                if (isset($bundleInfo['tier_index']) && isset($product->bundle_settings['tiers'][$bundleInfo['tier_index']])) {
                    $matchedTier = $product->bundle_settings['tiers'][$bundleInfo['tier_index']];
                } else {
                    foreach ($product->bundle_settings['tiers'] as $t) {
                        if (($t['quantity'] ?? 0) == $request->quantity) {
                            $matchedTier = $t;
                            break;
                        }
                    }
                }
            }

            if ($matchedTier) {
                $tierQty = intval($matchedTier['quantity']);
                $baseTotal = $tierQty * $price;
                $discount = ($matchedTier['discount_type'] === 'percentage')
                    ? ($baseTotal * floatval($matchedTier['discount_value'])) / 100
                    : floatval($matchedTier['discount_value']);
                $discount = min($discount, $baseTotal);
                $price = ($baseTotal - $discount) / max(1, $tierQty);
            } elseif (isset($bundleInfo['unit_price'])) {
                $price = floatval($bundleInfo['unit_price']);
            }
        }

        // Find or create user
        $user = auth()->check() ? auth()->user() : User::where('phone', $request->phone)->first();
        if (! $user) {
            $user = User::create([
                'name' => $request->name,
                'phone' => $request->phone,
                'password' => Hash::make(Str::random(16)), // Dummy password
            ]);
        }

        // Save Address to User Account
        $addressExists = Address::where('user_id', $user->id)
            ->where('address', $request->address)
            ->where('district', $request->district)
            ->exists();

        if (! $addressExists) {
            Address::create([
                'user_id' => $user->id,
                'name' => $request->name,
                'phone' => $request->phone,
                'address' => $request->address,
                'district' => $request->district,
                'label' => 'Home',
                'is_default' => Address::where('user_id', $user->id)->count() === 0,
            ]);
        }

        // Calculate Totals
        $subTotal = $price * $request->quantity;
        $discountAmount = 0;
        $appliedCoupon = null;

        if ($request->coupon_code) {
            $coupon = Coupon::where('code', strtoupper($request->coupon_code))
                ->where('is_active', true)
                ->first();

            if ($coupon && (! $coupon->expires_at || ! $coupon->expires_at->isPast()) && (! $coupon->usage_limit || $coupon->times_used < $coupon->usage_limit) && (! $coupon->min_order_value || $subTotal >= $coupon->min_order_value)) {
                if ($coupon->type === 'fixed') {
                    $discountAmount = $coupon->value;
                } else {
                    $discountAmount = ($subTotal * $coupon->value) / 100;
                }

                if ($discountAmount > $subTotal) {
                    $discountAmount = $subTotal;
                }

                $appliedCoupon = $coupon->code;
                $coupon->increment('times_used');
            }
        }

        $deliveryMethod = DeliveryMethod::find($request->delivery_method_id);
        $deliveryCost = $deliveryMethod ? $deliveryMethod->cost : 0;

        $bumpProduct = null;
        $bumpPrice = 0;
        if ($request->has('order_bump_product_id') && $request->order_bump_product_id) {
            $bumpProduct = Product::find($request->order_bump_product_id);
            if ($bumpProduct) {
                $bumpPrice = $bumpProduct->sale_price ?? $bumpProduct->price;
            }
        }

        $totalAmount = $subTotal - $discountAmount + $deliveryCost + $bumpPrice;
        $fullAddress = $request->address.', '.$request->district;

        $order = Order::create([
            'user_id' => $user->id,
            'name' => $request->name,
            'phone' => $request->phone,
            'shipping_address' => $fullAddress,
            'total_amount' => $totalAmount,
            'discount_amount' => $discountAmount,
            'coupon_code' => $appliedCoupon,
            'delivery_method_id' => $request->delivery_method_id,
            'delivery_cost' => $deliveryCost,
            'status' => 'pending',
        ]);

        // Create Main Order Item(s)
        if ($isBundle && ! empty($bundleInfo['items']) && count($bundleInfo['items']) > 0) {
            foreach ($bundleInfo['items'] as $bItem) {
                $itemVarName = ! empty($bItem['variation_name']) ? $bItem['variation_name'] : $variationName;
                $fullVarName = trim(($itemVarName ? $itemVarName.' ' : '').'['.($bundleInfo['bundle_name'] ?? 'Bundle Deal').']');
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'variation_id' => $bItem['variation_id'] ?? $request->variation_id,
                    'product_name' => $product->name,
                    'variation_name' => $fullVarName,
                    'price' => $price,
                    'quantity' => 1,
                ]);
            }
        } else {
            $fullVarName = $variationName;
            if ($isBundle && ! empty($bundleInfo['bundle_name'])) {
                $fullVarName = trim(($variationName ? $variationName.' ' : '').'['.$bundleInfo['bundle_name'].']');
            }
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'variation_id' => $request->variation_id,
                'product_name' => $product->name,
                'variation_name' => $fullVarName,
                'price' => $price,
                'quantity' => $request->quantity,
            ]);
        }

        // Create Order Bump Item
        if ($bumpProduct) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $bumpProduct->id,
                'variation_id' => null,
                'product_name' => $bumpProduct->name.' (Order Bump)',
                'variation_name' => null,
                'price' => $bumpPrice,
                'quantity' => 1,
            ]);
        }

        $categoryPixels = [];
        foreach ($product->categories as $category) {
            if ($category->fb_pixel_id) {
                $categoryPixels[] = [
                    'pixel_id' => $category->fb_pixel_id,
                    'capi_token' => $category->fb_capi_token,
                ];
            }
        }

        FacebookCAPIService::sendEvent('Purchase', [
            'content_name' => $product->name,
            'content_ids' => [$product->id],
            'content_type' => 'product',
            'value' => $totalAmount,
            'currency' => 'BDT',
            'num_items' => $request->quantity,
        ], [
            'fn' => hash('sha256', $request->name),
            'ph' => hash('sha256', $request->phone),
            'ct' => hash('sha256', $request->district),
            'country' => hash('sha256', 'bd'),
        ], $order->id, $categoryPixels);

        // Send Push Notification to Admins
        $admins = User::whereNotNull('fcm_token')->get();
        foreach ($admins as $admin) {
            FirebasePushService::send(
                $admin->fcm_token,
                'New Order Received! 🛍️',
                "Order #{$order->id} from {$request->name} for ৳{$totalAmount}",
                ['order_id' => (string) $order->id, 'url' => route('admin.orders.show', $order->id)]
            );
        }

        // Delete any abandoned cart entries for this user/session
        $sessionId = session()->getId();
        AbandonedCart::where('phone', $request->phone)
            ->orWhere('session_id', $sessionId)
            ->delete();

        return response()->json([
            'success' => true,
            'redirect_url' => route('order.show', $order->id),
        ]);
    }

    public function processCart(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'required|string',
            'district' => 'required|string',
            'coupon_code' => 'nullable|string',
            'delivery_method_id' => 'required|exists:delivery_methods,id',
        ]);

        $cart = session()->get('cart', []);
        if (empty($cart)) {
            return response()->json(['success' => false, 'message' => 'Cart is empty'], 400);
        }

        // Find or create user
        $user = auth()->check() ? auth()->user() : User::where('phone', $request->phone)->first();
        if (! $user) {
            $user = User::create([
                'name' => $request->name,
                'phone' => $request->phone,
                'password' => Hash::make(Str::random(16)),
            ]);
        }

        // Save Address
        $addressExists = Address::where('user_id', $user->id)
            ->where('address', $request->address)
            ->where('district', $request->district)
            ->exists();

        if (! $addressExists) {
            Address::create([
                'user_id' => $user->id,
                'name' => $request->name,
                'phone' => $request->phone,
                'address' => $request->address,
                'district' => $request->district,
                'label' => 'Home',
                'is_default' => Address::where('user_id', $user->id)->count() === 0,
            ]);
        }

        // Calculate Totals
        $subTotal = 0;
        foreach ($cart as $item) {
            $subTotal += $item['price'] * $item['quantity'];
        }

        $discountAmount = 0;
        $appliedCoupon = null;

        if ($request->coupon_code) {
            $coupon = Coupon::where('code', strtoupper($request->coupon_code))
                ->where('is_active', true)
                ->first();

            if ($coupon && (! $coupon->expires_at || ! $coupon->expires_at->isPast()) && (! $coupon->usage_limit || $coupon->times_used < $coupon->usage_limit) && (! $coupon->min_order_value || $subTotal >= $coupon->min_order_value)) {
                if ($coupon->type === 'fixed') {
                    $discountAmount = $coupon->value;
                } else {
                    $discountAmount = ($subTotal * $coupon->value) / 100;
                }

                if ($discountAmount > $subTotal) {
                    $discountAmount = $subTotal;
                }

                $appliedCoupon = $coupon->code;
                $coupon->increment('times_used');
            }
        }

        $deliveryMethod = DeliveryMethod::find($request->delivery_method_id);
        $deliveryCost = $deliveryMethod ? $deliveryMethod->cost : 0;

        $bumpProduct = null;
        $bumpPrice = 0;
        if ($request->has('order_bump_product_id') && $request->order_bump_product_id) {
            $bumpProduct = Product::find($request->order_bump_product_id);
            if ($bumpProduct) {
                $bumpPrice = $bumpProduct->sale_price ?? $bumpProduct->price;
            }
        }

        $totalAmount = $subTotal - $discountAmount + $deliveryCost + $bumpPrice;
        $fullAddress = $request->address.', '.$request->district;

        $order = Order::create([
            'user_id' => $user->id,
            'name' => $request->name,
            'phone' => $request->phone,
            'shipping_address' => $fullAddress,
            'total_amount' => $totalAmount,
            'discount_amount' => $discountAmount,
            'coupon_code' => $appliedCoupon,
            'delivery_method_id' => $request->delivery_method_id,
            'delivery_cost' => $deliveryCost,
            'status' => 'pending',
        ]);

        $categoryPixels = [];
        $totalItemsCount = 0;
        $contentIds = [];

        // Create Order Items from Cart
        foreach ($cart as $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item['product_id'],
                'variation_id' => $item['variation_id'],
                'product_name' => $item['name'],
                'variation_name' => $item['variation_name'],
                'price' => $item['price'],
                'quantity' => $item['quantity'],
            ]);

            $totalItemsCount += $item['quantity'];
            $contentIds[] = $item['product_id'];

            $product = Product::find($item['product_id']);
            if ($product) {
                foreach ($product->categories as $category) {
                    if ($category->fb_pixel_id) {
                        $categoryPixels[] = [
                            'pixel_id' => $category->fb_pixel_id,
                            'capi_token' => $category->fb_capi_token,
                        ];
                    }
                }
            }
        }

        // Create Order Bump Item
        if ($bumpProduct) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $bumpProduct->id,
                'variation_id' => null,
                'product_name' => $bumpProduct->name.' (Order Bump)',
                'variation_name' => null,
                'price' => $bumpPrice,
                'quantity' => 1,
            ]);
        }

        FacebookCAPIService::sendEvent('Purchase', [
            'content_name' => 'Cart Purchase',
            'content_ids' => $contentIds,
            'content_type' => 'product',
            'value' => $totalAmount,
            'currency' => 'BDT',
            'num_items' => $totalItemsCount,
        ], [
            'fn' => hash('sha256', $request->name),
            'ph' => hash('sha256', $request->phone),
            'ct' => hash('sha256', $request->district),
            'country' => hash('sha256', 'bd'),
        ], $order->id, $categoryPixels);

        // Send Push Notification
        $admins = User::whereNotNull('fcm_token')->get();
        foreach ($admins as $admin) {
            FirebasePushService::send(
                $admin->fcm_token,
                'New Order Received! 🛍️',
                "Order #{$order->id} from {$request->name} for ৳{$totalAmount}",
                ['order_id' => (string) $order->id, 'url' => route('admin.orders.show', $order->id)]
            );
        }

        // Clear cart & abandoned carts
        session()->forget('cart');
        $sessionId = session()->getId();
        AbandonedCart::where('phone', $request->phone)
            ->orWhere('session_id', $sessionId)
            ->delete();

        return response()->json([
            'success' => true,
            'redirect_url' => route('order.show', $order->id),
        ]);
    }

    public function applyCoupon(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
            'order_total' => 'required|numeric',
        ]);

        $coupon = Coupon::where('code', strtoupper($request->code))
            ->where('is_active', true)
            ->first();

        if (! $coupon) {
            return response()->json(['success' => false, 'message' => 'Invalid coupon code.']);
        }

        if ($coupon->expires_at && $coupon->expires_at->isPast()) {
            return response()->json(['success' => false, 'message' => 'This coupon has expired.']);
        }

        if ($coupon->usage_limit && $coupon->times_used >= $coupon->usage_limit) {
            return response()->json(['success' => false, 'message' => 'Coupon usage limit reached.']);
        }

        if ($coupon->min_order_value && $request->order_total < $coupon->min_order_value) {
            return response()->json(['success' => false, 'message' => 'Minimum order value of ৳'.$coupon->min_order_value.' required.']);
        }

        $discountAmount = 0;
        if ($coupon->type === 'fixed') {
            $discountAmount = $coupon->value;
        } else {
            $discountAmount = ($request->order_total * $coupon->value) / 100;
        }

        // Cap discount to not exceed order total
        if ($discountAmount > $request->order_total) {
            $discountAmount = $request->order_total;
        }

        return response()->json([
            'success' => true,
            'message' => 'Coupon applied!',
            'discount_amount' => round($discountAmount, 2),
            'coupon_code' => $coupon->code,
        ]);
    }

    public function getDeliveryMethods()
    {
        $methods = DeliveryMethod::where('is_active', true)->orderBy('cost')->get();

        return response()->json($methods);
    }

    public function saveAbandonedCart(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|max:20',
            'cart_data' => 'required|array',
            'name' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'district' => 'nullable|string',
        ]);

        $sessionId = session()->getId();

        AbandonedCart::updateOrCreate(
            ['session_id' => $sessionId, 'phone' => $request->phone],
            [
                'name' => $request->name,
                'address' => $request->address,
                'district' => $request->district,
                'cart_data' => $request->cart_data,
                'user_id' => auth()->id(),
            ]
        );

        return response()->json(['success' => true]);
    }
}
