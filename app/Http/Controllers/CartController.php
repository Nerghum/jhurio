<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductVariation;

class CartController extends Controller
{
    public function get(Request $request)
    {
        $cart = session()->get('cart', []);
        
        $totalItems = 0;
        $totalPrice = 0;
        
        foreach ($cart as $item) {
            $totalItems += $item['quantity'];
            $totalPrice += $item['price'] * $item['quantity'];
        }
        
        return response()->json([
            'items' => array_values($cart),
            'totalItems' => $totalItems,
            'totalPrice' => round($totalPrice, 2)
        ]);
    }

    public function add(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'variation_id' => 'nullable|exists:product_variations,id'
        ]);

        $product = Product::findOrFail($request->product_id);
        
        $price = $product->sale_price ?? $product->price;
        $variationName = null;
        $variationId = $request->variation_id;

        if ($variationId) {
            $variation = ProductVariation::where('product_id', $product->id)->find($variationId);
            if ($variation) {
                // Variations don't have separate prices right now, but could in the future
                $variationName = $variation->name;
            } else {
                $variationId = null;
            }
        }

        $cart = session()->get('cart', []);
        
        // Create a unique key for the item
        $cartKey = $product->id . ($variationId ? '-' . $variationId : '');

        if (isset($cart[$cartKey])) {
            $cart[$cartKey]['quantity'] += $request->quantity;
        } else {
            $cart[$cartKey] = [
                'id' => $cartKey,
                'product_id' => $product->id,
                'variation_id' => $variationId,
                'name' => $product->name,
                'variation_name' => $variationName,
                'price' => $price,
                'quantity' => $request->quantity,
                'image' => $product->thumbnail->url ?? null,
                'url' => route('product.show', $product->slug)
            ];
        }

        session()->put('cart', $cart);

        return $this->get($request);
    }

    public function remove(Request $request)
    {
        $request->validate([
            'id' => 'required|string'
        ]);

        $cart = session()->get('cart', []);
        
        if (isset($cart[$request->id])) {
            unset($cart[$request->id]);
            session()->put('cart', $cart);
        }

        return $this->get($request);
    }
    
    public function updateQuantity(Request $request)
    {
        $request->validate([
            'id' => 'required|string',
            'quantity' => 'required|integer|min:1'
        ]);

        $cart = session()->get('cart', []);
        
        if (isset($cart[$request->id])) {
            $cart[$request->id]['quantity'] = $request->quantity;
            session()->put('cart', $cart);
        }

        return $this->get($request);
    }
}
