<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{

    protected $fillable = [
        'name',
        'description',
        'icon',
        'color',
        'status',
        'thumbnail',
    ];

    use HasFactory;

    public function modules()
    {
        return $this->hasMany(Module::class);
    }
}
