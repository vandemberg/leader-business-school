<?php

namespace App\Http\Controllers\Owner;

use App\Domains\Owner\Repositories\CourseRepository;
use App\Domains\Owner\UseCases\Courses\RegisterCourseUseCase;
use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;

class CoursesController extends Controller
{
    private RegisterCourseUseCase $usecase;

    public function __construct()
    {
        $repository = new CourseRepository(new Course());
        $this->usecase = new RegisterCourseUseCase($repository);
    }

    public function create(Request $request)
    {
        $input = $request->only(['title', 'description', 'responsible_id']);
        $course = $this->usecase->execute($input);

        return response()->json($course, status: 201);
    }
}
