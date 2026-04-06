<?php

/**
 * FCM NOTIFICATION USAGE EXAMPLES
 * 
 * This file contains practical examples of how to use the FCM notification
 * system in various scenarios throughout the application.
 */

namespace App\Examples;

use App\Facades\FCM;
use App\Infrastructure\Repositories\FCMRepository;
use App\Jobs\Notifications\SendFCMNotificationToTopic;
use App\Jobs\Notifications\SendFCMNotificationToTokens;
use App\Models\Order;
use App\Models\Driver;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * EXAMPLE 1: Order Notifications
 * Notify customer when order status changes
 */
class OrderNotificationExample
{
    public function notifyOrderConfirmed(Order $order)
    {
        // Send to user's devices
        SendFCMNotificationToTopic::dispatch(
            'order_' . $order->id,
            '✅ Order Confirmed',
            'Your order from ' . $order->restaurant->name . ' is confirmed',
            [
                'order_id' => (string)$order->id,
                'status' => 'confirmed',
                'restaurant' => $order->restaurant->name,
                'total' => (string)$order->total_amount,
                'action' => 'order_detail',
            ],
            [
                'priority' => 'high',
                'tag' => 'order_' . $order->id,
            ]
        );
    }

    public function notifyOrderPreparing(Order $order)
    {
        SendFCMNotificationToTopic::dispatch(
            'order_' . $order->id,
            '👨‍🍳 Order Preparing',
            'Your order is being prepared',
            [
                'order_id' => (string)$order->id,
                'status' => 'preparing',
                'action' => 'order_detail',
            ],
            ['tag' => 'order_' . $order->id]
        );
    }

    public function notifyOrderReady(Order $order)
    {
        SendFCMNotificationToTopic::dispatch(
            'order_' . $order->id,
            '📦 Order Ready for Pickup',
            'Your order is ready! Driver will pick it up soon',
            [
                'order_id' => (string)$order->id,
                'status' => 'ready',
                'action' => 'order_detail',
            ],
            ['priority' => 'high', 'tag' => 'order_' . $order->id]
        );
    }

    public function notifyOrderOnTheWay(Order $order)
    {
        SendFCMNotificationToTopic::dispatch(
            'order_' . $order->id,
            '🚗 Driver on Your Way',
            'Driver ' . $order->driver->user->name . ' is heading to you',
            [
                'order_id' => (string)$order->id,
                'status' => 'on_the_way',
                'driver_name' => $order->driver->user->name,
                'driver_id' => (string)$order->driver->id,
                'action' => 'track_order',
            ],
            ['priority' => 'high']
        );
    }

    public function notifyOrderDelivered(Order $order)
    {
        SendFCMNotificationToTopic::dispatch(
            'order_' . $order->id,
            '✓ Order Delivered',
            'Your order has been delivered. Thank you!',
            [
                'order_id' => (string)$order->id,
                'status' => 'delivered',
                'action' => 'order_detail',
            ]
        );
    }

    public function notifyOrderCancelled(Order $order, string $reason = 'Customer Request')
    {
        SendFCMNotificationToTopic::dispatch(
            'order_' . $order->id,
            '❌ Order Cancelled',
            'Order cancelled: ' . $reason,
            [
                'order_id' => (string)$order->id,
                'status' => 'cancelled',
                'reason' => $reason,
                'refund' => (string)$order->total_amount,
                'action' => 'order_detail',
            ]
        );
    }
}

/**
 * EXAMPLE 2: Restaurant Notifications
 * Notify restaurant owner about new orders
 */
class RestaurantNotificationExample
{
    public function __construct(private FCMRepository $fcmRepository) {}

    public function notifyNewOrder(Order $order)
    {
        $restaurant = $order->restaurant;
        
        // Get all active devices for restaurant staff
        $tokens = $this->fcmRepository->getTokensArrayForUsers([$restaurant->owner_id]);

        if (empty($tokens)) {
            Log::info('No active tokens for restaurant', ['restaurant_id' => $restaurant->id]);
            return;
        }

        SendFCMNotificationToTokens::dispatch(
            $tokens,
            '🔔 New Order Received',
            'Order #' . $order->id . ' for ' . ($order->total_amount) . ' from ' . $order->customer->name,
            [
                'order_id' => (string)$order->id,
                'customer' => $order->customer->name,
                'items_count' => $order->items()->count(),
                'total' => (string)$order->total_amount,
                'action' => 'restaurant_order_detail',
            ],
            ['priority' => 'high']
        )->onQueue('notifications');
    }

