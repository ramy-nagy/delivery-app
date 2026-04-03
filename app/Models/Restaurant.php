<?php

namespace App\Models;

use Database\Factories\RestaurantFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Restaurant extends Model implements HasMedia
{
    /** @use HasFactory<RestaurantFactory> */
    use HasFactory;

    use InteractsWithMedia;
    use SoftDeletes;

    /**
     * Register media collections for logo and background.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('logo')->singleFile();
        $this->addMediaCollection('background')->singleFile();
    }

    protected $fillable = [
        'owner_id',
        'restaurant_category_id',
        'main_category_id',
        'name',
        'slug',
        'description',
        'phone',
        'minimum_order_cents',
        'delivery_fee_cents',
        'latitude',
        'longitude',
        'opening_hours',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
            'opening_hours' => 'array',
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(RestaurantCategory::class, 'restaurant_category_id');
    }

    public function mainCategory(): BelongsTo
    {
        return $this->belongsTo(MenuCategory::class, 'main_category_id');
    }

    public function menuItems(): HasMany
    {
        return $this->hasMany(MenuItem::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Determine if the restaurant is open based on opening_hours and current time.
     */
    public function isOpen(): bool
    {
        if (empty($this->opening_hours) || !is_array($this->opening_hours)) {
            return false;
        }
        $now = now();
        $day = strtolower($now->format('l'));
        $todayHours = $this->opening_hours[$day] ?? null;
        if (!$todayHours || empty($todayHours['open']) || empty($todayHours['close'])) {
            return false;
        }
        $open = $now->copy()->setTimeFromTimeString($todayHours['open']);
        $close = $now->copy()->setTimeFromTimeString($todayHours['close']);
        // Handle overnight (close < open)
        if ($close->lessThanOrEqualTo($open)) {
            $close->addDay();
        }
        return $now->between($open, $close);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }
}
