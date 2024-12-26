<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Course;

class CoursesController extends Controller
{
    public function index()
    {
        $courses = Course::all();
        return response()->json($courses);
    }

    public function create()
    {
        return response()->json(['message' => 'Display form for creating a course']);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
        ]);

        $course = Course::create($request->all());

        return response()->json(['success' => 'Course created successfully.', 'course' => $course]);
    }

    public function edit(Course $course)
    {
        return response()->json(['course' => $course]);
    }

    public function update(Request $request, Course $course)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
        ]);

        $course->update($request->all());

        return response()->json(['success' => 'Course updated successfully.', 'course' => $course]);
    }

    public function destroy(Course $course)
    {
        $course->delete();

        return response()->json(['success' => 'Course deleted successfully.']);
    }
}
