<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FCMNotificationController;

Route::middleware('auth:sanctum')->group(function (): void {
    // User FCM device token management
    Route::prefix('fcm')->group(function (): void {
        // Register device token
        Route::post('register', [FCMNotificationController::class, 'registerDeviceToken']);

        // Get user's devices
        Route::get('devices', [FCMNotificationController::class, 'getDeviceTokens']);

        // Remove device token
        Route::delete('devices/{tokenId}', [FCMNotificationController::class, 'removeDeviceToken']);

        // Logout from all devices
        Route::post('logout-all-devices', [FCMNotificationController::class, 'logoutAllDevices']);

        // Topic subscription
        Route::post('subscribe', [FCMNotificationController::class, 'subscribeTopic']);
        Route::post('unsubscribe', [FCMNotificationController::class, 'unsubscribeTopic']);

        // Test notification
        Route::post('test', [FCMNotificationController::class, 'sendTestNotification']);
    });
});

// Admin routes - require admin/staff role
Route::middleware(['auth:sanctum', 'role:admin|staff'])->group(function (): void {
    Route::prefix('admin/fcm')->group(function (): void {
        // Get user's device tokens
        Route::get('users/{userId}/devices', [FCMNotificationController::class, 'getUserDeviceTokens']);

        // Get FCM statistics
        Route::get('stats', [FCMNotificationController::class, 'getStats']);
    });
});
