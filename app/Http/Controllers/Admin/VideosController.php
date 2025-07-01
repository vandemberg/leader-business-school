<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Controller;
use App\Models\Course;
use App\Models\Module;
use App\Models\Video;
use Illuminate\Http\Request;

class VideosController extends Controller
{
    public function store(Request $request, Course $course, Module $module)
    {
        $validatedData = $request->validate(rules: [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'url' => 'required|string|max:255',
            'transcription' => 'nullable|string',
            'time_in_seconds' => 'nullable|string|max:255',
        ]);

        $video = new Video($validatedData);
        $video->course_id = $course->id;
        $video->module_id = $module->id;
        $video->save();

        return response()->json($video, 201);
    }

    public function update(Request $request, Course $course, Module $module, Video $video)
    {
        $validatedData = $request->validate([
            'title' => 'string|max:255',
            'description' => 'nullable|string',
            'url' => 'string|max:255',
            'transcription' => 'nullable|string',
            'time_in_seconds' => 'nullable|integer',
            'status' => 'nullable|in:draft,published',
        ]);

        $video->update($validatedData);

        return response()->json($video, 200);
    }

    public function disable(Course $course, Module $module, Video $video)
    {
        $video->update(['status' => 'draft']);
        return response()->noContent(status: 204);
    }

    public function enable(Course $course, Module $module, Video $video)
    {
        $video->update(['status' => 'published']);
        return response()->noContent(status: 204);
    }

    public function destroy(Course $course, Module $module, Video $video)
    {
        $video->watchVideos()->delete();
        $video->delete();
        return response()->noContent(status: 204);
    }
}
