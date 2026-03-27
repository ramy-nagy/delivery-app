<?php

namespace App\Models;

use Database\Factories\ShopFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Shop extends Model
{
    use HasFactory;
    use SoftDeletes;

    /** @use HasFactory<ShopFactory> */
    protected $fillable = [
        'owner_id',
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

    public function isOpen(): bool
    {
        return (bool) $this->is_open;
    }
}

