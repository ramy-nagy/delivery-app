<?php

namespace App\Domain\Shared\ValueObjects;

class Address
{
    public function __construct(
        public readonly string $line1,
        public readonly ?string $line2 = null,
        public readonly ?string $city = null,
        public readonly ?string $region = null,
        public readonly ?string $postalCode = null,
        public readonly ?string $country = null,
        public readonly ?Coordinate $coordinate = null,
    ) {}

    public static function fromArray(array $data): self
    {
        $coord = null;
        if (isset($data['latitude'], $data['longitude'])) {
            $coord = new Coordinate((float) $data['latitude'], (float) $data['longitude']);
        }

        return new self(
            line1: (string) ($data['line1'] ?? $data['address_line1'] ?? ''),
            line2: isset($data['line2']) ? (string) $data['line2'] : null,
            city: isset($data['city']) ? (string) $data['city'] : null,
            region: isset($data['region']) ? (string) $data['region'] : null,
            postalCode: isset($data['postal_code']) ? (string) $data['postal_code'] : null,
            country: isset($data['country']) ? (string) $data['country'] : null,
            coordinate: $coord,
        );
    }
}
