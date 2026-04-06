# FCM Notification System - Setup Checklist

## Quick Setup (5-10 minutes)

### Step 1: Get Firebase Service Account Key ✓
- [ ] Go to [Firebase Console](https://console.firebase.google.com/)
- [ ] Select your project
- [ ] Go to Project Settings → Service Accounts
- [ ] Click "Generate New Private Key"
- [ ] Save as `storage/app/firebase-credentials.json`

### Step 2: Environment Configuration ✓
- [ ] Copy `.env.fcm.example` values to `.env`
- [ ] Set `FCM_ENABLED=true`
- [ ] Configure queue connection (database or redis)

```bash
cp .env.fcm.example .env.fcm.template
# Then merge the values into your .env
```

### Step 3: Database Setup ✓
- [ ] Run migrations
```bash
php artisan migrate
```

This creates:
- `user_device_tokens` table - stores FCM tokens
- `failed_jobs` table - tracks failed queue jobs
- `job_batches` table - batch job tracking

### Step 4: Bootstrap Registration ✓
- [ ] Verify `FCMServiceProvider` is registered in `bootstrap/providers.php`
- [ ] Check database migrations ran successfully

```bash
php artisan migrate:status
```

### Step 5: Test Setup ✓
- [ ] Verify credentials file exists
```bash
ls -la storage/app/firebase-credentials.json
```

- [ ] Test service initialization
```bash
php artisan tinker
>>> $fcm = app('fcm')
>>> // Should initialize without errors
```

### Step 6: Queue Configuration ✓
For development (database queue):
```bash
php artisan queue:work --queue=notifications
```

For production (Redis recommended):
```bash
# In your deployment, Redis queue should be running
redis-server
# And Laravel queue worker should be running
php artisan queue:work redis --queue=notifications
```

---

## File Structure Summary

```
✓ Database
  └─ migrations/
    ├─ 2026_04_06_000000_create_user_device_tokens_table.php
    └─ 2026_04_06_000001_create_failed_jobs_and_job_batches.php

✓ Application Code
  ├─ app/Services/
  │  └─ FCMService.php (Core FCM service)
  ├─ app/Infrastructure/Repositories/
  │  └─ FCMRepository.php (Database operations)
  ├─ app/Models/
  │  └─ UserDeviceToken.php (Token model)
  ├─ app/Http/Controllers/
  │  └─ FCMNotificationController.php (API routes)
  ├─ app/Jobs/Notifications/
  │  ├─ SendFCMNotificationToToken.php
  │  ├─ SendFCMNotificationToTokens.php
  │  └─ SendFCMNotificationToTopic.php
  ├─ app/Console/Commands/
  │  └─ CleanupStaleDeviceTokens.php
  ├─ app/Providers/
  │  └─ FCMServiceProvider.php
  └─ app/Facades/
    └─ FCM.php

✓ Configuration
  ├─ config/services.php (Updated with Firebase config)
  ├─ bootstrap/providers.php (Updated with FCMServiceProvider)
  └─ routes/api_v2.php (FCM API routes)

✓ Documentation
  ├─ docs/fcm-notifications-guide.md (Complete guide)
  ├─ docs/FCM_EXAMPLES.php (Usage examples)
  ├─ docs/FCM_SETUP_CHECKLIST.md (This file)
  └─ .env.fcm.example (Configuration template)

✓ User Model
  └─ app/Models/User.php (Added deviceTokens relationship)
```

---

## API Routes Summary

All routes require authentication (`auth:sanctum`)

### User Routes (Base: `/api/v2/fcm`)

```
POST   /fcm/register              - Register device token
GET    /fcm/devices               - Get user's devices
DELETE /fcm/devices/{tokenId}     - Remove device
POST   /fcm/logout-all-devices    - Logout all devices
POST   /fcm/subscribe             - Subscribe to topic
POST   /fcm/unsubscribe           - Unsubscribe from topic
POST   /fcm/test                  - Send test notification
```

### Admin Routes (Base: `/api/v2/admin/fcm`)

```
GET    /admin/fcm/users/{userId}/devices  - Get user's tokens
GET    /admin/fcm/stats                   - Get FCM statistics
```

---

## Usage Patterns

### 1. Send to Single Token
```php
use App\Services\FCMService;

FCM::sendToToken(
    $fcmToken,
    'Title',
    'Body',
    ['key' => 'value']
);
```

### 2. Send to Multiple Tokens
```php
FCM::sendToTokens(
    [$token1, $token2, $token3],
    'Title',
    'Body'
);
```

### 3. Send to Topic (Fastest)
```php
FCM::sendToTopic(
    'order_updates',
    'Title',
    'Body'
);
```

### 4. Queue Job (Async - Recommended)
```php
use App\Jobs\Notifications\SendFCMNotificationToTopic;

SendFCMNotificationToTopic::dispatch(
    'topic_name',
    'Title',
    'Body'
);
```

---

## Common Topic Names

```
order_{orderId}              - Specific order
restaurant_{restaurantId}    - Restaurant updates
driver_{driverId}            - Driver-specific
zone_{zoneId}               - Delivery zone
promo_{userId}              - User promotions
system_notifications        - System-wide
app_updates                 - App updates
order_updates               - All orders (broadcast)
driver_available_orders     - Available for drivers
```

---

## Testing

### Manual API Test
```bash
# Register device
curl -X POST http://localhost:8000/api/v2/fcm/register \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "fcm_token": "eHy76...",
    "device_type": "android"
  }'

# Send test notification
curl -X POST http://localhost:8000/api/v2/fcm/test \
  -H "Authorization: Bearer {token}"

# Get devices
curl -X GET http://localhost:8000/api/v2/fcm/devices \
  -H "Authorization: Bearer {token}"
```

### Artisan Testing
```bash
php artisan tinker

# Check service is registered
>>> $fcm = app('fcm')
>>> $fcm // Should show FCMService instance

# Show device stats
>>> app(App\Infrastructure\Repositories\FCMRepository::class)->getDeviceStats()

# Send test notification
>>> FCM::sendToTopic('test_topic', 'Test', 'Message body')
```

---

## Production Checklist

### Before Going Live
- [ ] Firebase credentials securely stored (not in git)
- [ ] Queue worker running (Redis recommended)
- [ ] Database migrations applied
- [ ] `.env` configured with `FCM_ENABLED=true`
- [ ] Test notifications work end-to-end
- [ ] Monitoring/alerting setup for failed jobs
- [ ] Scheduled task for stale token cleanup

### Scheduled Tasks
```php
// app/Console/Kernel.php
protected function schedule(Schedule $schedule)
{
    $schedule->command('fcm:cleanup-stale-tokens')
        ->daily()
        ->at('02:00'); // 2 AM daily
}
```

### Monitoring
- Monitor failed jobs: `SELECT * FROM failed_jobs`
- Check stale tokens: Monitor `last_used_at` column
- Track delivery: Check application logs
- Alert on high inactive ratio (>30%)

### Performance
- Use batch sends for multiple tokens
- Use topics for broadcast messages
- Queue all notifications (don't wait)
- Run queue worker with proper concurrency
- Use Redis queue for production

---

## Troubleshooting

### Error: Firebase credentials not found
```
Solution: Verify storage/app/firebase-credentials.json exists
```

### Error: SQLSTATE[42S02]: Table not found
```
Solution: Run php artisan migrate
```

### Notifications not received
1. Check Firebase Project ID in credentials
2. Verify token format (min 100 chars)
3. Check mobile app has notification permission
4. Test with /api/v2/fcm/test endpoint

### Queue jobs failing
```bash
# Check failed jobs
php artisan queue:failed

# Retry failed job
php artisan queue:retry {id}

# Purge failed jobs
php artisan queue:flush
```

---

## Performance Tuning

### High Volume Notifications
1. Use topics instead of multiple tokens
2. Implement batch processing
3. Use Redis queue (not database)
4. Run multiple queue workers
5. Monitor queue backlog

### Database Optimization
```php
// Indexes already in place:
- user_id
- fcm_token (unique)
- device_id
- (user_id, is_active)
- last_used_at
```

### Queue Configuration
```env
QUEUE_CONNECTION=redis
QUEUE_DRIVER=redis
```

---

## Documentation Files

1. **fcm-notifications-guide.md** - Complete technical guide
2. **FCM_EXAMPLES.php** - 10 practical usage examples
3. **FCM_SETUP_CHECKLIST.md** - This setup checklist
4. **.env.fcm.example** - Environment template

---

## Support Resources

- Firebase Docs: https://firebase.google.com/docs/messaging
- Laravel Queues: https://laravel.com/docs/queues
- FCM API Reference: https://firebase.google.com/docs/cloud-messaging/concept-options

---

## Next Steps

✓ Follow the Setup Checklist above
✓ Register device in your mobile app
✓ Test with `/api/v2/fcm/test` endpoint
✓ Integrate notifications into your business logic
✓ Monitor with stats endpoint
✓ Set up stale token cleanup cronjob

Good luck! 🚀
