<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Package extends Model
{
    protected $fillable = [
        'title',
        'price_weekly',
        'price_monthly',
        'price_yearly',
        'price_offer',
        'free_trail_day',
        'description',
        'image',
        'status',
    ];

    protected $casts = [
        'title' => 'string',
        'price_weekly' => 'decimal:2',
        'price_monthly' => 'decimal:2',
        'price_yearly' => 'decimal:2',
        'price_offer' => 'decimal:2',
        'free_trail_day' => 'integer',
        'description' => 'string',
        'image' => 'string',
        'status' => 'string',
    ];

    /**
     * Get the features that belong to the package.
     */
    public function features(): BelongsToMany
    {
        return $this->belongsToMany(Feature::class, 'package_feature')
                    ->withPivot(['description', 'image', 'status'])
                    ->withTimestamps();
    }

    /**
     * Get the subscriptions for the package.
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subcription::class);
    }
}
