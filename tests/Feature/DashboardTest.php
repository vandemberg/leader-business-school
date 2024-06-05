<?php

use App\Models\User;


test('Should list the current courses', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get('/dashboard');

    $this->assertOk();
});
