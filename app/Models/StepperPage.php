<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StepperPage extends Model
{
    protected $fillable = [
        'title',
        'description',
        'image',
        'order_no',
        'status',
    ];

    protected $casts = [
        'title' => 'string',
        'description' => 'string',
        'image' => 'string',
        'order_no' => 'integer',
        'status' => 'string',
    ];
}
