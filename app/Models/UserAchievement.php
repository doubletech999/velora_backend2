<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserAchievement extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'achievement_id',
        'progress',
        'unlocked_at',
    ];

    protected $casts = [
        'progress' => 'decimal:2',
        'unlocked_at' => 'datetime',
    ];

    /**
     * Get the user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the achievement
     */
    public function achievement(): BelongsTo
    {
        return $this->belongsTo(Achievement::class);
    }

    /**
     * Check if achievement is unlocked
     */
    public function isUnlocked(): bool
    {
        return $this->unlocked_at !== null;
    }

    /**
     * Update progress
     */
    public function updateProgress(float $progress): void
    {
        $this->update(['progress' => min(100, $progress)]);
    }

    /**
     * Unlock achievement
     */
    public function unlock(): void
    {
        if (!$this->isUnlocked()) {
            $this->update([
                'progress' => 100,
                'unlocked_at' => now(),
            ]);
        }
    }

    /**
     * Scope for unlocked achievements
     */
    public function scopeUnlocked($query)
    {
        return $query->whereNotNull('unlocked_at');
    }
}
