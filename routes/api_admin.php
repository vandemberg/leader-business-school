<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Course;
use App\Models\Module;
use App\Models\Video;

Route::get('/health_check', function (Request $request) {
    return response()->json(['message' => 'OK']);
});

Route::post('/course', function (Request $request) {
    $courseData = $request->validate([
        'title' => 'required|string',
        'description' => 'required|string',
        'icon' => 'required|string',
        'thumbnail' => 'required|string',
        'videos' => 'required|array',
    ]);

    $course = new Course();
    $course->name = $courseData['name'];
    $course->description = $courseData['description'];

    return response()->json($course->with('modules'));
});
