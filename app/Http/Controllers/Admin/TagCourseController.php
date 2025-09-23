<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Controller;
use App\Models\TagCourse;
use App\Models\Tag;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class TagCourseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = TagCourse::with(['tag', 'course']);

        if ($tagId = $request->get('tag_id')) {
            $query->where('tag_id', $tagId);
        }

        if ($courseId = $request->get('course_id')) {
            $query->where('course_id', $courseId);
        }

        $tagCourses = $query->orderBy('created_at', 'desc')->get();

        return response()->json($tagCourses);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'tag_id' => 'required|integer|exists:tags,id',
            'course_id' => 'required|integer|exists:courses,id',
        ]);

        // Verificar se a associação já existe
        $existingTagCourse = TagCourse::where('tag_id', $data['tag_id'])
            ->where('course_id', $data['course_id'])
            ->first();

        if ($existingTagCourse) {
            return response()->json([
                'message' => 'Esta tag já está associada a este curso.'
            ], 422);
        }

        $tagCourse = TagCourse::create($data);
        $tagCourse->load(['tag', 'course']);

        return response()->json($tagCourse, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(TagCourse $tagCourse): JsonResponse
    {
        $tagCourse->load(['tag', 'course']);

        return response()->json($tagCourse);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TagCourse $tagCourse)
    {
        $tagCourse->delete();

        return response()->noContent(204);
    }

    /**
     * Remove tag-course association by tag and course IDs
     */
    public function destroyByTagAndCourse(Request $request)
    {
        $data = $request->validate([
            'tag_id' => 'required|integer|exists:tags,id',
            'course_id' => 'required|integer|exists:courses,id',
        ]);

        $tagCourse = TagCourse::where('tag_id', $data['tag_id'])
            ->where('course_id', $data['course_id'])
            ->first();

        if (!$tagCourse) {
            return response()->json([
                'message' => 'Associação entre tag e curso não encontrada.'
            ], 404);
        }

        $tagCourse->delete();

        return response()->noContent(204);
    }

    /**
     * Get tags for a specific course
     */
    public function getTagsByCourse(Course $course): JsonResponse
    {
        $tagCourses = TagCourse::where('course_id', $course->id)
            ->with('tag')
            ->get();

        return response()->json($tagCourses);
    }

    /**
     * Get courses for a specific tag
     */
    public function getCoursesByTag(Tag $tag): JsonResponse
    {
        $tagCourses = TagCourse::where('tag_id', $tag->id)
            ->with('course')
            ->get();

        return response()->json($tagCourses);
    }
}
