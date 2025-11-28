<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VideoComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'video_id',
        'user_id',
        'content',
    ];

    public function video()
    {
        return $this->belongsTo(Video::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function replies()
    {
        return $this->hasMany(VideoCommentReply::class, 'comment_id');
    }

    public function likes()
    {
        return $this->hasMany(VideoCommentLike::class, 'comment_id');
    }

    public function likeCount()
    {
        return $this->likes()->where('type', 'like')->count();
    }

    public function dislikeCount()
    {
        return $this->likes()->where('type', 'dislike')->count();
    }
}
