<?php

namespace Database\Factories;

use App\Models\Course;
use Illuminate\Database\Eloquent\Factories\Factory;

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

    public function complete()
    {
        return $this->state([
            'status' => Course::STATUS_COMPLETE,
        ]);
    }

    public function withVideos()
    {
        return $this->afterCreating(function (Course $course) {
            $module = $course->modules()->create([
                'name' => $this->faker->sentence(2),
                'description' => $this->faker->paragraph(2),
            ]);

            $module->videos()->create([
                'title' => $this->faker->sentence(3),
                'description' => $this->faker->paragraph(2),
                'url' => $this->faker->url,
                'status' => 'published',
                'transcription' => $this->faker->paragraph(3),
                'time_in_seconds' => $this->faker->numberBetween(60, 3600),
                'course_id' => $course->id,
            ]);
        });
    }
}
