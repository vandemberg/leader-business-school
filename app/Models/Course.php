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
}
