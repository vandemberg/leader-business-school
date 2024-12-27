<?php

namespace Tests\Feature\Admin\Courses;

use App\Models\Course;
use App\Models\Module;
use Database\Factories\CourseFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShowCourseTest extends TestCase
{
    use RefreshDatabase;

    public function test_show_course_details()
    {
        $course = CourseFactory::new()->withVideos()->create();

        $response = $this->get('/api/admin/courses/' . $course->id);

        $response->assertStatus(200);
        $response->assertJson([
            'id' => $course->id,
            'title' => $course->title,
            'description' => $course->description,
            'icon' => $course->icon,
            'color' => $course->color,
            'status' => $course->status,
        ]);

        $response->assertJsonStructure([
            'id',
            'title',
            'description',
            'icon',
            'color',
            'status',
            'responsible',
            'modules',
        ]);
    }

    public function test_not_found_course()
    {
        $response = $this->get('/api/admin/courses/1')
            ->assertStatus(404);

        $response->assertStatus(404);
    }
}
