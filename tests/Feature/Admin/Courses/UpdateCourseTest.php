<?php

namespace Tests\Feature\Admin\Courses;

use App\Models\Course;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UpdateCourseTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * Test creating a course with all attributes.
     */
    public function test_update_attributes()
    {
        $course = Course::factory()->create();

        Storage::fake('public');
        $file = UploadedFile::fake()->image('thumbnail.jpg');

        $data = [
            'title' => $this->faker->sentence,
            'description' => $this->faker->paragraph,
            'icon' => $this->faker->word,
            'color' => $this->faker->hexColor,
            'status' => Course::STATUS_IN_PROGRESS,
            'thumbnail' => $file,
        ];

        $response = $this->put(route('courses.update', $course), $data);

        $response->assertStatus(200);
        $this->assertDatabaseHas('courses', [
            'title' => $data['title'],
            'description' => $data['description'],
            'icon' => $data['icon'],
            'color' => $data['color'],
            'status' => $data['status'],
        ]);

        Storage::disk('public')->assertExists('thumbnails/thumbnail_' . $course->title . '.jpg');
    }

    public function test_update_required_fields()
    {
        $course = Course::factory()->create();

        $data = [
            'title' => '',
            'description' => '',
            'icon' => '',
            'color' => '',
            'status' => '',
        ];

        $response = $this->put(route('courses.update', $course), $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['title', 'description', 'status']);
    }
}
