<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExternalFirm extends Model
{
    use SoftDeletes;
    
        protected $fillable = [
            'firm_name', 'bank_name', 'bank_branch', 'bank_account_number',
            'postal_address', 'kra_pin', 'email', 'phone', 'physical_address',
            'website', 'notes', 'can_login', 'user_id'
        ];
    
        protected $casts = [
            'can_login' => 'boolean',
        ];
    
        public function user()
        {
            return $this->belongsTo(User::class);
        }
}
