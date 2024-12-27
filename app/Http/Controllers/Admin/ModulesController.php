<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Module;
use Illuminate\Http\Request;

class ModulesController extends Controller
{
    public function store(Request $request, Course $course)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'description' => 'required|string',
        ]);

        $data['status'] = Module::STATUS_DRAFT;
        $module = $course->modules()->create($data);

        return response()->json(data: $module, status: 201);
    }

    public function update(Request $request, Course $course, Module $module)
    {
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
        $module->delete();
        return response()->json(status: 204);
    }
}
