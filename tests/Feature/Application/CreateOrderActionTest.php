<?php

namespace Tests\Feature\Application;

use App\Application\Orders\Contracts\CreateOrderActionInterface;
use App\Application\Orders\Dto\CreateOrderDto;
use App\Domain\Shared\ValueObjects\Coordinate;
use App\Domain\Shared\ValueObjects\Money;
use App\Models\MenuItem;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CreateOrderActionTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function create_order_action_persists_order_and_items(): void
    {
        $customer = User::factory()->create();
        $restaurant = Restaurant::factory()->create(['minimum_order_cents' => 0, 'is_open' => true]);
        $item = MenuItem::factory()->for($restaurant)->create(['price_cents' => 1000]);

        $dto = new CreateOrderDto(
            customerId: $customer->id,
            restaurantId: $restaurant->id,
            items: [['menu_item_id' => $item->id, 'quantity' => 1]],
            deliveryLocation: new Coordinate(30.0, 31.0),
            subtotal: Money::fromFloat(10.00),
            deliveryFee: Money::fromFloat(0),
            tax: Money::fromFloat(0),
        );

        $order = app(CreateOrderActionInterface::class)->execute($dto);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'customer_id' => $customer->id,
            'restaurant_id' => $restaurant->id,
        ]);
        $this->assertCount(1, $order->items);
    }
}
