<?php

namespace Tests\Feature\Admin;

use App\Models\Course;
use App\Models\Module;
use App\Models\Video;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Faker\Factory as Faker;

class VideosControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_video()
    {
        $faker = Faker::create();
        $course = Course::factory()->create();
        $module = Module::factory()->create(['course_id' => $course->id]);

        $response = $this->postJson($this->url($course, $module), [
            'title' => $faker->sentence,
            'description' => $faker->paragraph,
            'url' => $faker->url,
            'transcription' => $faker->text,
            'time_in_seconds' => (string) $faker->numberBetween(60, 3600),
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('videos', [
            'title' => $response['title'],
            'description' => $response['description'],
            'url' => $response['url'],
            'transcription' => $response['transcription'],
            'time_in_seconds' => $response['time_in_seconds'],
            'course_id' => $course->id,
            'module_id' => $module->id,
        ]);
    }

    public function test_update_video()
    {
        $faker = Faker::create();
        $course = Course::factory()->create();
        $module = Module::factory()->create(['course_id' => $course->id]);
        $video = Video::factory()->create(['course_id' => $course->id, 'module_id' => $module->id]);

        $url = $this->url($course, $module) . '/' . $video->id;
        $response = $this->putJson($url, [
            'title' => $faker->sentence,
            'description' => $faker->paragraph,
            'url' => $faker->url,
            'transcription' => $faker->text,
            'time_in_seconds' => (string) $faker->numberBetween(60, 3600),
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('videos', [
            'title' => $response['title'],
            'description' => $response['description'],
            'url' => $response['url'],
            'transcription' => $response['transcription'],
            'time_in_seconds' => $response['time_in_seconds'],
            'course_id' => $course->id,
            'module_id' => $module->id,
        ]);
    }

    public function test_destroy_video()
    {
        $course = Course::factory()->create();
        $module = Module::factory()->create(['course_id' => $course->id]);
        $video = Video::factory()->create(['course_id' => $course->id, 'module_id' => $module->id]);

        $url = $this->url($course, $module) . '/' . $video->id;
        $response = $this->deleteJson($url);

        $response->assertStatus(204);
        $this->assertDatabaseMissing('videos', [
            'id' => $video->id,
        ]);
    }

    private function url(Course $course, Module $module)
    {
        return '/api/admin/courses/' . $course->id . '/modules/' . $module->id . '/videos';
    }
}
