<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskAttachments extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'task_id',
        'user_id',
        'file_name',
        'size',
        'extension',
        'type',
        'description'
    ];
    
    public function user(){
       return $this->belongsTo(User::class);
    }
}