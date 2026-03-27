<?php

namespace App\Http\Requests\Driver;

use App\Enums\VehicleType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDriverProfileRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'vehicle_type' => ['sometimes', 'nullable', Rule::enum(VehicleType::class)],
            'license_plate' => ['sometimes', 'nullable', 'string', 'max:32'],
        ];
    }
}
