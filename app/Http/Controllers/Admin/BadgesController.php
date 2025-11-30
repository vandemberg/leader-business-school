<?php

namespace App\Http\Controllers\Admin;

use App\Models\Badge;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BadgesController extends Controller
{
    public function index(Request $request)
    {
        $platformId = $this->getPlatformId($request);

        $query = Badge::query();

        if ($platformId) {
            $query->where('platform_id', $platformId);
        }

        $badges = $query->orderBy('created_at', 'desc')->get();

        return response()->json($badges, 200);
    }

    public function store(Request $request)
    {
        $platformId = $this->getPlatformId($request);

        if (!$platformId) {
            return response()->json([
                'message' => 'Plataforma não identificada'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'type' => 'required|string|in:' . implode(',', Badge::TYPES),
            'icon' => 'nullable|string|max:255',
            'color' => 'required|string|in:' . implode(',', Badge::COLORS),
            'threshold' => 'required|integer|min:1',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Dados inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $validator->validated();
        $data['platform_id'] = $platformId;
        $data['is_active'] = $request->has('is_active') ? $data['is_active'] : true;

        $badge = Badge::create($data);

        return response()->json($badge, 201);
    }

    public function show(Request $request, Badge $badge)
    {
        $platformId = $this->getPlatformId($request);

        if ($platformId && $badge->platform_id !== $platformId) {
            return response()->json([
                'message' => 'Badge não pertence à sua plataforma'
            ], 403);
        }

        return response()->json($badge, 200);
    }

    public function update(Request $request, Badge $badge)
    {
        $platformId = $this->getPlatformId($request);

        if ($platformId && $badge->platform_id !== $platformId) {
            return response()->json([
                'message' => 'Badge não pertence à sua plataforma'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'type' => 'sometimes|required|string|in:' . implode(',', Badge::TYPES),
            'icon' => 'nullable|string|max:255',
            'color' => 'sometimes|required|string|in:' . implode(',', Badge::COLORS),
            'threshold' => 'sometimes|required|integer|min:1',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Dados inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $validator->validated();
        $badge->update($data);

        return response()->json($badge, 200);
    }

    public function destroy(Request $request, Badge $badge)
    {
        $platformId = $this->getPlatformId($request);

        if ($platformId && $badge->platform_id !== $platformId) {
            return response()->json([
                'message' => 'Badge não pertence à sua plataforma'
            ], 403);
        }

        $badge->delete();

        return response()->json([
            'message' => 'Badge removido com sucesso'
        ], 200);
    }
}

