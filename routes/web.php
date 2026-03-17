<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\App;

// Web Cron Link
Route::get('/cron/run', [\App\Http\Controllers\Admin\BackupController::class, 'runScheduler'])->name('cron.run');


// ===== Frontend Controllers =====
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\Frontend\ProfileController;
use App\Http\Controllers\OrderTrackingController;
use App\Http\Controllers\Auth\WhatsAppVerificationController;
use App\Http\Controllers\Api\WhatsAppWebhookController;
use App\Http\Controllers\Auth\OtpVerificationController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\ProductReviewController;
use App\Http\Controllers\Frontend\WalletController;
use App\Models\Product;
use App\Models\Order;
use App\Services\TelegramService;
use App\Http\Controllers\ProductRequestController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\Admin\ContactMessageController;


// ===== Admin Controllers =====
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\OrderController;
// ===== Admin Auth Routes =====
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/', function () {
        if (Auth::guard('admin')->check()) {
            return redirect()->route('admin.dashboard');
        }
        return redirect()->route('admin.login');
    });
    Route::get('login', [\App\Http\Controllers\Admin\Auth\LoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [\App\Http\Controllers\Admin\Auth\LoginController::class, 'login']);
    Route::post('logout', [\App\Http\Controllers\Admin\Auth\LoginController::class, 'logout'])->name('logout');
});

Route::middleware(['auth:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('orders/trash', [OrderController::class, 'trash'])->name('orders.trash');  // استخدم OrderController
    Route::resource('orders', OrderController::class);                                    // استخدم OrderController
});
use App\Http\Controllers\Admin\ImportController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\ManagerController;
use App\Http\Controllers\Admin\RoleController;
// use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\DiscountCodeController;
use App\Http\Controllers\Admin\HomepageSlideController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\BackupController;
use App\Http\Controllers\Admin\BlogCategoryController;
use App\Http\Controllers\Admin\PostController;
use App\Http\Controllers\Admin\CustomerTierController;
use App\Http\Controllers\Admin\ReviewAdminController;
use App\Http\Controllers\Admin\BarcodeController;
use App\Http\Controllers\Admin\PrimaryCategoryController;
use App\Http\Controllers\Admin\WalletController as AdminWalletController;

// ===== A) Authentication Routes =====
Auth::routes(['verify' => false]);

// ===== B) Maintenance & Language Switch =====
Route::get('/maintenance', fn() => view('frontend.maintenance'))->name('maintenance.page');

// TEMP DEBUG: remove after testing
Route::get('/debug-locale', function (Request $request) {
    $sessionLocale = Session::get('locale');
    if ($sessionLocale) {
        App::setLocale($sessionLocale);
    }
    return response()->json([
        'session_locale' => $sessionLocale,
        'cookie_locale' => $request->cookie('app_locale'),
        'app_locale_before' => app()->getLocale(),
        'set_to' => $sessionLocale ?? 'nothing',
        'app_locale_after' => App::getLocale(),
        'session_id' => Session::getId(),
        'all_session' => Session::all(),
        'middleware_ran' => true,
    ]);
});

// Replace your existing route with this:
Route::get('/lang/{locale}', function ($locale, Request $request) {
    $availableLocales = ['ar', 'en'];

    if (!in_array($locale, $availableLocales)) {
        abort(400);
    }

    Session::put('locale', $locale);
    App::setLocale($locale);

    // Use the ?from= param (current page URL sent by the switcher links)
    $from = $request->query('from', '');
    
    // Automatically remove index.php from the URL if it was injected
    $from = str_replace(['/index.php/', '/index.php'], ['/', ''], $from);

    // Safety: only redirect to same-host relative paths
    $skipPatterns = ['cart/count', 'wishlist/count', 'live-search', 'cart/content', '/lang/'];
    $safe = true;
    foreach ($skipPatterns as $pattern) {
        if (str_contains($from, $pattern)) { $safe = false; break; }
    }

    $redirectTo = ($safe && !empty($from)) ? $from : route('homepage');

    // Set a plain cookie as backup using '/' path so it works everywhere on localhost
    return redirect($redirectTo)->withCookie(
        cookie()->make('app_locale', $locale, 60 * 24 * 365, '/', null, false, false)
    );
})->name('language.switch');

