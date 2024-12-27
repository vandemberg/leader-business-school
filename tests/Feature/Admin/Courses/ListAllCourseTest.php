<?php

namespace Tests\Feature\Admin\Courses;

use App\Models\Course;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListAllCourseTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test listing all courses.
     */
    public function test_list_all_courses(): void
    {
        Course::factory()->count(3)->create();

        $response = $this->get('/api/admin/courses');
        $response->assertStatus(200);
        $response->assertJsonCount(3);
    }

    /**
     * Test listing courses when there are no courses.
     */
    public function test_list_no_courses(): void
    {

        $response = $this->get('/api/admin/courses');
        $response->assertStatus(200);
        $response->assertJsonCount(0);
    }

    public function test_search_courses()
    {
        Course::factory()->create(['title' => 'Laravel Basics']);
        Course::factory()->create(['title' => 'Advanced Laravel']);

        $response = $this->getJson('/api/admin/courses?search=Basics');

        $response->assertStatus(200)
                 ->assertJsonCount(1)
                 ->assertJsonFragment(['title' => 'Laravel Basics']);
    }
}
