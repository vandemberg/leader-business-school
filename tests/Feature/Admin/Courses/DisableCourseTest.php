<?php

namespace Tests\Feature\Admin\Courses;

use App\Models\Course;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DisableCourseTest extends TestCase
{
    use RefreshDatabase;

    public function test_disable_course_sets_status_to_draft(): void
    {
        $course = Course::factory()->create([
            'status' => Course::STATUS_COMPLETE,
        ]);

        $response = $this->postJson(route('admin.courses.disable', $course));

        $response->assertNoContent();
        $this->assertEquals(Course::STATUS_DRAFT, $course->refresh()->status);
    }
}
