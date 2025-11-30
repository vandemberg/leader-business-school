<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Controller;
use App\Models\HelpArticle;
use App\Models\HelpCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class HelpArticlesController extends Controller
{
    public function index(Request $request)
    {
        $platformId = $this->getPlatformId($request);
        $query = HelpArticle::with('category', 'platform');

        // Filtrar por plataforma
        if ($platformId) {
            $query->where('platform_id', $platformId);
        }

        // Filtrar por FAQ
        if ($request->has('is_faq')) {
            $query->where('is_faq', $request->boolean('is_faq'));
        }

        // Filtrar por categoria
        if ($request->has('category_id')) {
            $query->where('category_id', $request->get('category_id'));
        }

        // Busca
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('question', 'like', "%{$search}%")
                    ->orWhere('answer', 'like', "%{$search}%");
            });
        }

        $articles = $query->orderBy('created_at', 'desc')->get();

        return response()->json($articles);
    }

    public function show(HelpArticle $helpArticle)
    {
        $this->validatePlatformAccess($helpArticle);
        $helpArticle->load('category', 'platform');

        return response()->json($helpArticle);
    }

    public function store(Request $request)
    {
        $platformId = $this->getPlatformId($request);

        $validated = $request->validate([
            'category_id' => 'required|integer|exists:help_categories,id',
            'question' => 'required|string|max:255',
            'answer' => 'required|string',
            'is_faq' => 'nullable|boolean',
            'platform_id' => 'nullable|integer|exists:platforms,id',
        ]);

        // Validar que a categoria pertence à plataforma
        if ($platformId) {
            $category = HelpCategory::findOrFail($validated['category_id']);
            if ($category->platform_id !== null && $category->platform_id !== $platformId) {
                return response()->json(['error' => 'Categoria não pertence à sua plataforma'], 403);
            }
        }

        // Usar platform_id do request ou do contexto
        $validated['platform_id'] = $validated['platform_id'] ?? $platformId;

        $article = HelpArticle::create($validated);
        $article->load('category', 'platform');

        return response()->json($article, 201);
    }

    public function update(Request $request, HelpArticle $helpArticle)
    {
        $this->validatePlatformAccess($helpArticle);
        $platformId = $this->getPlatformId($request);

        $validated = $request->validate([
            'category_id' => 'sometimes|required|integer|exists:help_categories,id',
            'question' => 'sometimes|required|string|max:255',
            'answer' => 'sometimes|required|string',
            'is_faq' => 'nullable|boolean',
            'platform_id' => 'nullable|integer|exists:platforms,id',
        ]);

        // Validar que a categoria pertence à plataforma se fornecida
        if (isset($validated['category_id']) && $platformId) {
            $category = HelpCategory::findOrFail($validated['category_id']);
            if ($category->platform_id !== null && $category->platform_id !== $platformId) {
                return response()->json(['error' => 'Categoria não pertence à sua plataforma'], 403);
            }
        }

        // Não permitir alterar platform_id se já estiver definido
        if (isset($validated['platform_id']) && $helpArticle->platform_id !== null) {
            if ($validated['platform_id'] !== $helpArticle->platform_id) {
                return response()->json(['error' => 'Não é possível alterar a plataforma do artigo'], 403);
            }
        } else {
            // Se não tiver platform_id definido, usar do contexto
            $validated['platform_id'] = $validated['platform_id'] ?? $platformId;
        }

        $helpArticle->update($validated);
        $helpArticle->load('category', 'platform');

        return response()->json($helpArticle);
    }

    public function destroy(HelpArticle $helpArticle)
    {
        $this->validatePlatformAccess($helpArticle);
        $helpArticle->delete();

        return response()->json(['message' => 'Artigo removido com sucesso'], 200);
    }
}