    public function notifyByTopic(Order $order)
    {
        // Alternative: Send to all users subscribed to restaurant topic
        SendFCMNotificationToTopic::dispatch(
            'restaurant_' . $order->restaurant_id,
            '🔔 New Order',
            'New order received!',
            ['order_id' => (string)$order->id],
            ['priority' => 'high']
        );
    }
}

/**
 * EXAMPLE 3: Driver Notifications
 * Notify drivers about available orders
 */
class DriverNotificationExample
{
    public function __construct(private FCMRepository $fcmRepository) {}

    public function notifyAvailableOrders(array $orderIds, int $zoneId)
    {
        // Get all active drivers in this zone
        $drivers = Driver::where('delivery_zone_id', $zoneId)
            ->where('is_active', true)
            ->get();

        $driverUserIds = $drivers->pluck('user_id')->toArray();

        // Get all their active tokens
        $tokens = $this->fcmRepository->getTokensArrayForUsers($driverUserIds);

        if (empty($tokens)) {
            return;
        }

        SendFCMNotificationToTokens::dispatch(
            $tokens,
            '📋 New Orders Available',
            count($orderIds) . ' new orders available in your area',
            [
                'orders_count' => count($orderIds),
                'zone_id' => (string)$zoneId,
                'action' => 'available_orders',
            ],
            ['priority' => 'high']
        );
    }

    public function notifyOrderClaimed(Driver $driver, Order $order)
    {
        // Get driver's devices
        $tokens = $this->fcmRepository->getUserActiveTokens($driver->user)
            ->pluck('fcm_token')
            ->toArray();

        if (empty($tokens)) {
            return;
        }

        SendFCMNotificationToTokens::dispatch(
            $tokens,
            '✓ Order Assigned',
            'Order #' . $order->id . ' assigned to you',
            [
                'order_id' => (string)$order->id,
                'customer_name' => $order->customer->name,
                'pickup_address' => $order->restaurant->address,
                'delivery_address' => $order->delivery_location,
                'action' => 'order_detail',
            ]
        );
    }

    public function notifyDriverApproaching(Order $order)
    {
        SendFCMNotificationToTopic::dispatch(
            'order_' . $order->id,
            '🚗 Driver Nearby',
            'Your driver is approaching your location',
            [
                'order_id' => (string)$order->id,
                'driver_name' => $order->driver->user->name,
                'eta' => '5 min',
            ],
            ['priority' => 'high']
        );
    }
}

/**
 * EXAMPLE 4: Promotional Notifications
 * Send promotions to specific users or groups
 */
class PromoNotificationExample
{
    public function __construct(private FCMRepository $fcmRepository) {}

    public function sendPromotionToUser(User $user, string $promoTitle, string $promoBody, string $promoCode)
    {
        $tokens = $this->fcmRepository->getUserActiveTokens($user)
            ->pluck('fcm_token')
            ->toArray();

        if (empty($tokens)) {
            return;
        }

        SendFCMNotificationToTokens::dispatch(
            $tokens,
            '🎉 ' . $promoTitle,
            $promoBody,
            [
                'promo_code' => $promoCode,
                'action' => 'apply_promo',
            ]
        );
    }

    public function sendPromotionByTopic(string $topic, string $title, string $body, array $data)
    {
        // Send to all users subscribed to a specific topic
        SendFCMNotificationToTopic::dispatch(
            $topic,
            $title,
            $body,
            $data
        );
    }

    public function sendWeekendPromo()
    {
        SendFCMNotificationToTopic::dispatch(
            'system_notifications',
            '🎉 Weekend Special',
            'Get 30% off on all orders this weekend!',
            ['action' => 'browse_restaurants']
        );
    }
}

/**
 * EXAMPLE 5: System & Maintenance Notifications
 * Send system-wide notifications
 */
