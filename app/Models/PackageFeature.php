<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PackageFeature extends Model
{
    protected $fillable = [
        'package_id',
        'feature_id',
        'description',
        'image',
        'status',
    ];

    protected $casts = [
        'description' => 'string',
        'image' => 'string',
        'status' => 'string',
    ];

    /**
     * Get the package that owns the package feature.
     */
    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    /**
     * Get the feature that owns the package feature.
     */
    public function feature(): BelongsTo
    {
        return $this->belongsTo(Feature::class);
    }
}
