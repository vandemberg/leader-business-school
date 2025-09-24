<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\WatchVideo;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $allCourses = Course::all();

        // Usa o helper para obter plataforma atual (já garantida pelo middleware)
        $platform = current_platform();

        $coursesWithProgress = collect();
        $coursesInProgress = collect();
        $updatedCourses = collect();

        $allCourses->each(function ($course) use ($user, $coursesWithProgress) {
            $this->setCourseProgress($course, $user);
            $coursesWithProgress->push($course);
        });

        $coursesInProgress = $coursesWithProgress
            ->filter(function ($course) {
                return $course->progress > 0 && $course->progress < 100;
            })
            ->sortByDesc('progress')
            ->take(4);

        $updatedCourses = Course::all()
            ->take(4)
            ->map(function ($course) use ($user) {
                $this->setCourseProgress($course, $user);
                return $course;
            });

        return Inertia::render('Dashboard')
            ->with('coursesInProgress', $coursesInProgress)
            ->with('updatedCourses', $updatedCourses)
            ->with('platform', $platform);
    }

    private function setCourseProgress($course, $user)
    {
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
        $course->totalVideos = $totalVideos;
        $course->completedVideos = $totalDone;
    }
}
