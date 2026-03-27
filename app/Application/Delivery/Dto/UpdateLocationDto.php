<?php
namespace App\Application\Delivery\Dto;

class UpdateLocationDto
{
    public int $driverId;
    public float $latitude;
    public float $longitude;

    public function __construct(int $driverId, float $latitude, float $longitude)
    {
        $this->driverId = $driverId;
        $this->latitude = $latitude;
        $this->longitude = $longitude;
    }
}
