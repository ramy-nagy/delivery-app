<?php

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutOrderRequest extends FormRequest
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
}
