<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\StockPurchase;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class StockController extends Controller
{
    /**
     * Display stock management dashboard & purchase history.
     */
    public function index(Request $request): View
    {
        $search = $request->input('search');
        $statusFilter = $request->input('stock_status', 'all');
        $categoryId = $request->input('category_id');
        $tab = $request->input('tab', 'inventory'); // 'inventory' or 'history'

        // 1. Overview KPIs
        $totalProducts = Product::count();
        $totalUnits = (int) Product::sum('stock_quantity');
        $totalValuation = (float) Product::selectRaw('SUM(stock_quantity * COALESCE(purchase_price, 0)) as val')->value('val') ?? 0.0;
        $potentialRetailValue = (float) Product::selectRaw('SUM(stock_quantity * COALESCE(price, 0)) as val')->value('val') ?? 0.0;
        $lowStockCount = Product::where('stock_quantity', '>', 0)->where('stock_quantity', '<=', 5)->count();
        $outOfStockCount = Product::where('stock_quantity', '<=', 0)->count();

        // 2. Query Products for Stock Table
        $productsQuery = Product::with(['thumbnail', 'categories', 'variations']);

        if (! empty($search)) {
            $productsQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('id', $search);
            });
        }

        if (! empty($categoryId)) {
            $productsQuery->whereHas('categories', function ($q) use ($categoryId) {
                $q->where('categories.id', $categoryId);
            });
        }

        if ($statusFilter === 'low_stock') {
            $productsQuery->where('stock_quantity', '>', 0)->where('stock_quantity', '<=', 5);
        } elseif ($statusFilter === 'out_of_stock') {
            $productsQuery->where('stock_quantity', '<=', 0);
        } elseif ($statusFilter === 'in_stock') {
            $productsQuery->where('stock_quantity', '>', 5);
        }

        $sort = $request->input('sort', 'name_asc');
        if ($sort === 'stock_asc') {
            $productsQuery->orderBy('stock_quantity', 'asc');
        } elseif ($sort === 'stock_desc') {
            $productsQuery->orderBy('stock_quantity', 'desc');
        } elseif ($sort === 'valuation_desc') {
            $productsQuery->orderByRaw('(stock_quantity * COALESCE(purchase_price, 0)) DESC');
        } else {
            $productsQuery->orderBy('name', 'asc');
        }

        $products = $productsQuery->paginate(20, ['*'], 'inventory_page')->withQueryString();

        // 3. Query Stock Purchases History
        $purchasesQuery = StockPurchase::with(['product.thumbnail', 'variation', 'creator'])
            ->latest();

        if (! empty($search)) {
            $purchasesQuery->whereHas('product', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        $recentPurchases = $purchasesQuery->paginate(20, ['*'], 'history_page')->withQueryString();

        $allProductsList = Product::select('id', 'name', 'price', 'purchase_price', 'stock_quantity')
            ->orderBy('name')
            ->get();

        $categories = Category::orderBy('name')->get();

        return view('admin.stock.index', compact(
            'products',
            'recentPurchases',
            'allProductsList',
            'categories',
            'totalProducts',
            'totalUnits',
            'totalValuation',
            'potentialRetailValue',
            'lowStockCount',
            'outOfStockCount',
            'statusFilter',
            'search',
            'categoryId',
            'sort',
            'tab'
        ));
    }

    /**
     * Store a new stock purchase entry (updates stock & sets purchase price).
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'variation_id' => 'nullable|exists:product_variations,id',
            'quantity' => 'required|integer|min:1',
            'purchase_price' => 'required|numeric|min:0',
            'selling_price' => 'nullable|numeric|min:0',
            'supplier' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        DB::transaction(function () use ($validated) {
            $product = Product::findOrFail($validated['product_id']);
            $qty = (int) $validated['quantity'];
            $purchasePrice = (float) $validated['purchase_price'];
            $totalCost = $qty * $purchasePrice;

            $variation = null;
            if (! empty($validated['variation_id'])) {
                $variation = ProductVariation::where('product_id', $product->id)->find($validated['variation_id']);
            }

            // 1. Record stock purchase
            StockPurchase::create([
                'product_id' => $product->id,
                'variation_id' => $variation?->id,
                'type' => 'purchase',
                'quantity' => $qty,
                'purchase_price' => $purchasePrice,
                'total_cost' => $totalCost,
                'selling_price' => ! empty($validated['selling_price']) ? (float) $validated['selling_price'] : $product->price,
                'supplier' => $validated['supplier'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'created_by' => auth()->id(),
            ]);

            // 2. Update Product stock & latest purchase price
            $product->increment('stock_quantity', $qty);
            $product->purchase_price = $purchasePrice;

            if (! empty($validated['selling_price']) && (float) $validated['selling_price'] > 0) {
                $product->price = (float) $validated['selling_price'];
            }

            $product->save();

            // 3. If variation specified, update variation stock & purchase price
            if ($variation) {
                $variation->increment('stock_quantity', $qty);
                $variation->purchase_price = $purchasePrice;
                if (! empty($validated['selling_price']) && (float) $validated['selling_price'] > 0) {
                    $variation->price = (float) $validated['selling_price'];
                }
                $variation->save();
            }
        });

        return redirect()->route('admin.stock.index', ['tab' => 'history'])
            ->with('success', 'Stock added successfully with purchase price recorded.');
    }

    /**
     * Quick inline/modal update of stock quantity & purchase price.
     */
    public function quickUpdate(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'stock_quantity' => 'required|integer|min:0',
            'purchase_price' => 'required|numeric|min:0',
            'price' => 'nullable|numeric|min:0',
            'note' => 'nullable|string|max:500',
        ]);

        DB::transaction(function () use ($validated, $product) {
            $oldStock = (int) $product->stock_quantity;
            $newStock = (int) $validated['stock_quantity'];
            $stockDiff = $newStock - $oldStock;
            $purchasePrice = (float) $validated['purchase_price'];

            // Log adjustment in StockPurchase if there is a stock change or purchase price update
            if ($stockDiff !== 0 || (float) $product->purchase_price !== $purchasePrice) {
                StockPurchase::create([
                    'product_id' => $product->id,
                    'variation_id' => null,
                    'type' => 'adjustment',
                    'quantity' => $stockDiff,
                    'purchase_price' => $purchasePrice,
                    'total_cost' => max(0, $stockDiff) * $purchasePrice,
                    'selling_price' => ! empty($validated['price']) ? (float) $validated['price'] : $product->price,
                    'supplier' => 'Quick Adjustment',
                    'notes' => $validated['note'] ?? "Adjusted stock from {$oldStock} to {$newStock}",
                    'created_by' => auth()->id(),
                ]);
            }

            $product->stock_quantity = $newStock;
            $product->purchase_price = $purchasePrice;
            if (! empty($validated['price']) && (float) $validated['price'] > 0) {
                $product->price = (float) $validated['price'];
            }
            $product->save();
        });

        return back()->with('success', "Stock for {$product->name} updated successfully.");
    }
}
