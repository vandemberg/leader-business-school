<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{

    const STATUS_DRAFT = 'draft';
    const STATUS_IN_PROGRESS = 'inprogress';
    const STATUS_COMPLETE = 'complete';

    protected $fillable = [
        'title',
        'description',
        'icon',
        'color',
        'status',
        'thumbnail',
        'responsible_id',
    ];

    use HasFactory;

    protected $attributes = [
        'status' => 'draft',
    ];

    public function currentVideo($user): Video
    {
        $videos = $this->videos()->get();
        $videosIds = $videos->pluck('id');
        $lastWatchedVideo = $user->watchVideos()
            ->whereIn('video_id', $videosIds)
            ->orderBy('updated_at', 'desc')
            ->first();

        if ($lastWatchedVideo == null) {
            return $videos->first();
        }

        if ($lastWatchedVideo->status != WatchVideo::STATUS_WATCHED) {
            return $videos
                ->where('id', $lastWatchedVideo->video_id)
                ->first();
        } else {
            $nextVideo = $videos
                ->where('id', '>', $lastWatchedVideo->video_id)
                ->first();

            if ($nextVideo)
                return $nextVideo;
        }

        return $videos->first();
    }

    public function modules()
    {
        return $this->hasMany(Module::class);
    }

    public function responsible()
    {
        return $this->belongsTo(User::class, 'responsible_id');
    }

    public function videos()
    {
        return $this->hasManyThrough(Video::class, Module::class);
    }
}
