<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Course;
use App\Models\Tag;
use App\Models\WatchVideo;
use Illuminate\Http\Request;

class CoursesController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $platformId = current_platform_id();
        $search = $request->get('search', '');
        $category = $request->get('category', '');
        $level = $request->get('level', '');
        $sort = $request->get('sort', 'recent');

        $query = Course::with(['tags', 'responsible'])->whereNotIn('status', [Course::STATUS_DRAFT]);

        if ($platformId) {
            $query->where(function ($q) use ($platformId) {
                $q->where('platform_id', $platformId)
                    ->orWhereNull('platform_id');
            });
        }


        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }


        if ($category) {
            $query->whereHas('tags', function ($q) use ($category) {
                $q->where('tags.name', $category);
            });
        }


        switch ($sort) {
            case 'popular':

                $query->withCount('videos');
                break;
            case 'recent':
            default:
                break;
        }


        if ($sort === 'popular') {
            $query->orderBy('videos_count', 'desc');
        } else {
            $query->orderBy('updated_at', 'desc');
        }

        $courses = $query->get();


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
            $course->total_videos = $totalVideos;
        });


        // Get available categories (tags) - filter by platform if needed
        $categoriesQuery = Tag::withCount('coursesThroughPivot as courses_count');
        if ($platformId) {
            $categoriesQuery->where(function ($q) use ($platformId) {
                $q->where('platform_id', $platformId)
                    ->orWhereNull('platform_id');
            });
        }
        $categories = $categoriesQuery->orderBy('courses_count', 'desc')
            ->limit(10)
            ->get();

        return Inertia::render('Courses/Index', [
            'courses' => $courses,
            'categories' => $categories,
            'filters' => [
                'search' => $search,
                'category' => $category,
                'level' => $level,
                'sort' => $sort,
            ],
        ]);
    }

    public function show(Course $course)
    {
        return response()->noContent();
    }
}
