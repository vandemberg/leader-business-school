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
        'platform_id',
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
     * Supports both ID (numeric) and slug lookups.
     */
    public function resolveRouteBinding($value, $field = null)
    {
        $platformId = current_platform_id();

        // If field is explicitly set, use it
        if ($field !== null) {
            $query = $this->where($field, $value);
        } else {
            // If value is numeric, search by ID; otherwise search by slug
            if (is_numeric($value)) {
                $query = $this->where('id', $value);
            } else {
                $query = $this->where('slug', $value);
            }
        }

        if ($platformId) {
            $query->where('platform_id', $platformId);
        }

        $model = $query->first();

        if (!$model) {
            $routeKey = $field ?? (is_numeric($value) ? 'id' : 'slug');
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
