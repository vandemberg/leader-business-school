<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Course;

class CoursesController extends Controller
{

    public function index()
    {
        $courses = Course::all();

        return Inertia::render('Courses/Index')
            ->with('courses', $courses);
    }

    public function show(Course $course)
    {
        return response()->noContent();
    }
}
