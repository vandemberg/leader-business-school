<?php

namespace Database\Factories;

use App\Models\Platform;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\HelpCategory>
 */
class HelpCategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->randomElement([
            'Conta e Perfil',
            'Cursos e Aulas',
            'Pagamentos',
            'Certificados',
            'Problemas Técnicos',
            'Comunidade',
        ]);

        return [
            'platform_id' => Platform::factory(),
            'name' => $name,
            'slug' => Str::slug($name),
            'icon' => fake()->randomElement(['person', 'school', 'credit_card', 'workspace_premium', 'build', 'diversity_3']),
            'description' => fake()->sentence(),
            'order' => fake()->numberBetween(0, 10),
        ];
    }
}
