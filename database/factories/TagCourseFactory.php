<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\Tag;
use App\Models\TagCourse;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TagCourse>
 */
class TagCourseFactory extends Factory
{
    protected $model = TagCourse::class;

    public function definition(): array
    {
        return [
            'tag_id' => Tag::factory(),
            'course_id' => Course::factory(),
        ];
    }
}
