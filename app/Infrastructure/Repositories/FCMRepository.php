<?php

namespace App\Infrastructure\Repositories;

use App\Models\User;
use App\Models\UserDeviceToken;
use Illuminate\Database\Eloquent\Collection;

class FCMRepository
{
    /**
     * Register or update a device token.
     *
     * @param User|int $user User instance or ID
     * @param string $fcmToken FCM device token
     * @param array $deviceInfo Device information
     * @return UserDeviceToken
     */
    public function registerDevice(User|int $user, string $fcmToken, array $deviceInfo = []): UserDeviceToken
    {
        $userId = $user instanceof User ? $user->id : $user;

        $data = [
            'fcm_token' => $fcmToken,
            'last_registered_at' => now(),
            'is_active' => true,
        ];

        // Update or create with device info
        if (!empty($deviceInfo)) {
            $data = array_merge($data, [
                'device_id' => $deviceInfo['device_id'] ?? null,
                'device_type' => $deviceInfo['device_type'] ?? null,
                'device_name' => $deviceInfo['device_name'] ?? null,
                'os_version' => $deviceInfo['os_version'] ?? null,
                'app_version' => $deviceInfo['app_version'] ?? null,
            ]);
        }

        return UserDeviceToken::updateOrCreate(
            [
                'user_id' => $userId,
                'fcm_token' => $fcmToken,
            ],
            $data
        );
    }

    /**
     * Get active device tokens for a user.
     *
     * @param User|int $user User instance or ID
     * @return Collection<UserDeviceToken>
     */
    public function getUserActiveTokens(User|int $user): Collection
    {
        $userId = $user instanceof User ? $user->id : $user;

        return UserDeviceToken::forUser($userId)
            ->active()
            ->get();
    }

    /**
     * Get all device tokens for a user (including inactive).
     *
     * @param User|int $user User instance or ID
     * @return Collection<UserDeviceToken>
     */
    public function getUserAllTokens(User|int $user): Collection
    {
        $userId = $user instanceof User ? $user->id : $user;

        return UserDeviceToken::forUser($userId)->get();
    }

    /**
     * Get device token by token string.
     *
     * @param string $token FCM token
     * @return UserDeviceToken|null
     */
    public function getTokenByValue(string $token): ?UserDeviceToken
    {
        return UserDeviceToken::where('fcm_token', $token)->first();
    }

    /**
     * Disable a device token.
     *
     * @param string $token FCM token
     * @return bool
     */
    public function disableToken(string $token): bool
    {
        return (bool) UserDeviceToken::where('fcm_token', $token)
            ->update(['is_active' => false]);
    }

    /**
     * Remove a device token.
     *
     * @param string $token FCM token
     * @return bool
     */
    public function removeToken(string $token): bool
    {
        return (bool) UserDeviceToken::where('fcm_token', $token)->delete();
    }

    /**
     * Remove all tokens for a user (logout).
     *
     * @param User|int $user User instance or ID
     * @return int Number of deleted tokens
     */
    public function removeAllUserTokens(User|int $user): int
    {
        $userId = $user instanceof User ? $user->id : $user;

        return UserDeviceToken::forUser($userId)->delete();
    }

    /**
     * Subscribe device to a topic.
     *
     * @param string $token FCM token
     * @param string $topic Topic name
     * @return bool
     */
    public function subscribeToTopic(string $token, string $topic): bool
    {
        $deviceToken = $this->getTokenByValue($token);

        if (!$deviceToken) {
            return false;
        }

        $deviceToken->subscribeTo($topic);
        return true;
    }

    /**
     * Unsubscribe device from a topic.
     *
     * @param string $token FCM token
     * @param string $topic Topic name
     * @return bool
     */
    public function unsubscribeFromTopic(string $token, string $topic): bool
    {
        $deviceToken = $this->getTokenByValue($token);

        if (!$deviceToken) {
            return false;
        }

        $deviceToken->unsubscribeFrom($topic);
        return true;
    }

    /**
     * Get all tokens subscribed to a topic.
     *
     * @param string $topic Topic name
     * @return Collection<UserDeviceToken>
     */
    public function getTokensByTopic(string $topic): Collection
    {
        return UserDeviceToken::subscribedToTopic($topic)->get();
    }

