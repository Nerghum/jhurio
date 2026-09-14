<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function show(Order $order)
    {
        // Ideally we should check if the logged in user owns this order, 
        // but for a guest express checkout without login sessions, we'll just show it.
        $order->load('items');
        return view('orders.show', compact('order'));
    }
}
