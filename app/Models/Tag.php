<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tag extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'platform_id'];

    /**
     * Get the tag-course relationships for this tag.
     */
    public function courses(): HasMany
    {
        return $this->hasMany(TagCourse::class);
    }

    /**
     * Get the courses associated with this tag through the pivot table.
     */
    public function coursesThroughPivot()
    {
        return $this->belongsToMany(Course::class, 'tag_courses');
    }
}
