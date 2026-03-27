<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MenuItemOption extends Model
{
    protected $fillable = [
        'menu_item_option_group_id',
        'name',
        'price_delta_cents',
        'sort_order',
    ];

    public function group(): BelongsTo
    {
        return $this->belongsTo(MenuItemOptionGroup::class, 'menu_item_option_group_id');
    }
}
