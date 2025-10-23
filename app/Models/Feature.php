<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Feature extends Model
{
    protected $fillable = [
        'title',
        'description',
        'image',
        'access_level',
        'custom_tag',
        'custom_value',
        'status',
    ];

    protected $casts = [
        'title' => 'string',
        'description' => 'string',
        'image' => 'string',
        'access_level' => 'string',
        'custom_tag' => 'string',
        'custom_value' => 'string',
        'status' => 'string',
    ];

    /**
     * Get the packages that belong to the feature.
     */
    public function packages(): BelongsToMany
    {
        return $this->belongsToMany(Package::class, 'package_feature')
                    ->withPivot(['description', 'image', 'status'])
                    ->withTimestamps();
    }
}
