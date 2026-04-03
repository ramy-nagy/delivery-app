<?php

namespace App\Domain\Orders\Validators;

use App\Application\Orders\Dto\CreateOrderDto;
use App\Domain\Shared\Validators\Validator;
use App\Models\MenuItem;
use App\Models\Restaurant;

class CreateOrderValidator extends Validator
{
    public function __construct(private CreateOrderDto $dto) {}

    public function validate(): bool
    {
        // Items and restaurant validation is already done in the controller
        // Only validate minimum order amount here
        $this->validateMinimumOrder();

        return empty($this->errors);
    }

    private function validateMinimumOrder(): void
    {
        $restaurant = Restaurant::query()->find($this->dto->restaurantId);
        if (! $restaurant) {
            return;
        }

        $minimum = \App\Domain\Shared\ValueObjects\Money::fromCents($restaurant->minimum_order_cents);
        if ($this->dto->subtotal->lessThan($minimum)) {
            $this->addError(
                'subtotal',
                'Subtotal is below the restaurant minimum order amount'
            );
        }
    }
}
