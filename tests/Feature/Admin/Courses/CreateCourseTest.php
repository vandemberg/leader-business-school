<?php

namespace Tests\Feature\Admin\Courses;

use App\Models\Course;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CreateCourseTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * Test creating a course with all attributes.
     */
    public function test_create_with_all_attributes(): void
    {
        Storage::fake('public');

        $courseData = [
            'title' => $this->faker->sentence,
            'thumbnail' => UploadedFile::fake()->image('thumbnail.jpg'),
            'description' => $this->faker->paragraph,
            'icon' => 'trash',
            'color' => 'red',
        ];

        $response = $this->post('/api/admin/courses', $courseData);
        $courseId = json_decode($response->getContent())->id;
        $course = Course::find($courseId);

        $response->assertStatus(201);

        $this->assertEquals($courseData['title'], $course->title);
        $this->assertEquals($courseData['description'], $course->description);
        $this->assertEquals($courseData['icon'], $course->icon);
        $this->assertEquals($courseData['color'], $course->color);
        $this->assertEquals($this->user->id, $course->responsible_id);
        $this->assertEquals(Course::STATUS_DRAFT, $course->status);

        $thumbnail = Course::find($course->id)->thumbnail;
        $path_split  = explode('/', $thumbnail);
        $file_name = end($path_split);

        Storage::disk('public')->assertExists('thumbnails/' . $file_name);
        Storage::disk('public')->delete('thumbnails/' . $file_name);
    }

    /**
     * Test creating a course with required fields only.
     */
    public function test_create_with_required_fields(): void
    {
        $courseData = [
            'title' => $this->faker->sentence,
            'description' => $this->faker->paragraph,
        ];

        $response = $this->post('/api/admin/courses', $courseData);
        $courseId = json_decode($response->getContent())->id;
        $course = Course::find($courseId);

        $response->assertStatus(201);
        $this->assertEquals($courseData['title'], $course->title);
        $this->assertEquals($courseData['description'], $course->description);
        $this->assertEquals($this->user->id, $course->responsible_id);
        $this->assertEquals(Course::STATUS_DRAFT, $course->status);
    }

    /**
     * Test creating a course with missing required fields.
     */
    public function test_create_with_missing_required_fields(): void
    {
        $response = $this->post('/api/admin/courses', []);
        $response->assertStatus(422);
        $response->assertJsonFragment([
            'title' => ['The title field is required.'],
            'description' => ['The description field is required.'],
        ]);
    }

    /**
     * Test creating a course with invalid data.
     */
    public function test_create_with_invalid_data(): void
    {
        $courseData = [
            'title' => str_repeat('a', 256), // Exceeding max length
            'description' => $this->faker->paragraph,
            'icon' => 123, // Invalid type
            'color' => 456, // Invalid type
        ];

        $response = $this->post('/api/admin/courses', $courseData);
        $response->assertStatus(422);
        $response->assertJsonFragment([
            'title' => ['The title field must not be greater than 255 characters.'],
            'icon' => ['The icon field must be a string.'],
            'color' => ['The color field must be a string.'],
        ]);
    }
}
