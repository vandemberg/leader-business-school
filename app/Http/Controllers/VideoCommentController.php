<?php

namespace App\Http\Controllers;

use App\Models\Video;
use App\Models\VideoComment;
use App\Models\VideoCommentReply;
use App\Models\VideoCommentLike;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VideoCommentController extends Controller
{
    public function store(Request $request, Video $video)
    {
        $request->validate([
            'content' => 'required|string|max:5000',
        ]);

        $comment = VideoComment::create([
            'video_id' => $video->id,
            'user_id' => Auth::id(),
            'content' => $request->input('content'),
        ]);

        return response()->json([
            'success' => true,
            'comment' => $comment->load('user'),
        ]);
    }

    public function reply(Request $request, VideoComment $comment)
    {
        $request->validate([
            'content' => 'required|string|max:5000',
        ]);

        $reply = VideoCommentReply::create([
            'comment_id' => $comment->id,
            'user_id' => Auth::id(),
            'content' => $request->input('content'),
        ]);

        return response()->json([
            'success' => true,
            'reply' => $reply->load('user'),
        ]);
    }

    public function toggleLike(Request $request, VideoComment $comment)
    {
        $user = Auth::user();
        $type = $request->input('type', 'like'); // 'like' or 'dislike'

        $existingLike = VideoCommentLike::where('comment_id', $comment->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existingLike) {
            if ($existingLike->type === $type) {
                // Remove like/dislike if clicking the same type
                $existingLike->delete();
                $action = 'removed';
            } else {
                // Toggle type if clicking different type
                $existingLike->update(['type' => $type]);
                $action = 'toggled';
            }
        } else {
            // Create new like/dislike
            VideoCommentLike::create([
                'comment_id' => $comment->id,
                'user_id' => $user->id,
                'type' => $type,
            ]);
            $action = 'added';
        }

        return response()->json([
            'success' => true,
            'action' => $action,
            'likes' => $comment->likeCount(),
            'dislikes' => $comment->dislikeCount(),
        ]);
    }

    public function index(Request $request, Video $video)
    {
        $sortBy = $request->input('sort', 'recent'); // 'recent' or 'relevant'

        $comments = VideoComment::where('video_id', $video->id)
            ->with(['user', 'replies.user', 'likes'])
            ->get()
            ->map(function ($comment) {
                return [
                    'id' => $comment->id,
                    'content' => $comment->content,
                    'user' => [
                        'id' => $comment->user->id,
                        'name' => $comment->user->name,
                    ],
                    'created_at' => $comment->created_at->diffForHumans(),
                    'created_at_raw' => $comment->created_at->timestamp,
                    'likes' => $comment->likeCount(),
                    'dislikes' => $comment->dislikeCount(),
                    'replies' => $comment->replies->map(function ($reply) {
                        return [
                            'id' => $reply->id,
                            'content' => $reply->content,
                            'user' => [
                                'id' => $reply->user->id,
                                'name' => $reply->user->name,
                            ],
                            'created_at' => $reply->created_at->diffForHumans(),
                        ];
                    }),
                ];
            });

        // Apply sorting
        if ($sortBy === 'relevant') {
            // Sort by relevance: (likes - dislikes) descending, then by created_at descending
            $comments = $comments->sort(function ($a, $b) {
                $scoreA = $a['likes'] - $a['dislikes'];
                $scoreB = $b['likes'] - $b['dislikes'];

                // First sort by score (relevance)
                if ($scoreA !== $scoreB) {
                    return $scoreB <=> $scoreA; // Descending order
                }

                // If scores are equal, sort by date (most recent first)
                return $b['created_at_raw'] <=> $a['created_at_raw'];
            })->values();
        } else {
            // Sort by most recent (default)
            $comments = $comments->sortByDesc('created_at_raw')->values();
        }

        return response()->json([
            'success' => true,
            'comments' => $comments,
        ]);
    }
}
