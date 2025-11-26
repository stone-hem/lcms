<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CaseFirmParty extends Model
{
    use HasFactory;

    public function party()
    {
        return $this->belongsTo(FirmParty::class);
    }
}
