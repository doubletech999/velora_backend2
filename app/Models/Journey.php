<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Journey extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'path_id',
        'status',
        'started_at',
        'completed_at',
        'distance_traveled',
        'actual_duration',
        'visited_checkpoints',
        'recorded_positions',
        'weather_conditions',
        'notes',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'distance_traveled' => 'decimal:2',
        'recorded_positions' => 'array',
        'weather_conditions' => 'array',
    ];

    protected $appends = ['status_label', 'duration_formatted'];

    /**
     * Get the status label
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'started' => __('In Progress'),
            'paused' => __('Paused'),
            'completed' => __('Completed'),
            'abandoned' => __('Abandoned'),
            default => $this->status,
        };
    }

    /**
     * Get formatted duration
     */
    public function getDurationFormattedAttribute(): ?string
    {
        if (!$this->actual_duration) {
            return null;
        }

        $hours = floor($this->actual_duration / 60);
        $minutes = $this->actual_duration % 60;

        if ($hours > 0) {
            return sprintf('%dh %dm', $hours, $minutes);
        }

        return sprintf('%dm', $minutes);
    }

    /**
     * Get the user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the path
     */
    public function path(): BelongsTo
    {
        return $this->belongsTo(Path::class);
    }

    /**
     * Check if journey is active
     */
    public function isActive(): bool
    {
        return in_array($this->status, ['started', 'paused']);
    }

    /**
     * Check if journey is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Scope for active journeys
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['started', 'paused']);
    }

    /**
     * Scope for completed journeys
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }
}
