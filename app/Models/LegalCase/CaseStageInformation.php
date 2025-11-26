<?php

namespace App\Models\LegalCase;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CaseStageInformation extends Model
{
    use HasFactory;

    public function caseStage()
    {
        return $this->belongsTo(CaseStage::class, 'case_stage_id');
    }
}
