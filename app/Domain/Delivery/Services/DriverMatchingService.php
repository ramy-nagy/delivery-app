<?php

namespace App\Domain\Delivery\Services;

use App\Domain\Delivery\Models\Driver;
use App\Enums\DriverStatus;
use Illuminate\Support\Facades\Log;

class DriverMatchingService
{
    public function findAvailableDriver(int $orderId): ?Driver
    {
        $driver = Driver::query()
            ->where('status', DriverStatus::AVAILABLE)
            ->whereNotNull('verified_at')
            ->orderBy('id')
            ->first();

        if ($driver === null) {
            Log::debug('No online driver for order', ['order_id' => $orderId]);
        }

        return $driver;
    }
}
