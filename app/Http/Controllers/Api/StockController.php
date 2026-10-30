<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockPurchase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StockController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Product::with(['thumbnail', 'variations'])->latest();

        if ($request->filled('search')) {
            $s = $request->input('search');
            $query->where('name', 'like', "%{$s}%")
                ->orWhere('slug', 'like', "%{$s}%");
        }

        if ($request->boolean('low_stock')) {
            $query->where('stock_quantity', '<=', 5);
        }

        $products = $query->get(['id', 'name', 'slug', 'stock_quantity', 'price', 'purchase_price', 'status', 'thumbnail_media_id']);

        return response()->json([
            'total' => $products->count(),
            'products' => $products,
        ]);
    }

    public function adjust(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'stock_quantity' => 'nullable|integer|min:0',
            'delta' => 'nullable|integer',
            'purchase_price' => 'nullable|numeric|min:0',
            'selling_price' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $product = Product::findOrFail($validated['product_id']);

        $oldStock = $product->stock_quantity;
        if (isset($validated['stock_quantity'])) {
            $newStock = $validated['stock_quantity'];
        } elseif (isset($validated['delta'])) {
            $newStock = max(0, $oldStock + $validated['delta']);
        } else {
            return response()->json(['error' => 'Either stock_quantity or delta is required.'], 422);
        }

        $product->update([
            'stock_quantity' => $newStock,
            'purchase_price' => $validated['purchase_price'] ?? $product->purchase_price,
            'price' => $validated['selling_price'] ?? $product->price,
        ]);

        return response()->json([
            'message' => 'Stock adjusted successfully',
            'product_id' => $product->id,
            'name' => $product->name,
            'previous_stock' => $oldStock,
            'current_stock' => $newStock,
        ]);
    }

    public function storePurchase(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'variation_id' => 'nullable|exists:product_variations,id',
            'type' => 'nullable|string',
            'quantity' => 'required|integer|min:1',
            'purchase_price' => 'required|numeric|min:0',
            'selling_price' => 'nullable|numeric|min:0',
            'supplier' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $product = Product::findOrFail($validated['product_id']);
        $totalCost = $validated['quantity'] * $validated['purchase_price'];

        $purchase = StockPurchase::create([
            'product_id' => $product->id,
            'variation_id' => $validated['variation_id'] ?? null,
            'type' => $validated['type'] ?? 'regular',
            'quantity' => $validated['quantity'],
            'purchase_price' => $validated['purchase_price'],
            'total_cost' => $totalCost,
            'selling_price' => $validated['selling_price'] ?? $product->price,
            'supplier' => $validated['supplier'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'created_by' => null,
        ]);

        // Increment product stock
        $product->increment('stock_quantity', $validated['quantity']);
        $product->update([
            'purchase_price' => $validated['purchase_price'],
            'price' => $validated['selling_price'] ?? $product->price,
        ]);

        return response()->json([
            'message' => 'Stock purchase recorded successfully',
            'purchase' => $purchase,
            'current_stock' => $product->fresh()->stock_quantity,
        ], 201);
    }
}
