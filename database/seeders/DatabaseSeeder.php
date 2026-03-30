<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RolePermissionSeeder::class);

        $this->call(MainCategorySeeder::class);
        $this->call(RestaurantSeeder::class);
        $this->call(MenuCategorySeeder::class);
        $this->call(MenuItemSeeder::class);
        $this->call(ShopSeeder::class);
        $this->call(CartSeeder::class);
        $this->call(OrderSeeder::class);
    }
}
