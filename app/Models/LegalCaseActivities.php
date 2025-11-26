<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LegalCaseActivities extends Model
{
    use HasFactory;

    protected $casts = [
        "attachments" => "json",
    ];

    public function participants()
    {
        return $this->hasMany(LegalCaseActivityParticipants::class,"legal_case_activity_id","id")->with('user:users.id,first_name,middle_name,last_name,calling_code,email,phone,is_external_counsel');
    }

    public function activity(){
        return $this->belongsTo(CaseActivity::class,"case_activity_id","id");
    }
}
