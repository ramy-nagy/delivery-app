<?php

namespace Tests\Unit\Application\Dto;

use App\Application\Orders\Dto\CreateOrderDto;
use App\Domain\Shared\ValueObjects\Coordinate;
use App\Domain\Shared\ValueObjects\Money;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CreateOrderDtoTest extends TestCase
{
    #[Test]
    public function total_sums_subtotal_delivery_and_tax(): void
    {
        $dto = new CreateOrderDto(
            customerId: 1,
            restaurantId: 2,
            items: [['menu_item_id' => 1, 'quantity' => 1]],
            deliveryLocation: new Coordinate(30.0, 31.0),
            subtotal: Money::fromFloat(10.00),
            deliveryFee: Money::fromFloat(2.00),
            tax: Money::fromFloat(1.00),
        );

        $this->assertSame(1300, $dto->total()->cents());
    }
}
