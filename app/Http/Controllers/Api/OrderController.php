<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
