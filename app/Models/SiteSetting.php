<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'tagline',
        'about_us',
        'privacy_policy',
        'return_policy',
        'terms_and_conditions',
        'phones',
        'emails',
        'address_line_1',
        'address_line_2',
        'lat',
        'lng',
        'city',
        'state',
        'zip',
        'country',
        'app_store_logo_path',
        'google_play_store_logo_path',
        'site_logo_path',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        "emails" => "json",
        "phones" => "json",
        "about_us" => "json",
    ];


    public function images()
    {
        //    return $this->hasMany(Upload::class, 'item_id', 'id')->where('type', 'image')->where('table', 'site_setting');
    }
}
