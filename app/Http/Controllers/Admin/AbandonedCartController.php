<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AbandonedCart;

class AbandonedCartController extends Controller
{
    public function index()
    {
        $carts = AbandonedCart::latest()->paginate(20);

        return view('admin.abandoned-carts.index', compact('carts'));
    }

    public function destroy($id)
    {
        $cart = AbandonedCart::findOrFail($id);
        $cart->delete();

        return redirect()->route('admin.abandoned-carts.index')->with('success', 'Abandoned cart deleted successfully.');
    }
}
