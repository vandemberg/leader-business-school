<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Module extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'description', 'course_id'];

    public function videos(): HasMany
    {
        return $this->hasMany(Video::class);
    }
}
