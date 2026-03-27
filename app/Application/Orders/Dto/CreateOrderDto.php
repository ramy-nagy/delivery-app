<?php

namespace App\Application\Orders\Dto;

use App\Application\Dto\BaseDto;
use App\Domain\Shared\ValueObjects\Coordinate;
use App\Domain\Shared\ValueObjects\Money;
use App\Http\Requests\Order\PlaceOrderRequest;

class CreateOrderDto extends BaseDto
{
    public function __construct(
        public readonly int $customerId,
        public readonly int $restaurantId,
        public readonly array $items,
        public readonly Coordinate $deliveryLocation,
        public readonly Money $subtotal,
        public readonly Money $deliveryFee,
        public readonly Money $tax,
        public readonly ?string $notes = null,
    ) {}

    public function total(): Money
    {
        return $this->subtotal
            ->add($this->deliveryFee)
            ->add($this->tax);
    }

    public static function fromRequest(PlaceOrderRequest $request): self
    {
        return new self(
            customerId: (int) $request->user()->id,
            restaurantId: (int) $request->validated('restaurant_id'),
            items: $request->validated('items'),
            deliveryLocation: Coordinate::fromArray(
                $request->validated('delivery_location')
            ),
            subtotal: Money::fromFloat((float) $request->input('subtotal', 0)),
            deliveryFee: Money::fromFloat((float) $request->input('delivery_fee', 0)),
            tax: Money::fromFloat((float) $request->input('tax', 0)),
            notes: $request->input('notes'),
        );
    }
}
