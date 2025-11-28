<?php

namespace App\Http\Controllers;

use App\Models\HelpArticle;
use App\Models\HelpCategory;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HelpController extends Controller
{
    public function index(Request $request)
    {
        $platformId = current_platform_id();
        $search = $request->get('search', '');
        $categorySlug = $request->get('category', '');

        // Filter categories by platform
        $categoriesQuery = HelpCategory::withCount('articles');
        if ($platformId) {
            $categoriesQuery->where('platform_id', $platformId);
        }
        $categories = $categoriesQuery->orderBy('order')->get();

        // Filter articles by platform
        $query = HelpArticle::with('category');
        if ($platformId) {
            $query->where('platform_id', $platformId);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('question', 'like', "%{$search}%")
                    ->orWhere('answer', 'like', "%{$search}%");
            });
        }

        if ($categorySlug) {
            $query->whereHas('category', function ($q) use ($categorySlug) {
                $q->where('slug', $categorySlug);
            });
        }

        // Clone the query before applying FAQ filter to avoid affecting the articles query
        $faqs = (clone $query)->where('is_faq', true)
            ->orderBy('order')
            ->get();

        $articles = $query->orderBy('order')
            ->get();

        return Inertia::render('Help/Index', [
            'categories' => $categories,
            'faqs' => $faqs,
            'articles' => $articles,
            'filters' => [
                'search' => $search,
                'category' => $categorySlug,
            ],
        ]);
    }

    public function show(HelpArticle $article)
    {
        $article->increment('views_count');
        $article->load('category');

        return Inertia::render('Help/Show', [
            'article' => $article,
        ]);
    }

    public function category(HelpCategory $category)
    {
        $platformId = current_platform_id();
        $articlesQuery = $category->articles();

        // Ensure articles belong to current platform
        if ($platformId) {
            $articlesQuery->where('platform_id', $platformId);
        }

        $articles = $articlesQuery->orderBy('order')->get();

        return Inertia::render('Help/Category', [
            'category' => $category,
            'articles' => $articles,
        ]);
    }
}
