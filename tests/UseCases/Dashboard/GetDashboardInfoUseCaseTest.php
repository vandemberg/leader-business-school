<?php

namespace Tests\UseCases\Dashboard;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GetDashboardInfoUseCaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_dashboard_info(): void
    {
        $this->assertTrue(true);
    }
}
