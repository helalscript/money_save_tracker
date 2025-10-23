<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Payment extends Model
{
    protected $fillable = [
        'user_id',
        'payable_id',
        'payable_type',
        'pay_at',
        'intend_id',
        'amount',
        'status',
    ];

    protected $casts = [
        'pay_at' => 'datetime',
        'intend_id' => 'string',
        'amount' => 'decimal:2',
        'status' => 'string',
    ];

    /**
     * Get the user that owns the payment.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the payable model (subscription).
     */
    public function payable(): MorphTo
    {
        return $this->morphTo();
    }
}
