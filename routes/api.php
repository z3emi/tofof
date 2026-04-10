<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\OrdersController;
use App\Http\Controllers\Api\PinLoginController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\SettingController;
use App\Http\Controllers\Api\StoreController;
use Illuminate\Support\Facades\Route;

Route::match(['get', 'post'], '/login', PinLoginController::class);
Route::post('/employee/login', [AuthController::class, 'login']);

Route::prefix('store')->group(function () {
    Route::get('/sliders', [StoreController::class, 'sliders']);
    Route::get('/ui-content', [StoreController::class, 'uiContent']);
    Route::get('/sections', [StoreController::class, 'sections']);
    Route::get('/categories', [StoreController::class, 'categories']);
    Route::get('/products', [StoreController::class, 'products']);
    Route::get('/discount-codes', [StoreController::class, 'discountCodes']);
});

Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
    Route::post('/employee/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'me']);
    Route::get('/store/notifications', [StoreController::class, 'notifications']);

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
