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
        $allCoursesWithProgress = collect();

        $allCourses->each(function ($course) use ($user, $coursesWithProgress, $allCoursesWithProgress) {
            $this->setCourseProgress($course, $user);
            $coursesWithProgress->push($course);
            $allCoursesWithProgress->push($course);
        });

        // Calculate global progress
        $totalVideos = 0;
        $totalCompletedVideos = 0;
        $totalHoursWatched = 0;

        $allCoursesWithProgress->each(function ($course) use (&$totalVideos, &$totalCompletedVideos, &$totalHoursWatched, $user) {
            $videos = $course->modules()->with('videos')->get()->pluck('videos')->flatten();
            $totalVideos += $videos->count();
            
            $completedVideos = WatchVideo::where('user_id', $user->id)
                ->whereIn('video_id', $videos->pluck('id'))
                ->where('status', WatchVideo::STATUS_WATCHED)
                ->get();
            
            $totalCompletedVideos += $completedVideos->count();
            
            // Calculate hours watched from completed videos
            $completedVideoIds = $completedVideos->pluck('video_id');
            $watchedVideos = $videos->whereIn('id', $completedVideoIds);
            $totalHoursWatched += $watchedVideos->sum('time_in_seconds') / 3600;
        });

        $globalProgress = $totalVideos > 0 ? round(($totalCompletedVideos / $totalVideos) * 100, 0) : 0;
        $totalHoursWatched = round($totalHoursWatched, 0);

        // Recent courses (in progress)
        $coursesInProgress = $coursesWithProgress
            ->filter(function ($course) {
                return $course->progress > 0 && $course->progress < 100;
            })
            ->sortByDesc('progress')
            ->take(3);

        // All courses for the grid
        $allCoursesForGrid = $allCoursesWithProgress->sortByDesc('updated_at');

        return Inertia::render('Dashboard')
            ->with('coursesInProgress', $coursesInProgress)
            ->with('allCourses', $allCoursesForGrid)
            ->with('globalProgress', $globalProgress)
            ->with('totalHoursWatched', $totalHoursWatched)
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
