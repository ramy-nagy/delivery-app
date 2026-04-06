# FCM (Firebase Cloud Messaging) Notification System - Complete Documentation

## Overview
A production-ready FCM notification system for the DeliveryApp supporting notifications by **token** and by **topic** with comprehensive device management.

## Table of Contents
- [Installation & Setup](#installation--setup)
- [Database Migrations](#database-migrations)
- [Configuration](#configuration)
- [API Endpoints](#api-endpoints)
- [Usage Examples](#usage-examples)
- [Sending Notifications](#sending-notifications)
- [Topic Management](#topic-management)
- [Architecture & Components](#architecture--components)
- [Error Handling](#error-handling)
- [Monitoring & Cleanup](#monitoring--cleanup)

---

## Installation & Setup

### 1. Firebase Credentials Setup

Download your Firebase service account key:
1. Go to Firebase Console → Project Settings
2. Service Accounts tab
3. Generate New Private Key
4. Save as `firebase-credentials.json` in `storage/app/`

```bash
mv /path/to/firebase-credentials.json storage/app/firebase-credentials.json
```

### 2. Environment Variables

Add to `.env`:

```env
# Firebase Configuration
FCM_ENABLED=true
FCM_BATCH_SIZE=500
FCM_RETRY_ATTEMPTS=3
FCM_RETRY_DELAY=10
FCM_DEFAULT_TTL=86400
FCM_PRIORITY=high
FCM_STALE_TOKEN_DAYS=30

# Firebase Project Info (optional, from credentials file)
FIREBASE_PROJECT_ID=your-project-id
```

### 3. Run Migration

```bash
php artisan migrate
```

This creates the `user_device_tokens` table with:
- `user_id` - User relationship
- `fcm_token` - Firebase device token (unique)
- `device_id`, `device_type`, `device_name` - Device metadata
- `topics` - JSON array of subscribed topics
- `last_used_at`, `last_registered_at` - Timestamps
- `is_active` - Soft disable flag
- Indexes for optimal query performance

### 4. Database Schema

```sql
CREATE TABLE user_device_tokens (
    id BIGINT PRIMARY KEY,
    user_id BIGINT NOT NULL,
    fcm_token VARCHAR(255) UNIQUE NOT NULL,
    device_id VARCHAR(255),
    device_type VARCHAR(50), -- ios, android, web
    device_name VARCHAR(255),
    os_version VARCHAR(50),
    app_version VARCHAR(50),
    topics JSON,
    last_used_at TIMESTAMP,
    last_registered_at TIMESTAMP,
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    INDEXES:
    - user_id
    - fcm_token
    - device_id
    - (user_id, is_active)
    - last_used_at
);
```

---

## Configuration

All configs are in `config/services.php`:

```php
'firebase' => [
    'credentials_path' => env('FIREBASE_CREDENTIALS_PATH', storage_path('app/firebase-credentials.json')),
    'database_url' => env('FIREBASE_DATABASE_URL'),
    'project_id' => env('FIREBASE_PROJECT_ID'),
],

'fcm' => [
    'enabled' => env('FCM_ENABLED', true),
    'batch_size' => env('FCM_BATCH_SIZE', 500),
    'retry_attempts' => env('FCM_RETRY_ATTEMPTS', 3),
    'retry_delay' => env('FCM_RETRY_DELAY', 10),
    'default_ttl' => env('FCM_DEFAULT_TTL', 86400),
    'priority' => env('FCM_PRIORITY', 'high'),
    'stale_token_days' => env('FCM_STALE_TOKEN_DAYS', 30),
],
```

---

## API Endpoints

### Authentication Required
All endpoints require `Authorization: Bearer {token}` header

Base URL: `/api/v2/fcm`

#### 1. Register Device Token
```
POST /fcm/register
Content-Type: application/json

{
    "fcm_token": "eHy76...", // Required - from Firebase Client SDK
    "device_id": "unique-device-id",
    "device_type": "android", // ios, android, web
    "device_name": "Samsung Galaxy S21",
    "os_version": "12",
    "app_version": "1.2.3"
}

Response (201 Created):
{
    "success": true,
    "message": "Device registered successfully",
    "data": {
        "device_token_id": 1,
        "fcm_token": "eHy76...",
        "device_type": "android",
        "registered_at": "2026-04-06T10:30:00Z"
    }
}
```

#### 2. Get User's Devices
```
GET /fcm/devices

Response (200 OK):
{
    "success": true,
    "data": {
        "total": 3,
        "active": 2,
        "tokens": [
            {
                "id": 1,
                "device_type": "android",
                "device_name": "Samsung Galaxy S21",
                "app_version": "1.2.3",
                "is_active": true,
                "last_used_at": "2026-04-06T10:30:00Z",
                "last_registered_at": "2026-04-05T08:15:00Z",
                "created_at": "2026-04-05T08:15:00Z"
            }
        ]
    }
}
```

#### 3. Remove Device Token
```
DELETE /fcm/devices/{tokenId}

Response (200 OK):
{
    "success": true,
    "message": "Device token removed successfully"
}
```

#### 4. Logout All Devices
```
POST /fcm/logout-all-devices

Response (200 OK):
{
    "success": true,
    "message": "All devices logged out",
    "data": {
        "tokens_removed": 3
    }
}
```

#### 5. Subscribe to Topic
```
POST /fcm/subscribe
Content-Type: application/json

{
    "fcm_token": "eHy76...",
    "topic": "order_updates" // alphanumeric, dash, underscore, dot, tilde, percent
}

Response (200 OK):
{
    "success": true,
    "message": "Subscribed to topic successfully",
    "data": {
        "topic": "order_updates",
        "tokens_subscribed": 1,
        "status": "success"
    }
}
```

#### 6. Unsubscribe from Topic
```
POST /fcm/unsubscribe
Content-Type: application/json

{
    "fcm_token": "eHy76...",
    "topic": "order_updates"
}

Response (200 OK):
{
    "success": true,
    "message": "Unsubscribed from topic successfully",
    "data": {
        "topic": "order_updates",
        "tokens_unsubscribed": 1,
        "status": "success"
    }
}
```

#### 7. Send Test Notification
```
POST /fcm/test
Content-Type: application/json

{
    "title": "Test Title", // Optional
    "body": "Test Message" // Optional
}

Response (200 OK):
{
    "success": true,
    "message": "Test notifications sent",
    "data": {
        "successful": [
            {
                "token": "eHy76...",
                "message_id": "1234567890"
            }
        ],
        "failed": [],
        "total": 1
    }
}
```

### Admin Endpoints
Base URL: `/api/v2/admin/fcm` (requires admin/staff role)

#### 8. Get User's Device Tokens (Admin)
```
GET /admin/fcm/users/{userId}/devices

Response (200 OK):
{
    "success": true,
    "data": {
        "user_id": 5,
        "total": 2,
        "tokens": [
            "eHy76...",
            "abc123..."
        ]
    }
}
```

#### 9. Get FCM Statistics (Admin)
```
GET /admin/fcm/stats

Response (200 OK):
{
    "success": true,
    "data": {
        "total_tokens": 15420,
        "active_tokens": 14980,
        "inactive_tokens": 440,
        "by_device_type": {
            "android": 8500,
            "ios": 6200,
            "web": 720
        },
        "unique_users": 5200
    }
}
```

---

## Usage Examples

### Mobile App Setup (React Native / Flutter)

#### React Native
```javascript
import messaging from '@react-native-firebase/messaging';

// Get FCM token
messaging().getToken()
  .then(token => {
    // Send to backend
    fetch('https://api.deliveryapp.com/api/v2/fcm/register', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${authToken}`
      },
      body: JSON.stringify({
        fcm_token: token,
        device_id: getDeviceId(),
        device_type: 'android', // or 'ios'
        device_name: getDeviceName(),
        os_version: Platform.Version,
        app_version: '1.0.0'
      })
    });
  });

// Handle notifications when app is in foreground
messaging().onMessage(async (remoteMessage) => {
  console.log('Notification received:', remoteMessage);
  // Show local notification, update UI, etc.
});
```

#### Flutter
```dart
import 'firebase_messaging.dart';

Future<void> setupNotifications() async {
  FirebaseMessaging messaging = FirebaseMessaging.instance;
  
  String? token = await messaging.getToken();
  
  // Register device
  await dio.post(
    '/api/v2/fcm/register',
    data: {
      'fcm_token': token,
      'device_id': await getDeviceId(),
      'device_type': 'ios', // or 'android'
      'device_name': await getDeviceName(),
      'os_version': await getOSVersion(),
      'app_version': '1.0.0',
    },
  );
  
  // Handle foreground messages
  FirebaseMessaging.onMessage.listen((RemoteMessage message) {
    print('Message received: ${message.notification?.title}');
  });
}
```

### Backend Usage

#### Using Facade (Easy)
```php
use App\Facades\FCM;

// Send to single token
FCM::sendToToken(
    $fcmToken,
    'Order Confirmed',
    'Your order #1234 has been confirmed',
    ['order_id' => '1234', 'status' => 'confirmed']
);

// Send to multiple tokens
FCM::sendToTokens(
    $tokens,
    'New Order',
    'You have a new order!',
    ['order_id' => '1234']
);

// Send to topic
FCM::sendToTopic(
    'order_updates',
    'System Maintenance',
    'We are performing maintenance from 2-4 AM',
    ['duration' => '2 hours']
);

// Subscribe to topic
FCM::subscribeToTopic('order_notifications', [$token1, $token2]);

// Unsubscribe from topic
FCM::unsubscribeFromTopic('order_notifications', [$token1]);
```

#### Using Repository (Advanced)
```php
use App\Infrastructure\Repositories\FCMRepository;
use App\Services\FCMService;

class OrderService
{
    public function __construct(
        private FCMRepository $fcmRepository,
        private FCMService $fcmService
    ) {}

    public function notifyOrderStatus(Order $order, $status)
    {
        // Get all active devices for customer
        $tokens = $this->fcmRepository->getUserActiveTokens($order->customer)
            ->pluck('fcm_token')
            ->toArray();

        if (empty($tokens)) {
            return;
        }

        // Send via queue for performance
        SendFCMNotificationToTokens::dispatch(
            $tokens,
            'Order ' . $status,
            "Your order #{$order->id} is now {$status}",
            [
                'order_id' => $order->id,
                'status' => $status,
                'action' => 'order_detail'
            ]
        );
    }
}
```

---

## Sending Notifications

### Method 1: Direct from Controller
```php
use App\Services\FCMService;

class OrderController extends Controller
{
    public function __construct(private FCMService $fcmService) {}

    public function notifyRestaurant(Order $order)
    {
        try {
            $messageId = $this->fcmService->sendToTopic(
                'restaurant_' . $order->restaurant_id,
                'New Order',
                'You have received order #' . $order->id,
                [
                    'order_id' => $order->id,
                    'total_amount' => $order->total_amount,
                    'action' => 'order_detail'
                ],
                [
                    'priority' => 'high',
                    'ttl' => 3600, // 1 hour
                    'tag' => 'new_order'
                ]
            );

            Log::info('Order notification sent', ['message_id' => $messageId]);
        } catch (Exception $e) {
            Log::error('Failed to send order notification', ['error' => $e->getMessage()]);
        }
    }
}
```

### Method 2: Queue Job (Recommended for High Volume)
```php
use App\Jobs\Notifications\SendFCMNotificationToTopic;
use App\Jobs\Notifications\SendFCMNotificationToTokens;

// Send to topic (async)
SendFCMNotificationToTopic::dispatch(
    'driver_available_orders',
    'New Order Available',
    'Order from ' . $order->restaurant->name,
    ['order_id' => $order->id]
)->onQueue('notifications');

// Send to multiple tokens (async with retry)
SendFCMNotificationToTokens::dispatch(
    $tokens,
    'Order Update',
    'Your order is being prepared',
    ['order_id' => $order->id],
    ['priority' => 'high']
)->delay(now()->addSeconds(5))
  ->onQueue('notifications');
```

### Notification Options
```php
$options = [
    // General
    'priority' => 'high', // high, normal
    'ttl' => 86400, // time to live in seconds
    'tag' => 'order_notification', // notification grouping
    'click_action' => 'ORDER_DETAIL', // action on tap

    // Android
    'android_priority' => 'high',
    'exclude_android' => false,

    // iOS
    'ios_badge' => '1',
    'exclude_ios' => false,

    // WebPush
    'exclude_webpush' => false,
];
```

---

## Topic Management

### Common Topics Pattern

```php
// Topic naming convention
'order_updates' // All orders
'order_' . $order->id // Specific order
'restaurant_' . $restaurant->id // Restaurant orders
'driver_available_orders' // Available orders for drivers
'driver_' . $driver->id // Specific driver
'zone_' . $zone->id // Delivery zone
'promo_' . $user_id // User-specific promotions
'system_notifications' // System-wide announcements
'app_updates' // App update announcements
```

### Subscribing Users (At Registration)
```php
// Customer registers
$user->deviceTokens()->create([
    'fcm_token' => $request->fcm_token,
    'device_type' => $request->device_type,
    'topics' => [
        'order_updates',
        'promo_' . $user->id,
        'system_notifications'
    ]
]);

// Driver registers
$driver->user->deviceTokens()->create([
    'fcm_token' => $request->fcm_token,
    'device_type' => $request->device_type,
    'topics' => [
        'driver_available_orders',
        'driver_' . $driver->id,
        'system_notifications'
    ]
]);
```

### Dynamic Topic Subscription
```php
use App\Infrastructure\Repositories\FCMRepository;

class DeliveryZoneController extends Controller
{
    public function __construct(private FCMRepository $fcmRepository) {}

    public function subscribeToZone(Request $request)
    {
        $tokens = $request->user()->deviceTokens
            ->pluck('fcm_token')
            ->toArray();

        $this->fcmRepository->subscribeToTopic('zone_' . $request->zone_id, $tokens);

        return response()->json(['message' => 'Subscribed successfully']);
    }
}
```

---

## Architecture & Components

### 1. Models
- **UserDeviceToken** - Stores FCM tokens with metadata and subscription info

### 2. Services
- **FCMService** - Core service for sending notifications
  - `sendToToken($token, $title, $body, $data, $options)`
  - `sendToTokens($tokens, $title, $body, $data, $options)`
  - `sendToTopic($topic, $title, $body, $data, $options)`
  - `subscribeToTopic($topic, $tokens)`
  - `unsubscribeFromTopic($topic, $tokens)`

### 3. Repository
- **FCMRepository** - Database operations for device tokens
  - Device management (register, disable, remove)
  - Token queries and filtering
  - Topic subscription management
  - Statistics and analytics

### 4. Controllers
- **FCMNotificationController** - API endpoints
  - User device management
  - Topic subscription
  - Notifications sending
  - Admin statistics

### 5. Jobs
- **SendFCMNotificationToToken** - Queue job for single token
- **SendFCMNotificationToTokens** - Queue job for batch tokens
- **SendFCMNotificationToTopic** - Queue job for topic notification

### 6. Console Commands
- **fcm:cleanup-stale-tokens** - Remove inactive devices

---

## Error Handling

### Common Errors & Solutions

#### 1. Firebase Credentials Not Found
```
Error: Firebase credentials file not found
Solution: Ensure firebase-credentials.json is in storage/app/
```

#### 2. Invalid FCM Token
```
Error: Invalid registration token provided
Solution: Validate token length >= 100 chars using FCMService::isValidToken()
```

#### 3. Invalid Topic Name
```
Error: Malformed topic name
Solution: Use FCMService::isValidTopic() - only [a-zA-Z0-9-_.~%]
```

#### 4. Rate Limiting
```
Error: Rate limit exceeded
Solution: Use queue jobs with appropriate delays
```

#### 5. Token Expired/Unregistered
```
Error: Not Found. Could not authenticate the request
Solution: Device uninstalled app or revoked permission - mark token inactive
```

### Handling Errors in Code
```php
try {
    FCM::sendToToken($token, $title, $body, $data);
} catch (\Exception $e) {
    if (str_contains($e->getMessage(), 'Invalid registration token')) {
        // Mark token as inactive in database
        $this->fcmRepository->disableToken($token);
    } else if (str_contains($e->getMessage(), 'Not Found')) {
        // Remove token
        $this->fcmRepository->removeToken($token);
    }
    
    Log::error('FCM Error', ['error' => $e->getMessage()]);
    throw $e;
}
```

---

## Monitoring & Cleanup

### 1. Monitor Stale Tokens
Schedule daily cleanup:

```php
// app/Console/Kernel.php
protected function schedule(Schedule $schedule)
{
    $schedule->command('fcm:cleanup-stale-tokens')
        ->daily()
        ->at('02:00');
}
```

### 2. Monitor Token Health
```php
use App\Infrastructure\Repositories\FCMRepository;

$stats = (new FCMRepository())->getDeviceStats();

// Log stats for monitoring
Log::info('FCM Stats', $stats);

// Alert if too many inactive tokens
if ($stats['inactive_tokens'] / $stats['total_tokens'] > 0.3) {
    Notification::send($adminUser, new HighInactiveTokensAlert($stats));
}
```

### 3. Monitor Deliverability
```php
// Track notification delivery success
Log::channel('fcm')->info('Notification sent', [
    'message_id' => $messageId,
    'recipients' => count($tokens),
    'type' => 'order_update',
    'timestamp' => now()
]);
```

### 4. Email Alerts for Failures
```php
if (count($result['failed']) > 0) {
    Mail::send(new FCMNotificationFailed($result['failed']));
}
```

---

## Best Practices

1. **Always use queue jobs** for production notifications
2. **Validate tokens** before sending via `FCMService::isValidToken()`
3. **Validate topics** via `FCMService::isValidTopic()`
4. **Use retry mechanism** - jobs automatically retry 3 times
5. **Set appropriate TTL** - use `ttl` option for messages with time sensitivity
6. **Use topics** for broadcast messages (more reliable than multiple tokens)
7. **Clean stale tokens** regularly via scheduled command
8. **Group notifications** with `tag` option to prevent spam
9. **Monitor delivery** - log all sent messages
10. **Handle unsubscribes** - remove inactive tokens gracefully

---

## Testing

### Manual Testing via API
```bash
# 1. Register device
curl -X POST http://localhost:8000/api/v2/fcm/register \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "fcm_token": "eHy76...",
    "device_type": "android"
  }'

# 2. Send test notification
curl -X POST http://localhost:8000/api/v2/fcm/test \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Test",
    "body": "Test message"
  }'

# 3. Get devices
curl -X GET http://localhost:8000/api/v2/fcm/devices \
  -H "Authorization: Bearer {token}"
```

### Artisan Commands
```bash
# List all stale tokens
php artisan tinker
>>> App\Models\UserDeviceToken::where('is_active', false)->count()

# Check device stats
>>> App\Infrastructure\Repositories\FCMRepository::make()->getDeviceStats()

# Clean up manually
php artisan fcm:cleanup-stale-tokens
```

---

## Troubleshooting

### Notifications Not Received?
1. ✓ Check Firebase setup credentials
2. ✓ Verify tokens are registered in DB
3. ✓ Check if app has notification permissions
4. ✓ Monitor logs in `storage/logs/`
5. ✓ Test with `/api/v2/fcm/test` endpoint

### Tokens Not Persisting?
1. ✓ Ensure migration ran: `php artisan migrate`
2. ✓ Check user authentication
3. ✓ Verify FCM token format

### High Inactive Token Count?
1. ✓ Run cleanup command: `php artisan fcm:cleanup-stale-tokens`
2. ✓ Investigate in Firebase console
3. ✓ May indicate app uninstalls or permission revokes

---

## File Structure
```
app/
├── Services/
│   └── FCMService.php
├── Infrastructure/
│   └── Repositories/
│       └── FCMRepository.php
├── Models/
│   └── UserDeviceToken.php
├── Http/
│   └── Controllers/
│       └── FCMNotificationController.php
├── Jobs/
│   └── Notifications/
│       ├── SendFCMNotificationToToken.php
│       ├── SendFCMNotificationToTokens.php
│       └── SendFCMNotificationToTopic.php
├── Console/
│   └── Commands/
│       └── CleanupStaleDeviceTokens.php
├── Providers/
│   └── FCMServiceProvider.php
└── Facades/
    └── FCM.php

database/
└── migrations/
    └── 2026_04_06_000000_create_user_device_tokens_table.php

config/
└── services.php (updated)

routes/
└── api_v2.php (updated)

bootstrap/
└── providers.php (updated)
```

---

## Support & Issues
For issues or questions, check:
1. Firebase Console for credential/project issues
2. Application logs: `storage/logs/laravel.log`
3. Database: Check `user_device_tokens` table
4. Queue status: `php artisan queue:failed`
