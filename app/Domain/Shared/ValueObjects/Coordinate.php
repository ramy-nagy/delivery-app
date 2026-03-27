<?php

namespace App\Domain\Shared\ValueObjects;

class Coordinate
{
    public function __construct(
        public readonly float $latitude,
        public readonly float $longitude,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            (float) ($data['latitude'] ?? $data['lat'] ?? 0),
            (float) ($data['longitude'] ?? $data['lng'] ?? $data['lon'] ?? 0),
        );
    }

    public function toArray(): array
    {
        return [
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
        ];
    }
}
