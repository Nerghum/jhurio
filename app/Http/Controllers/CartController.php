<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductVariation;
use App\Services\FacebookCAPIService;
use Illuminate\Http\Request;

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
            'totalPrice' => round($totalPrice, 2),
        ]);
    }

    public function add(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'variation_id' => 'nullable|exists:product_variations,id',
            'bundle_info' => 'nullable|array',
        ]);

        $product = Product::findOrFail($request->product_id);

        $price = $product->sale_price ?? $product->price;
        $variationName = null;
        $variationId = $request->variation_id;

        if ($variationId) {
            $variation = ProductVariation::where('product_id', $product->id)->find($variationId);
            if ($variation) {
                $variationName = $variation->name;
                if ($variation->price) {
                    $price = $variation->price;
                }
            } else {
                $variationId = null;
            }
        }

        $bundleInfo = $request->bundle_info;
        $isBundle = ! empty($bundleInfo['is_bundle']);
        if ($isBundle) {
            $matchedTier = null;
            if (! empty($product->bundle_settings['enabled']) && ! empty($product->bundle_settings['tiers'])) {
                if (isset($bundleInfo['tier_index']) && isset($product->bundle_settings['tiers'][$bundleInfo['tier_index']])) {
                    $matchedTier = $product->bundle_settings['tiers'][$bundleInfo['tier_index']];
                } else {
                    foreach ($product->bundle_settings['tiers'] as $t) {
                        if (($t['quantity'] ?? 0) == $request->quantity) {
                            $matchedTier = $t;
                            break;
                        }
                    }
                }
            }

            if ($matchedTier) {
                $tierQty = intval($matchedTier['quantity']);
                $baseTotal = $tierQty * $price;
                $discount = ($matchedTier['discount_type'] === 'percentage')
                    ? ($baseTotal * floatval($matchedTier['discount_value'])) / 100
                    : floatval($matchedTier['discount_value']);
                $discount = min($discount, $baseTotal);
                $price = ($baseTotal - $discount) / max(1, $tierQty);
            } elseif (isset($bundleInfo['unit_price'])) {
                $price = floatval($bundleInfo['unit_price']);
            }

            $itemVarDescriptions = [];
            if (! empty($bundleInfo['items'])) {
                foreach ($bundleInfo['items'] as $idx => $bItem) {
                    if (! empty($bItem['variation_name'])) {
                        $itemVarDescriptions[] = '#'.($idx + 1).': '.$bItem['variation_name'];
                    }
                }
            }

            $bundleLabel = '['.($bundleInfo['bundle_name'] ?? 'Bundle Deal').']';
            if (! empty($itemVarDescriptions)) {
                $variationName = implode(', ', $itemVarDescriptions).' '.$bundleLabel;
            } else {
                $variationName = trim(($variationName ? $variationName.' ' : '').$bundleLabel);
            }
        }

        $cart = session()->get('cart', []);

        // Create a unique key for the item
        $cartKey = $product->id.($variationId ? '-'.$variationId : '').($isBundle ? '-bundle-'.uniqid() : '');

        if (! $isBundle && isset($cart[$cartKey])) {
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
                'url' => route('product.show', $product->slug),
            ];
        }

        session()->put('cart', $cart);

        $categoryPixels = [];
        foreach ($product->categories as $category) {
            if ($category->fb_pixel_id) {
                $categoryPixels[] = [
                    'pixel_id' => $category->fb_pixel_id,
                    'capi_token' => $category->fb_capi_token,
                ];
            }
        }

        FacebookCAPIService::sendEvent('AddToCart', [
            'content_name' => $product->name,
            'content_ids' => [$product->id],
            'content_type' => 'product',
            'value' => $price,
            'currency' => 'BDT',
            'num_items' => $request->quantity,
        ], [], null, $categoryPixels);

        return $this->get($request);
    }

    public function remove(Request $request)
    {
        $request->validate([
            'id' => 'required|string',
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
            'quantity' => 'required|integer|min:1',
        ]);

        $cart = session()->get('cart', []);

        if (isset($cart[$request->id])) {
            $cart[$request->id]['quantity'] = $request->quantity;
            session()->put('cart', $cart);
        }

        return $this->get($request);
    }
}
