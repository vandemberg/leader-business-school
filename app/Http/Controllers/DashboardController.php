<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\WatchVideo;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $courses = Course::all();

        $courses->each(function ($course) use ($user) {
            if (strpos($course->thumbnail, 'thumbnails/') !== false) {
                $course->thumbnail = url('/') . $course->thumbnail;
            }
        });

        $courses->each(function ($course) use ($user) {
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

        return Inertia::render('Dashboard')
            ->with('courses', $courses);
    }
}
