<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Badge extends Model
{
    use HasFactory;

    // Tipos de badges
    const TYPE_VIDEOS_COMPLETED = 'videos_completed';
    const TYPE_COURSES_COMPLETED = 'courses_completed';
    const TYPE_HOURS_WATCHED = 'hours_watched';
    const TYPE_COMMENTS_MADE = 'comments_made';
    const TYPE_RATINGS_GIVEN = 'ratings_given';
    const TYPE_COMMUNITY_POSTS = 'community_posts';

    const TYPES = [
        self::TYPE_VIDEOS_COMPLETED,
        self::TYPE_COURSES_COMPLETED,
        self::TYPE_HOURS_WATCHED,
        self::TYPE_COMMENTS_MADE,
        self::TYPE_RATINGS_GIVEN,
        self::TYPE_COMMUNITY_POSTS,
    ];

    // Cores disponíveis (limitadas)
    const COLOR_PRIMARY = '#8E2DE2';
    const COLOR_SECONDARY = '#4A00E0';
    const COLOR_YELLOW = '#FFD700';
    const COLOR_BLUE = '#3B82F6';
    const COLOR_GREEN = '#10B981';
    const COLOR_RED = '#EF4444';
    const COLOR_PURPLE = '#A855F7';
    const COLOR_ORANGE = '#F97316';

    const COLORS = [
        self::COLOR_PRIMARY,
        self::COLOR_SECONDARY,
        self::COLOR_YELLOW,
        self::COLOR_BLUE,
        self::COLOR_GREEN,
        self::COLOR_RED,
        self::COLOR_PURPLE,
        self::COLOR_ORANGE,
    ];

    protected $fillable = [
        'platform_id',
        'type',
        'title',
        'icon',
        'color',
        'threshold',
        'description',
        'is_active',
    ];

    protected $casts = [
        'threshold' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Get the platform that owns the badge.
     */
    public function platform(): BelongsTo
    {
        return $this->belongsTo(Platform::class);
    }

    /**
     * Get the users that have unlocked this badge.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_badges')
            ->withPivot('unlocked_at')
            ->withTimestamps();
    }

    /**
     * Get the type label in Portuguese.
     */
    public function getTypeLabelAttribute(): string
    {
        $labels = [
            self::TYPE_VIDEOS_COMPLETED => 'Vídeos Concluídos',
            self::TYPE_COURSES_COMPLETED => 'Cursos Concluídos',
            self::TYPE_HOURS_WATCHED => 'Horas Assistidas',
            self::TYPE_COMMENTS_MADE => 'Comentários Realizados',
            self::TYPE_RATINGS_GIVEN => 'Avaliações Realizadas',
            self::TYPE_COMMUNITY_POSTS => 'Postagens na Comunidade',
        ];

        return $labels[$this->type] ?? $this->type;
    }
}

