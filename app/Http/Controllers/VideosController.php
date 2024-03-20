<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Course;
use App\Models\Video;
use Inertia\Inertia;

class VideosController extends Controller
{
    public function show(Course $course, Video $video)
    {
        return Inertia::render('Videos/Index')
            ->with('course', $course)
            ->with('video', $video);
    }
}
