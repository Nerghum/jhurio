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
}
