<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class UserDeviceToken extends Model
{
    protected $table = 'user_device_tokens';

    protected $fillable = [
        'user_id',
        'fcm_token',
        'device_id',
        'device_type',
        'device_name',
        'os_version',
        'app_version',
        'topics',
        'last_used_at',
        'last_registered_at',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'topics' => 'array',
            'last_used_at' => 'datetime',
            'last_registered_at' => 'datetime',
            'is_active' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get the user that owns this device token.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to active tokens only.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get tokens by device type.
     */
    public function scopeByDeviceType(Builder $query, string $deviceType): Builder
    {
        return $query->where('device_type', $deviceType);
    }

    /**
     * Scope to get tokens by user.
     */
    public function scopeForUser(Builder $query, int|User $userId): Builder
    {
        $id = $userId instanceof User ? $userId->id : $userId;
        return $query->where('user_id', $id);
    }

    /**
     * Scope to get tokens subscribed to a topic.
     */
    public function scopeSubscribedToTopic(Builder $query, string $topic): Builder
    {
        return $query->where('is_active', true)
            ->whereJsonContains('topics', $topic);
    }

    /**
     * Subscribe device to a topic.
     */
    public function subscribeTo(string $topic): void
    {
        $topics = $this->topics ?? [];
        if (!in_array($topic, $topics)) {
            $topics[] = $topic;
            $this->update(['topics' => $topics]);
        }
    }

    /**
     * Unsubscribe device from a topic.
     */
    public function unsubscribeFrom(string $topic): void
    {
        $topics = $this->topics ?? [];
        $topics = array_filter($topics, fn($t) => $t !== $topic);
        $this->update(['topics' => array_values($topics)]);
    }

    /**
     * Update last used timestamp.
     */
    public function touchLastUsed(): void
    {
        $this->update(['last_used_at' => now()]);
    }

    /**
     * Check if token is stale (not used for 30 days).
     */
    public function isStale(): bool
    {
        return $this->last_used_at 
            ? $this->last_used_at->diffInDays(now()) > 30 
            : $this->created_at->diffInDays(now()) > 30;
    }
}
