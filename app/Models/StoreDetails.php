<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StoreDetails extends Model
{
    protected $fillable = [
        'user_id',
        'store_id',
        'name',
        'email',
        'phone',
        'batteries',
        'contact_name',
        'location',
        'status',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'store_id' => 'string',
        'name' => 'string',
        'email' => 'string',
        'phone' => 'string',
        'batteries' => 'string',
        'contact_name' => 'string',
        'location' => 'string',
        'status' => 'string',
    ];

    /**
     * Get the user that owns the store details.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the customers for the store details.
     */
    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class, 'store_detail_id');
    }
}
