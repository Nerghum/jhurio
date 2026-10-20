<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

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
        $order->load(['items.product', 'items.variation', 'user', 'logs']);
        $statuses = self::STATUSES;

        return view('admin.orders.show', compact('order', 'statuses'));
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
        $order->load(['items.product', 'items.variation', 'user']);

        $pdf = Pdf::loadView('admin.orders.invoice', compact('order'));

        return $pdf->download('invoice-'.str_pad($order->id, 5, '0', STR_PAD_LEFT).'.pdf');
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
