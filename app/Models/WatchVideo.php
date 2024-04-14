<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WatchVideo extends Model
{
    use HasFactory;

    const STATUS_WATCHING = 'watching';
    const STATUS_WATCHED = 'watched';

    protected $fillable = [
        'user_id',
        'video_id',
        'status',
    ];

    public function video()
    {
        return $this->belongsTo(Video::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
