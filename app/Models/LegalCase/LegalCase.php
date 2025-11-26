<?php

namespace App\Models\LegalCase;

use App\Models\CaseActivity;
use App\Models\CaseFirmParty;
use App\Models\CaseIndividualParty;
use App\Models\CaseStage;
use App\Models\Event;
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


    public function individual_parties()
    {
        return $this->hasMany(CaseIndividualParty::class); //->with('party:individual_parties.id,first_name,middle_name,last_name,calling_code,email,phone,physical_address,postal_address');
    }

    public function firm_parties()
    {
        return $this->hasMany(CaseFirmParty::class); //->with('party:individual_parties.id,name,calling_code,email,phone,physical_address,postal_address');
    }

    public function case_stage()
    {

        return $this->belongsTo(CaseStage::class);
    }

    public function parties()
    {
        return $this->hasMany(Party::class);
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

    public function caseActivities()
    {
        return $this->hasMany(CaseActivity::class);
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
        return $this->hasMany(CaseAttachment::class)->with('user:users.id,first_name,middle_name,last_name,calling_code,email,phone');
    }
}
