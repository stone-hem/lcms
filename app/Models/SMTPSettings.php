<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SMTPSettings extends Model
{
    use HasFactory;
    protected $table = 'smtp_settings';
    protected $fillable = [
        'smtp_host',
        'smtp_port',
        'smtp_encryption',
        'smtp_username',
        'smtp_password',
        'mail_from'
    ];

    public function type()
    {
        return $this->belongsTo(SMTPConfigTypes::class,'type_id','id');
    }

}