// ===== C) Public Frontend Routes =====
Route::get('/', [PageController::class, 'homepage'])->name('homepage');
Route::get('/home', [PageController::class, 'homepage'])->name('homepage');
Route::get('/shop', [PageController::class, 'shop'])->name('shop');
Route::get('/product/{product}', [PageController::class, 'productDetail'])->name('product.detail');
Route::get('/search', [ProductController::class, 'search'])->name('products.search');
Route::get('/live-search', [ProductController::class, 'liveSearch'])->name('products.liveSearch');
Route::get('/privacy-policy', [PageController::class, 'privacyPolicy'])->name('privacy.policy');
Route::get('/about-us', fn() => view('frontend.pages.about-us'))->name('about.us');
Route::get('/order-method', fn() => view('frontend.pages.order-method'))->name('order.method');
Route::get('/faq', fn() => view('frontend.pages.faq'))->name('faq');
Route::get('/contact-us', function () {
    return view('frontend.pages.contact-us');
})->name('page.contact-us');

Route::post('/contact-us', [ContactController::class, 'store'])
    ->name('contact.submit');

Route::get('/contact-us/success', [ContactController::class, 'success'])
    ->name('contact.success');
Route::get('/categories', [PageController::class, 'categories'])->name('categories.index');
Route::get('/payment-and-delivery', [PageController::class, 'paymentAndDelivery'])->name('payment.delivery'); 
Route::get('/return-policy', [PageController::class, 'returnpolicy'])->name('return.policy'); 

// ===== Track Order =====
Route::get('/track-order', [OrderTrackingController::class, 'showTrackingForm'])->name('tracking.form');
Route::post('/track-order', [OrderTrackingController::class, 'trackOrder'])->name('tracking.track');

// Blog Routes
Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{post:slug}', [BlogController::class, 'show'])->name('blog.show');


// عامّة دائمًا (بدون لوجن)
Route::get('/b/{code}.png', [BarcodeController::class, 'qr'])
    ->name('barcodes.qr.png');

Route::get('/b/{code}', [BarcodeController::class, 'go'])
    ->name('barcodes.go');
    Route::post('/product-requests', [ProductRequestController::class, 'store'])
    ->name('product-requests.store');


Route::get('/api/primary-categories/{primaryCategory}/categories', [\App\Http\Controllers\PageController::class, 'getCategoriesForPrimaryCategory'])->name('api.primary-category.categories');

// ===== Cart =====
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add', [CartController::class, 'store'])->name('cart.store');
Route::post('/cart/add-async', [CartController::class, 'store'])->name('cart.store.async');
Route::post('/cart/update', [CartController::class, 'update'])->name('cart.update');
Route::post('/cart/remove', [CartController::class, 'destroy'])->name('cart.destroy');
Route::post('/cart/apply-discount', [CartController::class, 'applyDiscount'])->name('cart.applyDiscount');
Route::post('/cart/remove-discount', [CartController::class, 'removeDiscount'])->name('cart.removeDiscount');
Route::get('/cart/count', [CartController::class, 'count'])->name('cart.count');
Route::get('/cart/content', [CartController::class, 'content'])->name('cart.content');

// ===== OTP & WhatsApp Password Reset =====
Route::get('/password/reset-phone', [ForgotPasswordController::class, 'showResetPhoneForm'])->name('password.reset.custom');
Route::post('/password/send-otp', [ForgotPasswordController::class, 'sendOtp'])->name('password.send.otp');
Route::get('/password/reset-with-otp', [ForgotPasswordController::class, 'showResetFormWithOtp'])->name('password.reset.otp.form');
Route::post('/password/update-with-otp', [ForgotPasswordController::class, 'resetPasswordWithOtp'])->name('password.update.with.otp');
Route::get('/password/reset-phone', [ForgotPasswordController::class, 'showResetPhoneForm'])->name('password.reset.phone.form');
Route::get('/verify-otp', [OtpVerificationController::class, 'show'])->name('otp.verification.show');
Route::post('/verify-otp', [OtpVerificationController::class, 'verify'])->name('otp.verification.verify');
Route::post('/resend-otp', [OtpVerificationController::class, 'resend'])->name('otp.verification.resend');
Route::post('/receive-whatsapp', [WhatsAppWebhookController::class, 'handleIncomingMessage']);

