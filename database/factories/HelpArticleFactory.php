<?php

namespace Database\Factories;

use App\Models\HelpCategory;
use App\Models\Platform;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\HelpArticle>
 */
class HelpArticleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'category_id' => HelpCategory::factory(),
            'platform_id' => Platform::factory(),
            'question' => fake()->sentence() . '?',
            'answer' => fake()->paragraphs(2, true),
            'order' => fake()->numberBetween(0, 10),
            'is_faq' => fake()->boolean(70),
            'views_count' => fake()->numberBetween(0, 1000),
        ];
    }
}
