<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Controller;
use Illuminate\Http\Request;
use App\Models\Teacher;
use Illuminate\Support\Facades\Storage;

class TeachersController extends Controller
{
    public function index(Request $request)
    {
        $platformId = $this->getPlatformId($request);

        $query = Teacher::query();

        // Filtrar teachers que têm cursos na plataforma atual
        if ($platformId) {
            $query->whereHas('courses', function ($q) use ($platformId) {
                $q->where(function ($subQ) use ($platformId) {
                    $subQ->where('platform_id', $platformId)
                         ->orWhereNull('platform_id');
                });
            });
        }

        if ($search = $request->get('search')) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        $teachers = $query->get();
        $teachers->each(function (Teacher $teacher) {
            if ($teacher->avatar_url) {
                $teacher->avatar_url = url('/') . $teacher->avatar_url;
            }
        });
        return response()->json($teachers);
    }

    public function show(Teacher $teacher)
    {
        $platformId = $this->getPlatformId(request());

        // Validar que teacher tem pelo menos um course na plataforma
        if ($platformId) {
            $hasCourseInPlatform = $teacher->courses()
                ->where(function ($q) use ($platformId) {
                    $q->where('platform_id', $platformId)
                      ->orWhereNull('platform_id');
                })
                ->exists();

            if (!$hasCourseInPlatform) {
                abort(403, 'Professor não possui cursos na sua plataforma');
            }
        }

        if ($teacher->avatar_url) {
            $teacher->avatar_url = url('/') . $teacher->avatar_url;
        }
        return response()->json($teacher);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:teachers,email',
            'phone' => 'nullable|string',
            'bio' => 'nullable|string',
            'avatar' => 'nullable|file|mimes:jpeg,jpg,png,gif|max:20480', // 20MB
        ]);
        $teacher = Teacher::create($data);
        if ($request->hasFile('avatar')) {
            $avatar = $this->registerAvatar($request, $teacher);
            $teacher->avatar_url = $avatar;
            $teacher->save();
        }
        return response()->json($teacher, 201);
    }

    public function update(Request $request, Teacher $teacher)
    {
        $platformId = $this->getPlatformId($request);

        // Validar que teacher tem courses na plataforma
        if ($platformId) {
            $hasCourseInPlatform = $teacher->courses()
                ->where(function ($q) use ($platformId) {
                    $q->where('platform_id', $platformId)
                      ->orWhereNull('platform_id');
                })
                ->exists();

            if (!$hasCourseInPlatform) {
                abort(403, 'Professor não possui cursos na sua plataforma');
            }
        }

        $data = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:teachers,email,' . $teacher->id,
            'phone' => 'nullable|string',
            'bio' => 'nullable|string',
            'avatar' => 'nullable|file|mimes:jpeg,jpg,png,gif|max:20480', // 20MB
        ]);
        $teacher->update($data);
        if ($request->hasFile('avatar')) {
            $avatar = $this->registerAvatar($request, $teacher);
            $teacher->avatar_url = $avatar;
            $teacher->save();
        }
        return response()->json($teacher);
    }

    public function destroy(Teacher $teacher)
    {
        $platformId = $this->getPlatformId(request());

        // Validar que teacher tem courses na plataforma
        if ($platformId) {
            $hasCourseInPlatform = $teacher->courses()
                ->where(function ($q) use ($platformId) {
                    $q->where('platform_id', $platformId)
                      ->orWhereNull('platform_id');
                })
                ->exists();

            if (!$hasCourseInPlatform) {
                abort(403, 'Professor não possui cursos na sua plataforma');
            }
        }

        $teacher->delete();
        return response()->json(['message' => 'Professor removido com sucesso.']);
    }

    private function registerAvatar(Request $request, Teacher $teacher)
    {
        $file = $request->file('avatar');
        $filename = 'avatar_' . $teacher->id . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('avatars', $filename, 'public');
        return '/storage/' . $path;
    }
}
