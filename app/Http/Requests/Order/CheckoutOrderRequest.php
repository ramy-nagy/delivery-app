<?php

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutOrderRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'delivery_location.latitude' => ['required', 'numeric'],
            'delivery_location.longitude' => ['required', 'numeric'],
            'delivery_fee' => ['sometimes', 'numeric', 'min:0'],
            'tax' => ['sometimes', 'numeric', 'min:0'],
            'notes' => ['sometimes', 'nullable', 'string', 'max:2000'],
        ];
    }
}
