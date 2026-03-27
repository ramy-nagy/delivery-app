<?php
namespace App\Domain\Delivery\Services;

use App\Domain\Shared\Services\CacheService;
use App\Domain\Delivery\Models\DeliveryZone;
use App\Domain\Shared\ValueObjects\Coordinate;

class DeliveryZoneService
{
    public function __construct(
        private CacheService $cache,
    ) {}

    public function getZoneByLocation(Coordinate $coordinate): ?DeliveryZone
    {
        return $this->cache->remember(
            key: "zone:{$coordinate->hash()}",
            callback: fn() => DeliveryZone::containing($coordinate)->first(),
            strategy: 'delivery_zones'
        );
    }

    public function refreshZone(int $zoneId): void
    {
        $this->cache->invalidate(['delivery-zones']);
    }
}
