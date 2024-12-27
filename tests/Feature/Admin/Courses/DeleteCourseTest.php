<?php

namespace Tests\Feature\Admin\Courses;

use App\Models\Course;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DeleteCourseTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * Test creating a course with all attributes.
     */
    public function test_delete_course(): void
    {
        $course = Course::factory()->create();

        $response = $this->delete(route('courses.destroy', $course));

        $response->assertStatus(204);
        $this->assertDatabaseMissing('courses', ['id' => $course->id]);
    }

    /**
     * Test creating a course with required fields only.
     */
    public function test_delete_not_found(): void
    {
        $response = $this->delete(route('courses.destroy', 1));

        $response->assertStatus(404);
    }
}
