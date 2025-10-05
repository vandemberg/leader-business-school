<?php

namespace Tests\Feature\Admin;

use App\Models\Teacher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TeachersControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_lists_teachers(): void
    {
        Teacher::factory()->count(3)->create();

        $response = $this->getJson('/api/admin/teachers');

        $response->assertStatus(200)
            ->assertJsonCount(3);
    }

    public function test_store_creates_teacher_with_avatar(): void
    {
        Storage::fake('public');

        $response = $this->post('/api/admin/teachers', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'bio' => 'Experienced mentor',
            'avatar' => UploadedFile::fake()->image('avatar.jpg'),
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('teachers', [
            'email' => 'john@example.com',
        ]);
    }

    public function test_show_returns_teacher(): void
    {
        $teacher = Teacher::factory()->create();

        $response = $this->getJson("/api/admin/teachers/{$teacher->id}");

        $response->assertStatus(200)
            ->assertJsonFragment(['id' => $teacher->id]);
    }

    public function test_update_modifies_teacher(): void
    {
        $teacher = Teacher::factory()->create();

        $response = $this->putJson("/api/admin/teachers/{$teacher->id}", [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('teachers', [
            'id' => $teacher->id,
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ]);
    }

    public function test_destroy_deletes_teacher(): void
    {
        $teacher = Teacher::factory()->create();

        $response = $this->deleteJson("/api/admin/teachers/{$teacher->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('teachers', ['id' => $teacher->id]);
    }
}
