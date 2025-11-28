<?php

namespace App\Models;

use App\Events\VideoCreated;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Video extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'url',
        'status',
        'transcription',
        'newsletter_path',
        'thumbnail',
        'time_in_seconds',
        'module_id',
        'course_id',
    ];

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::created(function (Video $video) {
            event(new VideoCreated($video));
        });
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function module()
    {
        return $this->belongsTo(Module::class);
    }

    public function watchVideos()
    {
        return $this->hasMany(WatchVideo::class);
    }

    public function watchStatus($user)
    {
        $watchVideo = $this->watchVideos()
            ->where('user_id', $user->id)
            ->first();

        if ($watchVideo) {
            return $watchVideo->status;
        }

        return 'pending';
    }

    public function comments()
    {
        return $this->hasMany(VideoComment::class);
    }

    public function ratings()
    {
        return $this->hasMany(VideoRating::class);
    }

    public function averageRating()
    {
        return $this->ratings()->avg('rating') ?? 0;
    }

    public function reports()
    {
        return $this->hasMany(VideoReport::class);
    }
}
