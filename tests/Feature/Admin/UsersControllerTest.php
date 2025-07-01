<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UsersControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_users()
    {
        $countBefore = User::all()->count();
        User::factory()->count(3)->create();

        $response = $this->getJson('/api/admin/users');

        $response->assertStatus(200);
        $response->assertJsonCount($countBefore + 3);
    }

    public function test_show_user()
    {
        $user = User::factory()->create();

        $response = $this->getJson("/api/admin/users/{$user->id}");

        $response->assertStatus(200);
        $response->assertJsonFragment(['email' => $user->email]);
    }

    public function test_store_user()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'role' => User::ROLE_USER,
            'password_confirmation' => 'password',
        ];

        $response = $this->postJson('/api/admin/users', $userData);

        $response->assertStatus(201);
        $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
    }

    public function test_update_user()
    {
        $user = User::factory()->create();

        $updateData = [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ];

        $response = $this->putJson("/api/admin/users/{$user->id}", $updateData);

        $response->assertStatus(200);
        $this->assertDatabaseHas('users', ['email' => 'updated@example.com']);
    }

    public function test_update_user_password()
    {
        $user = User::factory()->create();

        $updateData = [
            'password' => 'newpassword',
            'password_confirmation' => 'newpassword',
        ];

        $response = $this->putJson("/api/admin/users/{$user->id}", $updateData);

        $response->assertStatus(200);
        $this->assertTrue(Hash::check('newpassword', $user->fresh()->password));
    }

    public function test_destroy_user()
    {
        $user = User::factory()->create();

        $response = $this->deleteJson("/api/admin/users/{$user->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }
}
