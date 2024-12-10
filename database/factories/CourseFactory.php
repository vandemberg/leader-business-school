<?php

namespace Database\Factories;

use App\Models\Course;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CourseFactory extends Factory
{
    protected $model = Course::class;

    public function definition()
    {
        return [
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(4),
            'status' => 'draft',
            'thumbnail' => $this->faker->imageUrl(640, 480, 'education', true, 'course'),
            'icon' => $this->faker->word,
            'color' => $this->faker->hexColor,
        ];
    }

    public function published()
    {
        return $this->state([
            'status' => 'published',
        ]);
    }
}
