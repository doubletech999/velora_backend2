<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Path extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'name_ar',
        'description',
        'description_ar',
        'location',
        'location_ar',
        'images',
        'length',
        'estimated_duration',
        'difficulty',
        'coordinates',
        'warnings',
        'warnings_ar',
        'rating',
        'review_count',
        'is_featured',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'images' => 'array',
        'coordinates' => 'array',
        'warnings' => 'array',
        'warnings_ar' => 'array',
        'length' => 'decimal:2',
        'rating' => 'decimal:2',
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
    ];

    protected $appends = ['difficulty_label'];

    /**
     * Get the difficulty label
     */
    public function getDifficultyLabelAttribute(): string
    {
        return match($this->difficulty) {
            'easy' => __('Easy'),
            'moderate' => __('Moderate'),
            'hard' => __('Hard'),
            default => $this->difficulty,
        };
    }

    /**
     * Get the user who created the path
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the activities for the path
     */
    public function activities(): BelongsToMany
    {
        return $this->belongsToMany(Activity::class, 'path_activities')
            ->withTimestamps();
    }

    /**
     * Get the reviews for the path
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Get the journeys for the path
     */
    public function journeys(): HasMany
    {
        return $this->hasMany(Journey::class);
    }

    /**
     * Get users who saved this path
     */
    public function savedByUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'saved_paths')
            ->withTimestamps();
    }

    /**
     * Scope for active paths
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for featured paths
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope for difficulty
     */
    public function scopeOfDifficulty($query, string $difficulty)
    {
        return $query->where('difficulty', $difficulty);
    }
}
