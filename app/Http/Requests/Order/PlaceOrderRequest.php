<?php

namespace App\Http\Requests\Order;

use App\Application\Orders\Dto\CreateOrderDto;
use App\Domain\Orders\Validators\CreateOrderValidator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class PlaceOrderRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'restaurant_id' => ['required', 'integer', 'exists:restaurants,id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.menu_item_id' => ['required', 'integer', 'exists:menu_items,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.options' => ['sometimes', 'array'],
            'delivery_location.latitude' => ['required', 'numeric'],
            'delivery_location.longitude' => ['required', 'numeric'],
            'subtotal' => ['required', 'numeric', 'min:0'],
            'delivery_fee' => ['sometimes', 'numeric', 'min:0'],
            'tax' => ['sometimes', 'numeric', 'min:0'],
            'notes' => ['sometimes', 'nullable', 'string', 'max:2000'],
        ];
    }

    protected function passedValidation(): void
    {
        $dto = CreateOrderDto::fromRequest($this);
        $validator = new CreateOrderValidator($dto);

        if (! $validator->validate()) {
            throw ValidationException::withMessages($validator->getErrors());
        }
    }
}
