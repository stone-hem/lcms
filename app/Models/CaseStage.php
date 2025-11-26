<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CaseStage extends Model
{
    use HasFactory;
    use SoftDeletes;

    public function after(){
        return $this->belongsTo(CaseStage::class,'order_after','id')->select('id','name','description');
    }
}
