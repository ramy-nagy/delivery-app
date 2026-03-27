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
        $this->validateRestaurant();
        $this->validateItems();
        $this->validateMinimumOrder();

        return empty($this->errors);
    }

    private function validateRestaurant(): void
    {
        $restaurant = Restaurant::query()->find($this->dto->restaurantId);

        if (! $restaurant) {
            $this->addError('restaurant', 'Restaurant not found');

            return;
        }

        if (! $restaurant->isOpen()) {
            $this->addError('restaurant', 'Restaurant is currently closed');
        }
    }

    private function validateItems(): void
    {
        if ($this->dto->items === []) {
            $this->addError('items', 'Order must contain at least one item');

            return;
        }

        foreach ($this->dto->items as $item) {
            $menuItemId = (int) ($item['menu_item_id'] ?? 0);
            $menuItem = MenuItem::query()->find($menuItemId);
            if (! $menuItem || $menuItem->restaurant_id !== $this->dto->restaurantId) {
                $this->addError('items', "Menu item {$menuItemId} is invalid for this restaurant");
            }
        }
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
