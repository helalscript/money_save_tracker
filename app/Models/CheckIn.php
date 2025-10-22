<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CheckIn extends Model
{
    protected $fillable = [
        'goal_id',
        'amount_spending',
        'amount_saved',
        'target_amount',
        'check_date',
        'status',
    ];

    protected $casts = [
        'amount_spending' => 'decimal:2',
        'amount_saved' => 'decimal:2',
        'target_amount' => 'decimal:2',
        'check_date' => 'date',
        'status' => 'string',
    ];

    /**
     * Get the goal that owns the check-in.
     */
    public function goal(): BelongsTo
    {
        return $this->belongsTo(Goal::class);
    }
}
