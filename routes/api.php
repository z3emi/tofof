<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CustomerAuthController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\CheckoutController;
use App\Http\Controllers\Api\WishlistController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\OrdersController;
use App\Http\Controllers\Api\PinLoginController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\SettingController;
use App\Http\Controllers\Api\StoreController;
use Illuminate\Support\Facades\Route;

// Customer Mobile App Authentication (Public)
Route::prefix('auth')->group(function () {
    Route::post('/register', [CustomerAuthController::class, 'register']);
    Route::post('/login', [CustomerAuthController::class, 'login']);
    Route::post('/logout', [CustomerAuthController::class, 'logout'])->middleware('auth:sanctum');
    Route::get('/me', [CustomerAuthController::class, 'me'])->middleware('auth:sanctum');
});

// Employee/Admin Authentication
Route::match(['get', 'post'], '/login', PinLoginController::class);
Route::post('/employee/login', [AuthController::class, 'login']);

// Public Store Endpoints
Route::prefix('store')->group(function () {
    Route::get('/sliders', [StoreController::class, 'sliders']);
    Route::get('/ui-content', [StoreController::class, 'uiContent']);
    Route::get('/sections', [StoreController::class, 'sections']);
    Route::get('/categories', [StoreController::class, 'categories']);
    Route::get('/products', [StoreController::class, 'products']);
    Route::get('/products/{product}', [StoreController::class, 'product']);
    Route::get('/discount-codes', [StoreController::class, 'discountCodes']);
});


// Customer Mobile App - Authenticated Routes
Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {
    // Profile Management
    Route::prefix('profile')->group(function () {
        Route::get('/', [ProfileController::class, 'show']);
        Route::patch('/', [ProfileController::class, 'update']);
        Route::get('/orders', [ProfileController::class, 'orders']);
        Route::get('/orders/{orderId}', [ProfileController::class, 'showOrder']);
        Route::get('/addresses', [ProfileController::class, 'addresses']);
        Route::post('/addresses', [ProfileController::class, 'storeAddress']);
        Route::patch('/addresses/{addressId}', [ProfileController::class, 'updateAddress']);
        Route::delete('/addresses/{addressId}', [ProfileController::class, 'destroyAddress']);
        Route::get('/wallet', [ProfileController::class, 'wallet']);
        Route::get('/discounts', [ProfileController::class, 'discounts']);
        Route::get('/notifications', [ProfileController::class, 'notifications']);
    });

    // Cart Management
    Route::prefix('cart')->group(function () {
        Route::get('/', [CartController::class, 'index']);
        Route::post('/', [CartController::class, 'store']);
        Route::patch('/{selectionKey}', [CartController::class, 'update']);
        Route::delete('/{selectionKey}', [CartController::class, 'destroy']);
        Route::post('/discount', [CartController::class, 'applyDiscount']);
        Route::delete('/discount', [CartController::class, 'removeDiscount']);
        Route::delete('/', [CartController::class, 'clear']);
    });

    // Checkout
    Route::prefix('checkout')->group(function () {
        Route::get('/', [CheckoutController::class, 'index']);
        Route::post('/', [CheckoutController::class, 'store']);
    });

    // Orders Management
    Route::prefix('orders')->group(function () {
        Route::get('/', [OrdersController::class, 'index']);
        Route::get('/{orderId}', [OrdersController::class, 'show']);
        Route::post('/{orderId}/cancel', [OrdersController::class, 'cancel']);
    });

    // Wishlist/Favorites
    Route::prefix('wishlist')->group(function () {
        Route::get('/', [WishlistController::class, 'index']);
        Route::post('/', [WishlistController::class, 'store']);
        Route::post('/{productId}/toggle', [WishlistController::class, 'toggle']);
        Route::delete('/{productId}', [WishlistController::class, 'destroy']);
        Route::get('/count', [WishlistController::class, 'count']);
        Route::get('/{productId}/is-favorited', [WishlistController::class, 'isFavorited']);
    });

    // Store notifications for authenticated users
    Route::get('/store/notifications', [StoreController::class, 'notifications']);
});

// Admin/Employee - Authenticated Routes
Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
    Route::post('/employee/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'me']);

    Route::middleware(\App\Http\Middleware\CheckPermission::class)->group(function () {
        Route::get('/orders', [OrdersController::class, 'index'])->name('orders.index');
        Route::get('/orders/{id}', [OrdersController::class, 'show'])->name('orders.show');
        Route::get('/products', [ProductController::class, 'index'])->name('products.index');
        Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
        Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
    });
});
