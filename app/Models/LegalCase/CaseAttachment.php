<?php

namespace App\Models\LegalCase;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;


class CaseAttachment extends Model
{
    use HasFactory;
    
    protected $casts = [
        'files_meta' => 'json',
    ];
    
    public function user(){
       return $this->belongsTo(User::class);
    }
}
