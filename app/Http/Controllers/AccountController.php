<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Address;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AccountController extends Controller
{
    public function orders()
    {
        $orders = Order::with(['items'])
            ->where('user_id', Auth::id())
            ->latest()
            ->paginate(10);

        return view('account.orders', compact('orders'));
    }

    public function orderDetail(Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }
        $order->load(['items.product', 'items.variation']);
        return view('account.order-detail', compact('order'));
    }

    // ── Addresses ──────────────────────────────────────────────

    public function addresses()
    {
        $addresses = Address::where('user_id', Auth::id())->orderByDesc('is_default')->get();
        return view('account.addresses', compact('addresses'));
    }

    public function storeAddress(Request $request)
    {
        $data = $request->validate([
            'label'    => 'required|string|max:50',
            'name'     => 'required|string|max:100',
            'phone'    => 'required|string|max:20',
            'address'  => 'required|string',
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
        return back()->with('success', 'Address saved!');
    }

    public function updateAddress(Request $request, Address $address)
    {
        abort_unless($address->user_id === Auth::id(), 403);

        $data = $request->validate([
            'label'    => 'required|string|max:50',
            'name'     => 'required|string|max:100',
            'phone'    => 'required|string|max:20',
            'address'  => 'required|string',
            'district' => 'required|string|max:100',
        ]);

        $address->update($data);

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }
        return back()->with('success', 'Address updated!');
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
