<?php

namespace App\Models;

use Database\Factories\RestaurantCategoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RestaurantCategory extends Model
{
    /** @use HasFactory<RestaurantCategoryFactory> */
    use HasFactory;

    protected $fillable = ['name', 'slug', 'sort_order'];

    public function restaurants(): HasMany
    {
        return $this->hasMany(Restaurant::class, 'restaurant_category_id');
    }
}
