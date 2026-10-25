<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\Admin\AbandonedCartController;
use App\Http\Controllers\Admin\AccountsController;
use App\Http\Controllers\Admin\AnalyticsController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ContactMessageController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\DeliveryMethodController;
use App\Http\Controllers\Admin\FeaturedProductController;
use App\Http\Controllers\Admin\HomepageBuilderController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\ProductBundleController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ReviewController as AdminReviewController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\StockController;
use App\Http\Controllers\Admin\TagController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\CustomerAuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OrderController as FrontendOrderController;
use App\Http\Controllers\ProductController as FrontendProductController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\WishlistController;
use Illuminate\Support\Facades\Route;

// ─── Frontend ───────────────────────────────────────────────
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::view('/about', 'about')->name('about');
Route::get('/shop', [FrontendProductController::class, 'index'])->name('shop.index');
Route::get('/contact', [ContactController::class, 'show'])->name('contact.show');
Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');
Route::get('/product/{slug}', [FrontendProductController::class, 'show'])->name('product.show');
Route::get('/product/{slug}/quickview', [FrontendProductController::class, 'quickview'])->name('product.quickview');
Route::get('/search/ajax', [FrontendProductController::class, 'ajaxSearch'])->name('search.ajax');
Route::post('/product/{product}/review', [ReviewController::class, 'store'])->name('product.review.store');

// Cart
Route::prefix('cart')->name('cart.')->group(function () {
    Route::get('/', [CartController::class, 'get'])->name('get');
    Route::post('/add', [CartController::class, 'add'])->name('add');
    Route::post('/remove', [CartController::class, 'remove'])->name('remove');
    Route::post('/update', [CartController::class, 'updateQuantity'])->name('update');
});

// Checkout
Route::prefix('checkout')->name('checkout.')->group(function () {
    Route::get('/delivery-methods', [CheckoutController::class, 'getDeliveryMethods'])->name('delivery_methods');
    Route::post('/express', [CheckoutController::class, 'express'])->name('express');
    Route::post('/cart', [CheckoutController::class, 'processCart'])->name('cart');
    Route::post('/apply-coupon', [CheckoutController::class, 'applyCoupon'])->name('apply_coupon');
    Route::post('/abandoned-cart', [CheckoutController::class, 'saveAbandonedCart'])->name('abandoned_cart');
});

// Public order confirmation
Route::get('/order/{order}', [FrontendOrderController::class, 'show'])->name('order.show');

// ─── Customer Auth (Phone + OTP) ────────────────────────────
Route::get('/login', fn () => redirect()->route('account.login'))->name('customer.login');

Route::prefix('account')->name('account.')->group(function () {
    Route::get('/login', [CustomerAuthController::class, 'showPhoneLogin'])->name('login');
    Route::post('/send-otp', [CustomerAuthController::class, 'sendOtp'])->name('send_otp');
    Route::post('/verify-otp', [CustomerAuthController::class, 'verifyOtp'])->name('verify_otp');
    Route::post('/logout', [CustomerAuthController::class, 'logout'])->name('logout');

    // Protected customer pages
    Route::middleware('auth')->group(function () {
        Route::get('/', fn () => redirect()->route('account.orders'))->name('index');
        Route::get('/orders', [AccountController::class, 'orders'])->name('orders');
        Route::get('/orders/{order}', [AccountController::class, 'orderDetail'])->name('order_detail');
        Route::post('/orders/{order}/cancel', [AccountController::class, 'cancelOrder'])->name('orders.cancel');

        // Addresses
        Route::get('/addresses', [AccountController::class, 'addresses'])->name('addresses');
        Route::post('/addresses', [AccountController::class, 'storeAddress'])->name('addresses.store');
        Route::put('/addresses/{address}', [AccountController::class, 'updateAddress'])->name('addresses.update');
        Route::delete('/addresses/{address}', [AccountController::class, 'destroyAddress'])->name('addresses.destroy');
        Route::patch('/addresses/{address}/default', [AccountController::class, 'setDefault'])->name('addresses.set_default');

        // Wishlist
        Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist');

        // AJAX: get default address for checkout autofill
        Route::get('/default-address', [AccountController::class, 'defaultAddress'])->name('default_address');
    });
});

Route::post('/wishlist/toggle', [WishlistController::class, 'toggle'])->name('wishlist.toggle');

