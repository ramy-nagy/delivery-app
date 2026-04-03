<?php

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;

class PlaceOrderRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'delivery_location' => ['sometimes', 'array'],
            'delivery_location.latitude' => ['required_with:delivery_location', 'numeric'],
            'delivery_location.longitude' => ['required_with:delivery_location', 'numeric'],
            'notes' => ['sometimes', 'nullable', 'string', 'max:2000'],
        ];
    }

    protected function passedValidation(): void
    {
        // Validation is handled in the controller's store method
        // since items come from the cart instead of the request
    }
}
