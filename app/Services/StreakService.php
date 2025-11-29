<?php

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;

class StreakService
{
    /**
     * Increment user streak when they perform a valid activity
     */
    public function incrementStreak(User $user): void
    {
        $today = Carbon::today();
        $lastActivityDate = $user->last_activity_date ? Carbon::parse($user->last_activity_date) : null;

        // If last activity was today, don't increment again
        if ($lastActivityDate && $lastActivityDate->isToday()) {
            return;
        }

        // If last activity was yesterday, increment streak
        if ($lastActivityDate && $lastActivityDate->isYesterday()) {
            $user->increment('current_streak');
        } 
        // If last activity was more than 1 day ago, reset streak to 1
        elseif ($lastActivityDate && $lastActivityDate->lt($today->copy()->subDay())) {
            $user->update(['current_streak' => 1]);
        }
        // If no previous activity, start streak at 1
        else {
            $user->update(['current_streak' => 1]);
        }

        // Update longest streak if current streak exceeds it
        if ($user->current_streak > $user->longest_streak) {
            $user->update(['longest_streak' => $user->current_streak]);
        }

        // Update last activity date
        $user->update(['last_activity_date' => $today]);
    }

    /**
     * Get user's current streak information
     */
    public function getStreakInfo(User $user): array
    {
        $lastActivityDate = $user->last_activity_date ? Carbon::parse($user->last_activity_date) : null;
        $today = Carbon::today();

        // Check if streak is still active (last activity was today or yesterday)
        $isActive = false;
        if ($lastActivityDate) {
            $isActive = $lastActivityDate->isToday() || $lastActivityDate->isYesterday();
        }

        return [
            'current_streak' => $user->current_streak,
            'longest_streak' => $user->longest_streak,
            'last_activity_date' => $user->last_activity_date,
            'is_active' => $isActive,
        ];
    }
}