// ─── Admin ──────────────────────────────────────────────────
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'authenticate']);
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::middleware(['auth', 'admin'])->group(function () {
        Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');

        // Products, Categories, Tags, Reviews
        Route::resource('products', ProductController::class);
        Route::resource('categories', CategoryController::class);
        Route::resource('tags', TagController::class);
        Route::patch('/reviews/{review}/status', [AdminReviewController::class, 'updateStatus'])->name('reviews.update_status');
        Route::resource('reviews', AdminReviewController::class);

        // Stock Management
        Route::get('/stock', [StockController::class, 'index'])->name('stock.index');
        Route::post('/stock', [StockController::class, 'store'])->name('stock.store');
        Route::post('/stock/{product}/quick-update', [StockController::class, 'quickUpdate'])->name('stock.quick_update');

        // Accounts & Sales Overview
        Route::get('/accounts', [AccountsController::class, 'index'])->name('accounts.index');
        Route::get('/accounts/export', [AccountsController::class, 'export'])->name('accounts.export');

        // Orders & Customers
        Route::post('/orders/bulk-action', [AdminOrderController::class, 'bulkAction'])->name('orders.bulk_action');
        Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');

        // Abandoned Carts
        Route::get('/abandoned-carts', [AbandonedCartController::class, 'index'])->name('abandoned-carts.index');
        Route::delete('/abandoned-carts/{id}', [AbandonedCartController::class, 'destroy'])->name('abandoned-carts.destroy');

        Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
        Route::resource('/users', UserController::class)->except(['show']);
        Route::get('/orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
        Route::put('/orders/{order}', [AdminOrderController::class, 'update'])->name('orders.update');

        // Analytics & Tracking
        Route::prefix('analytics')->name('analytics.')->group(function () {
            Route::get('/dashboard', [AnalyticsController::class, 'dashboard'])->name('dashboard');
            Route::get('/live-data', [AnalyticsController::class, 'liveData'])->name('live_data');
            Route::get('/overview', [AnalyticsController::class, 'overview'])->name('overview');
            Route::get('/products', [AnalyticsController::class, 'products'])->name('products');
            Route::get('/geography', [AnalyticsController::class, 'geography'])->name('geography');
            Route::get('/geography/live-locations', [AnalyticsController::class, 'liveLocations'])->name('live_locations');
            Route::get('/traffic', [AnalyticsController::class, 'traffic'])->name('traffic');
            Route::get('/funnel', [AnalyticsController::class, 'funnel'])->name('funnel');
        });
        Route::get('/orders/{order}/invoice', [AdminOrderController::class, 'invoice'])->name('orders.invoice');
        Route::get('/orders/{order}/invoice/print', [AdminOrderController::class, 'printView'])->name('orders.invoice.print');

        Route::patch('/orders/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.update_status');

        // Messages
        Route::resource('contact', ContactMessageController::class)->only(['index', 'show', 'destroy']);

        // Homepage Builder & Banners
        Route::resource('banners', BannerController::class)->except(['create', 'show', 'edit']);
        Route::get('/homepage-builder', [HomepageBuilderController::class, 'index'])->name('homepage_builder.index');
        Route::post('/homepage-builder/save', [HomepageBuilderController::class, 'save'])->name('homepage_builder.save');

        // Settings
        Route::prefix('settings')->name('settings.')->group(function () {
            Route::get('/api', [SettingController::class, 'api'])->name('api');
            Route::post('/api', [SettingController::class, 'updateApi'])->name('api.update');
            Route::get('/tracking', [SettingController::class, 'tracking'])->name('tracking');
            Route::post('/tracking', [SettingController::class, 'updateTracking'])->name('tracking.update');

            Route::get('/notifications', [SettingController::class, 'notifications'])->name('notifications');
            Route::post('/notifications', [SettingController::class, 'updateNotifications'])->name('notifications.update');
            Route::post('/fcm-token', [SettingController::class, 'updateFcmToken'])->name('fcm_token');

            Route::get('/checkout', [SettingController::class, 'checkout'])->name('checkout');
            Route::post('/checkout', [SettingController::class, 'updateCheckout'])->name('checkout.update');

            Route::get('/marketing', [SettingController::class, 'marketing'])->name('marketing');
            Route::post('/marketing', [SettingController::class, 'updateMarketing'])->name('marketing.update');
        });

        Route::resource('delivery-methods', DeliveryMethodController::class)->except(['create', 'show', 'edit']);

        Route::get('/featured-products', [FeaturedProductController::class, 'index'])->name('featured_products.index');
        Route::get('/featured-products/search', [FeaturedProductController::class, 'search'])->name('featured_products.search');
        Route::post('/featured-products/add', [FeaturedProductController::class, 'add'])->name('featured_products.add');
        Route::delete('/featured-products/{product}', [FeaturedProductController::class, 'remove'])->name('featured_products.remove');
        Route::get('/homepage-builder/search-products', [HomepageBuilderController::class, 'searchProducts'])->name('homepage_builder.search_products');
        Route::post('/homepage-builder/reorder', [HomepageBuilderController::class, 'updateOrder'])->name('homepage_builder.reorder');
        Route::resource('homepage-builder', HomepageBuilderController::class)->except(['create', 'show', 'edit'])->parameters([
            'homepage-builder' => 'homepage_section',
        ]);

        // Media
        Route::get('/media', [MediaController::class, 'index'])->name('media.index');
        Route::post('/media', [MediaController::class, 'store'])->name('media.store');
        Route::post('/media/bulk-delete', [MediaController::class, 'bulkDestroy'])->name('media.bulk_destroy');
        Route::post('/media/{media}/replace', [MediaController::class, 'replace'])->name('media.replace');
        Route::delete('/media/{media}', [MediaController::class, 'destroy'])->name('media.destroy');

        // Coupons
        Route::resource('coupons', CouponController::class)->except(['create', 'show', 'edit']);

        // Product Bundles
        Route::resource('bundles', ProductBundleController::class)->except(['show']);
    });
});