// ===== WhatsApp Verification =====
Route::get('/whatsapp/verify', [WhatsAppVerificationController::class, 'show'])->name('whatsapp.verification.notice');
Route::post('/whatsapp/verify', [WhatsAppVerificationController::class, 'verify'])->name('whatsapp.verification.verify');
Route::get('/whatsapp-webhook', [WhatsAppWebhookController::class, 'verify']);
Route::post('/whatsapp-webhook', [WhatsAppWebhookController::class, 'handle']);

// ===== Authenticated User Routes =====
Route::middleware(['auth'])->group(function () {
    // Checkout
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
    Route::get('/checkout/success', [CheckoutController::class, 'success'])->name('checkout.success');
    Route::post('/checkout/address/store', [ProfileController::class, 'storeAddressAjax'])->name('checkout.address.store.ajax');

    // Wishlist
    Route::get('/wishlist', [FavoriteController::class, 'index'])->name('wishlist');
    Route::post('/wishlist/toggle/{product}', [FavoriteController::class, 'toggle'])->name('wishlist.toggle');
    Route::post('/wishlist/toggle-async/{product}', [FavoriteController::class, 'toggle'])->name('wishlist.toggle.async');
    Route::get('/wishlist/count', [FavoriteController::class, 'count'])->name('wishlist.count');

    // Profile
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.update-password');
    Route::get('/profile/orders', [ProfileController::class, 'orders'])->name('profile.orders');
    Route::get('/profile/orders/{order}', [ProfileController::class, 'showOrderDetails'])->name('profile.orders.show');
    Route::get('/account/wallet', [WalletController::class, 'index'])->name('wallet.index');
    
        Route::get('/my-notifications', [ProfileController::class, 'notifications'])->name('user.notifications.index');
    Route::post('/my-notifications/mark-as-read', [ProfileController::class, 'markAsRead'])->name('user.notifications.markAsRead');

    // Addresses
    Route::prefix('profile/addresses')->name('profile.addresses.')->group(function () {
        Route::get('/', [ProfileController::class, 'addresses'])->name('index');
        Route::get('/create', [ProfileController::class, 'createAddress'])->name('create');
        Route::post('/', [ProfileController::class, 'storeAddress'])->name('store');
        Route::delete('/{address}', [ProfileController::class, 'destroyAddress'])->name('destroy');
    });
    Route::get('/products/{product}/reviews', function (Product $product) {
    return redirect(url(route('product.detail', $product), false) . '#reviews');
})->name('products.reviews.index');
});

// ===== Product Reviews (AUTH but NOT admin) =====
Route::middleware('auth')->group(function () {
    Route::post('/products/{product}/reviews', [ProductReviewController::class, 'store'])
        ->name('products.reviews.store');

    // 👇 هذا هو مسار الحذف المطلوب خارج الـ admin
    Route::delete('/products/{product}/reviews/{review}', [ProductReviewController::class, 'destroy'])
        ->name('products.reviews.destroy');
});

