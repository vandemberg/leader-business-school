<?php

namespace App\Repositories;

use App\Models\Course;

class PlatformRepository
{

    public function mostAccessedCourses(string $platformId)
    {
        return Course::where('platform_id', '=', $platformId)
            ->take(5)
            ->get();
    }
}
