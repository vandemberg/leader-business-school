<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HelpArticle extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'platform_id',
        'question',
        'answer',
        'is_faq',
        'views_count',
    ];

    protected $casts = [
        'is_faq' => 'boolean',
        'views_count' => 'integer',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(HelpCategory::class, 'category_id');
    }

    public function platform(): BelongsTo
    {
        return $this->belongsTo(Platform::class);
    }
}
