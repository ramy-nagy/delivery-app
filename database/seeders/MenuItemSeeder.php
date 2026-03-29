<?php

namespace Database\Seeders;

use App\Models\MenuItem;
use App\Models\Restaurant;
use Illuminate\Database\Seeder;

class MenuItemSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'البيتزا',
            'السندوتشات',
            'المقبلات',
            'المشويات',
            'الأطباق الرئيسية',
            'الحلويات',
            'المشروبات',
        ];
        Restaurant::all()->each(function ($restaurant) use ($categories) {
            foreach ($categories as $category) {
                // لكل فئة، أضف 3 أصناف
                MenuItem::factory()->count(3)->create([
                    'restaurant_id' => $restaurant->id,
                    'category' => $category,
                ]);
            }
        });
    }
}
