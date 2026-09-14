<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductController as FrontendProductController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\TagController;
use App\Http\Controllers\Admin\HomepageBuilderController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\OrderController as FrontendOrderController;
use App\Http\Controllers\CustomerAuthController;
use App\Http\Controllers\AccountController;

// ─── Frontend ───────────────────────────────────────────────
Route::get('/',         [HomeController::class, 'index'])->name('home');
Route::view('/about',  'about')->name('about');
Route::get('/shop',    [FrontendProductController::class, 'index'])->name('shop.index');
Route::get('/contact', [\App\Http\Controllers\ContactController::class, 'show'])->name('contact.show');
Route::post('/contact', [\App\Http\Controllers\ContactController::class, 'store'])->name('contact.store');
Route::get('/product/{slug}',           [FrontendProductController::class, 'show'])->name('product.show');
Route::get('/product/{slug}/quickview', [FrontendProductController::class, 'quickview'])->name('product.quickview');
Route::get('/search/ajax',              [FrontendProductController::class, 'ajaxSearch'])->name('search.ajax');
Route::post('/product/{product}/review', [\App\Http\Controllers\ReviewController::class, 'store'])->name('product.review.store');

// Cart
Route::prefix('cart')->name('cart.')->group(function () {
    Route::get('/',          [CartController::class, 'get'])->name('get');
    Route::post('/add',      [CartController::class, 'add'])->name('add');
    Route::post('/remove',   [CartController::class, 'remove'])->name('remove');
    Route::post('/update',   [CartController::class, 'updateQuantity'])->name('update');
});

// Checkout
Route::post('/checkout/express',       [CheckoutController::class, 'express'])->name('checkout.express');
Route::post('/checkout/apply-coupon',  [CheckoutController::class, 'applyCoupon'])->name('checkout.apply_coupon');

// Public order confirmation
Route::get('/order/{order}', [FrontendOrderController::class, 'show'])->name('order.show');

// ─── Customer Auth (Phone + OTP) ────────────────────────────
Route::get('/login', fn() => redirect()->route('account.login'))->name('customer.login');

Route::prefix('account')->name('account.')->group(function () {
    Route::get('/login',      [CustomerAuthController::class, 'showPhoneLogin'])->name('login');
    Route::post('/send-otp',  [CustomerAuthController::class, 'sendOtp'])->name('send_otp');
    Route::post('/verify-otp',[CustomerAuthController::class, 'verifyOtp'])->name('verify_otp');
    Route::post('/logout',    [CustomerAuthController::class, 'logout'])->name('logout');

    // Protected customer pages
    Route::middleware('auth')->group(function () {
        Route::get('/orders',          [AccountController::class, 'orders'])->name('orders');
        Route::get('/orders/{order}',  [AccountController::class, 'orderDetail'])->name('order_detail');

        // Addresses
        Route::get('/addresses',              [AccountController::class, 'addresses'])->name('addresses');
        Route::post('/addresses',             [AccountController::class, 'storeAddress'])->name('addresses.store');
        Route::put('/addresses/{address}',    [AccountController::class, 'updateAddress'])->name('addresses.update');
        Route::delete('/addresses/{address}', [AccountController::class, 'destroyAddress'])->name('addresses.destroy');
        Route::patch('/addresses/{address}/default', [AccountController::class, 'setDefault'])->name('addresses.set_default');

        // AJAX: get default address for checkout autofill
        Route::get('/default-address', [AccountController::class, 'defaultAddress'])->name('default_address');
    });
});

// ─── Admin ──────────────────────────────────────────────────
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login',  [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'authenticate']);
    Route::post('/logout',[AuthController::class, 'logout'])->name('logout');

    Route::middleware('auth')->group(function () {
        Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');

        // Products, Categories, Tags
        Route::resource('products',   ProductController::class);
        Route::resource('categories', CategoryController::class);
        Route::resource('tags',       TagController::class);

        // Orders & Customers
        Route::get('/orders',                         [AdminOrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{order}',                 [AdminOrderController::class, 'show'])->name('orders.show');
        Route::get('/orders/{order}/invoice',         [AdminOrderController::class, 'invoice'])->name('orders.invoice');
        Route::patch('/orders/{order}/status',        [AdminOrderController::class, 'updateStatus'])->name('orders.update_status');
        
        Route::get('/customers',                      [\App\Http\Controllers\Admin\CustomerController::class, 'index'])->name('customers.index');
        
        // Messages
        Route::resource('contact',                    \App\Http\Controllers\Admin\ContactMessageController::class)->only(['index', 'show', 'destroy']);

        // Homepage Builder & Banners
        Route::resource('banners', \App\Http\Controllers\Admin\BannerController::class)->except(['create', 'show', 'edit']);
        Route::get('/homepage-builder', [\App\Http\Controllers\Admin\HomepageBuilderController::class, 'index'])->name('homepage_builder.index');
        Route::post('/homepage-builder/save', [\App\Http\Controllers\Admin\HomepageBuilderController::class, 'save'])->name('homepage_builder.save');
        
        // Settings
        Route::get('/settings/api', [\App\Http\Controllers\Admin\SettingController::class, 'api'])->name('settings.api');
        Route::post('/settings/api', [\App\Http\Controllers\Admin\SettingController::class, 'updateApi'])->name('settings.api.update');
        
        Route::get('/featured-products',       [\App\Http\Controllers\Admin\FeaturedProductController::class, 'index'])->name('featured_products.index');
        Route::get('/featured-products/search',[\App\Http\Controllers\Admin\FeaturedProductController::class, 'search'])->name('featured_products.search');
        Route::post('/featured-products/add',  [\App\Http\Controllers\Admin\FeaturedProductController::class, 'add'])->name('featured_products.add');
        Route::delete('/featured-products/{product}', [\App\Http\Controllers\Admin\FeaturedProductController::class, 'remove'])->name('featured_products.remove');
        Route::get('/homepage-builder/search-products', [HomepageBuilderController::class, 'searchProducts'])->name('homepage_builder.search_products');
        Route::post('/homepage-builder/reorder',        [HomepageBuilderController::class, 'updateOrder'])->name('homepage_builder.reorder');
        Route::resource('homepage-builder', HomepageBuilderController::class)->except(['create', 'show', 'edit'])->parameters([
            'homepage-builder' => 'homepage_section'
        ]);

        // Media
        Route::get('/media',  [MediaController::class, 'index'])->name('media.index');
        Route::post('/media', [MediaController::class, 'store'])->name('media.store');

        // Coupons
        Route::resource('coupons', CouponController::class)->except(['create', 'show', 'edit']);
    });
});
