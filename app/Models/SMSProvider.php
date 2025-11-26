<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SMSProvider extends Model
{
    use HasFactory;
    protected $table = 'sms_providers';
    protected $casts = [
        'active' => 'boolean',
    ];
    protected $fillable = [
        'identifier',
        'auth_token',
        'provider',
        'active'
    ];
}
