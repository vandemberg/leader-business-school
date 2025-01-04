<?php

namespace Tests\Feature\Admin\Modules;

use App\Models\Course;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CreateModuleTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_create_new_module()
    {
        $course = Course::factory()->create();

        $moduleData = [
            'name' => $this->faker->sentence,
            'description' => $this->faker->paragraph,
        ];

        $response = $this->post("/api/admin/courses/{$course->id}/modules", $moduleData);
        $moduleId = json_decode($response->getContent())->id;
        $module = $course->modules()->find($moduleId);

        $response->assertStatus(201);
        $this->assertEquals($moduleData['name'], $module->name);
        $this->assertEquals($moduleData['description'], $module->description);
    }

    public function test_not_found_course()
    {
        $moduleData = [
            'name' => $this->faker->sentence,
            'description' => $this->faker->paragraph,
        ];

        $response = $this->post('/api/admin/courses/1/modules', $moduleData);
        $response->assertStatus(404);
    }

    public function test_missing_attributes_module()
    {
        $course = Course::factory()->create();

        $response = $this->post("/api/admin/courses/{$course->id}/modules", []);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name']);
    }
}
