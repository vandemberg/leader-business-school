<?php

use App\Domains\Owner\Repositories\CourseRepository;
use App\Models\Course;

describe('.register a new course', function () {
    test('Should register a new course', function () {
        $courseRepository = new CourseRepository(new Course());
        $course = $courseRepository->register([
            'title' => 'Test Course',
            'description' => 'This is a test course.',
        ]);

        expect($course)->toBeInstanceOf(Course::class);
        expect($course->title)->toBe('Test Course');
        expect($course->description)->toBe('This is a test course.');
        expect($course->created_at)->not()->toBeNull();
        expect($course->updated_at)->not()->toBeNull();
    });
});
