<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PostTag>
 */
class PostTagFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['Liderança', 'Marketing', 'Finanças', 'Inovação', 'GestãoDePessoas', 'Estratégia']),
            'usage_count' => fake()->numberBetween(0, 100),
        ];
    }
}
