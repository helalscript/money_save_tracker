<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TimePackage extends Model
{
    protected $fillable = [
        'name',
        'description',
        'day',
        'is_unlimited',
        'status',
    ];

    protected $casts = [
        'name' => 'string',
        'description' => 'string',
        'day' => 'string',
        'is_unlimited' => 'boolean',
        'status' => 'string',
    ];

    /**
     * Get the customers for the time package.
     */
    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class, 'time_package_id');
    }
}
