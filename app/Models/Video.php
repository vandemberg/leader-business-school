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
        'module_id',
    ];

    public function nmodule()
    {
        return $this->belongsTo(Module::class);
    }

}
