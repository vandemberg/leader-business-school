<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TagCourse extends Model
{
    use HasFactory;

    protected $fillable = ['tag_id', 'course_id'];

    /**
     * Get the tag that owns the tag-course relationship.
     */
    public function tag(): BelongsTo
    {
        return $this->belongsTo(Tag::class);
    }

    /**
     * Get the course that owns the tag-course relationship.
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }
}
