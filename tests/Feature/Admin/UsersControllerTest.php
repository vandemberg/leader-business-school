<?php

namespace Tests\Feature\Admin;

use App\Models\Course;
use App\Models\Module;
use App\Models\Platform;
use App\Models\PlatformUser;
use App\Models\User;
use App\Models\Video;
use App\Models\WatchVideo;
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

        $payload = $response->json();
        $this->assertNotEmpty($payload);
        $this->assertArrayHasKey('progress_percent', $payload[0]);
        $this->assertArrayHasKey('completed_videos', $payload[0]);
        $this->assertSame(0, $payload[0]['progress_percent']);
        $this->assertSame(0, $payload[0]['completed_videos']);
    }

    public function test_index_users_returns_progress_information()
    {
        $platform = Platform::factory()->create();
        $course = Course::factory()->create(['platform_id' => $platform->id]);
        $module = Module::factory()->create(['course_id' => $course->id]);

        $videos = Video::factory()
            ->count(4)
            ->create([
                'course_id' => $course->id,
                'module_id' => $module->id,
                'platform_id' => $platform->id,
                'status' => 'published',
            ]);

        $users = User::factory()->count(2)->create([
            'role' => User::ROLE_USER,
        ]);

        foreach ($users as $user) {
            PlatformUser::create([
                'platform_id' => $platform->id,
                'user_id' => $user->id,
            ]);
        }

        $videos->take(2)->each(function (Video $video) use ($users) {
            WatchVideo::create([
                'user_id' => $users[0]->id,
                'video_id' => $video->id,
                'status' => WatchVideo::STATUS_WATCHED,
            ]);
        });

        $response = $this->getJson("/api/admin/users?platform_id={$platform->id}");

        $response->assertStatus(200);

        $payload = collect($response->json())->keyBy('id');

        $this->assertSame(50, $payload[$users[0]->id]['progress_percent']);
        $this->assertSame(2, $payload[$users[0]->id]['completed_videos']);
        $this->assertSame(0, $payload[$users[1]->id]['progress_percent']);
        $this->assertSame(0, $payload[$users[1]->id]['completed_videos']);
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
