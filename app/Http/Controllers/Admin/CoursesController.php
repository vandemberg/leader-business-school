<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;

class CoursesController extends Controller
{
    public function index()
    {
        $courses = Course::all();

        return view('admin.courses.index')
            ->with('courses', $courses);
    }

    public function new()
    {
        $course = new Course();

        return view('admin.courses.new')
            ->with('course', $course);
    }

    public function create(Request $request)
    {
        $courseData = $request->only('name', 'description');
        $course = new Course($courseData);

        return view('admin.courses.edit')
            ->with('course', $course);
    }

    public function edit(Course $course)
    {
        return view('admin.courses.edit')
            ->with('course', $course);
    }

    public function update(Request $request, Course $course)
    {
        $courseData = $request->only('name', 'description');
        $course->fill($courseData);

        return view('admin.courses.edit')
            ->with('course', $course);
    }

    public function delete(Course $course)
    {
        $course->delete();

        return redirect()->route('admin.courses.index');
    }
}
