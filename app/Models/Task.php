<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'course_id',
        'status',
        'due_date',
        'responsible_id',
    ];

    protected $attributes = [
        'status' => 'pending',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}
