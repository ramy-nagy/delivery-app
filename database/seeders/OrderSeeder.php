<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Order;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $users = \App\Models\User::inRandomOrder()->take(20)->get();
        $restaurants = \App\Models\Restaurant::inRandomOrder()->take(10)->get();

        foreach (range(1, 20) as $i) {
            $user = $users->random();
            $restaurant = $restaurants->random();
            \App\Models\Order::factory()->create([
                'customer_id' => $user->id,
                'restaurant_id' => $restaurant->id,
            ]);
        }
    }
}
