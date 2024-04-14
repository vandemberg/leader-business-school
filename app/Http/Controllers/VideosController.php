<?php

namespace App\Http\Controllers;

use App\Models\WatchVideo;
use App\Models\Video;

class VideosController extends Controller
{
    public function startWatch(Video $video)
    {
        $user = auth()->user();

        WatchVideo::create([
            'user_id' => $user->id,
            'video_id' => $video->id,
            'status' => 'watching',
        ]);

        return response()->json([
            'success' => true
        ])->setStatusCode(200);
    }

    public function finishWatch(Video $video)
    {
        $user = auth()->user();

        $watchVideo = WatchVideo::where('user_id', $user->id)
            ->where('video_id', $video->id)
            ->first();

        $watchVideo->update([
            'status' => 'finished',
            'finished_at' => now(),
        ]);

        return response()->json([
            'success' => true
        ])->setStatusCode(200);
    }
}