class SystemNotificationExample
{
    public function sendMaintenanceAlert(string $startTime, string $endTime)
    {
        SendFCMNotificationToTopic::dispatch(
            'system_notifications',
            '⚠️ Scheduled Maintenance',
            "We'll be down for maintenance from $startTime to $endTime",
            [
                'start_time' => $startTime,
                'end_time' => $endTime,
                'action' => 'alert',
            ],
            ['priority' => 'high']
        );
    }

    public function sendAppUpdateNotification()
    {
        SendFCMNotificationToTopic::dispatch(
            'app_updates',
            '📱 Update Available',
            'A new version of the app is available. Please update to get the latest features.',
            [
                'version' => '1.2.0',
                'action' => 'open_store',
            ]
        );
    }

    public function sendEmergencyAlert(string $message)
    {
        SendFCMNotificationToTopic::dispatch(
            'system_notifications',
            '🚨 Emergency Alert',
            $message,
            ['action' => 'alert'],
            ['priority' => 'high', 'ttl' => 300] // 5 minutes TTL for urgent alerts
        );
    }
}

/**
 * EXAMPLE 6: Using Direct Service (No Queue)
 * When you need immediate delivery (not recommended for heavy load)
 */
class DirectNotificationExample
{
    public function sendUrgentNotification(string $token, string $title, string $body)
    {
        try {
            $messageId = FCM::sendToToken(
                $token,
                $title,
                $body,
                ['urgent' => 'true'],
                ['priority' => 'high']
            );

            Log::info('Urgent notification sent', ['message_id' => $messageId]);
        } catch (\Exception $e) {
            Log::error('Failed to send urgent notification', ['error' => $e->getMessage()]);
        }
    }

    public function sendDirectBatch(array $tokens, string $title, string $body)
    {
        try {
            $result = FCM::sendToTokens($tokens, $title, $body);

            Log::info('Batch sent', [
                'successful' => count($result['successful']),
                'failed' => count($result['failed']),
            ]);
        } catch (\Exception $e) {
            Log::error('Batch send failed', ['error' => $e->getMessage()]);
        }
    }
}

/**
 * EXAMPLE 7: Device Management with Tokens
 * Register, update, and manage device tokens
 */
class DeviceManagementExample
{
    public function __construct(private FCMRepository $fcmRepository) {}

    /**
     * When user logs in from new device
     */
    public function registerNewDevice(User $user, array $deviceInfo)
    {
        $deviceToken = $this->fcmRepository->registerDevice(
            $user,
            $deviceInfo['fcm_token'],
            [
                'device_id' => $deviceInfo['device_id'],
                'device_type' => $deviceInfo['device_type'],
                'device_name' => $deviceInfo['device_name'],
                'os_version' => $deviceInfo['os_version'],
                'app_version' => $deviceInfo['app_version'],
            ]
        );

        // Subscribe to default topics
        $this->fcmRepository->subscribeToTopic($deviceToken->fcm_token, 'order_updates');
        $this->fcmRepository->subscribeToTopic($deviceToken->fcm_token, 'promo_' . $user->id);

        return $deviceToken;
    }

    /**
     * Get all user devices
     */
    public function getUserDevices(User $user)
    {
        return $this->fcmRepository->getUserAllTokens($user);
    }

    /**
     * Handle logout from specific device
     */
    public function logoutDevice(int $tokenId)
    {
        $token = $this->fcmRepository->getTokenByValue($tokenId);
        if ($token) {
            $token->delete();
        }
    }

    /**
     * Handle logout from all devices
     */
    public function logoutAllDevices(User $user)
    {
        return $this->fcmRepository->removeAllUserTokens($user);
    }
}

/**
 * EXAMPLE 8: Topic Subscription Management
 * Manage user subscriptions to topics
 */
class TopicSubscriptionExample
{
    public function __construct(private FCMRepository $fcmRepository) {}

    /**
     * Subscribe user to restaurant updates when they bookmark it
     */
    public function subscribeToRestaurant(User $user, Restaurant $restaurant)
    {
        $tokens = $user->deviceTokens()
            ->where('is_active', true)
            ->pluck('fcm_token')
            ->toArray();

        $topic = 'restaurant_' . $restaurant->id;

        foreach ($tokens as $token) {
            $this->fcmRepository->subscribeToTopic($token, $topic);
        }
    }

