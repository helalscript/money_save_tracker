<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Customer extends Model
{
    protected $fillable = [
        'store_detail_id',
        'time_package_id',
        'time_package_name',
        'name',
        'rfid_sn_left',
        'rfid_sn_right',
        'phone',
        'email',
        'start_time',
        'end_time',
        'status',
    ];

    protected $casts = [
        'store_detail_id' => 'integer',
        'time_package_id' => 'integer',
        'time_package_name' => 'string',
        'name' => 'string',
        'rfid_sn_left' => 'string',
        'rfid_sn_right' => 'string',
        'phone' => 'string',
        'email' => 'string',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'status' => 'string',
    ];

    /**
     * Get the store details that owns the customer.
     */
    public function storeDetails(): BelongsTo
    {
        return $this->belongsTo(StoreDetails::class, 'store_detail_id');
    }

    /**
     * Get the time package that owns the customer.
     */
    public function timePackage(): BelongsTo
    {
        return $this->belongsTo(TimePackage::class, 'time_package_id');
    }
}
