<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SavedPath extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'path_id',
    ];

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
}
