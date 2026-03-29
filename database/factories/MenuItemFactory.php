<?php

namespace Database\Factories;

use App\Models\MenuItem;
use App\Models\Restaurant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MenuItem>
 */
class MenuItemFactory extends Factory
{
    protected $model = MenuItem::class;

    public function definition(): array
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
        $arabicNames = [
            'شاورما دجاج', 'بيتزا مارجريتا', 'كباب لحم', 'فلافل', 'حمص بالطحينة',
            'سلطة فتوش', 'مندي دجاج', 'كشري', 'ملوخية', 'طاجن بامية',
            'سمبوسك جبنة', 'مكرونة بشاميل', 'كبدة اسكندراني', 'محشي ورق عنب', 'برياني لحم'
        ];
        $arabicDescriptions = [
            'طبق شهي من المطبخ العربي.',
            'وجبة تقليدية محضرة بمكونات طازجة.',
            'نكهات أصيلة وتوابل مميزة.',
            'اختيار مثالي لمحبي الطعام الشرقي.',
            'طبق غني بالبروتين والطاقة.',
            'وجبة نباتية لذيذة وصحية.',
            'طبق رئيسي يقدم مع الأرز.',
            'مذاق لا يقاوم من المطبخ المصري.',
            'وجبة خفيفة مناسبة للجميع.',
            'طبق جانبي غني بالنكهات.'
        ];
        return [
            'restaurant_id' => Restaurant::factory(),
            'category' => $this->faker->randomElement($categories),
            'name' => $this->faker->randomElement($arabicNames),
            'description' => $this->faker->randomElement($arabicDescriptions),
            'price_cents' => fake()->numberBetween(500, 5000),
            'is_available' => true,
            'sort_order' => 0,
        ];
    }
}
