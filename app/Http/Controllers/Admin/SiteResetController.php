<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AbandonedCart;
use App\Models\Category;
use App\Models\ContactMessage;
use App\Models\Media;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductBundle;
use App\Models\ProductVariation;
use App\Models\Review;
use App\Models\StockPurchase;
use App\Models\TrackingEvent;
use App\Models\TrackingSession;
use App\Models\TrackingVisitor;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class SiteResetController extends Controller
{
    /**
     * Display the site factory reset control panel (Superadmin only).
     */
    public function index()
    {
        $user = auth()->user();
        if (! $user || ! $user->isSuperAdmin()) {
            abort(403, 'Unauthorized. Only the Superadmin (User #1) has access to Site Factory Reset.');
        }

        $counts = [
            'orders' => Order::count(),
            'order_items' => OrderItem::count(),
            'abandoned_carts' => AbandonedCart::count(),
            'tracking_visitors' => TrackingVisitor::count(),
            'tracking_sessions' => TrackingSession::count(),
            'tracking_events' => TrackingEvent::count(),
            'products' => Product::count(),
            'variations' => ProductVariation::count(),
            'categories' => Category::count(),
            'bundles' => class_exists(ProductBundle::class) ? ProductBundle::count() : 0,
            'reviews' => Review::count(),
            'stock_purchases' => StockPurchase::count(),
            'media' => Media::count(),
            'customers' => User::where('id', '!=', 1)->where('role', 'customer')->count(),
            'messages' => ContactMessage::count(),
            'wishlists' => Wishlist::count(),
        ];

        return view('admin.settings.site-reset', compact('counts'));
    }

    /**
     * Execute the selective site reset.
     */
    public function reset(Request $request)
    {
        $user = auth()->user();
        if (! $user || ! $user->isSuperAdmin()) {
            abort(403, 'Unauthorized. Only the Superadmin (User #1) can execute a Site Reset.');
        }

        $request->validate([
            'confirm_phrase' => 'required|string',
            'password' => 'required|string',
        ]);

        if (strtoupper(trim($request->confirm_phrase)) !== 'RESET') {
            return back()->with('error', 'Confirmation failed: You must type "RESET" in capital letters to confirm.');
        }

        if (! Hash::check($request->password, $user->password)) {
            return back()->with('error', 'Authentication failed: Incorrect superadmin password provided.');
        }

        $deletedSummary = [];

        try {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');

            // 1. Orders, Order Items, Logs
            if ($request->boolean('wipe_orders')) {
                $ordersCount = Order::count();
                DB::table('order_items')->truncate();
                DB::table('order_logs')->truncate();
                DB::table('orders')->truncate();
                $deletedSummary[] = "{$ordersCount} Orders, Order Items & Logs deleted";
            }

            // 2. Abandoned Carts
            if ($request->boolean('wipe_abandoned_carts')) {
                $cartsCount = AbandonedCart::count();
                DB::table('abandoned_carts')->truncate();
                $deletedSummary[] = "{$cartsCount} Abandoned Cart sessions deleted";
            }

            // 3. Visitor Tracking & Analytics Data
            if ($request->boolean('wipe_tracking')) {
                $eventsCount = TrackingEvent::count();
                $sessionsCount = TrackingSession::count();
                $visitorsCount = TrackingVisitor::count();
                DB::table('tracking_events')->truncate();
                DB::table('tracking_sessions')->truncate();
                DB::table('tracking_visitors')->truncate();
                $deletedSummary[] = "Visitor tracking data wiped ({$visitorsCount} visitors, {$sessionsCount} sessions, {$eventsCount} events)";
            }

            // 4. Products & Variations
            if ($request->boolean('wipe_products')) {
                $prodCount = Product::count();
                DB::table('product_variations')->truncate();
                DB::table('product_tag')->truncate();
                DB::table('category_product')->truncate();
                DB::table('products')->truncate();
                $deletedSummary[] = "{$prodCount} Products & Variations deleted";
            }

            // 5. Categories
            if ($request->boolean('wipe_categories')) {
                $catCount = Category::count();
                DB::table('category_product')->truncate();
                DB::table('categories')->truncate();
                $deletedSummary[] = "{$catCount} Categories deleted";
            }

            // 6. Bundles
            if ($request->boolean('wipe_bundles') && Schema::hasTable('product_bundles')) {
                $bundlesCount = DB::table('product_bundles')->count();
                if (Schema::hasTable('bundle_product')) {
                    DB::table('bundle_product')->truncate();
                }
                DB::table('product_bundles')->truncate();
                $deletedSummary[] = "{$bundlesCount} Product Bundles deleted";
            }

            // 7. Product Reviews
            if ($request->boolean('wipe_reviews')) {
                $revCount = Review::count();
                DB::table('reviews')->truncate();
                $deletedSummary[] = "{$revCount} Product Reviews deleted";
            }

            // 8. Stock Purchases
            if ($request->boolean('wipe_stock_purchases')) {
                $stockCount = StockPurchase::count();
                DB::table('stock_purchases')->truncate();
                $deletedSummary[] = "{$stockCount} Stock Purchase records deleted";
            }

            // 9. Media & Uploaded Files (DB + Storage Disk)
            if ($request->boolean('wipe_media')) {
                $mediaItems = Media::all();
                $mediaCount = $mediaItems->count();

                foreach ($mediaItems as $media) {
                    if ($media->file_path && ! str_starts_with($media->file_path, 'http')) {
                        // Delete from public disk
                        if (Storage::disk('public')->exists($media->file_path)) {
                            Storage::disk('public')->delete($media->file_path);
                        }
                    }
                }

                DB::table('media')->truncate();
                if (Schema::hasTable('product_media')) {
                    DB::table('product_media')->truncate();
                }

                $deletedSummary[] = "{$mediaCount} Media assets and physical files deleted from storage";
            }

            // 10. Customer Accounts (NEVER deletes User ID 1 or admin accounts)
            if ($request->boolean('wipe_customers')) {
                $custCount = User::where('id', '!=', 1)->where('role', 'customer')->count();
                User::where('id', '!=', 1)->where('role', 'customer')->delete();
                $deletedSummary[] = "{$custCount} Customer accounts deleted (Superadmin preserved)";
            }

            // 11. Wishlists
            if ($request->boolean('wipe_wishlists')) {
                $wishCount = Wishlist::count();
                DB::table('wishlists')->truncate();
                $deletedSummary[] = "{$wishCount} Wishlist entries deleted";
            }

            // 12. Contact Messages
            if ($request->boolean('wipe_messages')) {
                $msgCount = ContactMessage::count();
                DB::table('contact_messages')->truncate();
                $deletedSummary[] = "{$msgCount} Contact Messages deleted";
            }

            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            // Clear application and view caches
            Artisan::call('optimize:clear');

            if (empty($deletedSummary)) {
                return back()->with('info', 'No items were selected for deletion. Your site data was preserved.');
            }

            return back()->with('success', 'Site reset executed successfully! '.implode(' • ', $deletedSummary));
        } catch (\Throwable $e) {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            return back()->with('error', 'Reset failed with error: '.$e->getMessage());
        }
    }
}
