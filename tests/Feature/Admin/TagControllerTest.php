<?php

namespace Tests\Feature\Admin;

use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TagControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_lists_tags(): void
    {
        Tag::factory()->count(3)->create();

        $response = $this->getJson('/api/admin/tags');

        $response->assertStatus(200)
            ->assertJsonCount(3);
    }

    public function test_store_creates_tag(): void
    {
        $response = $this->postJson('/api/admin/tags', [
            'name' => 'Leadership',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('tags', ['name' => 'Leadership']);
    }

    public function test_show_returns_tag(): void
    {
        $tag = Tag::factory()->create();

        $response = $this->getJson("/api/admin/tags/{$tag->id}");

        $response->assertStatus(200)
            ->assertJsonFragment(['id' => $tag->id]);
    }

    public function test_update_modifies_tag(): void
    {
        $tag = Tag::factory()->create(['name' => 'Old Name']);

        $response = $this->putJson("/api/admin/tags/{$tag->id}", [
            'name' => 'New Name',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('tags', [
            'id' => $tag->id,
            'name' => 'New Name',
        ]);
    }

    public function test_destroy_deletes_tag_without_relations(): void
    {
        $tag = Tag::factory()->create();

        $response = $this->deleteJson("/api/admin/tags/{$tag->id}");

        $response->assertNoContent();
        $this->assertDatabaseMissing('tags', ['id' => $tag->id]);
    }
}
