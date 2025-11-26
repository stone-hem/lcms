<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DocumentTemplate extends Model
{
    use HasFactory;
    use SoftDeletes;
    
    public function document_type(){
        return $this->belongsTo(DocumentTypes::class,'document_type_id','id');
    }

    protected $casts = [
        "templates" => "json",
    ];



}
