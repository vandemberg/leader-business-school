<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Controller;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Tymon\JWTAuth\Facades\JWTAuth;

class TagController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $platformId = $this->getPlatformId($request);

        $query = Tag::query();

        // Filtrar tags por platform_id
        if ($platformId) {
            $query->where('platform_id', $platformId);
        }

        if ($search = $request->get('search')) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        $tags = $query->orderBy('name')->get();

        return response()->json($tags);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $platformId = $this->getPlatformId($request);

        if (!$platformId) {
            abort(403, 'Plataforma não identificada');
        }

        $data = $request->validate([
            'name' => 'required|string|max:255|unique:tags,name',
        ]);

        $data['platform_id'] = $platformId;
        $tag = Tag::create($data);

        return response()->json($tag, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Tag $tag): JsonResponse
    {
        $this->validatePlatformAccess($tag);

        return response()->json($tag);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Tag $tag): JsonResponse
    {
        $this->validatePlatformAccess($tag);

        $data = $request->validate([
            'name' => 'required|string|max:255|unique:tags,name,' . $tag->id,
        ]);

        $tag->update($data);

        return response()->json($tag);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Tag $tag)
    {
        $this->validatePlatformAccess($tag);

        // Verificar se a tag está sendo usada em algum curso
        if ($tag->courses()->count() > 0) {
            return response()->json([
                'message' => 'Não é possível remover uma tag que está associada a cursos.',
                'courses_count' => $tag->courses()->count()
            ], 422);
        }

        $tag->delete();

        return response()->noContent(204);
    }

    /**
     * Get courses associated with a tag
     */
    public function courses(Tag $tag): JsonResponse
    {
        $courses = $tag->courses()->with('course')->get();

        return response()->json($courses);
    }
}
