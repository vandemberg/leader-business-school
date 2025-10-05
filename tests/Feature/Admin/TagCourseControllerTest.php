<?php

namespace Tests\Feature\Admin;

use App\Models\Course;
use App\Models\Tag;
use App\Models\TagCourse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TagCourseControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_lists_tag_courses(): void
    {
        TagCourse::factory()->count(2)->create();

        $response = $this->getJson('/api/admin/tag-courses');

        $response->assertStatus(200)
            ->assertJsonCount(2);
    }

    public function test_store_creates_tag_course(): void
    {
        $tag = Tag::factory()->create();
        $course = Course::factory()->create();

        $response = $this->postJson('/api/admin/tag-courses', [
            'tag_id' => $tag->id,
            'course_id' => $course->id,
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('tag_courses', [
            'tag_id' => $tag->id,
            'course_id' => $course->id,
        ]);
    }

    public function test_show_returns_tag_course(): void
    {
        $tagCourse = TagCourse::factory()->create();

        $response = $this->getJson("/api/admin/tag-courses/{$tagCourse->id}");

        $response->assertStatus(200)
            ->assertJsonFragment(['id' => $tagCourse->id]);
    }

    public function test_destroy_deletes_tag_course(): void
    {
        $tagCourse = TagCourse::factory()->create();

        $response = $this->deleteJson("/api/admin/tag-courses/{$tagCourse->id}");

        $response->assertNoContent();
        $this->assertDatabaseMissing('tag_courses', ['id' => $tagCourse->id]);
    }

    public function test_destroy_by_tag_and_course_route(): void
    {
        $tagCourse = TagCourse::factory()->create();

        $response = $this->deleteJson(
            "/api/admin/tag-courses/tag/{$tagCourse->tag_id}/course/{$tagCourse->course_id}",
            [
                'tag_id' => $tagCourse->tag_id,
                'course_id' => $tagCourse->course_id,
            ]
        );

        $response->assertNoContent();
        $this->assertDatabaseMissing('tag_courses', [
            'tag_id' => $tagCourse->tag_id,
            'course_id' => $tagCourse->course_id,
        ]);
    }

    public function test_get_tags_by_course(): void
    {
        $course = Course::factory()->create();
        $firstTag = Tag::factory()->create();
        $secondTag = Tag::factory()->create();
        TagCourse::factory()->create([
            'course_id' => $course->id,
            'tag_id' => $firstTag->id,
        ]);
        TagCourse::factory()->create([
            'course_id' => $course->id,
            'tag_id' => $secondTag->id,
        ]);

        $response = $this->getJson("/api/admin/courses/{$course->id}/tags");

        $response->assertStatus(200)
            ->assertJsonCount(2)
            ->assertJsonFragment(['tag_id' => $firstTag->id])
            ->assertJsonFragment(['tag_id' => $secondTag->id]);
    }

    public function test_get_courses_by_tag(): void
    {
        $tag = Tag::factory()->create();
        $firstCourse = Course::factory()->create();
        $secondCourse = Course::factory()->create();
        TagCourse::factory()->create([
            'tag_id' => $tag->id,
            'course_id' => $firstCourse->id,
        ]);
        TagCourse::factory()->create([
            'tag_id' => $tag->id,
            'course_id' => $secondCourse->id,
        ]);

        $response = $this->getJson("/api/admin/tags/{$tag->id}/courses");

        $response->assertStatus(200)
            ->assertJsonCount(2)
            ->assertJsonFragment(['course_id' => $firstCourse->id])
            ->assertJsonFragment(['course_id' => $secondCourse->id]);
    }
}
