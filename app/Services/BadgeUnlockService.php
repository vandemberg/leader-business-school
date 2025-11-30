<?php

namespace App\Services;

use App\Models\Badge;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class BadgeUnlockService
{
    /**
     * Verifica e desbloqueia badges automaticamente baseado no tipo e valor atual
     *
     * @param User $user
     * @param string $type Tipo do badge (Badge::TYPE_*)
     * @param int $currentValue Valor atual do usuário para este tipo
     * @return array Array de badges desbloqueados
     */
    public function checkAndUnlockBadges(User $user, string $type, int $currentValue): array
    {
        $platformId = $user->current_platform_id;

        if (!$platformId) {
            return [];
        }

        // Busca badges ativos do tipo especificado da plataforma do usuário
        $badges = Badge::where('platform_id', $platformId)
            ->where('type', $type)
            ->where('is_active', true)
            ->where('threshold', '<=', $currentValue)
            ->get();

        $unlockedBadges = [];

        foreach ($badges as $badge) {
            // Verifica se o badge já está desbloqueado
            $alreadyUnlocked = DB::table('user_badges')
                ->where('user_id', $user->id)
                ->where('badge_id', $badge->id)
                ->exists();

            if (!$alreadyUnlocked) {
                // Desbloqueia o badge
                DB::table('user_badges')->insert([
                    'user_id' => $user->id,
                    'badge_id' => $badge->id,
                    'unlocked_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $unlockedBadges[] = $badge;
            }
        }

        return $unlockedBadges;
    }

    /**
     * Verifica e desbloqueia badges de vídeos concluídos
     */
    public function checkVideosCompleted(User $user): array
    {
        $platformId = $user->current_platform_id;
        
        if (!$platformId) {
            return [];
        }

        // Conta vídeos assistidos da plataforma
        $videoIds = $this->getPlatformVideoIds($platformId);
        $completedVideos = \App\Models\WatchVideo::where('user_id', $user->id)
            ->whereIn('video_id', $videoIds)
            ->where('status', \App\Models\WatchVideo::STATUS_WATCHED)
            ->count();

        return $this->checkAndUnlockBadges($user, Badge::TYPE_VIDEOS_COMPLETED, $completedVideos);
    }

    /**
     * Verifica e desbloqueia badges de cursos concluídos
     */
    public function checkCoursesCompleted(User $user): array
    {
        $platformId = $user->current_platform_id;
        
        if (!$platformId) {
            return [];
        }

        // Conta cursos completados (100% de progresso)
        $courses = \App\Models\Course::where(function ($q) use ($platformId) {
            $q->where('platform_id', $platformId)
              ->orWhereNull('platform_id');
        })
        ->whereNotIn('status', [\App\Models\Course::STATUS_DRAFT])
        ->get();

        $completedCourses = 0;
        foreach ($courses as $course) {
            $videos = $course->modules()->with('videos')->get()->pluck('videos')->flatten();
            $totalVideos = $videos->count();
            
            if ($totalVideos > 0) {
                $completedVideos = \App\Models\WatchVideo::where('user_id', $user->id)
                    ->whereIn('video_id', $videos->pluck('id'))
                    ->where('status', \App\Models\WatchVideo::STATUS_WATCHED)
                    ->count();
                
                $progress = ($completedVideos / $totalVideos) * 100;
                if ($progress >= 100) {
                    $completedCourses++;
                }
            }
        }

        return $this->checkAndUnlockBadges($user, Badge::TYPE_COURSES_COMPLETED, $completedCourses);
    }

    /**
     * Verifica e desbloqueia badges de horas assistidas
     */
    public function checkHoursWatched(User $user): array
    {
        $platformId = $user->current_platform_id;
        
        if (!$platformId) {
            return [];
        }

        // Calcula horas assistidas
        $videoIds = $this->getPlatformVideoIds($platformId);
        $completedVideos = \App\Models\WatchVideo::where('user_id', $user->id)
            ->whereIn('video_id', $videoIds)
            ->where('status', \App\Models\WatchVideo::STATUS_WATCHED)
            ->pluck('video_id');

        $watchedVideos = \App\Models\Video::whereIn('id', $completedVideos)->get();
        $totalHours = round($watchedVideos->sum('time_in_seconds') / 3600, 0);

        return $this->checkAndUnlockBadges($user, Badge::TYPE_HOURS_WATCHED, (int)$totalHours);
    }

    /**
     * Verifica e desbloqueia badges de comentários realizados
     */
    public function checkCommentsMade(User $user): array
    {
        $platformId = $user->current_platform_id;
        
        if (!$platformId) {
            return [];
        }

        // Conta comentários em vídeos da plataforma
        $videoIds = $this->getPlatformVideoIds($platformId);
        $commentsCount = \App\Models\VideoComment::where('user_id', $user->id)
            ->whereIn('video_id', $videoIds)
            ->count();

        return $this->checkAndUnlockBadges($user, Badge::TYPE_COMMENTS_MADE, $commentsCount);
    }

    /**
     * Verifica e desbloqueia badges de avaliações realizadas
     */
    public function checkRatingsGiven(User $user): array
    {
        $platformId = $user->current_platform_id;
        
        if (!$platformId) {
            return [];
        }

        // Conta avaliações em vídeos da plataforma
        $videoIds = $this->getPlatformVideoIds($platformId);
        $ratingsCount = \App\Models\VideoRating::where('user_id', $user->id)
            ->whereIn('video_id', $videoIds)
            ->count();

        return $this->checkAndUnlockBadges($user, Badge::TYPE_RATINGS_GIVEN, $ratingsCount);
    }

    /**
     * Verifica e desbloqueia badges de postagens na comunidade
     */
    public function checkCommunityPosts(User $user): array
    {
        $platformId = $user->current_platform_id;
        
        if (!$platformId) {
            return [];
        }

        // Conta postagens na comunidade da plataforma
        $postsCount = \App\Models\CommunityPost::where('user_id', $user->id)
            ->where('platform_id', $platformId)
            ->count();

        return $this->checkAndUnlockBadges($user, Badge::TYPE_COMMUNITY_POSTS, $postsCount);
    }

    /**
     * Obtém IDs de vídeos da plataforma
     */
    private function getPlatformVideoIds(?int $platformId): \Illuminate\Support\Collection
    {
        $query = \App\Models\Video::query();

        if ($platformId) {
            $query->where(function ($videoQuery) use ($platformId) {
                $videoQuery
                    ->whereHas('course', function ($courseQuery) use ($platformId) {
                        $courseQuery->where(function ($inner) use ($platformId) {
                            $inner
                                ->where('platform_id', $platformId)
                                ->orWhereNull('platform_id');
                        });
                    })
                    ->orWhere(function ($videoQuery) use ($platformId) {
                        $videoQuery
                            ->whereNull('course_id')
                            ->where(function ($inner) use ($platformId) {
                                $inner
                                    ->where('platform_id', $platformId)
                                    ->orWhereNull('platform_id');
                            });
                    });
            });
        }

        return $query->pluck('id');
    }
}

