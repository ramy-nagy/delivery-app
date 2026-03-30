<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Cart;
use App\Models\User;
use App\Models\Restaurant;

class CartSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::inRandomOrder()->take(10)->get();
        $restaurants = Restaurant::inRandomOrder()->take(5)->get();

        foreach ($users as $user) {
            $restaurant = $restaurants->random();
            Cart::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'restaurant_id' => $restaurant->id,
                    'items' => [],
                ]
            );
        }
    }
}
