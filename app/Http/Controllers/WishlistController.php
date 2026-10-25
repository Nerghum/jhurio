<?php

namespace App\Http\Controllers;

use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WishlistController extends Controller
{
    public function index()
    {
        return redirect()->route('account.orders', ['tab' => 'wishlist']);
    }

    public function toggle(Request $request)
    {
        if (! Auth::check()) {
            return response()->json(['success' => false, 'message' => 'Please login to add to wishlist.', 'redirect' => route('login')]);
        }

        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        $wishlist = Wishlist::where('user_id', Auth::id())
            ->where('product_id', $request->product_id)
            ->first();

        if ($wishlist) {
            $wishlist->delete();

            return response()->json(['success' => true, 'action' => 'removed', 'message' => 'Removed from wishlist.']);
        }

        Wishlist::create([
            'user_id' => Auth::id(),
            'product_id' => $request->product_id,
        ]);

        return response()->json(['success' => true, 'action' => 'added', 'message' => 'Added to wishlist.']);
    }
}
