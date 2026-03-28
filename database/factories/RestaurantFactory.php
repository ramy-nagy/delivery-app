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
            'minimum_order_cents' => 0,
            'latitude' => fake()->latitude(),
            'longitude' => fake()->longitude(),
            'opening_hours' => [
                'saturday' => ['open' => '09:00', 'close' => '23:00'],
                'sunday' => ['open' => '09:00', 'close' => '23:00'],
                'monday' => ['open' => '09:00', 'close' => '23:00'],
                'tuesday' => ['open' => '09:00', 'close' => '23:00'],
                'wednesday' => ['open' => '09:00', 'close' => '23:00'],
                'thursday' => ['open' => '09:00', 'close' => '23:00'],
                'friday' => ['open' => '09:00', 'close' => '23:00'],
            ],
        ];
    }
    public function configure()
    {
        return $this->afterCreating(function (\App\Models\Restaurant $restaurant) {
            // Use random images from the internet for logo and background
            $logoUrl = 'https://source.unsplash.com/100x100/?restaurant,logo,food&sig=' . rand(1, 10000);
            $bgUrl = 'https://source.unsplash.com/600x300/?restaurant,background,food&sig=' . rand(1, 10000);
            try {
                $restaurant->addMediaFromUrl($logoUrl)->toMediaCollection('logo');
            } catch (\Exception $e) {}
            try {
                $restaurant->addMediaFromUrl($bgUrl)->toMediaCollection('background');
            } catch (\Exception $e) {}
        });
    }
}
