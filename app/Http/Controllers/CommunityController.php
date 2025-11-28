<?php

namespace App\Http\Controllers;

use App\Models\CommunityPost;
use App\Models\PostComment;
use App\Models\PostLike;
use App\Models\PostTag;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class CommunityController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $platformId = current_platform_id();
        $search = $request->get('search', '');
        $tag = $request->get('tag', '');

        $query = CommunityPost::with(['user', 'likes', 'comments'])
            ->withCount(['likes', 'comments']);

        // Filter by platform
        if ($platformId) {
            $query->where('platform_id', $platformId);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%");
            });
        }

        if ($tag) {
            $query->where('tag', $tag);
        }

        $posts = $query->orderBy('created_at', 'desc')
            ->paginate(10);

        // Popular tags (filtered by platform)
        $popularTagsQuery = PostTag::orderBy('usage_count', 'desc');
        // Note: PostTag doesn't have platform_id, but we can filter by posts if needed
        $popularTags = $popularTagsQuery->limit(10)->get();

        // Top contributors (users with most posts in current platform)
        $topContributorsQuery = DB::table('community_posts')
            ->select('users.id', 'users.name', DB::raw('COUNT(community_posts.id) as posts_count'))
            ->join('users', 'community_posts.user_id', '=', 'users.id');

        if ($platformId) {
            $topContributorsQuery->where('community_posts.platform_id', $platformId);
        }

        $topContributors = $topContributorsQuery->groupBy('users.id', 'users.name')
            ->orderBy('posts_count', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($contributor) {
                return [
                    'id' => $contributor->id,
                    'name' => $contributor->name,
                    'posts_count' => $contributor->posts_count,
                    'points' => $contributor->posts_count * 10, // Simple points calculation
                ];
            });

        // Mark if user liked each post
        foreach ($posts->items() as $post) {
            $post->is_liked = $post->isLikedBy($user->id);
        }

        return Inertia::render('Community/Index', [
            'posts' => $posts,
            'popularTags' => $popularTags,
            'topContributors' => $topContributors,
            'filters' => [
                'search' => $search,
                'tag' => $tag,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'tag' => 'nullable|string|max:50',
        ]);

        $post = CommunityPost::create([
            'user_id' => auth()->id(),
            'platform_id' => current_platform_id(),
            'title' => $validated['title'],
            'content' => $validated['content'],
            'tag' => $validated['tag'] ?? null,
        ]);

        // Update tag usage count
        if ($validated['tag']) {
            PostTag::firstOrCreate(['name' => $validated['tag']])
                ->increment('usage_count');
        }

        return redirect()->back()->with('success', 'Postagem criada com sucesso!');
    }

    public function toggleLike(Request $request, CommunityPost $post)
    {
        $user = auth()->user();
        $like = PostLike::where('post_id', $post->id)
            ->where('user_id', $user->id)
            ->first();

        if ($like) {
            $like->delete();
            $post->decrement('likes_count');
            $liked = false;
        } else {
            PostLike::create([
                'post_id' => $post->id,
                'user_id' => $user->id,
            ]);
            $post->increment('likes_count');
            $liked = true;
        }

        return response()->json([
            'liked' => $liked,
            'likes_count' => $post->fresh()->likes_count,
        ]);
    }

    public function storeComment(Request $request, CommunityPost $post)
    {
        $validated = $request->validate([
            'content' => 'required|string',
            'parent_id' => 'nullable|exists:post_comments,id',
        ]);

        $comment = PostComment::create([
            'post_id' => $post->id,
            'user_id' => auth()->id(),
            'content' => $validated['content'],
            'parent_id' => $validated['parent_id'] ?? null,
        ]);

        $post->increment('comments_count');

        return redirect()->back()->with('success', 'Comentário adicionado!');
    }
}
