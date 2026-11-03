<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    public function index(): JsonResponse
    {
        $coupons = Coupon::latest()->get();

        return response()->json([
            'total' => $coupons->count(),
            'coupons' => $coupons,
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $coupon = Coupon::find($id);

        if (! $coupon) {
            return response()->json(['error' => 'Coupon not found'], 404);
        }

        return response()->json([
            'coupon' => $coupon,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|unique:coupons,code|max:50',
            'type' => 'required|in:fixed,percentage',
            'value' => 'required|numeric|min:0',
            'min_order_value' => 'nullable|numeric|min:0',
            'expires_at' => 'nullable|date',
            'usage_limit' => 'nullable|integer|min:1',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['code'] = strtoupper($validated['code']);
        $validated['is_active'] = $validated['is_active'] ?? true;

        $coupon = Coupon::create($validated);

        return response()->json([
            'message' => 'Coupon created successfully',
            'coupon' => $coupon,
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $coupon = Coupon::find($id);

        if (! $coupon) {
            return response()->json(['error' => 'Coupon not found'], 404);
        }

        $validated = $request->validate([
            'code' => 'sometimes|required|string|max:50|unique:coupons,code,'.$id,
            'type' => 'nullable|in:fixed,percentage',
            'value' => 'nullable|numeric|min:0',
            'min_order_value' => 'nullable|numeric|min:0',
            'expires_at' => 'nullable|date',
            'usage_limit' => 'nullable|integer|min:1',
            'is_active' => 'nullable|boolean',
        ]);

        if (! empty($validated['code'])) {
            $validated['code'] = strtoupper($validated['code']);
        }

        $coupon->update(array_filter($validated, fn ($v) => ! is_null($v)));

        return response()->json([
            'message' => 'Coupon updated successfully',
            'coupon' => $coupon->fresh(),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $coupon = Coupon::find($id);

        if (! $coupon) {
            return response()->json(['error' => 'Coupon not found'], 404);
        }

        $coupon->delete();

        return response()->json([
            'message' => 'Coupon deleted successfully',
        ]);
    }

    public function apply(Request $request): JsonResponse
    {
        $request->validate([
            'code' => 'required|string',
            'order_total' => 'required|numeric|min:0',
        ]);

        $coupon = Coupon::where('code', strtoupper($request->code))
            ->where('is_active', true)
            ->first();

        if (! $coupon) {
            return response()->json(['success' => false, 'message' => 'Invalid coupon code.'], 404);
        }

        if ($coupon->expires_at && $coupon->expires_at->isPast()) {
            return response()->json(['success' => false, 'message' => 'This coupon has expired.'], 422);
        }

        if ($coupon->usage_limit && $coupon->times_used >= $coupon->usage_limit) {
            return response()->json(['success' => false, 'message' => 'Coupon usage limit reached.'], 422);
        }

        if ($coupon->min_order_value && $request->order_total < $coupon->min_order_value) {
            return response()->json(['success' => false, 'message' => 'Minimum order value of ৳'.$coupon->min_order_value.' required.'], 422);
        }

        $discountAmount = ($coupon->type === 'fixed')
            ? (float) $coupon->value
            : ($request->order_total * (float) $coupon->value) / 100;

        if ($discountAmount > $request->order_total) {
            $discountAmount = $request->order_total;
        }

        return response()->json([
            'success' => true,
            'message' => 'Coupon applied successfully!',
            'coupon_code' => $coupon->code,
            'type' => $coupon->type,
            'value' => (float) $coupon->value,
            'discount_amount' => round($discountAmount, 2),
            'final_total' => round($request->order_total - $discountAmount, 2),
        ]);
    }
}
