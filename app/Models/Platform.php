<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Platform extends Model
{
    protected $fillable = ['name', 'slug', 'brand'];

    use HasFactory;

    public function users()
    {
        return $this->belongsToMany(User::class, 'platform_users', 'platform_id', 'user_id');
    }
}
