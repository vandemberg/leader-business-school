<?php

namespace Tests\Feature\Admin;

use App\Models\Course;
use App\Models\Module;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModulesControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_module()
    {
        $course = Course::factory()->create();

        $response = $this->postJson($this->url($course), [
            'name' => 'Test Module',
            'description' => 'Test Description',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('modules', [
            'name' => 'Test Module',
            'description' => 'Test Description',
            'course_id' => $course->id,
            'status' => Module::STATUS_DRAFT,
        ]);
    }

    public function test_update_module()
    {
        $course = Course::factory()->create();
        $module = Module::factory()->create(['course_id' => $course->id]);

        $url = $this->url($course) . '/' . $module->id;
        $response = $this->putJson($url, [
            'name' => 'Updated Module',
            'description' => 'Updated Description',
            'status' => 'published',
        ]);

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'name' => 'Updated Module',
            'description' => 'Updated Description',
            'status' => 'published',
            'course_id' => $course->id,
        ]);
    }

    public function test_destroy_module()
    {
        $course = Course::factory()->create();
        $module = Module::factory()->create(['course_id' => $course->id]);

        $url = $this->url($course) . '/' . $module->id;
        $response = $this->deleteJson($url);

        $response->assertStatus(204);
        $this->assertDatabaseMissing('modules', [
            'id' => $module->id,
        ]);
    }

    private function url(Course $course)
    {
        return '/api/admin/courses/' . $course->id . '/modules';
    }
}
