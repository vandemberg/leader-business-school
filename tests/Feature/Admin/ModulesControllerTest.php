<?php

namespace Tests\Feature\Admin;

use App\Models\Course;
use App\Models\Module;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Faker\Factory as Faker;

class ModulesControllerTest extends TestCase
{
    use RefreshDatabase;

    private $faker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->faker = Faker::create();
    }

    public function test_index_modules(): void
    {
        $course = Course::factory()->create();
        Module::factory()->count(3)->create(['course_id' => $course->id]);

        $response = $this->getJson($this->url($course));

        $response->assertStatus(200)
            ->assertJsonCount(3);
    }

    public function test_store_module()
    {
        $course = Course::factory()->create();

        $response = $this->postJson($this->url($course), [
            'name' => $this->faker->word,
            'description' => $this->faker->sentence,
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('modules', [
            'name' => $response['name'],
            'description' => $response['description'],
            'course_id' => $course->id,
            'status' => Module::STATUS_DRAFT,
        ]);
    }

    public function test_store_module_validation()
    {
        $course = Course::factory()->create();

        $response = $this->postJson($this->url($course), [
            'name' => '',
            'description' => '',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name']);
    }

    public function test_update_module()
    {
        $course = Course::factory()->create();
        $module = Module::factory()->create(['course_id' => $course->id]);

        $url = $this->url($course) . '/' . $module->id;
        $response = $this->putJson($url, [
            'name' => $this->faker->word,
            'description' => $this->faker->sentence,
            'status' => 'published',
        ]);

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'name' => $response['name'],
            'description' => $response['description'],
            'status' => 'published',
            'course_id' => $course->id,
        ]);
    }

    public function test_update_module_validation()
    {
        $course = Course::factory()->create();
        $module = Module::factory()->create(['course_id' => $course->id]);

        $url = $this->url($course) . '/' . $module->id;
        $response = $this->putJson($url, [
            'name' => '',
            'description' => '',
            'status' => '',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name', 'description', 'status']);
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