// ===== E) Admin Panel Routes (Now Fully Protected) =====
Route::middleware(['auth:admin', 'can:view-admin-panel'])->prefix('admin')->name('admin.')->group(function () {

    // Admin Profile
    Route::get('/profile', [\App\Http\Controllers\Admin\ProfileController::class, 'show'])->name('profile');
    Route::patch('/profile/password', [\App\Http\Controllers\Admin\ProfileController::class, 'updatePassword'])->name('profile.password');

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Notifications (accessible to all admins)
    Route::get('/notifications', [\App\Http\Controllers\Admin\DashboardController::class, 'notifications'])->name('notifications.index');
    Route::post('/notifications/mark-as-read', [\App\Http\Controllers\Admin\DashboardController::class, 'markNotificationAsRead'])->name('notifications.markAsRead');
    Route::post('/notifications/mark-all-read', [\App\Http\Controllers\Admin\DashboardController::class, 'markAllNotificationsAsRead'])->name('notifications.markAllRead');
    Route::post('/notifications/clear-all', [\App\Http\Controllers\Admin\DashboardController::class, 'clearAllNotifications'])->name('notifications.clearAll');
    Route::post('push-subscriptions', [\App\Http\Controllers\Admin\PushSubscriptionController::class, 'update'])->name('push_subscriptions.update');
    Route::delete('push-subscriptions', [\App\Http\Controllers\Admin\PushSubscriptionController::class, 'destroy'])->name('push_subscriptions.destroy');
    
    // Backup Routes
    Route::prefix('backups')->name('backups.')->middleware('can:manage-backups')->group(function () {
        Route::get('/', [BackupController::class, 'index'])->name('index');
        Route::get('/create-db', [BackupController::class, 'createDbBackup'])->name('create-db');
        Route::get('/create-full', [BackupController::class, 'createFullBackup'])->name('create-full');
        Route::get('/download/{fileName}', [BackupController::class, 'download'])->name('download');
        Route::delete('/destroy', [BackupController::class, 'destroy'])->name('destroy');
        Route::post('/restore', [BackupController::class, 'restore'])->name('restore');
        Route::post('/upload', [BackupController::class, 'upload'])->name('upload');
        Route::get('/settings', [BackupController::class, 'settings'])->name('settings');
        Route::post('/settings', [BackupController::class, 'storeSettings'])->name('settings.store');
    });

    // Reports Routes
    Route::prefix('reports')->name('reports.')->middleware('can:view-reports')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/financial', [ReportController::class, 'financial'])->name('financial');
        Route::get('/financial/export', [ReportController::class, 'exportExcel'])->name('financial.export');
        Route::get('/inventory', [ReportController::class, 'inventory'])->name('inventory');
        Route::get('/customers', [ReportController::class, 'customers'])->name('customers');
        Route::get('/customers/export', [ReportController::class, 'exportCustomersExcel'])->name('customers.export');
        Route::get('/stock', [ReportController::class, 'stockReport'])->name('stock');
        Route::get('/stock/export', [ReportController::class, 'exportStockExcel'])->name('stock.export');
    });

    // Resource Routes
    Route::prefix('products')->name('products.')->group(function () {
        Route::get('/trash', [AdminProductController::class, 'trash'])->name('trash')->middleware('can:view-products');
        Route::post('/{id}/restore', [AdminProductController::class, 'restore'])->name('restore')->middleware('can:edit-products');
        Route::delete('/{id}/force-delete', [AdminProductController::class, 'forceDelete'])->name('forceDelete')->middleware('can:edit-products');
        Route::get('/export', [AdminProductController::class, 'exportExcel'])->name('export')->middleware('can:view-products');
        
        // Add the missing route
        Route::get('/import-quantity', [ImportController::class, 'importQuantityForm'])->name('import_quantity')->middleware('can:manage-imports');
        Route::post('/import-quantity', [ImportController::class, 'importQuantity'])->name('import_quantity.store')->middleware('can:manage-imports');
    });
    Route::resource('products', AdminProductController::class)->middleware('can:view-products');

    Route::prefix('categories')->name('categories.')->group(function () {
        Route::get('/trash', [CategoryController::class, 'trash'])->name('trash')->middleware('can:view-categories');
        Route::post('/{id}/restore', [CategoryController::class, 'restore'])->name('restore')->middleware('can:edit-categories');
        Route::delete('/{id}/force-delete', [CategoryController::class, 'forceDelete'])->name('forceDelete')->middleware('can:edit-categories');
        Route::get('/export', [CategoryController::class, 'exportExcel'])->name('export')->middleware('can:view-categories');
    });
    Route::resource('categories', CategoryController::class)->except(['show'])->middleware('can:view-categories');

    Route::get('/orders/export', [OrderController::class, 'exportExcel'])->name('orders.export')->middleware('can:view-orders');
    Route::resource('orders', OrderController::class)->middleware('can:view-orders');
    
    // Managers
    Route::get('managers/trash', [ManagerController::class, 'trash'])->name('managers.trash')->middleware('can:view-managers');
    Route::resource('managers', ManagerController::class)->middleware('can:view-managers');
    
    // Static user routes MUST come before the resource route
    Route::get('/users/inactive', [UserController::class, 'inactive'])->name('users.inactive')->middleware('can:view-users');
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/trash', [UserController::class, 'trash'])->name('trash')->middleware('can:view-users');
        Route::post('/{id}/restore', [UserController::class, 'restore'])->name('restore')->middleware('can:edit-users');
        Route::delete('/{id}/force-delete', [UserController::class, 'forceDelete'])->name('forceDelete')->middleware('can:edit-users');
    });
    Route::resource('users', UserController::class)->middleware('can:view-users');
    Route::get('users/{id}/addresses', [UserController::class, 'getAddresses'])->name('users.getAddress')->middleware('can:view-users');
    Route::resource('roles', RoleController::class)->middleware('can:view-roles');
    
    Route::prefix('discount-codes')->name('discount-codes.')->group(function () {
        Route::get('/trash', [DiscountCodeController::class, 'trash'])->name('trash')->middleware('can:view-discount-codes');
        Route::post('/{id}/restore', [DiscountCodeController::class, 'restore'])->name('restore')->middleware('can:edit-discount-codes');
        Route::delete('/{id}/force-delete', [DiscountCodeController::class, 'forceDelete'])->name('forceDelete')->middleware('can:edit-discount-codes');
        Route::get('/export', [DiscountCodeController::class, 'exportExcel'])->name('export')->middleware('can:view-discount-codes');
    });
    Route::resource('discount-codes', DiscountCodeController::class)->middleware('can:view-discount-codes');

    // Orders - Extended Routes
    Route::prefix('orders')->name('orders.')->group(function () {
        Route::get('/trash', [OrderController::class, 'trash'])->name('trash')->middleware('can:view-trashed-orders');
        Route::post('/trash/{id}/restore', [OrderController::class, 'restore'])->name('restore')->middleware('can:restore-orders');
        Route::delete('/trash/{id}/force-delete', [OrderController::class, 'forceDelete'])->name('forceDelete')->middleware('can:force-delete-orders');
        Route::post('/{order}/update-status', [OrderController::class, 'updateStatus'])->name('updateStatus')->middleware('can:edit-orders');
        Route::get('/{order}/invoice', [OrderController::class, 'invoice'])->name('invoice')->middleware('can:view-orders');
        Route::post('/apply-discount', [OrderController::class, 'applyDiscount'])->name('applyDiscount')->middleware('can:edit-orders');
    });

    // Product extras
    Route::post('products/{product}/toggle-status', [AdminProductController::class, 'toggleStatus'])->name('products.toggleStatus')->middleware('can:edit-products');
    Route::post('products/{product}/update-stock', [AdminProductController::class, 'updateStock'])->name('products.updateStock')->middleware('can:edit-products');
    Route::delete('/products/images/{image}', [AdminProductController::class, 'destroyImage'])->name('products.images.destroy')->middleware('can:edit-products');
    Route::delete('/products/{product}/images/{image}', [ProductController::class, 'destroyImage'])->name('admin.products.images.destroy')->middleware('can:edit-products');

    // Contact Messages
    Route::get('contact-messages', [ContactMessageController::class, 'index'])
        ->name('contact-messages.index')
        ->middleware('can:view-admin-panel');

    Route::get('contact-messages/{contactMessage}', [ContactMessageController::class, 'show'])
        ->name('contact-messages.show')
        ->middleware('can:view-admin-panel');

    Route::post('contact-messages/{contactMessage}/update-status', [ContactMessageController::class, 'updateStatus'])
        ->name('contact-messages.updateStatus')
        ->middleware('can:view-admin-panel');

    Route::delete('contact-messages/{contactMessage}', [ContactMessageController::class, 'destroy'])
        ->name('contact-messages.destroy')
        ->middleware('can:view-admin-panel');

    Route::post('managers/{manager}/restore', [ManagerController::class, 'restore'])->name('managers.restore')->middleware('can:edit-managers');
    Route::delete('managers/{manager}/force-delete', [ManagerController::class, 'forceDelete'])->name('managers.forceDelete')->middleware('can:edit-managers');
    Route::post('managers/{manager}/ban', [ManagerController::class, 'ban'])->name('managers.ban')->middleware('can:edit-managers');
    Route::post('managers/{manager}/unban', [ManagerController::class, 'unban'])->name('managers.unban')->middleware('can:edit-managers');
    Route::post('managers/{manager}/force-logout', [ManagerController::class, 'forceLogout'])->name('managers.forceLogout')->middleware('can:edit-managers');
    Route::post('managers/force-logout-all', [ManagerController::class, 'forceLogoutAll'])->name('managers.forceLogoutAll')->middleware('can:edit-managers');
    Route::post('managers/{manager}/impersonate', [ManagerController::class, 'impersonate'])->name('managers.impersonate')->middleware('can:impersonate-managers');
    Route::post('managers/stop-impersonate', [ManagerController::class, 'stopImpersonate'])->name('managers.stopImpersonate')->middleware('can:impersonate-managers');
    Route::get('managers/{manager}/orders', [ManagerController::class, 'showOrders'])->name('managers.orders')->middleware('can:view-managers');

    // Users
    Route::post('users/{user}/ban', [UserController::class, 'ban'])->name('users.ban')->middleware('can:edit-users');
    Route::post('users/{user}/direct-activate', [UserController::class, 'directActivate'])->name('users.directActivate')->middleware('can:edit-users');
    Route::post('users/{user}/unban', [UserController::class, 'unban'])->name('users.unban')->middleware('can:edit-users');
    Route::post('users/{user}/force-logout', [UserController::class, 'forceLogout'])->name('users.forceLogout')->middleware('can:edit-users');
    Route::post('users/force-logout-all', [UserController::class, 'forceLogoutAll'])->name('users.forceLogoutAll')->middleware('can:edit-users');
    Route::post('users/{user}/impersonate', [UserController::class, 'impersonate'])->name('users.impersonate')->middleware('can:impersonate-users');
    Route::post('users/stop-impersonate', [UserController::class, 'stopImpersonate'])->name('users.stopImpersonate')->middleware('can:impersonate-users');
    Route::get('users/{user}/orders', [UserController::class, 'showUserOrders'])->name('users.orders')->middleware('can:view-users');
    
    Route::post('wallet/{user}/deposit', [AdminWalletController::class, 'deposit'])
         ->name('wallet.deposit')
         ->middleware(\Spatie\Permission\Middleware\RoleMiddleware::class . ':Super-Admin|admin');
    Route::post('wallet/{user}/withdraw', [AdminWalletController::class, 'withdraw'])
         ->name('wallet.withdraw')
         ->middleware(\Spatie\Permission\Middleware\RoleMiddleware::class . ':Super-Admin|admin');

    // Discount Codes
    Route::post('discount-codes/{discount_code}/toggle-status', [DiscountCodeController::class, 'toggleStatus'])->name('discount-codes.toggleStatus')->middleware('can:edit-discount-codes');
    Route::get('/discount_codes/create', [DiscountCodeController::class, 'create'])->name('discount_codes.create')->middleware('can:edit-discount-codes');
    Route::get('/discount_codes/{discount_code}/edit', [DiscountCodeController::class, 'edit'])->name('discount_codes.edit')->middleware('can:edit-discount-codes');

    // Inventory & Settings
    // Route::get('inventory', [InventoryController::class, 'index'])->name('inventory.index')->middleware('can:view-inventory');
    // Route::post('inventory/{product}/update-stock', [InventoryController::class, 'updateStock'])->name('inventory.updateStock')->middleware('can:view-inventory');
    Route::get('settings', [SettingsController::class, 'index'])->name('settings.index')->middleware('can:edit-settings');
    Route::patch('settings', [SettingsController::class, 'update'])->name('settings.update')->middleware('can:edit-settings');
    Route::post('settings/logout-all', [SettingsController::class, 'logoutAllUsers'])->name('settings.logoutAll')->middleware('can:edit-settings');
    Route::resource('homepage-slides', HomepageSlideController::class)
        ->except(['show'])
        ->parameters(['homepage-slides' => 'homepageSlide'])
        ->middleware('can:edit-settings');
    Route::post('homepage-slides/{homepageSlide}/toggle-status', [HomepageSlideController::class, 'toggleStatus'])
        ->name('homepage-slides.toggle-status')
        ->middleware('can:edit-settings');
    Route::post('homepage-slides/{homepageSlide}/move/{direction}', [HomepageSlideController::class, 'move'])
        ->name('homepage-slides.move')
        ->middleware('can:edit-settings');

    // Import
    Route::prefix('imports')->name('imports.')->middleware('can:manage-imports')->group(function () {
        Route::get('/', [ImportController::class, 'index'])->name('index');
        Route::post('/', [ImportController::class, 'store'])->name('store');
        Route::post('/preview', [ImportController::class, 'preview'])->name('preview');
        Route::post('/import', [ImportController::class, 'import'])->name('import');
    });

    // Activity Log
    Route::get('/activity-log', [ActivityLogController::class, 'index'])->name('activity-log.index')->middleware('can:view-activity-log');
    
    // Customer Tiers
    Route::prefix('customer-tiers')->name('customer-tiers.')->middleware('can:manage-customer-tiers')->group(function () {
        Route::get('/', [CustomerTierController::class, 'index'])->name('index');
        Route::post('/', [CustomerTierController::class, 'update'])->name('update');
    });

    // Blog Management Routes
    Route::prefix('blog')->name('blog.')->middleware('can:manage-blog')->group(function () {
        Route::prefix('categories')->name('categories.')->group(function () {
            Route::get('/trash', [BlogCategoryController::class, 'trash'])->name('trash');
            Route::post('/{id}/restore', [BlogCategoryController::class, 'restore'])->name('restore');
            Route::delete('/{id}/force-delete', [BlogCategoryController::class, 'forceDelete'])->name('forceDelete');
        });
        Route::resource('categories', BlogCategoryController::class)->except(['show']);

        Route::prefix('posts')->name('posts.')->group(function () {
            Route::get('/trash', [PostController::class, 'trash'])->name('trash');
            Route::post('/{id}/restore', [PostController::class, 'restore'])->name('restore');
            Route::delete('/{id}/force-delete', [PostController::class, 'forceDelete'])->name('forceDelete');
        });
        Route::resource('posts', PostController::class);
    });
    
    // Review Management
    Route::delete('/products/{product}/reviews/{review}', [ProductReviewController::class, 'destroy'])
        ->name('products.reviews.destroy')->middleware('can:manage-reviews');
});

