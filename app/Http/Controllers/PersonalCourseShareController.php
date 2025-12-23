<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Inertia\Inertia;

class PersonalCourseShareController extends Controller
{
    public function show(string $token)
    {
        $user = auth()->user();

        $course = Course::where('share_token', $token)
            ->where('is_personal', true)
            ->firstOrFail();

        $course->load([
            'modules' => function ($query) {
                $query->orderBy('order')->with([
                    'videos' => function ($videoQuery) {
                        $videoQuery->orderBy('order');
                    },
                ]);
            },
        ]);

        $isOwner = $course->responsible_id === $user->id;
        $isEnrolled = $course->enrolledUsers()
            ->where('user_id', $user->id)
            ->exists();

        return Inertia::render('PersonalCourses/Share', [
            'course' => $course,
            'isOwner' => $isOwner,
            'isEnrolled' => $isOwner || $isEnrolled,
        ]);
    }

    public function enroll(string $token)
    {
        $user = auth()->user();

        $course = Course::where('share_token', $token)
            ->where('is_personal', true)
            ->firstOrFail();

        if ($course->responsible_id !== $user->id) {
            $course->enrolledUsers()->syncWithoutDetaching([$user->id]);
        }

        return redirect()->route('courses.watch', $course);
    }
}
