<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{

    protected $fillable = [
        'title',
        'description',
        'icon',
        'color',
        'status',
        'thumbnail',
    ];

    use HasFactory;

    public function videos()
    {
        return $this->hasMany(Video::class);
    }

    public function currentVideo($user): Video
    {
        $videosIds = $this->videos()->get()->pluck('id');
        $lastWatchedVideo = $user->watchVideos()
            ->whereIn('video_id', $videosIds)
            ->orderBy('updated_at', 'desc')
            ->first();

        if ($lastWatchedVideo) {
            return $this->videos()
                ->where('id', $lastWatchedVideo->video_id)
                ->first();
        }

        return $this->videos()->first();
    }
}
