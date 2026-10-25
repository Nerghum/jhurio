<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderLog;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AccountController extends Controller
{
    public function orders(Request $request)
    {
        $userId = Auth::id();

        // 1. Current / Active Orders (Pending, Processing, Shipped)
        $currentOrders = Order::with(['items.product.thumbnail', 'items.variation'])
            ->where('user_id', $userId)
            ->whereIn('status', ['pending', 'processing', 'shipped'])
            ->latest()
            ->get();

        // 2. Completed Orders (Delivered, Completed)
        $completedOrders = Order::with(['items.product.thumbnail', 'items.variation'])
            ->where('user_id', $userId)
            ->whereIn('status', ['delivered', 'completed'])
            ->latest()
            ->get();

        // 3. Cancelled Orders
        $cancelledOrders = Order::with(['items.product.thumbnail', 'items.variation'])
            ->where('user_id', $userId)
            ->where('status', 'cancelled')
            ->latest()
            ->get();

        // 4. All Orders (Paginated)
        $allOrders = Order::with(['items.product.thumbnail', 'items.variation'])
            ->where('user_id', $userId)
            ->latest()
            ->paginate(15);

        // 5. Wishlist Items
        $wishlists = Wishlist::where('user_id', $userId)
            ->with(['product.thumbnail'])
            ->latest()
            ->get();

        // 6. Cart Items from Session
        $cart = session()->get('cart', []);
        $cartTotal = 0;
        $cartCount = 0;
        foreach ($cart as $item) {
            $qty = intval($item['quantity'] ?? 1);
            $cartCount += $qty;
            $cartTotal += floatval($item['price'] ?? 0) * $qty;
        }

        // 7. Saved Addresses
        $addresses = Address::where('user_id', $userId)->orderByDesc('is_default')->get();

        // Active tab from query param (defaults to 'current', or 'all' if no current orders)
        $defaultTab = $currentOrders->count() > 0 ? 'current' : ($allOrders->total() > 0 ? 'all' : 'current');
        $currentTab = $request->query('tab', $defaultTab);

        return view('account.orders', compact(
            'currentOrders',
            'completedOrders',
            'cancelledOrders',
            'allOrders',
            'wishlists',
            'cart',
            'cartTotal',
            'cartCount',
            'addresses',
            'currentTab'
        ));
    }

    public function orderDetail(Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }
        $order->load(['items.product.thumbnail', 'items.variation', 'logs']);

        return view('account.order-detail', compact('order'));
    }

    public function cancelOrder(Request $request, Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        if ($order->status !== 'pending') {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only pending orders can be cancelled. Please contact our support team.',
                ], 422);
            }

            return back()->with('error', 'Only pending orders can be cancelled. Please contact our support team.');
        }

        $reason = $request->input('reason', 'Cancelled by customer');
        $order->update(['status' => 'cancelled']);

        if ($order->coupon_code) {
            Coupon::where('code', $order->coupon_code)->where('times_used', '>', 0)->decrement('times_used');
        }

        OrderLog::create([
            'order_id' => $order->id,
            'status' => 'cancelled',
            'note' => 'Order cancelled by customer. Reason: '.$reason,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Order #{$order->id} has been cancelled successfully.",
            ]);
        }

        return back()->with('success', "Order #{$order->id} has been cancelled successfully.");
    }

    public function cart()
    {
        return redirect()->route('account.orders', ['tab' => 'cart']);
    }

    // ── Addresses ──────────────────────────────────────────────

    public function addresses()
    {
        return redirect()->route('account.orders', ['tab' => 'addresses']);
    }

    public function storeAddress(Request $request)
    {
        $data = $request->validate([
            'label' => 'required|string|max:50',
            'name' => 'required|string|max:100',
            'phone' => 'required|string|max:20',
            'address' => 'required|string',
            'district' => 'required|string|max:100',
        ]);

        $data['user_id'] = Auth::id();

        // If first address or marked as default, clear others
        if ($request->boolean('is_default') || Address::where('user_id', Auth::id())->count() === 0) {
            Address::where('user_id', Auth::id())->update(['is_default' => false]);
            $data['is_default'] = true;
        }

        Address::create($data);

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Address saved successfully!');
    }

    public function updateAddress(Request $request, Address $address)
    {
        abort_unless($address->user_id === Auth::id(), 403);

        $data = $request->validate([
            'label' => 'required|string|max:50',
            'name' => 'required|string|max:100',
            'phone' => 'required|string|max:20',
            'address' => 'required|string',
            'district' => 'required|string|max:100',
        ]);

        $address->update($data);

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Address updated successfully!');
    }

    public function destroyAddress(Address $address)
    {
        abort_unless($address->user_id === Auth::id(), 403);
        $wasDefault = $address->is_default;
        $address->delete();

        // Promote next address to default
        if ($wasDefault) {
            $next = Address::where('user_id', Auth::id())->first();
            $next?->update(['is_default' => true]);
        }

        return back()->with('success', 'Address removed.');
    }

    public function setDefault(Address $address)
    {
        abort_unless($address->user_id === Auth::id(), 403);
        Address::where('user_id', Auth::id())->update(['is_default' => false]);
        $address->update(['is_default' => true]);

        return response()->json(['success' => true]);
    }

    public function defaultAddress()
    {
        $addresses = Address::where('user_id', Auth::id())->orderByDesc('is_default')->get();

        return response()->json($addresses);
    }
}
