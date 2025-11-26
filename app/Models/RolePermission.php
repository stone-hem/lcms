<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RolePermission extends Model
{
    
        public $timestamps = false; // or true if you want timestamps on pivot
    
        protected $fillable = ['role_id', 'permission_id'];
}
