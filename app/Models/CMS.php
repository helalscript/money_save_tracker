<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CMS extends Model
{
    use HasFactory;
    protected $fillable = [
        'page',             // Page enum or string (e.g., home_page)
        'section',          // Section name (e.g., banner, about_us)
        'title',            // Title of the section
        'sub_title',        // Subtitle of the section
        'image',            // Image URL or path
        'background_image', // Background Image URL or path
        'description',      // Main description text
        'sub_description',  // Additional description text
        'button_text',      // Text for any button in the section
        'link_url',         // URL link for the button or other purpose
        'tags',             // JSON field for additional data (e.g., SEO tags, metadata)
        'status',            // Status of the section (e.g., active, inactive)
    ];

    public function getImageAttribute($value): string|null
    {
        if (filter_var($value, FILTER_VALIDATE_URL)) {
            return $value;
        }
        // Check if the request is an API request
        if (request()->is('api/*') && !empty($value)) {
            // Return the full URL for API requests
            return url($value);
        }

        // Return only the path for web requests
        return $value;
    }
    public function getBackgroundImageAttribute($value): string|null
    {
        if (filter_var($value, FILTER_VALIDATE_URL)) {
            return $value;
        }
        // Check if the request is an API request
        if (request()->is('api/*') && !empty($value)) {
            // Return the full URL for API requests
            return url($value);
        }

        // Return only the path for web requests
        return $value;
    }
}
