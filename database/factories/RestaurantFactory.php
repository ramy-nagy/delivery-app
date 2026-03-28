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
        $egyptianNames = [
            'مطعم الكشري المصري',
            'مطعم أم حسن',
            'مطعم أبو طارق',
            'مطعم جاد',
            'مطعم حضرموت',
            'مطعم البرنس',
            'مطعم شيخ البلد',
            'مطعم زوبا',
            'مطعم الشبراوي',
            'مطعم مؤمن',
        ];
        $name = fake()->unique()->randomElement($egyptianNames);
        return [
            'restaurant_category_id' => RestaurantCategory::factory(),
            'name' => $name,
            'slug' => str($name)->slug(),
            'description' => 'أفضل الأكلات المصرية الأصيلة',
            'phone' => fake()->phoneNumber(),
            'is_open' => true,
            'minimum_order_cents' => 0,
            'latitude' => fake()->latitude(),
            'longitude' => fake()->longitude(),
        ];
    }
}
    public function configure()
    {
        return $this->afterCreating(function (\App\Models\Restaurant $restaurant) {
            // Attach logo and background images from local storage or URLs
            $logoPath = public_path('build/sample-restaurant-logo.png');
            $bgPath = public_path('build/sample-restaurant-bg.jpg');
            if (file_exists($logoPath)) {
                $restaurant->addMedia($logoPath)->toMediaCollection('logo');
            }
            if (file_exists($bgPath)) {
                $restaurant->addMedia($bgPath)->toMediaCollection('background');
            }
        });
    }
