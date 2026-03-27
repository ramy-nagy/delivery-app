<?php

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        $subtotal = fake()->numberBetween(1000, 10000);
        $fee = 500;
        $tax = (int) round($subtotal * 0.1);

        return [
            'uuid' => (string) Str::uuid(),
            'customer_id' => User::factory(),
            'restaurant_id' => Restaurant::factory(),
            'driver_id' => null,
            'status' => OrderStatus::PENDING,
            'subtotal_cents' => $subtotal,
            'delivery_fee_cents' => $fee,
            'tax_cents' => $tax,
            'total_cents' => $subtotal + $fee + $tax,
            'notes' => null,
            'delivery_latitude' => fake()->latitude(),
            'delivery_longitude' => fake()->longitude(),
        ];
    }
}
