<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable;

    const ROLE_ADMIN = 'admin';
    const ROLE_USER = 'student';
    const ROLES = [
        self::ROLE_ADMIN,
        self::ROLE_USER,
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'current_platform_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */

    public function platforms()
    {
        return $this->belongsToMany(Platform::class, 'platform_users', 'user_id', 'platform_id');
    }
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function watchVideos()
    {
        return $this->hasMany(WatchVideo::class);
    }

    public function videoComments()
    {
        return $this->hasMany(VideoComment::class);
    }

    public function videoCommentReplies()
    {
        return $this->hasMany(VideoCommentReply::class);
    }

    public function videoRatings()
    {
        return $this->hasMany(VideoRating::class);
    }

    public function videoCommentLikes()
    {
        return $this->hasMany(VideoCommentLike::class);
    }

    public function communityPosts()
    {
        return $this->hasMany(CommunityPost::class);
    }

    public function postComments()
    {
        return $this->hasMany(PostComment::class);
    }

    public function postLikes()
    {
        return $this->hasMany(PostLike::class);
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
}
