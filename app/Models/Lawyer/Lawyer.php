<?php

namespace App\Models\Lawyer;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Shared\Address;

class Lawyer extends Model
{
    use HasFactory;
    public function lawyerType(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(LawyerType::class);
    }

    public function address(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Address::class);
    }

    public function getLawyerTypeNameAttribute(): ?string
    {
        return $this->lawyerType ? $this->lawyerType->name : null;
    }

    public function getLawyerAddressTownAttribute(): ?string
    {
        return $this->address ? $this->address->town : null;
    }

    public function getCountyNameAttribute(): ?string
    {
        return $this->address ? $this->address->county_code : null;
    }

    public function getSubCountyNameAttribute(): ?string
    {
        return $this->address ? $this->address->sub_code : null;
    }

    protected $appends = ['lawyer_type_name', 'lawyer_address_town', 'county_name', 'sub_county_name'];
}
