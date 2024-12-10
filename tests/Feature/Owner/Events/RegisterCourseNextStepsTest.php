<?php


use App\Models\Course;
use App\Models\User;
use App\Models\Task;
use App\Domains\Owner\Events\RegisterCourseNextSteps;

describe('.handle', function () {
    test('Should register new tasks for the course', function () {
        $user = User::factory()->create();
        $course = Course::factory()->create(['responsible_id' => $user->id, 'thumbnail' => null]);

        $event = new stdClass();
        $event->course = $course;
        $listener = new RegisterCourseNextSteps();
        $listener->handle($event);

        $tasks = Task::where('course_id', $course->id)->get();
        expect($tasks->count())->toBe(2);
    });
});
