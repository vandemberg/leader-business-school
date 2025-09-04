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
        $query = Teacher::query();
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
