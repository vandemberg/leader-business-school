<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class HelpCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'icon',
        'description',
        'order',
        'platform_id',
    ];

    protected $casts = [
        'order' => 'integer',
    ];

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Retrieve the model for route model binding, filtering by platform_id.
     */
    public function resolveRouteBinding($value, $field = null)
    {
        $platformId = current_platform_id();
        $routeKey = $field ?? $this->getRouteKeyName();

        $query = $this->where($routeKey, $value);

        if ($platformId) {
            $query->where('platform_id', $platformId);
        }

        $model = $query->first();

        if (!$model) {
            throw new ModelNotFoundException("No query results for model [{$this->getMorphClass()}] with {$routeKey} [{$value}]");
        }

        return $model;
    }

    public function articles(): HasMany
    {
        return $this->hasMany(HelpArticle::class, 'category_id');
    }

    public function platform(): BelongsTo
    {
        return $this->belongsTo(Platform::class);
    }
}
