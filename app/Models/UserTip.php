<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserTip extends Model
{
    protected $fillable = [
        'tip_id',
        'goal_id',
        'user_id',
        'is_seen',
        'seen_at',
        'status',
    ];

    protected $casts = [
        'is_seen' => 'boolean',
        'seen_at' => 'datetime',
        'status' => 'string',
    ];

    /**
     * Get the tip that owns the user tip.
     */
    public function tip(): BelongsTo
    {
        return $this->belongsTo(Tip::class);
    }

    /**
     * Get the goal that owns the user tip.
     */
    public function goal(): BelongsTo
    {
        return $this->belongsTo(Goal::class);
    }

    /**
     * Get the user that owns the user tip.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
