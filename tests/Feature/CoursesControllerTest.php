<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CoursesControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_courses_page(): void
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)->get(route('courses.index'));

        $response->assertStatus(200);
    }

    public function test_user_can_search_courses(): void
    {
        $user = User::factory()->create();
        Course::factory()->create(['title' => 'Laravel Advanced']);
        Course::factory()->create(['title' => 'PHP Basics']);

        $response = $this->actingAs($user)->get(route('courses.index', ['search' => 'Laravel']));

        $response->assertStatus(200);
    }

    public function test_user_can_filter_courses_by_category(): void
    {
        $user = User::factory()->create();
        $tag = Tag::factory()->create(['name' => 'Liderança']);
        $course = Course::factory()->create();
        $course->tags()->attach($tag->id);

        $response = $this->actingAs($user)->get(route('courses.index', ['category' => 'Liderança']));

        $response->assertStatus(200);
    }

    public function test_user_can_sort_courses_by_popularity(): void
    {
        $user = User::factory()->create();
        Course::factory()->count(3)->create();

        $response = $this->actingAs($user)->get(route('courses.index', ['sort' => 'popular']));

        $response->assertStatus(200);
    }
}
