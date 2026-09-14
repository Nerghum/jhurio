<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::where('status', 'published')->with(['thumbnail', 'categories']);

        // Search filter
        if ($request->filled('q')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->q . '%')
                  ->orWhere('description', 'like', '%' . $request->q . '%');
            });
        }

        // Category filter
        if ($request->filled('category')) {
            $query->whereHas('categories', function($q) use ($request) {
                $q->where('slug', $request->category);
            });
        }

        // Price filters
        if ($request->filled('min')) {
            $query->where('price', '>=', $request->min);
        }
        if ($request->filled('max')) {
            $query->where('price', '<=', $request->max);
        }

        // Sorting
        $sort = $request->get('sort', 'latest');
        if ($sort == 'price_asc') {
            $query->orderBy('price', 'asc');
        } elseif ($sort == 'price_desc') {
            $query->orderBy('price', 'desc');
        } else {
            $query->latest();
        }

        $products = $query->paginate(12)->withQueryString();
        $categories = \App\Models\Category::all();

        return view('products.index', compact('products', 'categories'));
    }

    public function show($slug)
    {
        $product = Product::where('slug', $slug)
            ->where('status', 'published')
            ->with(['categories', 'thumbnail', 'gallery', 'variations.media', 'tags', 'reviews' => function($q) {
                $q->where('status', 'approved')->with('user')->latest();
            }])
            ->firstOrFail();

        // Fetch related products from the same categories
        $categoryIds = $product->categories->pluck('id')->toArray();
        $relatedProducts = Product::whereHas('categories', function($q) use ($categoryIds) {
                $q->whereIn('categories.id', $categoryIds);
            })
            ->where('id', '!=', $product->id)
            ->where('status', 'published')
            ->with('thumbnail', 'categories')
            ->latest()
            ->take(4)
            ->get();

        if ($relatedProducts->count() < 4) {
            $moreProducts = Product::where('id', '!=', $product->id)
                ->whereNotIn('id', $relatedProducts->pluck('id')->toArray())
                ->where('status', 'published')
                ->with('thumbnail', 'categories')
                ->latest()
                ->take(4 - $relatedProducts->count())
                ->get();
            $relatedProducts = $relatedProducts->merge($moreProducts);
        }

        return view('products.show', compact('product', 'relatedProducts'));
    }

    public function quickview($slug)
    {
        $product = Product::where('slug', $slug)
            ->where('status', 'published')
            ->with(['thumbnail', 'gallery', 'variations.media'])
            ->firstOrFail();

        return view('products.quickview', compact('product'));
    }

    public function ajaxSearch(Request $request)
    {
        $query = strtolower(trim($request->get('q')));
        
        $results = collect();
        $didYouMean = null;

        if (!empty($query)) {
            // Split the query into words to allow partial matches (e.g., "man's cotton" matching "Men's Cotton")
            $words = array_filter(explode(' ', $query), function($word) {
                return strlen($word) > 2; // Only search words longer than 2 characters
            });
            
            // If all words are short, just search the original string
            if (empty($words)) {
                $words = [$query];
            }

            $products = Product::where('status', 'published')
                ->where(function($q) use ($words) {
                    foreach ($words as $word) {
                        $q->orWhere('name', 'like', '%' . $word . '%')
                          ->orWhere('description', 'like', '%' . $word . '%');
                    }
                })
                ->select('id', 'name', 'slug', 'price', 'compare_at_price', 'thumbnail_media_id')
                ->with('thumbnail')
                ->take(4)
                ->get();

            $results = $products->map(function($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'url' => route('product.show', $product->slug),
                    'image' => $product->thumbnail ? $product->thumbnail->url : 'https://via.placeholder.com/50',
                    'price' => number_format($product->price, 2),
                    'compare_at_price' => $product->compare_at_price > $product->price ? number_format($product->compare_at_price, 2) : null,
                ];
            });

            // If no results, check for typos
            if ($results->isEmpty()) {
                // Get all product names to build a dictionary
                $allNames = Product::where('status', 'published')->pluck('name')->toArray();
                $dictionary = [];
                foreach ($allNames as $name) {
                    // Split into words, clean punctuation, and make lowercase
                    $nameWords = explode(' ', strtolower(preg_replace('/[^a-zA-Z0-9\s]/', '', $name)));
                    foreach ($nameWords as $w) {
                        if (strlen($w) > 3) {
                            $dictionary[] = $w;
                        }
                    }
                }
                $dictionary = array_unique($dictionary);

                // Find the closest word
                $closestWord = null;
                $shortestDistance = -1;
                
                // We check the last word of the query or the full query (without punctuation)
                $cleanQuery = strtolower(preg_replace('/[^a-zA-Z0-9\s]/', '', $query));

                foreach ($dictionary as $word) {
                    $lev = levenshtein($cleanQuery, $word);
                    // Match if distance is 1 or 2 (for reasonable typos)
                    if ($lev <= 2) {
                        if ($lev == 0) {
                            $closestWord = $word;
                            $shortestDistance = 0;
                            break;
                        }
                        if ($lev < $shortestDistance || $shortestDistance < 0) {
                            $closestWord  = $word;
                            $shortestDistance = $lev;
                        }
                    }
                }

                if ($closestWord && $shortestDistance > 0) {
                    $didYouMean = $closestWord;
                }
            }
        }

        // Always get 2 suggested products (e.g. latest)
        $suggestedProducts = Product::where('status', 'published')
            ->select('id', 'name', 'slug', 'price', 'compare_at_price', 'thumbnail_media_id')
            ->with('thumbnail')
            ->inRandomOrder()
            ->take(2)
            ->get();
            
        $suggested = $suggestedProducts->map(function($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'url' => route('product.show', $product->slug),
                'image' => $product->thumbnail ? $product->thumbnail->url : 'https://via.placeholder.com/50',
                'price' => number_format($product->price, 2),
                'compare_at_price' => $product->compare_at_price > $product->price ? number_format($product->compare_at_price, 2) : null,
            ];
        });

        return response()->json([
            'results' => $results,
            'suggested' => $suggested,
            'did_you_mean' => $didYouMean
        ]);
    }
}
