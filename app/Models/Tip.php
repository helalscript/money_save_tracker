<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tip extends Model
{
    protected $fillable = [
        'text',
        'type',
        'status',
    ];

    protected $casts = [
        'text' => 'string',
        'type' => 'string',
        'status' => 'string',
    ];

    /**
     * Get the user tips for the tip.
     */
    public function userTips(): HasMany
    {
        return $this->hasMany(UserTip::class);
    }
}
