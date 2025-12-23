<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class PersonalCoursesController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $courses = Course::where('is_personal', true)
            ->where('responsible_id', $user->id)
            ->orderBy('updated_at', 'desc')
            ->get()
            ->map(function (Course $course) {
                if (!$course->share_token) {
                    $course->share_token = Str::uuid();
                    $course->save();
                }

                return $course;
            });

        return Inertia::render('PersonalCourses/Index', [
            'courses' => $courses,
        ]);
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $course = new Course($data);
        $course->status = Course::STATUS_COMPLETE;
        $course->responsible_id = $user->id;
        $course->is_personal = true;
        $course->share_token = Str::uuid();
        $course->thumbnail = 'https://via.placeholder.com/500';
        $course->save();

        return redirect()->route('personal-courses.edit', $course);
    }

    public function edit(Course $course)
    {
        $this->authorizePersonalCourse($course);

        if (!$course->share_token) {
            $course->share_token = Str::uuid();
            $course->save();
        }

        $course->load([
            'modules' => function ($query) {
                $query->orderBy('order')->with([
                    'videos' => function ($videoQuery) {
                        $videoQuery->orderBy('order');
                    }
                ]);
            },
        ]);

        return Inertia::render('PersonalCourses/Edit', [
            'course' => $course,
            'shareUrl' => route('personal-courses.share', $course->share_token),
        ]);
    }

    public function update(Request $request, Course $course)
    {
        $this->authorizePersonalCourse($course);

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $course->update($data);

        return redirect()->route('personal-courses.edit', $course);
    }

    private function authorizePersonalCourse(Course $course): void
    {
        if (!$course->is_personal || $course->responsible_id !== auth()->id()) {
            abort(403, 'Curso não pertence ao usuário.');
        }
    }
}
