<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Course;

class CoursesController extends Controller
{
    public function show(Course $course)
    {
        $videos = [];
        $course->modules()->each(function ($module) use (&$videos) {
            $module->videos->each(function ($video) use (&$videos) {
                $videos[] = $video;
            });
        });

        return Inertia::render('Courses/Index')
            ->with('course', $course)
            ->with('videos', $videos)
            ->with('modules', $course->modules)
            ->with('currentVideo', $videos[0])
            ->with('currentModule', $course->modules[0]);
    }
}
