<?php

namespace App\Http\Controllers;

use App\Models\WatchVideo;
use App\Models\VideoComment;
use App\Models\VideoRating;
use App\Services\StreakService;
use App\Services\BadgeUnlockService;
use Inertia\Inertia;
use App\Models\Course;
use App\Models\Video;
use Illuminate\Support\Str;

class WatchController extends Controller
{
    public function index(Course $course)
    {
        $user = auth()->user();
        if ($redirect = $this->ensurePersonalCourseAccess($course, $user)) {
            return $redirect;
        }
        $currentVideo = $course->currentVideo($user);

        return response()->redirectTo("/courses/{$course->id}/videos/{$currentVideo->id}");
    }

    public function show(Course $course, Video $video)
    {
        $user = auth()->user();
        if ($redirect = $this->ensurePersonalCourseAccess($course, $user)) {
            return $redirect;
        }
        $currentVideo = Video::where('id', $video->id)
            ->with(['module', 'course'])
            ->first();

        $videos = $course->videos()
            ->with('module')
            ->orderBy('order')
            ->get()
            ->map(function ($video) use ($user) {
                $watched = WatchVideo::where('user_id', $user->id)
                    ->where('video_id', $video->id)
                    ->where('status', WatchVideo::STATUS_WATCHED)
                    ->exists();

                return [
                    'id' => $video->id,
                    'title' => $video->title,
                    'description' => $video->description,
                    'url' => $video->url,
                    'time_in_seconds' => $video->time_in_seconds,
                    'watched' => $watched,
                    'module' => $video->module ? [
                        'id' => $video->module->id,
                        'name' => $video->module->name,
                    ] : null,
                ];
            });

        $this->createWatchVideo($currentVideo);

        // Get user rating for current video
        $userRating = VideoRating::where('video_id', $currentVideo->id)
            ->where('user_id', $user->id)
            ->first();

        // Get comments count
        $commentsCount = VideoComment::where('video_id', $currentVideo->id)->count();

        // Calculate progress
        $totalVideos = $videos->count();
        $completedVideos = $videos->filter(fn($v) => $v['watched'])->count();
        $progress = $totalVideos > 0 ? round(($completedVideos / $totalVideos) * 100) : 0;

        return Inertia::render('Watch/Index', [
            'course' => [
                'id' => $course->id,
                'title' => $course->title,
                'description' => $course->description,
                'thumbnail' => $course->thumbnail,
            ],
            'currentVideo' => [
                'id' => $currentVideo->id,
                'title' => $currentVideo->title,
                'description' => $currentVideo->description,
                'url' => $currentVideo->url,
                'time_in_seconds' => $currentVideo->time_in_seconds,
                'module' => $currentVideo->module ? [
                    'id' => $currentVideo->module->id,
                    'name' => $currentVideo->module->name,
                ] : null,
            ],
            'videos' => $videos,
            'userRating' => $userRating,
            'commentsCount' => $commentsCount,
            'progress' => $progress,
        ]);
    }

    public function complete(Video $video)
    {
        $user = auth()->user();
        $watchVideo = WatchVideo::where('user_id', $user->id)
            ->where('video_id', $video->id)
            ->first();

        if ($watchVideo && $watchVideo->status === WatchVideo::STATUS_WATCHED) {
            // Se já está concluído, desmarca (deleta o registro)
            $watchVideo->delete();
            return response()->json(['message' => 'Video uncompleted', 'completed' => false]);
        } else {
            // Se não está concluído, marca como concluído
            if ($watchVideo) {
                $watchVideo->update([
                    'status' => WatchVideo::STATUS_WATCHED,
                    'finished_at' => now(),
                ]);
            } else {
                WatchVideo::create([
                    'user_id' => $user->id,
                    'video_id' => $video->id,
                    'status' => WatchVideo::STATUS_WATCHED,
                    'finished_at' => now(),
                ]);
            }

            // Increment streak when video is completed
            $streakService = new StreakService();
            $streakService->incrementStreak($user);

            // Check and unlock badges for videos completed
            $badgeService = new BadgeUnlockService();
            $badgeService->checkVideosCompleted($user);
            $badgeService->checkCoursesCompleted($user);
            $badgeService->checkHoursWatched($user);

            return response()->json(['message' => 'Video completed', 'completed' => true]);
        }
    }

    private function createWatchVideo(Video $video)
    {
        $user = auth()->user();
        $watchVideo = WatchVideo::where('user_id', $user->id)
            ->where('video_id', $video->id)
            ->first();

        if (!$watchVideo) {
            WatchVideo::create([
                'user_id' => $user->id,
                'video_id' => $video->id,
                'status' => WatchVideo::STATUS_WATCHING,
            ]);
        }
    }

    private function ensurePersonalCourseAccess(Course $course, $user)
    {
        if (!$course->is_personal) {
            return null;
        }

        if ($course->responsible_id === $user->id) {
            return null;
        }

        $isEnrolled = $course->enrolledUsers()
            ->where('user_id', $user->id)
            ->exists();

        if ($isEnrolled) {
            return null;
        }

        if (!$course->share_token) {
            $course->share_token = Str::uuid();
            $course->save();
        }

        return redirect()->route('personal-courses.share', $course->share_token);
    }
}
