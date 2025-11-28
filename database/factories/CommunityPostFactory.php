<?php

namespace Database\Factories;

use App\Models\Platform;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CommunityPost>
 */
class CommunityPostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'platform_id' => Platform::factory(),
            'title' => fake()->sentence(),
            'content' => fake()->paragraphs(3, true),
            'tag' => fake()->randomElement(['Liderança', 'Marketing', 'Finanças', 'Inovação', null]),
            'likes_count' => 0,
            'comments_count' => 0,
        ];
    }
}
