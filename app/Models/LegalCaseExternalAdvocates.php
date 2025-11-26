<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LegalCaseExternalAdvocates extends Model
{
    use HasFactory;


    public function advocate(){
        return $this->belongsTo(ExternalFirm::class,'external_advocate_id','id');
    }
}
