<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Module;
use Illuminate\Http\Request;

class PersonalCourseModulesController extends Controller
{
    public function store(Request $request, Course $course)
    {
        $this->authorizePersonalCourse($course);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $maxOrder = $course->modules()->max('order') ?? 0;

        $module = $course->modules()->create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'order' => $maxOrder + 1,
        ]);

        return response()->json($module, 201);
    }

    public function update(Request $request, Course $course, Module $module)
    {
        $this->authorizePersonalCourse($course);
        $this->ensureModuleBelongsToCourse($course, $module);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $module->update($data);

        return response()->json($module, 200);
    }

    public function destroy(Course $course, Module $module)
    {
        $this->authorizePersonalCourse($course);
        $this->ensureModuleBelongsToCourse($course, $module);

        $module->videos()->delete();
        $module->delete();

        return response()->noContent(204);
    }

    private function authorizePersonalCourse(Course $course): void
    {
        if (!$course->is_personal || $course->responsible_id !== auth()->id()) {
            abort(403, 'Curso não pertence ao usuário.');
        }
    }

    private function ensureModuleBelongsToCourse(Course $course, Module $module): void
    {
        if ($module->course_id !== $course->id) {
            abort(404);
        }
    }
}
