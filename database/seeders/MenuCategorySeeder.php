<?php

namespace Database\Seeders;

use App\Models\MenuCategory;
use Illuminate\Database\Seeder;

class MenuCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'بيتزا', 'slug' => 'pizza', 'sort_order' => 1],
            ['name' => 'سندوتشات', 'slug' => 'sandwiches', 'sort_order' => 2],
            ['name' => 'مقبلات', 'slug' => 'starters', 'sort_order' => 3],
            ['name' => 'مشويات', 'slug' => 'grills', 'sort_order' => 4],
            ['name' => 'أطباق رئيسية', 'slug' => 'main-dishes', 'sort_order' => 5],
            ['name' => 'حلويات', 'slug' => 'desserts', 'sort_order' => 6],
            ['name' => 'مشروبات', 'slug' => 'drinks', 'sort_order' => 7],
        ];
        foreach ($categories as $cat) {
            MenuCategory::firstOrCreate(['slug' => $cat['slug']], $cat);
        }
    }
}
