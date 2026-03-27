<?php

namespace Database\Factories;

use App\Models\Shop;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Shop>
 */
class ShopFactory extends Factory
{
    protected $model = Shop::class;

    public function definition(): array
    {
        return [
            'owner_id' => User::factory(),
            'name' => fake()->company(),
            'slug' => fake()->unique()->slug(),
            'description' => fake()->sentence(),
            'phone' => fake()->phoneNumber(),
            'is_open' => true,
            'minimum_order_cents' => 0,
            'latitude' => fake()->latitude(),
            'longitude' => fake()->longitude(),
        ];
    }
}

