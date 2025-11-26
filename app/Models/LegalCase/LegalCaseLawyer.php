<?php

namespace App\Models\LegalCase;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LegalCaseLawyer extends Model
{
    use HasFactory;

    public function user(){
        return $this->belongsTo(User::class,'lawyer_id','id');
    }

}
