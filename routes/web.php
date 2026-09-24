<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\CustomerAuthController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\DeliveryChargeController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\SliderController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Front-end Storefront Routes
Route::get('/', [StoreController::class, 'index'])->name('home');
Route::get('/categories', [StoreController::class, 'categories'])->name('categories');
Route::get('/shop', [StoreController::class, 'shop'])->name('shop');
Route::get('/product/{slug}', [StoreController::class, 'product'])->name('product.detail');

// Cart Routes
Route::get('/cart', [StoreController::class, 'cart'])->name('cart');
Route::post('/cart/add', [StoreController::class, 'addToCart'])->name('cart.add');
Route::post('/cart/update', [StoreController::class, 'updateCart'])->name('cart.update');
Route::post('/cart/remove', [StoreController::class, 'removeFromCart'])->name('cart.remove');

// Checkout & Order Placement
Route::get('/checkout', [StoreController::class, 'checkout'])->name('checkout');
Route::post('/checkout/order', [StoreController::class, 'placeOrder'])->name('checkout.place');
Route::get('/order-success/{order}', [StoreController::class, 'orderSuccess'])->name('order.success');

// Customer Authentication & Account
Route::middleware('guest')->group(function () {
    Route::get('/register', [CustomerAuthController::class, 'showRegister'])->name('customer.register');
    Route::post('/register', [CustomerAuthController::class, 'register'])->name('customer.register.submit');
    Route::get('/login', [CustomerAuthController::class, 'showLogin'])->name('customer.login');
    Route::post('/login', [CustomerAuthController::class, 'login'])->name('customer.login.submit');
    Route::get('/forgot-password', [CustomerAuthController::class, 'showForgotPassword'])->name('customer.forgot');
    Route::post('/forgot-password', [CustomerAuthController::class, 'sendResetLink'])->name('customer.forgot.submit');
    Route::get('/reset-password/{token}', [CustomerAuthController::class, 'showResetForm'])->name('customer.reset.form');
    Route::post('/reset-password', [CustomerAuthController::class, 'resetPassword'])->name('customer.reset.submit');
});

// Authenticated Customer Account Routes
Route::middleware(['customer'])->group(function () {
    Route::get('/account', [CustomerAuthController::class, 'dashboard'])->name('customer.dashboard');
    Route::get('/account/profile/edit', [CustomerAuthController::class, 'editProfile'])->name('customer.profile.edit');
    Route::put('/account/profile', [CustomerAuthController::class, 'updateProfile'])->name('customer.profile.update');
    Route::post('/logout', [CustomerAuthController::class, 'logout'])->name('customer.logout');
});

// Admin Panel Routes
Route::prefix('admin')->group(function () {
    // Auth Routes
    Route::get('/login', [AuthController::class, 'showLogin'])->name('admin.login');
    Route::post('/login', [AuthController::class, 'login'])->name('admin.login.submit');
    Route::post('/logout', [AuthController::class, 'logout'])->name('admin.logout');
    Route::get('/logout', [AuthController::class, 'logout']); // Fallback for simple link click

    // Dashboard & Admin Management (Middleware Protected)
    Route::middleware(['admin'])->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('admin.dashboard');
        Route::redirect('/dashboard', '/admin');
        
        // Category CRUD
        Route::get('/categories', [CategoryController::class, 'index'])->name('admin.categories.index');
        Route::get('/categories/create', [CategoryController::class, 'create'])->name('admin.categories.create');
        Route::post('/categories', [CategoryController::class, 'store'])->name('admin.categories.store');
        Route::get('/categories/{category}/edit', [CategoryController::class, 'edit'])->name('admin.categories.edit');
        Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('admin.categories.update');
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('admin.categories.destroy');

        // Product CRUD
        Route::get('/products', [ProductController::class, 'index'])->name('admin.products.index');
        Route::get('/products/create', [ProductController::class, 'create'])->name('admin.products.create');
        Route::post('/products', [ProductController::class, 'store'])->name('admin.products.store');
        Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('admin.products.edit');
        Route::put('/products/{product}', [ProductController::class, 'update'])->name('admin.products.update');
        Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('admin.products.destroy');
        Route::post('/products/reorder', [ProductController::class, 'updateOrder'])->name('admin.products.reorder');
        Route::get('/products/export-csv', [ProductController::class, 'exportCsv'])->name('admin.products.export-csv');
        Route::post('/products/import-csv', [ProductController::class, 'importCsv'])->name('admin.products.import-csv');

        // Delivery Charges CRUD
        Route::get('/delivery-charges', [DeliveryChargeController::class, 'index'])->name('admin.delivery-charges.index');
        Route::get('/delivery-charges/create', [DeliveryChargeController::class, 'create'])->name('admin.delivery-charges.create');
        Route::post('/delivery-charges', [DeliveryChargeController::class, 'store'])->name('admin.delivery-charges.store');
        Route::get('/delivery-charges/{deliveryCharge}/edit', [DeliveryChargeController::class, 'edit'])->name('admin.delivery-charges.edit');
        Route::put('/delivery-charges/{deliveryCharge}', [DeliveryChargeController::class, 'update'])->name('admin.delivery-charges.update');
        Route::delete('/delivery-charges/{deliveryCharge}', [DeliveryChargeController::class, 'destroy'])->name('admin.delivery-charges.destroy');

        // Orders Management
        Route::get('/orders', [OrderController::class, 'index'])->name('admin.orders.index');
        Route::get('/orders/{order}', [OrderController::class, 'show'])->name('admin.orders.show');
        Route::post('/orders/{order}/status', [OrderController::class, 'updateStatus'])->name('admin.orders.status');
        Route::post('/orders/{order}/send-to-steadfast', [OrderController::class, 'sendToSteadfast'])->name('admin.orders.send-to-steadfast');
        Route::get('/orders/export-csv', [OrderController::class, 'exportCsv'])->name('admin.orders.export-csv');
        Route::post('/orders/import-csv', [OrderController::class, 'importCsv'])->name('admin.orders.import-csv');
        Route::delete('/orders/{order}', [OrderController::class, 'destroy'])->name('admin.orders.destroy');

        // Customers Management
        Route::get('/customers', [CustomerController::class, 'index'])->name('admin.customers.index');
        Route::get('/customers/{customer}', [CustomerController::class, 'show'])->name('admin.customers.show');
        Route::delete('/customers/{customer}', [CustomerController::class, 'destroy'])->name('admin.customers.destroy');

        // Slider Management
        Route::get('/sliders', [SliderController::class, 'index'])->name('admin.sliders.index');
        Route::get('/sliders/create', [SliderController::class, 'create'])->name('admin.sliders.create');
        Route::post('/sliders', [SliderController::class, 'store'])->name('admin.sliders.store');
        Route::get('/sliders/{slider}/edit', [SliderController::class, 'edit'])->name('admin.sliders.edit');
        Route::put('/sliders/{slider}', [SliderController::class, 'update'])->name('admin.sliders.update');
        Route::delete('/sliders/{slider}', [SliderController::class, 'destroy'])->name('admin.sliders.destroy');

        // Settings
        Route::get('/settings', [SettingController::class, 'edit'])->name('admin.settings.edit');
        Route::put('/settings', [SettingController::class, 'update'])->name('admin.settings.update');
    });
});

