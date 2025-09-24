<?php

namespace Tests\Feature\Admin;

use App\Models\Platform;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlatformAuthTest extends TestCase
{
  use RefreshDatabase;

  public function test_login_always_includes_platform_id()
  {
    // Arrange: Cria usuário admin sem plataforma
    $user = User::factory()->create([
      'email' => 'admin@test.com',
      'password' => bcrypt('password'),
      'role' => User::ROLE_ADMIN,
      'current_platform_id' => null
    ]);

    // Act: Faz login
    $response = $this->postJson('/api/admin/login', [
      'email' => 'admin@test.com',
      'password' => 'password'
    ]);

    // Assert: Verifica se response inclui platform_id
    $response->assertStatus(200);
    $response->assertJsonStructure([
      'access_token',
      'platform_id'
    ]);

    // Verifica se platform_id não é null
    $data = $response->json();
    $this->assertNotNull($data['platform_id']);

    // Verifica se usuário agora tem current_platform_id definido
    $user->refresh();
    $this->assertNotNull($user->current_platform_id);
  }

  public function test_platform_context_middleware_sets_platform()
  {
    // Arrange: Cria usuário e plataforma
    $platform = Platform::factory()->create([
      'name' => 'Test Platform',
      'slug' => 'test'
    ]);

    $user = User::factory()->create([
      'role' => User::ROLE_ADMIN,
      'current_platform_id' => $platform->id
    ]);

    // Act: Faz requisição autenticada
    $response = $this->actingAs($user, 'api')
      ->getJson('/api/admin/users');

    // Assert: Middleware deve ter definido platform context
    $response->assertStatus(200);

    // Verifica se current_platform_id está disponível via helper
    $this->assertEquals($platform->id, current_platform_id());
  }

  public function test_helpers_work_correctly()
  {
    // Arrange
    $platform = Platform::factory()->create();
    app()->instance('current_platform_id', $platform->id);

    // Act & Assert
    $this->assertEquals($platform->id, current_platform_id());
    $this->assertEquals($platform->name, current_platform()->name);
  }
}
