<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Controller;
use App\Models\HelpCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class HelpCategoriesController extends Controller
{
    public function index(Request $request)
    {
        $platformId = $this->getPlatformId($request);
        $query = HelpCategory::withCount('articles')->with('platform');

        // Filtrar por plataforma
        if ($platformId) {
            $query->where('platform_id', $platformId);
        }

        // Busca
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $categories = $query->orderBy('created_at', 'desc')->get();

        return response()->json($categories);
    }

    public function show(HelpCategory $helpCategory)
    {
        $this->validatePlatformAccess($helpCategory);
        $helpCategory->load('platform');
        $helpCategory->loadCount('articles');

        return response()->json($helpCategory);
    }

    public function store(Request $request)
    {
        $platformId = $this->getPlatformId($request);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'icon' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'platform_id' => 'nullable|integer|exists:platforms,id',
        ]);

        // Usar platform_id do request ou do contexto antes de validar slug único
        $validated['platform_id'] = $validated['platform_id'] ?? $platformId;

        // Gerar slug se não fornecido
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);

            // Garantir que o slug seja único para esta plataforma
            $originalSlug = $validated['slug'];
            $counter = 1;
            while (HelpCategory::where('slug', $validated['slug'])
                ->where('platform_id', $validated['platform_id'])
                ->exists()) {
                $validated['slug'] = $originalSlug . '-' . $counter;
                $counter++;
            }
        } else {
            // Verificar se o slug já existe para esta plataforma
            if (HelpCategory::where('slug', $validated['slug'])
                ->where('platform_id', $validated['platform_id'])
                ->exists()) {
                return response()->json(['error' => 'Slug já existe para esta plataforma'], 422);
            }
        }

        $category = HelpCategory::create($validated);
        $category->load('platform');
        $category->loadCount('articles');

        return response()->json($category, 201);
    }

    public function update(Request $request, HelpCategory $helpCategory)
    {
        $this->validatePlatformAccess($helpCategory);
        $platformId = $this->getPlatformId($request);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'slug' => 'sometimes|nullable|string|max:255',
            'icon' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'platform_id' => 'nullable|integer|exists:platforms,id',
        ]);

        // Não permitir alterar platform_id se já estiver definido
        if (isset($validated['platform_id']) && $helpCategory->platform_id !== null) {
            if ($validated['platform_id'] !== $helpCategory->platform_id) {
                return response()->json(['error' => 'Não é possível alterar a plataforma da categoria'], 403);
            }
        } else {
            // Se não tiver platform_id definido, usar do contexto
            $validated['platform_id'] = $validated['platform_id'] ?? $platformId;
        }

        // Gerar slug se nome foi alterado e slug não foi fornecido
        if (isset($validated['name']) && !isset($validated['slug'])) {
            $newSlug = Str::slug($validated['name']);
            if ($newSlug !== $helpCategory->slug) {
                $originalSlug = $newSlug;
                $counter = 1;
                $finalPlatformId = $validated['platform_id'] ?? $helpCategory->platform_id;
                while (HelpCategory::where('slug', $newSlug)
                    ->where('platform_id', $finalPlatformId)
                    ->where('id', '!=', $helpCategory->id)
                    ->exists()) {
                    $newSlug = $originalSlug . '-' . $counter;
                    $counter++;
                }
                $validated['slug'] = $newSlug;
            }
        } else if (isset($validated['slug'])) {
            // Validar se o slug já existe para esta plataforma (exceto o próprio registro)
            $finalPlatformId = $validated['platform_id'] ?? $helpCategory->platform_id;
            if (HelpCategory::where('slug', $validated['slug'])
                ->where('platform_id', $finalPlatformId)
                ->where('id', '!=', $helpCategory->id)
                ->exists()) {
                return response()->json(['error' => 'Slug já existe para esta plataforma'], 422);
            }
        }

        $helpCategory->update($validated);
        $helpCategory->load('platform');
        $helpCategory->loadCount('articles');

        return response()->json($helpCategory);
    }

    public function destroy(HelpCategory $helpCategory)
    {
        $this->validatePlatformAccess($helpCategory);

        // Verificar se há artigos associados
        if ($helpCategory->articles()->count() > 0) {
            return response()->json(['error' => 'Não é possível remover categoria com artigos associados'], 422);
        }

        $helpCategory->delete();

        return response()->json(['message' => 'Categoria removida com sucesso'], 200);
    }
}

