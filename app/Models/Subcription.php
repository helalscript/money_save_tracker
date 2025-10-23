<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Subcription extends Model
{
    protected $fillable = [
        'user_id',
        'package_id',
        'start_time',
        'end_time',
        'status',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'status' => 'string',
    ];

    /**
     * Get the user that owns the subscription.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the package that owns the subscription.
     */
    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    /**
     * Get the payments for the subscription.
     */
    public function payments(): MorphMany
    {
        return $this->morphMany(Payment::class, 'payable');
    }
}
