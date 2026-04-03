<?php

use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\Auth\PasswordResetController;
use App\Http\Controllers\Api\V1\Auth\SocialAuthController;
use App\Http\Controllers\Api\V1\Customer\AddressController;
use App\Http\Controllers\Api\V1\Customer\CartController;
use App\Http\Controllers\Api\V1\Customer\OrderController as CustomerOrderController;
use App\Http\Controllers\Api\V1\Customer\ReviewController;
use App\Http\Controllers\Api\V1\Driver\DriverLocationController;
use App\Http\Controllers\Api\V1\Driver\DriverOrderController;
use App\Http\Controllers\Api\V1\Driver\DriverProfileController;
use App\Http\Controllers\Api\V1\Payment\PaymentController;
use App\Http\Controllers\Api\V1\Payment\WebhookController;
use App\Http\Controllers\Api\V1\Restaurant\MenuController;
use App\Http\Controllers\Api\V1\Restaurant\MenuItemController;
use App\Http\Controllers\Api\V1\Restaurant\RestaurantController;
use App\Http\Controllers\Api\V1\Restaurant\RestaurantOrderController;
use App\Http\Controllers\Api\V1\Shared\NotificationController;
use App\Http\Controllers\Api\V1\Shared\TrackingController;
use App\Http\Controllers\Api\V1\Shared\UploadController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\RestaurantCategoryController;
use App\Http\Controllers\Api\V1\MenuCategoryController;
use App\Http\Controllers\Api\V1\MainCategoryController;
use App\Http\Controllers\Api\V1\SliderController;

Route::prefix('auth')->group(function (): void {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login'])->middleware('throttle:10,1');
    Route::post('forgot-password', [PasswordResetController::class, 'sendResetLink']);
    Route::post('reset-password', [PasswordResetController::class, 'reset']);
    Route::get('social/{provider}', [SocialAuthController::class, 'redirect']);
    Route::post('social/{provider}/callback', [SocialAuthController::class, 'callback']);
});

Route::get('restaurants', [RestaurantController::class, 'index']);
Route::get('restaurants/{restaurant}', [RestaurantController::class, 'show']);
Route::get('restaurants/{restaurant}/menu-items', [MenuController::class, 'index']);
Route::get('restaurants/{restaurant}/delivery-fee', [RestaurantController::class, 'getDeliveryFee']);

Route::get('restaurant-categories', [RestaurantCategoryController::class, 'index']);

Route::get('track/{uuid}', [TrackingController::class, 'show'])
    ->where('uuid', '[0-9a-fA-F\\-]{36}');

Route::post('payments/webhooks/stripe', [WebhookController::class, 'stripe']);
Route::post('payments/webhooks/paymob', [WebhookController::class, 'paymob']);

Route::middleware('auth:sanctum')->group(function (): void {
    Route::prefix('auth')->group(function (): void {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('user', [AuthController::class, 'user']);
    });

    Route::get('notifications', [NotificationController::class, 'index']);
    Route::post('notifications/{id}/read', [NotificationController::class, 'markRead']);
    Route::post('notifications/read-all', [NotificationController::class, 'markAllRead']);

    Route::post('upload', [UploadController::class, 'store']);

    Route::middleware('role:customer')->prefix('customer')->group(function (): void {
        Route::get('cart', [CartController::class, 'show']);
        Route::put('cart', [CartController::class, 'sync']);
        Route::delete('cart/{menuItemId}', [CartController::class, 'destroy']);

        Route::get('orders', [CustomerOrderController::class, 'index']);
        Route::post('orders', [CustomerOrderController::class, 'store'])
            ->middleware('restaurant.open');
        Route::post('orders/checkout', [CustomerOrderController::class, 'checkout']);
        Route::get('orders/{order}', [CustomerOrderController::class, 'show']);
        Route::post('orders/{order}/cancel', [CustomerOrderController::class, 'cancel']);

        Route::apiResource('addresses', AddressController::class);

        Route::post('reviews', [ReviewController::class, 'store']);

        Route::post('payments/process', [PaymentController::class, 'process']);
    });

    Route::middleware('role:driver')->prefix('driver')->group(function (): void {
        Route::get('profile', [DriverProfileController::class, 'show']);
        Route::put('profile', [DriverProfileController::class, 'update']);

        Route::put('location', [DriverLocationController::class, 'update'])
            ->middleware('driver.verified');
        Route::patch('status', [DriverLocationController::class, 'updateStatus'])
            ->middleware('driver.verified');

        Route::middleware('driver.verified')->group(function (): void {
            Route::get('orders/available', [DriverOrderController::class, 'available']);
            Route::get('orders', [DriverOrderController::class, 'index']);
            Route::get('orders/{order}', [DriverOrderController::class, 'show']);
            Route::post('orders/{order}/claim', [DriverOrderController::class, 'claim']);
            Route::patch('orders/{order}/status', [DriverOrderController::class, 'updateStatus']);
        });
    });

    Route::middleware('role:restaurant')->prefix('restaurant')->group(function (): void {
        Route::get('me', [RestaurantController::class, 'me']);
        Route::put('me', [RestaurantController::class, 'update']);

        Route::get('orders', [RestaurantOrderController::class, 'index']);
        Route::get('orders/{order}', [RestaurantOrderController::class, 'show']);
        Route::patch('orders/{order}/status', [RestaurantOrderController::class, 'updateStatus']);

        Route::get('menu-items', [MenuItemController::class, 'index']);
        Route::post('menu-items', [MenuItemController::class, 'store']);
        Route::put('menu-items/{menuItem}', [MenuItemController::class, 'update']);
        Route::delete('menu-items/{menuItem}', [MenuItemController::class, 'destroy']);
    });
});

Route::apiResource('menu-categories', MenuCategoryController::class);

Route::apiResource('sliders', SliderController::class);

// Main Categories routes
Route::get('main-categories', [MainCategoryController::class, 'index']);
Route::get('main-categories/{menu_category}', [MainCategoryController::class, 'show']);
