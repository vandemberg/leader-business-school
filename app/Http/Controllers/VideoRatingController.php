<?php

namespace App\Http\Controllers;

use App\Models\Video;
use App\Models\VideoRating;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VideoRatingController extends Controller
{
    public function store(Request $request, Video $video)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'feedback' => 'nullable|string|max:2000',
        ]);

        $rating = VideoRating::updateOrCreate(
            [
                'video_id' => $video->id,
                'user_id' => Auth::id(),
            ],
            [
                'rating' => $request->rating,
                'feedback' => $request->feedback,
            ]
        );

        return response()->json([
            'success' => true,
            'rating' => $rating,
            'average_rating' => $video->fresh()->averageRating(),
        ]);
    }

    public function show(Video $video)
    {
        $userRating = VideoRating::where('video_id', $video->id)
            ->where('user_id', Auth::id())
            ->first();

        return response()->json([
            'success' => true,
            'user_rating' => $userRating,
            'average_rating' => $video->averageRating(),
            'total_ratings' => $video->ratings()->count(),
        ]);
    }
}
