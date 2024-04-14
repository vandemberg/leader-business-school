<?php

namespace App\Http\Controllers;

use App\Models\WatchVideo;
use Inertia\Inertia;
use App\Models\Course;
use App\Models\Video;

class WatchController extends Controller
{
    public function index(Course $course)
    {
        $user = auth()->user();
        $currentVideo = $course->currentVideo($user);

        return response()->redirectTo("/courses/{$course->id}/videos/{$currentVideo->id}");
    }

    public function show(Course $course, Video $video)
    {
        $videos = $course->videos()->get();
        $currentVideo = $videos->where('id', $video->id)->first();

        $this->createWatchVideo($currentVideo);

        return Inertia::render('Watch/Index', [
            'course' => $course,
            'currentVideo' => $currentVideo,
            'videos' => $videos,
        ]);
    }

    public function complete(Video $video)
    {
        $user = auth()->user();
        $watchVideo = WatchVideo::where('user_id', $user->id)
            ->where('video_id', $video->id)
            ->first();

        if ($watchVideo) {
            $watchVideo->update([
                'status' => WatchVideo::STATUS_WATCHED,
            ]);
        }

        return response()->json(['message' => 'Video completed']);
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
}
