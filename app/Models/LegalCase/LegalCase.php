<?php

namespace App\Models\LegalCase;

use App\Models\Attachment;
use App\Models\CaseStage;
use App\Models\Event;
use App\Models\LegalCaseActivities;
use App\Models\LegalFees\ContingentLiability;
use App\Models\LegalCase\CaseStageInformation;
use App\Models\LegalCase\CaseType as LegalCaseCaseType;
use App\Models\LegalCaseDGApproval;
use App\Models\LegalCaseExternalAdvocates;
use App\Models\LegalCaseFinalFeeNote;
use App\Models\LegalCaseInterimFeeNote;
use App\Models\LegalCaseJudgement;
use App\Models\LegalCaseProcurementAuthorityDocuments;
use App\Models\Party\Party;
use App\Models\Task;
use App\Models\User;
use File;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LegalCase extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        "serial_number",
        "title",
        "court_name",
        "year",
        "description",
        "starting_date",
        "case_type_id",
        "case_stage_id",
        "attachment"
    ];

    protected $casts = [
        "activities" => "json",
        "meta" => "json",
        "notes" => "json"
    ];
    
    protected $appends = ['total_contingent_liability'];


    public function case_type()
    {
        return $this->belongsTo(LegalCaseCaseType::class);
    }

    public function nature_of_claim()
    {
        return $this->belongsTo(NatureOfClaim::class);
    }

    public function filed_by()
    {
        return $this->belongsTo(User::class, "user_id", "id");
    }
    public function lawyers()
    {
        return $this->hasMany(LegalCaseLawyer::class)->with('user:users.id,first_name,middle_name,last_name,calling_code,email,phone,is_external_counsel');
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }
    public function external_advocates()
    {
        return $this->hasMany(LegalCaseExternalAdvocates::class)->with('advocate:external_firms.id,external_firms.firm_name');
    }

    public function sla()
    {
        return $this->hasMany(CaseSLA::class);
    }

    public function interim_fee_note()
    {
        return $this->hasOne(LegalCaseInterimFeeNote::class);
    }
    public function final_fee_note()
    {
        return $this->hasOne(LegalCaseFinalFeeNote::class);
    }
    public function contingent_liability()
    {
        return $this->hasMany(ContingentLiability::class);
    }
    public function procurement_authority_documents()
    {
        return $this->hasMany(LegalCaseProcurementAuthorityDocuments::class);
    }
    public function judgement_attachments()
    {
        return $this->hasMany(LegalCaseJudgement::class);
    }
    public function dg_approval_attachments()
    {
        return $this->hasMany(LegalCaseDGApproval::class);
    }


    public function case_stage()
    {

        return $this->belongsTo(CaseStage::class);
    }

    public function parties()
    {
        return $this->belongsToMany(
               Party::class,
               'legal_case_parties',   // pivot table
               'legal_case_id',        // FK on pivot referring to this model
               'party_id'              // FK on pivot referring to Party model
           )->withPivot('party_type_id') // if you want to access party_type_id
             ->withTimestamps();
    }


    public function caseStagesInformation()
    {
        return $this->hasMany(CaseStageInformation::class)->with('caseStage');
    }

    public function latestCaseStageInformation()
    {
        return $this->hasOne(CaseStageInformation::class)
            ->latest('created_at') // Adjust the column if necessary
            ->with('caseStage');
    }

    public function activities()
    {
        return $this->hasMany(LegalCaseActivities::class);
    }

    public function legalCaseLawyers()
    {
        return $this->hasMany(LegalCaseLawyer::class);
    }

    public function legalCaseParties()
    {
        return $this->hasMany(LegalCaseParty::class);
    }

    public function events()
    {
        return $this->hasMany(Event::class);
    }

    public function attachments()
    {
        return $this->hasMany(Attachment::class)->with('user:users.id,first_name,middle_name,last_name,calling_code,email,phone');
    }
    
    public function getTotalContingentLiabilityAttribute()
    {
        return $this->contingent_liability()->sum('amount');
    }

    
    
}
