<?php

namespace App\Models;

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
        'thumbnail',
        'time_in_seconds',
        'course_id',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
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
}
