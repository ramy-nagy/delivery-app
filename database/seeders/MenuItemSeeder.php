<?php

namespace Database\Seeders;

use App\Models\MenuItem;
use App\Models\Restaurant;
use Illuminate\Database\Seeder;

class MenuItemSeeder extends Seeder
{
    public function run(): void
    {
        $categories = \App\Models\MenuCategory::all();
        $images = [
            public_path('images/food1.jpg'),
            public_path('images/food2.jpg'),
            public_path('images/food3.jpg'),
            public_path('images/food4.jpg'),
        ];
        Restaurant::all()->each(function ($restaurant) use ($categories, $images) {
            foreach ($categories as $category) {
                $items = \App\Models\MenuItem::factory()->count(3)->create([
                    'restaurant_id' => $restaurant->id,
                    'category_id' => $category->id,
                ]);
                foreach ($items as $item) {
                    $item->addMedia($images[array_rand($images)])
                        ->preservingOriginal()
                        ->toMediaCollection('image');
                }
            }
        });
    }
}
