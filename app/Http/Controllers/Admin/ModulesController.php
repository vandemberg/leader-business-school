<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Controller;
use App\Models\Course;
use App\Models\Module;
use Illuminate\Http\Request;

class ModulesController extends Controller
{
    public function index(Request $request, Course $course)
    {
        $this->validateCoursePlatform($course);

        $modules = $course->modules()->orderBy('order')->with('videos')->get();

        return response()->json(data: $modules, status: 200);
    }

    public function store(Request $request, Course $course)
    {
        $this->validateCoursePlatform($course);

        $data = $request->validate([
            'name' => 'required|string',
            'description' => 'string',
        ]);

        $data['status'] = Module::STATUS_DRAFT;

        $maxOrder = Module::where('course_id', $course->id)->max('order') ?? 0;
        $data['order'] = $maxOrder + 1;

        // Define platform_id baseado no course
        $platformId = $this->getPlatformId($request);
        if ($platformId) {
            $data['platform_id'] = $platformId;
        } elseif ($course->platform_id) {
            $data['platform_id'] = $course->platform_id;
        }

        $module = $course->modules()->create($data);

        return response()->json(data: $module, status: 201);
    }

    public function update(Request $request, Course $course, Module $module)
    {
        $this->validateCoursePlatform($course);
        $this->validatePlatformAccessThroughCourse($module, $course);

        $data = $request->validate([
            'name' => 'string',
            'description' => 'string',
            'status' => 'string|in:draft,published',
        ]);

        $module->update($data);

        return response()->json(data: $module, status: 200);
    }

    public function destroy(Course $course, Module $module)
    {
        $this->validateCoursePlatform($course);
        $this->validatePlatformAccessThroughCourse($module, $course);

        $module->videos()->delete();

        $module->delete();
        return response()->json(status: 204);
    }

    public function reorder(Request $request, Course $course)
    {
        $this->validateCoursePlatform($course);

        $validatedData = $request->validate([
            'modules' => 'required|array',
            'modules.*.id' => 'required|exists:modules,id',
            'modules.*.order' => 'required|integer|min:1',
        ]);

        // Validar que todos os módulos pertencem ao curso
        $moduleIds = collect($validatedData['modules'])->pluck('id');
        $courseModuleIds = $course->modules()->select('modules.id')->pluck('modules.id');

        $invalidModules = $moduleIds->diff($courseModuleIds);
        if ($invalidModules->isNotEmpty()) {
            return response()->json([
                'message' => 'Alguns módulos não pertencem a este curso',
                'invalid_modules' => $invalidModules->values()
            ], 422);
        }

        // Validar que todos os módulos pertencem à plataforma
        $platformId = $this->getPlatformId($request);
        if ($platformId) {
            foreach ($validatedData['modules'] as $moduleData) {
                $module = Module::find($moduleData['id']);
                if ($module && $module->course_id !== $course->id) {
                    continue; // Já validado acima
                }
                if ($module && $module->course->platform_id !== null && $module->course->platform_id !== $platformId) {
                    abort(403, 'Alguns módulos não pertencem à sua plataforma');
                }
            }
        }

        // Atualizar order de cada módulo
        foreach ($validatedData['modules'] as $moduleData) {
            Module::where('id', $moduleData['id'])->update([
                'order' => $moduleData['order'],
            ]);
        }

        return response()->json(['message' => 'Ordem dos módulos atualizada com sucesso'], 200);
    }
}
