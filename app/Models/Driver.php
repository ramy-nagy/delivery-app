<?php

namespace App\Models;

use App\Enums\DriverStatus;
use App\Enums\VehicleType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Driver extends Model
{
    protected $fillable = [
        'user_id',
        'status',
        'vehicle_type',
        'license_plate',
        'verified_at',
        'last_latitude',
        'last_longitude',
    ];

    protected function casts(): array
    {
        return [
            'status' => DriverStatus::class,
            'vehicle_type' => VehicleType::class,
            'verified_at' => 'datetime',
            'last_latitude' => 'float',
            'last_longitude' => 'float',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(DriverDocument::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
