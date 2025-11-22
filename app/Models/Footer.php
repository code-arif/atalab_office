<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Footer extends Model
{

    protected $fillable = [
        'logo',
        'business_name',
        'slogan',
        'subscribe_title',
        'description',
        'subscribe_description',
        'copyright',
        'social_links',
    ];


    protected $casts = [
        'social_links' => 'array',
        'footer_links' => 'array',
    ];

    public function getLogoUrlAttribute()
    {
        return $this->logo ? asset('/' . $this->logo) : asset('default/logo.png');
    }

    // Active footer scope
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
