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
        $videosIds = $this->videos()->get()->pluck('id');
        $lastWatchedVideo = $user->watchVideos()
            ->whereIn('video_id', $videosIds)
            ->orderBy('updated_at', 'desc')
            ->first();

        if ($lastWatchedVideo == null) {
            return $this->videos()->first();
        }

        if ($lastWatchedVideo->status != WatchVideo::STATUS_WATCHED) {
            return $this->videos()
                ->where('id', $lastWatchedVideo->video_id)
                ->first();
        } else {
            $nextVideo = $this->videos()
                ->where('id', '>', $lastWatchedVideo->video_id)
                ->first();

            if ($nextVideo)
                return $nextVideo;
        }

        return $this->videos()->first();
    }

    public function modules()
    {
        return $this->hasMany(Module::class);
    }

    public function responsible()
    {
        return $this->belongsTo(User::class, 'responsible_id');
    }
}
