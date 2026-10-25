<?php

namespace App\Http\Controllers;

use App\Models\Order;

class OrderController extends Controller
{
    public function show(Order $order)
    {
        // Eager load items with product thumbnails, variations, and delivery method for the invoice
        $order->load([
            'items.product.thumbnail',
            'items.variation',
            'deliveryMethod',
            'user',
        ]);

        return view('orders.show', compact('order'));
    }
}
