<?php

namespace App\Models;

use Database\Factories\MenuItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class MenuItem extends Model implements HasMedia
{
    /** @use HasFactory<MenuItemFactory> */
    use HasFactory;
    use SoftDeletes;
    use InteractsWithMedia;
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('image')->singleFile();
    }

    protected $fillable = [
        'restaurant_id',
        'category_id',
        'name',
        'description',
        'price_cents',
        'is_available',
        'sort_order',
    ];
    public function category(): BelongsTo
    {
        return $this->belongsTo(\App\Models\MenuCategory::class, 'category_id');
    }

    protected function casts(): array
    {
        return [
            'is_available' => 'boolean',
        ];
    }

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function optionGroups(): HasMany
    {
        return $this->hasMany(MenuItemOptionGroup::class);
    }
}
