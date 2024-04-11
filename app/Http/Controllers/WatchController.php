<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Course;
use App\Models\Video;

class WatchController extends Controller
{
    public function index(Course $course)
    {
        $currentVideo = $course->videos()->first();

        return response()->redirectTo("/courses/{$course->id}/videos/{$currentVideo->id}");
    }

    public function show(Course $course, Video $video)
    {
        $videos = $course->videos()->get();
        $currentVideo = $videos->where('id', $video->id)->first();
        return Inertia::render('Watch/Index', [
            'course' => $course,
            'currentVideo' => $currentVideo,
            'videos' => $videos,
        ]);
    }

    public function doneVideo(Video $video)
    {
        return response()->noContent();
    }
}
