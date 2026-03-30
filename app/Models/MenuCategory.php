<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MenuCategory extends Model implements \Spatie\MediaLibrary\HasMedia
{
    use HasFactory;
    use \Spatie\MediaLibrary\InteractsWithMedia;

     /**
     * Register media collection for category image.
     */

    protected $fillable = ['name', 'slug', 'sort_order', 'image'];

    public function items(): HasMany
    {
        return $this->hasMany(MenuItem::class, 'category_id');
    }
}
