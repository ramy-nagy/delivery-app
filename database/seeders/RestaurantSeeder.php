<?php

namespace Database\Seeders;

use App\Models\Restaurant;
use Illuminate\Database\Seeder;

class RestaurantSeeder extends Seeder
{
    public function run(): void
    {
        // Create 10 Egyptian restaurants with images and categories
        \App\Models\Restaurant::factory()->count(10)->create();
    }
}

