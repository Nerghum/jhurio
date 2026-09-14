<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\User;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Coupon;
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
            'coupon_code' => 'nullable|string'
        ]);

        $product = Product::findOrFail($request->product_id);
        $price = $product->sale_price ?? $product->price;
        $variationName = null;

        if ($request->variation_id) {
            $variation = ProductVariation::where('product_id', $product->id)->find($request->variation_id);
            if ($variation) {
                $variationName = $variation->name;
            }
        }

        // Find or create user
        $user = auth()->check() ? auth()->user() : User::where('phone', $request->phone)->first();
        if (!$user) {
            $user = User::create([
                'name' => $request->name,
                'phone' => $request->phone,
                'password' => Hash::make(Str::random(16)), // Dummy password
            ]);
        }

        // Save Address to User Account
        $addressExists = \App\Models\Address::where('user_id', $user->id)
            ->where('address', $request->address)
            ->where('district', $request->district)
            ->exists();

        if (!$addressExists) {
            \App\Models\Address::create([
                'user_id' => $user->id,
                'name' => $request->name,
                'phone' => $request->phone,
                'address' => $request->address,
                'district' => $request->district,
                'label' => 'Home',
                'is_default' => \App\Models\Address::where('user_id', $user->id)->count() === 0,
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

            if ($coupon && (!$coupon->expires_at || !$coupon->expires_at->isPast()) && (!$coupon->usage_limit || $coupon->times_used < $coupon->usage_limit) && (!$coupon->min_order_value || $subTotal >= $coupon->min_order_value)) {
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

        $totalAmount = $subTotal - $discountAmount;
        $fullAddress = $request->address . ', ' . $request->district;

        $order = Order::create([
            'user_id' => $user->id,
            'name' => $request->name,
            'phone' => $request->phone,
            'shipping_address' => $fullAddress,
            'total_amount' => $totalAmount,
            'discount_amount' => $discountAmount,
            'coupon_code' => $appliedCoupon,
            'status' => 'pending',
        ]);

        // Create Order Item
        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'variation_id' => $request->variation_id,
            'product_name' => $product->name,
            'variation_name' => $variationName,
            'price' => $price,
            'quantity' => $request->quantity,
        ]);

        return response()->json([
            'success' => true,
            'redirect_url' => route('order.show', $order->id)
        ]);
    }

    public function applyCoupon(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
            'order_total' => 'required|numeric'
        ]);

        $coupon = Coupon::where('code', strtoupper($request->code))
                        ->where('is_active', true)
                        ->first();

        if (!$coupon) {
            return response()->json(['success' => false, 'message' => 'Invalid coupon code.']);
        }

        if ($coupon->expires_at && $coupon->expires_at->isPast()) {
            return response()->json(['success' => false, 'message' => 'This coupon has expired.']);
        }

        if ($coupon->usage_limit && $coupon->times_used >= $coupon->usage_limit) {
            return response()->json(['success' => false, 'message' => 'Coupon usage limit reached.']);
        }

        if ($coupon->min_order_value && $request->order_total < $coupon->min_order_value) {
            return response()->json(['success' => false, 'message' => 'Minimum order value of ৳' . $coupon->min_order_value . ' required.']);
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
            'coupon_code' => $coupon->code
        ]);
    }
}
