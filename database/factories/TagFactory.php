<?php

namespace Database\Factories;

use App\Models\Platform;
use App\Models\Tag;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Tag>
 */
class TagFactory extends Factory
{
    protected $model = Tag::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->words(asText: true),
            'platform_id' => Platform::factory(),
        ];
    }
}
