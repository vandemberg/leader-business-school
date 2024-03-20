<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Course;

class CoursesController extends Controller
{
    public function show(Course $course)
    {
        return Inertia::render('Courses/Index')->with('course', $course);
    }
}