    /**
     * Get token count for a topic.
     *
     * @param string $topic Topic name
     * @return int
     */
    public function getTopicSubscriberCount(string $topic): int
    {
        return UserDeviceToken::subscribedToTopic($topic)->count();
    }

    /**
     * Get active tokens by device type.
     *
     * @param string $deviceType Device type (ios, android, web)
     * @return Collection<UserDeviceToken>
     */
    public function getTokensByDeviceType(string $deviceType): Collection
    {
        return UserDeviceToken::byDeviceType($deviceType)
            ->active()
            ->get();
    }

    /**
     * Clean up stale tokens (not used for 30+ days).
     *
     * @return int Number of disabled tokens
     */
    public function cleanupStaleTokens(): int
    {
        $staleDate = now()->subDays(30);

        return UserDeviceToken::where('is_active', true)
            ->where(function ($query) use ($staleDate) {
                $query->whereNull('last_used_at')
                    ->orWhere('last_used_at', '<', $staleDate);
            })
            ->update(['is_active' => false]);
    }

    /**
     * Get device tokens for a user and specific topic.
     *
     * @param User|int $user User instance or ID
     * @param string $topic Topic name
     * @return Collection<UserDeviceToken>
     */
    public function getUserTokensForTopic(User|int $user, string $topic): Collection
    {
        $userId = $user instanceof User ? $user->id : $user;

        return UserDeviceToken::forUser($userId)
            ->active()
            ->whereJsonContains('topics', $topic)
            ->get();
    }

    /**
     * Update last used timestamp for a token.
     *
     * @param string $token FCM token
     * @return bool
     */
    public function updateLastUsed(string $token): bool
    {
        $deviceToken = $this->getTokenByValue($token);

        if (!$deviceToken) {
            return false;
        }

        $deviceToken->touchLastUsed();
        return true;
    }

    /**
     * Get device statistics.
     *
     * @return array
     */
    public function getDeviceStats(): array
    {
        return [
            'total_tokens' => UserDeviceToken::count(),
            'active_tokens' => UserDeviceToken::where('is_active', true)->count(),
            'inactive_tokens' => UserDeviceToken::where('is_active', false)->count(),
            'by_device_type' => UserDeviceToken::selectRaw('device_type, COUNT(*) as count')
                ->groupBy('device_type')
                ->pluck('count', 'device_type')
                ->toArray(),
            'unique_users' => UserDeviceToken::distinct('user_id')->count('user_id'),
        ];
    }

    /**
     * Get tokens for bulk notification.
     *
     * @param array $userIds Array of user IDs
     * @param bool $activeOnly Get only active tokens
     * @return array Tokens grouped by user ID
     */
    public function getTokensForUsers(array $userIds, bool $activeOnly = true): array
    {
        $query = UserDeviceToken::whereIn('user_id', $userIds);

        if ($activeOnly) {
            $query->active();
        }

        return $query->get()
            ->groupBy('user_id')
            ->map(fn ($tokens) => $tokens->pluck('fcm_token')->toArray())
            ->toArray();
    }

    /**
     * Get flat array of tokens for users.
     *
     * @param array $userIds Array of user IDs
     * @param bool $activeOnly Get only active tokens
     * @return array Flat array of FCM tokens
     */
    public function getTokensArrayForUsers(array $userIds, bool $activeOnly = true): array
    {
        $query = UserDeviceToken::whereIn('user_id', $userIds);

        if ($activeOnly) {
            $query->active();
        }

        return $query->pluck('fcm_token')->toArray();
    }

    /**
     * Batch update token metadata.
     *
     * @param array $tokens Array of tokens with metadata
     * @return int Number of updated records
     */
    public function batchUpdateTokens(array $tokens): int
    {
        $updated = 0;

        foreach ($tokens as $tokenData) {
            $token = $tokenData['fcm_token'] ?? null;
            if (!$token) {
                continue;
            }

            $updated += UserDeviceToken::where('fcm_token', $token)
                ->update(array_diff_key($tokenData, ['fcm_token' => null]));
        }

        return $updated;
    }
}
