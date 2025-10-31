<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Goal extends Model
{
    protected $fillable = [
        'user_id',
        'monthly_income',
        'fixed_expense',
        'alredy_saved',
        'debt_blance',
        'goal_type',
        'target_amount',
        'start_date',
        'end_date',
        'daily_target',
        'current_streak',
        'deadline_indicator',
        'status',
    ];

    protected $casts = [
        'monthly_income' => 'decimal:2',
        'fixed_expense' => 'decimal:2',
        'alredy_saved' => 'decimal:2',
        'debt_blance' => 'decimal:2',
        'target_amount' => 'decimal:2',
        'daily_target' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'goal_type' => 'string',
        'current_streak' => 'integer',
        'deadline_indicator' => 'string',
        'status' => 'string',
    ];

    /**
     * Get the user that owns the goal.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the check-ins for the goal.
     */
    public function checkIns(): HasMany
    {
        return $this->hasMany(CheckIn::class);
    }
}
