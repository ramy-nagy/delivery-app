<?php

namespace Database\Seeders;

use App\Models\MenuItem;
use App\Models\Restaurant;
use Illuminate\Database\Seeder;

class MenuItemSeeder extends Seeder
{
    public function run(): void
    {
        // For each restaurant, create 5 menu items
        Restaurant::all()->each(function ($restaurant) {
            MenuItem::factory()->count(5)->create([
                'restaurant_id' => $restaurant->id,
            ]);
        });
    }
}