// These groups are left unchanged as per the request not to introduce other changes.
Route::prefix('admin')->name('admin.')->middleware(['auth:admin'])->group(function () {
    Route::get('barcodes',              [BarcodeController::class, 'index'])->name('barcodes.index');
    Route::get('barcodes/create',       [BarcodeController::class, 'create'])->name('barcodes.create');
    Route::post('barcodes',             [BarcodeController::class, 'store'])->name('barcodes.store');
    Route::get('barcodes/{barcode}/edit',[BarcodeController::class, 'edit'])->name('barcodes.edit');
    Route::put('barcodes/{barcode}',    [BarcodeController::class, 'update'])->name('barcodes.update');
    Route::delete('barcodes/{barcode}', [BarcodeController::class, 'destroy'])->name('barcodes.destroy');

    // تفعيل/إيقاف
    Route::post('barcodes/{barcode}/toggle', [BarcodeController::class, 'toggle'])
        ->name('barcodes.toggle');

    // (اختياري) إعادة توليد كود عشوائي جديد لنفس السجل
    Route::post('barcodes/{barcode}/regenerate', [BarcodeController::class, 'regenerate'])
        ->name('barcodes.regenerate');
});
Route::middleware(['auth:admin']) // أضف صلاحياتك مثل can:manage-catalog لو تريد
    ->prefix('admin')->name('admin.')
    ->group(function () {
        Route::prefix('primary-categories')->name('primary-categories.')->group(function () {
            Route::get('/trash', [PrimaryCategoryController::class, 'trash'])->name('trash');
            Route::post('/{id}/restore', [PrimaryCategoryController::class, 'restore'])->name('restore');
            Route::delete('/{id}/force-delete', [PrimaryCategoryController::class, 'forceDelete'])->name('forceDelete');
            Route::get('/export', [PrimaryCategoryController::class, 'exportExcel'])->name('export');
        });
        Route::resource('primary-categories', PrimaryCategoryController::class);
        Route::patch('primary-categories/{primary_category}/toggle', [PrimaryCategoryController::class, 'toggle'])
              ->name('primary-categories.toggle');
        Route::patch('primary-categories/{primary_category}/toggle', [PrimaryCategoryController::class, 'toggle'])
         ->name('primary-categories.toggle');
             Route::get('primary-categories/{primary_category}/children', [PrimaryCategoryController::class, 'children'])
         ->name('primary-categories.children');
    });

Route::get('/_test-telegram-order/{id}', function ($id, TelegramService $tg) {
    $order = \App\Models\Order::with(['items.product','customer'])->findOrFail($id);
    return $tg->sendOrder($order) ? 'OK' : 'FAILED';
})->middleware('auth');
