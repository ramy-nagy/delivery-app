<?php

namespace Database\Factories;

use App\Models\RestaurantCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RestaurantCategory>
 */
class RestaurantCategoryFactory extends Factory
{
    protected $model = RestaurantCategory::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'name' => $name,
            'slug' => str($name)->slug(),
            'sort_order' => 0,
        ];
    }
}
