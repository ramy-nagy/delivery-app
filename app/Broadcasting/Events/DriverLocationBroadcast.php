<?php
namespace App\Broadcasting\Events;

class DriverLocationBroadcast
{
    public $driverId;
    public $latitude;
    public $longitude;

    public function __construct($driverId, $latitude, $longitude)
    {
        $this->driverId = $driverId;
        $this->latitude = $latitude;
        $this->longitude = $longitude;
    }
}
