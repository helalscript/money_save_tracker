<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SystemSetting extends Model
{
    use HasFactory;

    protected $table = 'system_settings';



    protected $fillable = [
        'title',
        'system_name',
        'email',
        'contact_number',
        'company_open_hour',
        'copyright_text',
        'logo',
        'favicon',
        'address',
        'description',
        'footer_logo'
    ];
    protected $casts = [
        'id' => 'integer',
        'title' => 'string',
        'system_name' => 'string',
        'email' => 'string',
        'contact_number' => 'string',
        'company_open_hour' => 'string',
        'copyright_text' => 'string',
        'logo' => 'string',
        'favicon' => 'string',
        'address' => 'string',
        'description' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'footer_logo' => 'string'
    ];

    public function getFileUrlAttribute($value): ?string
    {
        if (filter_var($value, FILTER_VALIDATE_URL)) {
            return $value;
        }

        if (request()->is('api/*') && !empty($value)) {
            return url($value);
        }

        return $value;
    }

    // Use this function in your existing attributes
    public function getLogoAttribute($value): ?string
    {
        return $this->getFileUrlAttribute($value);
    }

    public function getFaviconAttribute($value): ?string
    {
        return $this->getFileUrlAttribute($value);
    }

    public function getFooterLogoAttribute($value): ?string
    {
        return $this->getFileUrlAttribute($value);
    }
}
