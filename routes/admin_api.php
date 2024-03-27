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
        'name' => 'required|string',
        'description' => 'required|string',
        'modules' => [
            'required',
            'array',
            'min:1',
            'each' => 'required|array',
        ],
    ]);

    $course = new Course();
    $course->name = $courseData['name'];
    $course->description = $courseData['description'];

    $module = new Module();
    $module->name = 'Module 1';
    $module->description = 'Module 1 description';


    return response()->json($course->with('modules'));
});
