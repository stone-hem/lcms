<?php

namespace App\Models\Party;

use App\Models\LegalCase\LegalCase;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Party extends Model
{
    use HasFactory;
    
    public function legalCases()
    {
        return $this->hasMany(LegalCase::class);
    }

    
    public function partyType()
    {
        return $this->belongsTo(PartyType::class);
    }
}
