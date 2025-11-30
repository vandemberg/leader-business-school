<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Controller;
use Illuminate\Http\Request;
use App\Models\Course;
use Illuminate\Support\Facades\Storage;

class CoursesController extends Controller
{
    public function index(Request $request)
    {
        $query = Course::query();

        // Captura o platform_id usando o método do controller base
        $platformId = $this->getPlatformId($request);
        if ($platformId) {
            $query->where(function ($q) use ($platformId) {
                $q->where('platform_id', $platformId)
                  ->orWhereNull('platform_id');
            });
        }

        if ($search = $request->get('search')) {
            $query->where(column: 'title', operator: 'like', value: '%' . $search . '%');
        }

        $courses = $query->get();

        $courses->each(function (Course $course) {
            $course->thumbnail = url('/') . $course->thumbnail;
        });

        return response()->json($courses);
    }

    public function show(Course $course)
    {
        $this->validateCoursePlatform($course);

        $course
            ->load('responsible')
            ->load([
                'modules' => function ($query) {
                    $query->orderBy('order', 'desc');
                }
            ])
            ->load([
                'modules.videos' => function ($query) {
                    $query->orderBy('order');
                }
            ]);

        $course->thumbnail = url('/') . $course->thumbnail;

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
            'thumbnail' => 'nullable|file|mimes:jpeg,jpg,png,gif|max:20480', // 20MB
            'platform_id' => 'nullable|integer|exists:platforms,id',
        ]);

        $course = new Course(attributes: $data);
        $course->status = Course::STATUS_DRAFT;
        $course->responsible_id = auth()->id();
        $course->thumbnail = 'https://via.placeholder.com/500';

        // Captura o platform_id usando o método do controller base
        $platformId = $this->getPlatformId($request);
        if ($platformId) {
            $course->platform_id = $platformId;
        }

        if ($request->hasFile('thumbnail')) {
            $thumbnail = $this->registerThumbnail(request: $request, course: $course);
            $course->thumbnail = $thumbnail;
        }

        $course->save();

        return response()->json(data: $course, status: 201);
    }

    public function update(Request $request, Course $course)
    {
        $this->validateCoursePlatform($course);

        $data = $request->validate(rules: [
            'title' => 'string|max:255',
            'description' => 'string',
            'icon' => 'string',
            'color' => 'string',
            'status' => 'in:draft,inprogress,complete',
            'thumbnail' => 'nullable|file|mimes:jpeg,jpg,png,gif|max:20480', // 20MB
            'platform_id' => 'nullable|integer|exists:platforms,id',
        ]);

        // Captura o platform_id usando o método do controller base
        $platformId = $this->getPlatformId($request);
        if ($platformId) {
            $data['platform_id'] = $platformId;
        }

        if ($request->hasFile(key: 'thumbnail')) {
            $thumbnail = $this->registerThumbnail(request: $request, course: $course);
            $data['thumbnail'] = $thumbnail;
        }

        $data = collect($data)->reject(fn($value) => is_null($value) || $value === '')->toArray();

        $course->update(attributes: $data);

        return response()->json(data: $course);
    }

    public function destroy(Course $course)
    {
        $this->validateCoursePlatform($course);

        foreach ($course->modules as $module) {
            $module->videos()->delete();
            $module->delete();
        }

        $course->delete();

        return response()->noContent(status: 204);
    }

    public function disable(Course $course)
    {
        $this->validateCoursePlatform($course);

        $course->update(['status' => Course::STATUS_DRAFT]);

        return response()->noContent(status: 204);
    }

    public function enable(Course $course)
    {
        $this->validateCoursePlatform($course);

        $course->update(['status' => Course::STATUS_COMPLETE]);

        return response()->noContent(status: 204);
    }

    private function registerThumbnail(Request $request, Course $course)
    {
        $file = $request->file(key: 'thumbnail');
        $filename = 'thumbnail_' . $course->title . '.' . $file->getClientOriginalExtension();
        $filename = str_replace(' ', '_', $filename);
        $path = $file->storeAs(path: 'thumbnails', name: $filename, options: 'public');

        $url = Storage::url($path);

        return $url;
    }
}