    /**
     * Unsubscribe when user removes bookmark
     */
    public function unsubscribeFromRestaurant(User $user, Restaurant $restaurant)
    {
        $tokens = $user->deviceTokens()
            ->pluck('fcm_token')
            ->toArray();

        $topic = 'restaurant_' . $restaurant->id;

        foreach ($tokens as $token) {
            $this->fcmRepository->unsubscribeFromTopic($token, $topic);
        }
    }

    /**
     * Subscribe to zone when user sets delivery area
     */
    public function subscribeToDeliveryZone(User $user, int $zoneId)
    {
        $tokens = $user->deviceTokens()
            ->where('is_active', true)
            ->pluck('fcm_token')
            ->toArray();

        $topic = 'zone_' . $zoneId;

        foreach ($tokens as $token) {
            $this->fcmRepository->subscribeToTopic($token, $topic);
        }
    }
}

/**
 * EXAMPLE 9: Analytics & Monitoring
 * Track and monitor notification delivery
 */
class NotificationAnalyticsExample
{
    public function __construct(private FCMRepository $fcmRepository) {}

    /**
     * Get FCM statistics
     */
    public function getStats()
    {
        return $this->fcmRepository->getDeviceStats();
    }

    /**
     * Log notification event
     */
    public function logNotificationEvent(string $type, string $topic, int $recipientCount)
    {
        Log::channel('fcm_analytics')->info('Notification sent', [
            'type' => $type,
            'topic' => $topic,
            'recipients' => $recipientCount,
            'timestamp' => now(),
        ]);
    }

    /**
     * Monitor inactive tokens
     */
    public function checkInactiveTokens()
    {
        $stats = $this->fcmRepository->getDeviceStats();
        $inactiveRatio = $stats['inactive_tokens'] / $stats['total_tokens'];

        if ($inactiveRatio > 0.3) {
            Log::warning('High inactive token ratio', ['ratio' => $inactiveRatio, 'stats' => $stats]);
        }

        return $stats;
    }

    /**
     * Clean up stale tokens
     */
    public function cleanupStaleTokens()
    {
        $cleaned = $this->fcmRepository->cleanupStaleTokens();
        Log::info('Cleaned stale tokens', ['count' => $cleaned]);
        return $cleaned;
    }
}

/**
 * EXAMPLE 10: Error Handling & Retries
 * Proper error handling for production use
 */
class ErrorHandlingExample
{
    public function __construct(private FCMRepository $fcmRepository) {}

    /**
     * Send notification with error handling
     */
    public function safeSendNotification(User $user, string $title, string $body)
    {
        try {
            $tokens = $this->fcmRepository->getUserActiveTokens($user)
                ->pluck('fcm_token')
                ->toArray();

            if (empty($tokens)) {
                Log::warning('No active tokens for user', ['user_id' => $user->id]);
                return false;
            }

            SendFCMNotificationToTokens::dispatch($tokens, $title, $body)
                ->onQueue('notifications');

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send notification', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return false;
        }
    }

    /**
     * Validate token before sending
     */
    public function validateAndSend(string $token, string $title, string $body)
    {
        if (!FCM::isValidToken($token)) {
            Log::warning('Invalid FCM token format', ['token' => substr($token, 0, 20)]);
            return false;
        }

        try {
            FCM::sendToToken($token, $title, $body);
            return true;
        } catch (\Exception $e) {
            if (str_contains($e->getMessage(), 'Invalid registration token')) {
                $this->fcmRepository->disableToken($token);
            } else if (str_contains($e->getMessage(), 'Not Found')) {
                $this->fcmRepository->removeToken($token);
            }

            return false;
        }
    }

    /**
     * Validate topic before sending
     */
    public function validateAndSendToTopic(string $topic, string $title, string $body)
    {
        if (!FCM::isValidTopic($topic)) {
            Log::warning('Invalid FCM topic name', ['topic' => $topic]);
            return false;
        }

        try {
            SendFCMNotificationToTopic::dispatch($topic, $title, $body);
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send to topic', [
                'topic' => $topic,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
