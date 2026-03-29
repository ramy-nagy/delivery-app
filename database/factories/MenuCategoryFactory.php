<?php

namespace Database\Factories;

use App\Models\MenuCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MenuCategory>
 */
class MenuCategoryFactory extends Factory
{
    protected $model = MenuCategory::class;

    public function definition(): array
    {
        $categories = [
            'بيتزا',
            'سندوتشات',
            'مقبلات',
            'مشويات',
            'أطباق رئيسية',
            'حلويات',
            'مشروبات',
        ];
        $name = fake()->unique()->randomElement($categories);
        return [
            'name' => $name,
            'slug' => str($name)->slug(),
            'sort_order' => 0,
        ];
    }
}
