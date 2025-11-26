<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CaseActivity extends Model
{
    use HasFactory;
    use SoftDeletes;
    
    protected $casts = [
        'fields' => 'json',
    ];
    
    public function after(){
        return $this->belongsTo(CaseActivity::class,'after','id')->select('id','name','description');
    }
    // public function tasks()
    // {
    //     return $this->hasMany(Task::class);
    // }
    
    // public function events()
    // {
    //     return $this->hasMany(Event::class);
    // }
}
