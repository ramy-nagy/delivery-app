<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DriverResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        if ($this->resource === null) {
            return [];
        }

        $status = $this->status instanceof \BackedEnum ? $this->status->value : $this->status;
        $vehicle = $this->vehicle_type === null
            ? null
            : ($this->vehicle_type instanceof \BackedEnum ? $this->vehicle_type->value : $this->vehicle_type);

        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'status' => $status,
            'vehicle_type' => $vehicle,
            'license_plate' => $this->license_plate,
            'verified_at' => $this->verified_at?->toIso8601String(),
            'last_latitude' => $this->last_latitude,
            'last_longitude' => $this->last_longitude,
            'user' => new UserResource($this->whenLoaded('user')),
        ];
    }
}
