<?php

test('should create a new course from request', function () {
    $response = $this->post('api/owner/v1/courses', [
        'title' => 'Test Course',
        'description' => 'This is a test course.',
    ]);

    $response->assertStatus(201);
    $response->assertJson([
        'title' => 'Test Course',
        'description' => 'This is a test course.',
        'status' => 'draft',
    ]);
});
