<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_login(): void
    {
        User::factory()->create([
            'email' => 'test@email.com',
            'password' => bcrypt($password = 'password'),
            'role' => 'admin',
        ]);

        $response = $this->post('/api/admin/login', [
            'email' => 'test@email.com',
            'password' => $password,
        ]);

        $response->assertStatus(200);
    }

    public function test_login_role_user(): void
    {
        User::factory()->create([
            'email' => 'test@email.com',
            'password' => bcrypt($password = 'password'),
            'role' => User::ROLE_USER,
        ]);

        $response = $this->post('/api/admin/login', [
            'email' => 'test@email.com',
            'password' => $password,
        ]);

        $response->assertStatus(401);
    }
}
