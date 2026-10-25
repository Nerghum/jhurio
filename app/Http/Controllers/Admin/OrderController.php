<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\DeliveryMethod;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    const STATUSES = [
        'pending' => ['label' => 'Pending',    'color' => 'yellow'],
        'processing' => ['label' => 'Processing', 'color' => 'blue'],
        'shipped' => ['label' => 'Shipped',    'color' => 'purple'],
        'delivered' => ['label' => 'Delivered',  'color' => 'indigo'],
        'completed' => ['label' => 'Completed',  'color' => 'green'],
        'cancelled' => ['label' => 'Cancelled',  'color' => 'red'],
    ];

    public function index(Request $request)
    {
        $query = Order::with(['items', 'user'])->latest();

        if ($request->status && array_key_exists($request->status, self::STATUSES)) {
            $query->where('status', $request->status);
        }

        if ($request->search) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                    ->orWhere('phone', 'like', "%{$s}%")
                    ->orWhere('id', $s);
            });
        }

        $orders = $query->paginate(20)->withQueryString();
        $statuses = self::STATUSES;
        $counts = Order::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        return view('admin.orders.index', compact('orders', 'statuses', 'counts'));
    }

    public function show(Order $order)
    {
        $order->load([
            'items.product.thumbnail',
            'items.variation',
            'user',
            'logs',
            'deliveryMethod',
            'trackingVisitor.sessions',
            'trackingSession',
        ]);

        $trackingVisitor = $order->trackingVisitor;
        $trackingSession = $order->trackingSession;
        $visitorEvents = $trackingVisitor ? $trackingVisitor->events()->latest()->take(15)->get() : collect();

        $statuses = self::STATUSES;
        $deliveryMethods = DeliveryMethod::where('is_active', true)->get();
        $coupons = Coupon::where('is_active', true)->get();
        $products = Product::with(['thumbnail', 'variations'])
            ->where('status', 'published')
            ->get(['id', 'name', 'price', 'compare_at_price', 'stock_quantity']);

        return view('admin.orders.show', compact(
            'order',
            'statuses',
            'deliveryMethods',
            'coupons',
            'products',
            'trackingVisitor',
            'trackingSession',
            'visitorEvents'
        ));
    }

    public function update(Request $request, Order $order)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:50',
            'shipping_address' => 'required|string|max:1000',
            'delivery_method_id' => 'nullable|exists:delivery_methods,id',
            'delivery_cost' => 'nullable|numeric|min:0',
            'coupon_code' => 'nullable|string|max:50',
            'discount_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.id' => 'nullable|integer',
            'items.*.product_id' => 'nullable|exists:products,id',
            'items.*.variation_id' => 'nullable|exists:product_variations,id',
            'items.*.product_name' => 'required|string|max:255',
            'items.*.variation_name' => 'nullable|string|max:255',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        DB::transaction(function () use ($order, $validated) {
            $subTotal = 0;
            $submittedItemIds = [];

            foreach ($validated['items'] as $itemData) {
                $linePrice = floatval($itemData['price']);
                $lineQty = intval($itemData['quantity']);
                $subTotal += ($linePrice * $lineQty);

                if (! empty($itemData['id'])) {
                    // Update existing item
                    $item = OrderItem::where('order_id', $order->id)->where('id', $itemData['id'])->first();
                    if ($item) {
                        $item->update([
                            'product_id' => $itemData['product_id'] ?? $item->product_id,
                            'variation_id' => $itemData['variation_id'] ?? null,
                            'product_name' => $itemData['product_name'],
                            'variation_name' => $itemData['variation_name'] ?? null,
                            'price' => $linePrice,
                            'quantity' => $lineQty,
                        ]);
                        $submittedItemIds[] = $item->id;
                    }
                    // Create newly added item
                    $prod = ! empty($itemData['product_id']) ? Product::find($itemData['product_id']) : null;
                    $newItem = OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $itemData['product_id'] ?? null,
                        'variation_id' => $itemData['variation_id'] ?? null,
                        'product_name' => $itemData['product_name'],
                        'variation_name' => $itemData['variation_name'] ?? null,
                        'price' => $linePrice,
                        'purchase_price' => $prod?->purchase_price ?? 0,
                        'quantity' => $lineQty,
                    ]);
                    $submittedItemIds[] = $newItem->id;
                }
            }

            // Delete removed items
            OrderItem::where('order_id', $order->id)
                ->whereNotIn('id', $submittedItemIds)
                ->delete();

            $deliveryCost = isset($validated['delivery_cost']) ? floatval($validated['delivery_cost']) : floatval($order->delivery_cost);
            if (! empty($validated['delivery_method_id']) && ! isset($validated['delivery_cost'])) {
                $dm = DeliveryMethod::find($validated['delivery_method_id']);
                if ($dm) {
                    $deliveryCost = floatval($dm->cost);
                }
            }

            $discountAmount = floatval($validated['discount_amount'] ?? 0);
            $totalAmount = max(0, $subTotal - $discountAmount + $deliveryCost);

            $order->update([
                'name' => $validated['name'],
                'phone' => $validated['phone'],
                'shipping_address' => $validated['shipping_address'],
                'delivery_method_id' => $validated['delivery_method_id'] ?? null,
                'delivery_cost' => $deliveryCost,
                'coupon_code' => ! empty($validated['coupon_code']) ? strtoupper(trim($validated['coupon_code'])) : null,
                'discount_amount' => $discountAmount,
                'total_amount' => $totalAmount,
            ]);

            $order->logs()->create([
                'status' => $order->status,
                'note' => 'Order details & invoice items updated by admin.',
            ]);
        });

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Order updated successfully',
                'order' => $order->fresh(['items.product.thumbnail', 'deliveryMethod']),
            ]);
        }

        return redirect()->route('admin.orders.show', $order)->with('success', 'Order invoice updated successfully.');
    }

    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => ['required', 'in:'.implode(',', array_keys(self::STATUSES))],
        ]);

        if ($order->status !== $request->status) {
            $order->update(['status' => $request->status]);

            // Log the change
            $order->logs()->create([
                'status' => $request->status,
                'note' => $request->note ?? null,
            ]);
        }

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Status updated to '.self::STATUSES[$request->status]['label']);
    }

    public function invoice(Order $order)
    {
        $order->load(['items.product', 'items.variation', 'user', 'deliveryMethod']);

        $pdf = Pdf::loadView('admin.orders.invoice', [
            'order' => $order,
            'autoPrint' => false,
        ])->setPaper('a4', 'portrait');

        return $pdf->download('invoice-'.str_pad($order->id, 5, '0', STR_PAD_LEFT).'.pdf');
    }

    public function printView(Order $order)
    {
        $order->load(['items.product', 'items.variation', 'user', 'deliveryMethod']);

        return view('admin.orders.invoice', [
            'order' => $order,
            'autoPrint' => true,
        ]);
    }

    public function bulkAction(Request $request)
    {
        $request->validate([
            'order_ids' => 'required|array',
            'order_ids.*' => 'exists:orders,id',
            'action' => ['required', 'in:'.implode(',', array_keys(self::STATUSES))],
        ]);

        $orders = Order::whereIn('id', $request->order_ids)->get();
        $status = $request->action;

        foreach ($orders as $order) {
            if ($order->status !== $status) {
                $order->update(['status' => $status]);
                $order->logs()->create([
                    'status' => $status,
                    'note' => 'Bulk update',
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => count($request->order_ids).' orders updated to '.self::STATUSES[$status]['label'],
        ]);
    }
}
