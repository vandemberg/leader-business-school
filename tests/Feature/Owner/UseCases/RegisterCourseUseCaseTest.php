<?php

use App\Domains\Owner\Repositories\CourseRepository;
use App\Domains\Owner\UseCases\Courses\RegisterCourseUseCase;
use App\Models\Course;
use App\Models\User;
use Database\Factories\UserFactory;

describe('.execute', function () {
    test('Should register a new course', function () {
        $user = UserFactory::new()->create();
        $courseRepository = new CourseRepository(new Course());
        $usecase = new RegisterCourseUseCase($courseRepository);

        $course = $usecase->execute([
            'title' => 'Test Course',
            'description' => 'This is a test course.',
            'responsible_id' => $user->id,
        ]);

        expect($course)->toBeInstanceOf(Course::class);
        expect($course->title)->toBe('Test Course');
        expect($course->description)->toBe('This is a test course.');
        expect($course->id)->not()->toBeNull();
        expect($course->status)->toEqual('draft');
        expect($course->created_at)->not()->toBeNull();
        expect($course->updated_at)->not()->toBeNull();
        expect($course->responsible->id)->toBe($user->id);
    });
});
