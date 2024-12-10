<?php

namespace App\Domains\Owner\UseCases\Courses;

use App\Events\CourseCreated;
use App\Protocols\IUseCase;
use App\Domains\Owner\Repositories\ICourseRepository;
use App\Models\Course;

class RegisterCourseUseCase implements IUseCase
{
    private ICourseRepository $courseRepository;

    public function __construct(ICourseRepository $courseRepository)
    {
        $this->courseRepository = $courseRepository;
    }

    public function execute($input = null): Course
    {
        $course = $this->courseRepository->register($input);

        $this->dispatchEvents($course);

        return $course;
    }

    private function dispatchEvents($course): void
    {
        CourseCreated::dispatch($course);
    }
}
