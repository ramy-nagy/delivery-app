<?php

namespace Database\Factories;

use App\Models\Restaurant;
use App\Models\RestaurantCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Restaurant>
 */
class RestaurantFactory extends Factory
{
    protected $model = Restaurant::class;

    public function definition(): array
    {
        return [
            'restaurant_category_id' => RestaurantCategory::factory(),
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
