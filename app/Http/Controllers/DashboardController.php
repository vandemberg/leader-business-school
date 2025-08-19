<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Platform;
use App\Models\PlatformUser;
use App\Models\WatchVideo;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Session\Session;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $courses = Course::all();

        $session = new Session();
        $platformId = $session->get('platform_id');

        if (empty($platform)) {
            $userPlatform = PlatformUser::where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->first();

            if (empty($platformId)) {
                $platformId = Platform::first()->id;
                PlatformUser::create([
                    'user_id' => $user->id,
                    'platform_id' => $platformId,
                ]);
            }
        }

        $platform = Platform::find($platformId);
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

        return Inertia::render('Dashboard')
            ->with('courses', $courses)
            ->with('platform', $platform);
    }
}
