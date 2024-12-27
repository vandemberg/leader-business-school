<?php

namespace Database\Factories;

use App\Models\Video;
use Illuminate\Database\Eloquent\Factories\Factory;

class VideoFactory extends Factory
{
    protected $model = Video::class;

    public function definition()
    {
        return [
            'title' => $this->faker->sentence,
            'description' => $this->faker->paragraph,
            'url' => $this->faker->url,
            'status' => $this->faker->randomElement(['draft', 'published']),
            'transcription' => $this->faker->optional()->paragraph,
            'thumbnail' => $this->faker->optional()->imageUrl,
            'time_in_seconds' => $this->faker->numberBetween(60, 3600),
        ];
    }
}
