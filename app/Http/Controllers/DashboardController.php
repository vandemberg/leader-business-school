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
            $videos = $course->videos()->get();
            $totalVideos = $videos->count();
            $totalDone = WatchVideo::where('user_id', $user->id)
                ->whereIn('video_id', $videos->pluck('id'))
                ->where('status', WatchVideo::STATUS_WATCHED)
                ->get()
                ->count();

            $progress = ($totalDone / $totalVideos) * 100;
            $course->progress = round($progress, 0);
        });

        return Inertia::render('Dashboard')
            ->with('courses', $courses);
    }
}
