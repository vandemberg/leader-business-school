<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Controller;
use Illuminate\Http\Request;
use App\Models\Course;

class CoursesController extends Controller
{
    public function index(Request $request)
    {
        $query = Course::query();

        if ($search = $request->get('search')) {
            $query->where(column: 'title', operator: 'like', value: '%' . $search . '%');
        }

        $courses = $query->get();
        return response()->json($courses);
    }

    public function show(Course $course)
    {
        $course
            ->load('responsible')
            ->load('modules')
            ->load('modules.videos');

        return response()->json(
            $course
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate(rules: [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'icon' => 'string',
            'color' => 'string',
        ]);

        $course = new Course(attributes: $data);
        $course->status = Course::STATUS_DRAFT;
        $course->responsible_id = auth()->id();
        $thumbnail = $this->registerThumbnail(request: $request, course: $course);
        $course->thumbnail = $thumbnail;
        $course->save();


        return response()->json(data: $course, status: 201);
    }

    public function update(Request $request, Course $course)
    {
        $data = $request->validate(rules: [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'icon' => 'string',
            'color' => 'string',
            'status' => 'in:draft,inprogress,complete',
        ]);

        if ($request->hasFile(key: 'thumbnail')) {
            $thumbnail = $this->registerThumbnail(request: $request, course: $course);
            $data['thumbnail'] = $thumbnail;
        }

        $course->update(attributes: $data);

        return response()->json(data: $course);
    }

    public function destroy(Course $course)
    {
        $course->delete();

        return response()->noContent(status: 204);
    }

    private function registerThumbnail(Request $request, Course $course)
    {
        if ($request->hasFile(key: 'thumbnail')) {
            $file = $request->file(key: 'thumbnail');
            $filename = 'thumbnail_' . $course->title . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs(path: 'thumbnails', name: $filename, options: 'public');

            return $path;
        }

        return '';
    }
}
