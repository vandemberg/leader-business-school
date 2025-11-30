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
        $this->validateCoursePlatform($course);
        $this->validatePlatformAccessThroughCourse($module, $course);

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

        // Define platform_id baseado no course
        $platformId = $this->getPlatformId($request);
        if ($platformId) {
            $video->platform_id = $platformId;
        } elseif ($course->platform_id) {
            $video->platform_id = $course->platform_id;
        }

        // Definir order como máximo + 1 do módulo
        $maxOrder = Video::where('module_id', $module->id)->max('order') ?? 0;
        $video->order = $maxOrder + 1;

        $video->save();

        return response()->json($video, 201);
    }

    public function update(Request $request, Course $course, Module $module, Video $video)
    {
        $this->validateCoursePlatform($course);
        $this->validatePlatformAccessThroughCourse($module, $course);
        $this->validatePlatformAccessThroughCourse($video, $course);

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
        $this->validateCoursePlatform($course);
        $this->validatePlatformAccessThroughCourse($video, $course);

        $video->update(['status' => 'draft']);
        return response()->noContent(status: 204);
    }

    public function enable(Course $course, Module $module, Video $video)
    {
        $this->validateCoursePlatform($course);
        $this->validatePlatformAccessThroughCourse($video, $course);

        $video->update(['status' => 'published']);
        return response()->noContent(status: 204);
    }

    public function destroy(Course $course, Module $module, Video $video)
    {
        $this->validateCoursePlatform($course);
        $this->validatePlatformAccessThroughCourse($video, $course);

        $video->watchVideos()->delete();
        $video->delete();
        return response()->noContent(status: 204);
    }

    public function reorder(Request $request, Course $course)
    {
        $this->validateCoursePlatform($course);

        $validatedData = $request->validate([
            'videos' => 'required|array',
            'videos.*.id' => 'required|exists:videos,id',
            'videos.*.order' => 'required|integer|min:1',
            'videos.*.module_id' => 'required|exists:modules,id',
        ]);

        // Validar que todos os vídeos pertencem ao curso
        $videoIds = collect($validatedData['videos'])->pluck('id');
        $courseVideoIds = $course->videos()->select('videos.id')->pluck('videos.id');

        $invalidVideos = $videoIds->diff($courseVideoIds);
        if ($invalidVideos->isNotEmpty()) {
            return response()->json([
                'message' => 'Alguns vídeos não pertencem a este curso',
                'invalid_videos' => $invalidVideos->values()
            ], 422);
        }

        // Validar que todos os módulos pertencem ao curso
        $moduleIds = collect($validatedData['videos'])->pluck('module_id')->unique();
        $courseModuleIds = $course->modules()->select('modules.id')->pluck('modules.id');

        $invalidModules = $moduleIds->diff($courseModuleIds);
        if ($invalidModules->isNotEmpty()) {
            return response()->json([
                'message' => 'Alguns módulos não pertencem a este curso',
                'invalid_modules' => $invalidModules->values()
            ], 422);
        }

        // Validar que todos os vídeos pertencem à plataforma
        $platformId = $this->getPlatformId($request);
        if ($platformId) {
            foreach ($validatedData['videos'] as $videoData) {
                $video = Video::find($videoData['id']);
                if ($video && $video->course_id !== $course->id) {
                    continue; // Já validado acima
                }
                if ($video && $video->course->platform_id !== null && $video->course->platform_id !== $platformId) {
                    abort(403, 'Alguns vídeos não pertencem à sua plataforma');
                }
            }
        }

        // Atualizar order e module_id de cada vídeo
        foreach ($validatedData['videos'] as $videoData) {
            Video::where('id', $videoData['id'])->update([
                'order' => $videoData['order'],
                'module_id' => $videoData['module_id'],
            ]);
        }

        return response()->json(['message' => 'Ordem dos vídeos atualizada com sucesso'], 200);
    }
}
