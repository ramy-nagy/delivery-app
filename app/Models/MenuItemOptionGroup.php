<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MenuItemOptionGroup extends Model
{
    protected $fillable = [
        'menu_item_id',
        'name',
        'min_select',
        'max_select',
        'sort_order',
    ];

    public function menuItem(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class);
    }

    public function options(): HasMany
    {
        return $this->hasMany(MenuItemOption::class, 'menu_item_option_group_id');
    }
}
