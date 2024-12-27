<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use App\Models\User;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(
            \App\Http\Middleware\VerifyCsrfToken::class
        );

        $this->user = $this->login();
    }

    public function login()
    {
        $user = User::factory()->create(['role' => USER::ROLE_ADMIN]);
        $token = \Tymon\JWTAuth\Facades\JWTAuth::fromUser($user);

        $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ]);

        return $user;
    }
}
