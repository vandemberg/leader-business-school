<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\WatchVideo;
use App\Services\StreakService;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $platformId = current_platform_id();
        $platform = current_platform();

        // Filter courses by platform - include courses without platform_id for backward compatibility
        // Exclude draft courses
        $coursesQuery = Course::query()->whereNotIn('status', [Course::STATUS_DRAFT]);
        if ($platformId) {
            $coursesQuery->where(function ($q) use ($platformId) {
                $q->where('platform_id', $platformId)
                  ->orWhereNull('platform_id');
            });
        }
        $allCourses = $coursesQuery->get();

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

        // Recent courses - based on last access time (when user last watched a video from the course)
        // If no videos watched, use course updated_at
        $recentCourses = $allCoursesWithProgress->map(function ($course) use ($user) {
            $videos = $course->modules()->with('videos')->get()->pluck('videos')->flatten();
            $videoIds = $videos->pluck('id');

            // Get the most recent watch video for this course
            $lastWatchVideo = WatchVideo::where('user_id', $user->id)
                ->whereIn('video_id', $videoIds)
                ->orderBy('updated_at', 'desc')
                ->first();

            // Set last accessed time: use watch video updated_at if exists, otherwise use course updated_at
            if ($lastWatchVideo) {
                $course->lastAccessedAt = $lastWatchVideo->updated_at;
                $course->hasBeenAccessed = true;
            } else {
                $course->lastAccessedAt = $course->updated_at;
                $course->hasBeenAccessed = false;
            }

            return $course;
        })
        ->sortByDesc(function ($course) {
            // Prioritize courses that have been accessed, then sort by date
            $priority = $course->hasBeenAccessed ? 1000000 : 0;
            return $priority + ($course->lastAccessedAt ? $course->lastAccessedAt->timestamp : 0);
        })
        ->take(3)
        ->values();

        // All courses for the grid
        $allCoursesForGrid = $allCoursesWithProgress->sortByDesc('updated_at');

        // Courses in progress (progress > 0 and < 100)
        $coursesInProgressForGrid = $allCoursesWithProgress
            ->filter(function ($course) {
                return $course->progress > 0 && $course->progress < 100;
            })
            ->sortByDesc('updated_at')
            ->values();

        // Get streak information
        $streakService = new StreakService();
        $streakInfo = $streakService->getStreakInfo($user);

        return Inertia::render('Dashboard')
            ->with('coursesInProgress', $recentCourses)
            ->with('allCourses', $allCoursesForGrid)
            ->with('coursesInProgressForGrid', $coursesInProgressForGrid)
            ->with('globalProgress', $globalProgress)
            ->with('totalHoursWatched', $totalHoursWatched)
            ->with('streak', $streakInfo);
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
