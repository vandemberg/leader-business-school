<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Module;
use App\Models\Video;
use Illuminate\Http\Request;

class PersonalCourseVideosController extends Controller
{
    public function store(Request $request, Course $course, Module $module)
    {
        $this->authorizePersonalCourse($course);
        $this->ensureModuleBelongsToCourse($course, $module);

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'url' => 'required|string|max:255',
            'time_in_seconds' => 'nullable|integer',
        ]);

        $maxOrder = Video::where('module_id', $module->id)->max('order') ?? 0;

        $video = new Video($data);
        $video->course_id = $course->id;
        $video->module_id = $module->id;
        $video->order = $maxOrder + 1;
        $video->save();

        return response()->json($video, 201);
    }

    public function update(Request $request, Course $course, Module $module, Video $video)
    {
        $this->authorizePersonalCourse($course);
        $this->ensureModuleBelongsToCourse($course, $module);
        $this->ensureVideoBelongsToCourse($course, $video, $module);

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'url' => 'required|string|max:255',
            'time_in_seconds' => 'nullable|integer',
        ]);

        $video->update($data);

        return response()->json($video, 200);
    }

    public function destroy(Course $course, Module $module, Video $video)
    {
        $this->authorizePersonalCourse($course);
        $this->ensureModuleBelongsToCourse($course, $module);
        $this->ensureVideoBelongsToCourse($course, $video, $module);

        $video->watchVideos()->delete();
        $video->delete();

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

    private function ensureVideoBelongsToCourse(Course $course, Video $video, Module $module): void
    {
        if ($video->course_id !== $course->id || $video->module_id !== $module->id) {
            abort(404);
        }
    }
}
