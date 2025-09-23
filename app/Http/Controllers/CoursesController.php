<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Course;
use App\Models\WatchVideo;

class CoursesController extends Controller
{

    public function index()
    {
        $user = auth()->user();
        $courses = Course::all();

        $courses->each(function ($course) use ($user) {
            if (strpos($course->thumbnail, 'thumbnails/') !== false) {
                $course->thumbnail = url('/') . $course->thumbnail;
            }

            $videos = $course->modules()->with('videos')->get()->pluck('videos')->flatten();
            $totalVideos = $videos->count();
            $totalDone = WatchVideo::where('user_id', $user->id)
                ->whereIn('video_id', $videos->pluck('id'))
                ->where('status', WatchVideo::STATUS_WATCHED)
                ->get()
                ->count();

            $progress = $totalVideos > 0 ? ($totalDone / $totalVideos) * 100 : 0;
            $course->progress = round($progress, 0);
        });

        return Inertia::render('Courses/Index')
            ->with('courses', $courses);
    }

    public function show(Course $course)
    {
        return response()->noContent();
    }
}
