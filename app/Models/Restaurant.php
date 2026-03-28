    public function reviews(): HasMany
    {
        return $this->hasMany(\App\Models\Review::class);
    }
<?php

namespace App\Models;

use Database\Factories\RestaurantFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Restaurant extends Model implements HasMedia
{
    /** @use HasFactory<RestaurantFactory> */
    use HasFactory;
    use SoftDeletes;
    use InteractsWithMedia;
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
        'name',
        'slug',
        'description',
        'phone',
        'is_open',
        'minimum_order_cents',
        'latitude',
        'longitude',
    ];

    protected function casts(): array
    {
        return [
            'is_open' => 'boolean',
            'latitude' => 'float',
            'longitude' => 'float',
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

    public function menuItems(): HasMany
    {
        return $this->hasMany(MenuItem::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function isOpen(): bool
    {
        return (bool) $this->is_open;
    }
}
