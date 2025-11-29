<?php

namespace App\Http\Controllers\LegalCase;

use App\Helpers\QueryHelper;
use App\Models\CaseActivity;
use App\Models\CaseSLA;
use App\Models\FirmParty;
use App\Models\IndividualParty;
use App\Models\LegalCase\LegalCase;
use App\Models\LegalCase\CaseAttachment;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

use App\Models\CaseStage;
use App\Models\LegalCaseActivities;
use App\Models\LegalCaseNotes;
use App\Models\LegalCaseActivityParticipants;


use App\Models\LegalCase\CaseStageInformation;
use App\Models\LegalCase\LegalCaseLawyer;
use App\Models\LegalFees\ContingentLiability;
use App\Models\Party\PartyType;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\CaseFirmParty;
use App\Models\CaseIndividualParty;
use App\Models\DocumentTypes;
use App\Models\LegalCase\CaseType;
use App\Models\LegalCase\NatureOfClaim;
use App\Models\LegalCaseDGApproval;
use App\Models\LegalCaseFinalFeeNote;
use App\Models\LegalCaseInterimFeeNote;
use App\Models\LegalCaseJudgement;
use App\Models\LegalCaseProcurementAuthorityDocuments;
use App\Models\Task;
use Inertia\Inertia;
use stdClass;

class LegalCaseController extends Controller
{

    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|min:2|unique:legal_cases',
            'case_number' => 'required|min:2|unique:legal_cases',
            'court_name' => 'required|min:2',
            'status' => 'required',
            'date_received' => 'required',
            'date_filed' => 'required',
            'case_type_id' => 'required|exists:case_types,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => true,
                'message' => $validator->errors()->first(),
                'payload' => $validator->errors()->toArray()
            ], 422);
        }

        $lastserial = LegalCase::get()->last();
        if ($lastserial) {
            $lastFour = substr($lastserial->serial_number, -4);
            $newNumber = (int)$lastFour + 1;
            $newLastFour = str_pad($newNumber, 4, '0', STR_PAD_LEFT);
            $serial = substr($lastserial->serial_number, 0, -4) . $newLastFour;
        } else {
            $serial = "LCF/" . date("Y") . "/" . date("m") . "/0001";
        }

        $dupTitleCount = LegalCase::where(DB::raw('lower(title)'), '=', strtolower($request->title))->count();
        if ($dupTitleCount > 0) {
            return response()->json(['error' => true, 'message' => "Another case has the same title"], 422);
        }
        $dupCaseNumberCount = LegalCase::where(DB::raw('lower(case_number)'), '=', strtolower($request->case_number))->count();
        if ($dupCaseNumberCount > 0) {
            return response()->json(['error' => true, 'message' => "Another case has the same case number"], 422);
        }

        if ($request->is_internal) {
            if (count($request->lawyer_id) == 0) {
                return response()->json(['error' => true, 'message' => "No lawyers selected"], 422);
            }
        } else {
            if (count($request->external_advocate_ids) == 0) {
                return response()->json(['error' => true, 'message' => "No external advocates selected"], 422);
            }
        }

        $legalcase = new LegalCase();
        $legalcase->serial_number = $serial;
        $legalcase->title = $request->title;
        $legalcase->court_name = $request->court_name;
        $legalcase->activities = $request->activities;
        $legalcase->description = $request->description;
        $legalcase->mention_date = $request->mention_date;
        $legalcase->user_id = $request->user()->id;
        $legalcase->date_received = $request->date_received;
        //$legalcase->status = $request->status;

        $legalcase->case_stage_id = $request->status;

        $legalcase->date_of_filing = $request->date_filed;
        $legalcase->case_number = $request->case_number;
        $legalcase->nature_of_claim_id = $request->nature_of_claim_id;
        // $legalcase->lawyer_id = $request->lawyer_id;
        $legalcase->is_internal = $request->is_internal;
        $legalcase->completion_date = $request->determination_date;



        //$legalcase->case_stage_id = 1;
        $legalcase->case_type_id = $request->input('case_type_id');
        $legalcase->year = date("Y");
        $legalcase->save();

        if ($request->lawyer_id) {
            foreach ($request->lawyer_id as $lawyer_id) {
                $legal_case_lawyer = new LegalCaseLawyer();
                $legal_case_lawyer->lawyer_id = $lawyer_id;
                $legal_case_lawyer->legal_case_id = $legalcase->id;
                $legal_case_lawyer->save();
            }
        }

        if ($request->external_advocate_ids) {
            foreach ($request->external_advocate_ids as $advocate_id) {
                $legal_case_advocate = new LegalCaseLawyer();
                $legal_case_advocate->lawyer_id = $advocate_id;
                $legal_case_advocate->legal_case_id = $legalcase->id;
                $legal_case_advocate->save();
            }
        }

        if ($request->contigent_liability) {
            $contigentliability = new ContingentLiability();
            $contigentliability->legal_case_id = $legalcase->id;
            $contigentliability->amount = $request->contigent_liability;
            $contigentliability->description = "Contigent liability for case $legalcase->title";
            $contigentliability->save();
        }

        if ($request->sla_agreed_amount) {
            $item = new CaseSLA();
            $item->legal_case_id = $legalcase->id;
            $item->amount = $request->sla_agreed_amount;
            $item->paid_amount = $request->sla_paid_amount;
            $item->balance = $request->sla_balance;
            $item->description = "SLA for case $legalcase->title";
            $item->save();
        }

        if ($request->attachments) {
            $attachment = new CaseAttachment();
            $attachment->legal_case_id = $legalcase->id;
            $attachment->user_id = $request->user()->id;
            $attachment->files_meta = $request->attachments;
            $attachment->identifier = "attachments";
            $attachment->save();
        }

        if ($request->procurement_attachments) {
            $attachment = new LegalCaseProcurementAuthorityDocuments();
            $attachment->legal_case_id = $legalcase->id;
            $attachment->user_id = $request->user()->id;
            $attachment->files_meta = $request->procurement_attachments;
            $attachment->identifier = "procurement_attachments";
            $attachment->save();
        }

        if ($request->interim_fee_note_amount) {
            $item = new LegalCaseInterimFeeNote();
            $item->legal_case_id = $legalcase->id;
            $item->amount = $request->interim_fee_note_amount;
            $item->is_paid = $request->interim_fee_note_is_paid;
            $item->paid_amount = $request->interim_fee_note_amount_paid;
            $item->balance = $request->interim_fee_note_balance;
            $item->save();
        }
        if ($request->final_fee_note_amount) {
            $item = new LegalCaseFinalFeeNote();
            $item->legal_case_id = $legalcase->id;
            $item->amount = $request->final_fee_note_amount;
            $item->is_paid = $request->final_fee_note_is_paid;
            $item->paid_amount = $request->final_fee_note_amount_paid;
            $item->balance = $request->final_fee_note_balance;
            $item->save();
        }

        if ($request->judgement_attachments) {
            $attachment = new LegalCaseJudgement();
            $attachment->legal_case_id = $legalcase->id;
            $attachment->user_id = $request->user()->id;
            $attachment->files_meta = $request->judgement_attachments;
            $attachment->identifier = "judgement_attachments";
            $attachment->save();
        }
        if ($request->dg_approval_attachments) {
            $attachment = new LegalCaseDGApproval();
            $attachment->legal_case_id = $legalcase->id;
            $attachment->user_id = $request->user()->id;
            $attachment->files_meta = $request->dg_approval_attachments;
            $attachment->identifier = "dg_approval_attachments";
            $attachment->save();
        }


        if ($request->parties != null) {
            foreach ($request->parties as $clientData) {
                $client = IndividualParty::where('email', strtolower($clientData['email']))->orWhere('phone', strtolower($clientData['phone']))->first();

                if (!$client) {
                    $client = new IndividualParty();
                }

                $client->first_name = $clientData['first_name'];
                $client->middle_name = $clientData['middle_name'];
                $client->last_name = $clientData['last_name'];
                $client->phone = strtolower($clientData['phone']);
                $client->email = strtolower($clientData['email']);
                $client->physical_address = $clientData['physical_address'];
                $client->postal_address = $clientData['postal_address'];

                $client->save();

                $case_individual_party = new CaseIndividualParty();
                $case_individual_party->legal_case_id = $legalcase->id;
                $case_individual_party->party_type_id = $clientData['party_type'] ? $clientData['party_type']['value'] : null;
                $case_individual_party->individual_party_id = $client->id;

                $case_individual_party->save();
            }
        }

        if ($request->firms != null) {
            foreach ($request->firms as $firmData) {
                $client = FirmParty::where('email', strtolower($firmData['email']))->orWhere('phone', strtolower($firmData['phone']))->first();
                if (!$client) {
                    $client = new FirmParty();
                }
                $client->name = $firmData['firm_name'];
                $client->phone = $firmData['phone'];
                $client->email = $firmData['email'];
                $client->physical_address = $clientData['physical_address'];
                $client->postal_address = $clientData['postal_address'];
                // $client->location = $firmData['location'];
                $client->save();

                $case_firm_party = new CaseFirmParty();
                $case_firm_party->legal_case_id = $legalcase->id;
                $case_firm_party->party_type_id = $firmData['party_type'] ? $firmData['party_type']['value'] : null;

                $case_firm_party->firm_party_id = $client->id;
                $case_firm_party->save();
            }
        }



        $item = LegalCase::withTrashed()->with('filed_by','lawyers', 'contingent_liability', 'case_type', 'nature_of_claim', "case_stage","interim_fee_note","final_fee_note","judgement_attachments","dg_approval_attachments")->where("id", $legalcase->id)->first();
        return redirect()->back()->with('done');
    }

    public function activate($id)
    {
        $item = LegalCase::withTrashed()->find($id);
        if ($item) {
            $item->restore();
        }
        return response()->json(["error" => false, "message" => "Case activated successfully"]);
    }


    public function deactivate($id)
    {
        $item = LegalCase::withTrashed()->find($id);
        if ($item) {
            $item->delete();
        }
        return response()->json(["error" => false, "message" => "Case deactivated successfully"]);
    }

    public function delete($id)
    {
        $item = LegalCase::withTrashed()->find($id);
        if ($item) {
            $item->forceDelete();
        }
        return response()->json(["error" => false, "message" => "Case deactivated successfully"]);
    }


    public function index(Request $request)
    {
        $user = $request->user();

        $items_per_page = $request->ipp ?? 100; //default items per page is 10
        $search_query = $request->s ?? "";
        $page = $request->p ?? 0;

        $sort_by = $request->sort_by ?? "created_at";
        $order_by = $request->order_by ?? "asc";
        $start_date = $request->sd ?? "";
        $end_date = $request->ed ?? "";


        $items = LegalCase::withTrashed()->with('filed_by', 'lawyers','contingent_liability', 'case_type', 'nature_of_claim', "case_stage","interim_fee_note","final_fee_note","judgement_attachments","dg_approval_attachments","sla");

        if ($user->role_id != 1) { //should be by lawyer
            $lawyer_cases_ids = LegalCaseLawyer::where('lawyer_id', $user->id)->pluck('legal_case_id')->toArray();

            $filed_by_cases_ids = LegalCase::where('user_id', $user->id)->pluck('id')->toArray();
            $ids = array_merge($lawyer_cases_ids, $filed_by_cases_ids);
            $items->whereIn('id', $ids);
        }

        if ($search_query) {
            $title_where_array = QueryHelper::get_where_clause_with_match_mode(
                "contains",
                $search_query,
                "legal_cases.case_number"
            );
            $description_where_array = QueryHelper::get_where_clause_with_match_mode(
                "contains",
                $search_query,
                "legal_cases.description"
            );
            $items = $items->whereNested(function ($query) use (
                $description_where_array,
                $title_where_array
            ) {
                $query
                    ->orWhere([$description_where_array])
                    ->orWhere([$title_where_array]);
            });
        }

        $item_count = $items->count();
        $items = $items->skip($page)->take($items_per_page);
        // if ($sort_by) {
        //     $items = $items->orderBy($sort_by, $order_by);
        // }else{
        $items = $items->orderBy('created_at', 'desc');

        //  }
        $items = $items->get();

        $case_types =  CaseType::all();
        $nature_of_claim = NatureOfClaim::all();
        $party_types = PartyType::all();
        $lawyers = User::where('role_id', 2)->get();
        $document_types = DocumentTypes::all();
        $case_activities = CaseActivity::all();
        $external_advocates = User::where('is_external_counsel', 1)->get();
        $base_file_path = asset('storage') . '/uploads/temp/';




        return Inertia::render('case/LegalCases', [
        "total_count" => $item_count,
        "items" => $items,
       "presets" =>[
       "case_types" => $case_types,
       "nature_of_claim" => $nature_of_claim,
       "party_types" => $party_types,
       "base_file_path" => $base_file_path,
       "case_activities" => $case_activities,
       "lawyers" => $lawyers,
       "external_advocates" => $external_advocates,
       "document_types" => $document_types,
       "statuses" => CaseStage::all(),
       ]
        ]);


    }

    public function show($id)
    {
        $case = LegalCase::with([
        'contingent_liability','sla','individual_parties','firm_parties',
                'case_type', 'nature_of_claim', 'case_stage',
                'tasks.assignees',
                'lawyers',
                'attachments',
                'procurement_authority_documents',
                'interim_fee_note',
                'final_fee_note',
                'judgement_attachments',
                'dg_approval_attachments',
                'events',
                'parties.partyType',
            ])->findOrFail($id);
            
            if ($case) {
                foreach ($case->individual_parties as $key => $value) {
                    $party = IndividualParty::find($value->individual_party_id);
                    $value->party = $party;
                }
                foreach ($case->firm_parties as $key => $value) {
                    $party = FirmParty::find($value->firm_party_id);
                    $value->party = $party;
                }
            }

            return Inertia::render('case/View', [
                'case' => $case
            ]);

    }



    function copyAllFieldsToStdClass($model)
    {
        $object = new stdClass();

        // Copy all attributes
        foreach ($model->getAttributes() as $key => $value) {
            $object->$key = $value;
        }

        // Include appended attributes (e.g., accessors)
        foreach ($model->getAppends() as $key) {
            $object->$key = $model->$key;
        }

        // Include relationships if needed
        foreach ($model->getRelations() as $key => $relation) {
            $object->$key = $relation;
        }

        return $object;
    }



    public function delete_party($id)
    {
        IndividualParty::where("id", $id)->delete();
        return redirect()->back()->with(["error" => false, "message" => "Party successfully deleted"]);
    }
    public function delete_firm_party($id)
    {
        FirmParty::where("id", $id)->delete();
        return redirect()->back()->with(["error" => false, "message" => "Firm successfully deleted"]);
    }

    public function case_parties_and_firms(Request $request, $id)
    {
        $parties = [];
        $firms = [];

        $item = LegalCase::withTrashed()->with('individual_parties', "firm_parties")->where('id', $id)->first();

        if ($item) {
            foreach ($item->individual_parties as $key => $value) {
                $party = IndividualParty::find($value->individual_party_id);
                if ($party) {
                    $copy = $this->copyAllFieldsToStdClass($party);
                    $party_type = PartyType::find($value->party_type_id);

                    $copy->party_type = $party_type;
                    array_push($parties, $copy);
                }
            }
            foreach ($item->firm_parties as $key => $firmValue) {
                $party = FirmParty::find($firmValue->firm_party_id);
                if ($party) {
                    //echo '----Firm party id ----> ' . $value->firm_party_id . ' ---- Party type id ---> ' . $value->party_type_id;

                    $copy = $this->copyAllFieldsToStdClass($party);
                    // echo json_encode($copy);

                    $party_type = PartyType::find($firmValue->party_type_id);
                    $copy->party_type = $party_type;

                    array_push($firms, $copy);
                }
            }
        }

        return [
            "parties" => $parties,
            "firms" => $firms
        ];
    }

    public function edit_case_parties(Request $request, $id)
    {
        if ($request->parties != null) {
            foreach ($request->parties as $clientData) {
                $client = IndividualParty::find($clientData['id']);
                if ($client) {
                    $client->first_name = $clientData['first_name'];
                    $client->middle_name = $clientData['middle_name'];
                    $client->last_name = $clientData['last_name'];
                    $client->phone = strtolower($clientData['phone']);
                    $client->email = strtolower($clientData['email']);
                    $client->physical_address = $clientData['physical_address'];
                    $client->postal_address = $clientData['postal_address'];
                    $client->save();


                    $item = CaseIndividualParty::where('legal_case_id', $id)->where("individual_party_id", $client->id)->first();
                    if ($item) {
                        $item->party_type_id = $clientData['party_type'] ? $clientData['party_type']['value'] : null;
                    }
                    $item->save();
                }
            }
        }

        $response = [];
        $item = LegalCase::withTrashed()->with('individual_parties')->where('id', $id)->first();

        if ($item) {
            foreach ($item->individual_parties as $key => $value) {
                $party = IndividualParty::find($value->individual_party_id);
                if ($party) {
                    $copy = $this->copyAllFieldsToStdClass($party);

                    $party_type = PartyType::find($value->party_type_id);
                    $copy->party_type = $party_type;
                    array_push($response, $copy);
                }
                //$value->party = $party;


                //$value->party_type = $party_type;
            }
        }

        return $response;
    }


    public function edit_case_firms(Request $request, $id)
    {
        if ($request->firms != null) {
            foreach ($request->firms as $firmData) {
                $client = FirmParty::find($firmData['id']);
                if ($client) {
                    $client->name = $firmData['firm_name'];
                    $client->phone = $firmData['phone'];
                    $client->email = $firmData['email'];
                    $client->physical_address = $firmData['physical_address'];
                    $client->postal_address = $firmData['postal_address'];
                    // $client->location = $firmData['location'];
                    $client->save();


                    $item = CaseFirmParty::where('legal_case_id', $id)->where("firm_party_id", $client->id)->first();
                    if ($item) {
                        $item->party_type_id = $firmData['party_type'] ? $firmData['party_type']['value'] : null;
                    }
                    $item->save();
                }
            }
        }

        $response = [];
        $item = LegalCase::withTrashed()->with('firm_parties')->where('id', $id)->first();
        if ($item) {
            foreach ($item->firm_parties as $key => $value) {
                $party = FirmParty::find($value->firm_party_id);
                if ($party) {
                    $copy = $this->copyAllFieldsToStdClass($party);

                    $party_type = PartyType::find($value->party_type_id);
                    $copy->party_type = $party_type;
                    array_push($response, $copy);
                }
            }
        }

        return $response;
    }


    public function store_case_parties(Request $request, $id)
    {
        if ($request->parties != null) {
            foreach ($request->parties as $clientData) {
                $client = IndividualParty::where('email', strtolower($clientData['email']))->orWhere('phone', strtolower($clientData['phone']))->first();

                if (!$client) {
                    $client = new IndividualParty();
                }

                $client->first_name = $clientData['first_name'];
                $client->middle_name = $clientData['middle_name'];
                $client->last_name = $clientData['last_name'];
                $client->phone = strtolower($clientData['phone']);
                $client->email = strtolower($clientData['email']);
                $client->physical_address = $clientData['physical_address'];
                $client->postal_address = $clientData['postal_address'];

                $client->save();

                $case_individual_party = new CaseIndividualParty();
                $case_individual_party->legal_case_id = $id;
                $case_individual_party->party_type_id = $clientData['party_type'] ? $clientData['party_type']['value'] : null;
                $case_individual_party->individual_party_id = $client->id;

                $case_individual_party->save();
            }
        }

        $response = [];
        $item = LegalCase::withTrashed()->with('individual_parties')->where('id', $id)->first();

        if ($item) {
            foreach ($item->individual_parties as $key => $value) {
                $party = IndividualParty::find($value->individual_party_id);
                if ($party) {
                    $copy = $this->copyAllFieldsToStdClass($party);

                    $party_type = PartyType::find($value->party_type_id);
                    $copy->party_type = $party_type;
                    array_push($response, $copy);
                }
                //$value->party = $party;


                //$value->party_type = $party_type;
            }
        }

        return $response;
    }

    public function store_case_firms(Request $request, $id)
    {
        if ($request->firms != null) {
            foreach ($request->firms as $firmData) {
                $client = FirmParty::where('email', strtolower($firmData['email']))->orWhere('phone', strtolower($firmData['phone']))->first();

                if (!$client) {
                    $client = new FirmParty();
                }

                $client->name = $firmData['firm_name'];
                $client->phone = $firmData['phone'];
                $client->email = $firmData['email'];
                $client->physical_address = $firmData['physical_address'];
                $client->postal_address = $firmData['postal_address'];
                // $client->location = $firmData['location'];
                $client->save();


                $case_firm_party = new CaseFirmParty();
                $case_firm_party->legal_case_id = $id;
                $case_firm_party->party_type_id = $firmData['party_type'] ? $firmData['party_type']['value'] : null;
                $case_firm_party->firm_party_id = $client->id;

                $case_firm_party->save();
            }
        }

        $response = [];
        $item = LegalCase::withTrashed()->with('firm_parties')->where('id', $id)->first();

        if ($item) {
            foreach ($item->firm_parties as $key => $value) {
                $party = FirmParty::find($value->firm_party_id);
                if ($party) {
                    $copy = $this->copyAllFieldsToStdClass($party);

                    $party_type = PartyType::find($value->party_type_id);
                    $copy->party_type = $party_type;
                    array_push($response, $copy);
                }
            }
        }

        return $response;
    }




    public function add_edit_note(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'note' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'error' => true,
                'message' => $validator->errors()->first(),
                'payload' => $validator->errors()->toArray()
            ], 422);
        }
        $legal_case = LegalCase::find($id);
        if (!$legal_case) {
            return response()->json([
                'error' => true,
                'message' => "Case not found"
            ], 422);
        }

        $notes = $legal_case->notes; //could be an array of notes empty
        if (!$notes) {
            $legal_case->notes = [$request->note];
        } else {
            $found = false;
            $id = $request->note["id"];
            $notes_buffer = [];
            foreach ($notes as $value) {
                if ($value["id"] == $id) {
                    $found = true;
                    array_push($notes_buffer, $request->note);
                } else {
                    array_push($notes_buffer, $value);
                }
            }
            if (!$found) {
                array_push($notes_buffer, $request->note);
            }
            $legal_case->notes = $notes_buffer;
        }
        $legal_case->save();

        return response()->json([
            'error' => false,
            'notes' => $legal_case->notes,
        ], 200);
    }

    public function update(Request $request, $id)
    {

        $validator = Validator::make($request->all(), [
            'title' => 'required|min:2|unique:legal_cases,title,' . $id,
            'case_number' => 'required|min:2|unique:legal_cases,case_number,' . $id,
            'court_name' => 'required|min:2',
            //'description' => 'required',
            'status' => 'required',
            'date_received' => 'required',
            'date_filed' => 'required',
            'case_type_id' => 'required|exists:case_types,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => true,
                'message' => $validator->errors()->first(),
                'payload' => $validator->errors()->toArray()
            ], 422);
        }

        $dupTitleCount = LegalCase::where(DB::raw('lower(title)'), '=', strtolower($request->title))->where('id', '!=', $id)->count();
        if ($dupTitleCount > 0) {
            return response()->json(['error' => true, 'message' => "Another case has the same title"], 422);
        }
        $dupCaseNumberCount = LegalCase::where(DB::raw('lower(case_number)'), '=', strtolower($request->case_number))->where('id', '!=', $id)->count();
        if ($dupCaseNumberCount > 0) {
            return response()->json(['error' => true, 'message' => "Another case has the same case number"], 422);
        }


        $legalcase = LegalCase::withTrashed()->find($id);
        if (!$legalcase) {
            return response()->json([
                'error' => true,
                'message' => "Legal case not found",
            ], 422);
        }

        if ($request->is_internal) {
            if (count($request->lawyer_id) == 0) {
                return response()->json(['error' => true, 'message' => "No lawyers selected"], 422);
            }
        } else {
            if (count($request->external_advocate_ids) == 0) {
                return response()->json(['error' => true, 'message' => "No external advocates selected"], 422);
            }
        }



        $legalcase->title = $request->title;
        $legalcase->court_name = $request->court_name;
        $legalcase->description = $request->description;
        $legalcase->activities = $request->activities;
        $legalcase->user_id = $request->user()->id;
        $legalcase->date_received = $request->date_received;
        $legalcase->mention_date = $request->mention_date;

        // $legalcase->status = $request->status;
        $legalcase->case_stage_id = $request->status;

        $legalcase->date_of_filing = $request->date_filed;
        $legalcase->case_number = $request->case_number;
        $legalcase->nature_of_claim_id = $request->nature_of_claim_id;
        // $legalcase->lawyer_id = $request->lawyer_id;
        $legalcase->is_internal = $request->is_internal;
        $legalcase->completion_date = $request->determination_date;



        //$legalcase->case_stage_id = 1;
        $legalcase->case_type_id = $request->input('case_type_id');
        //$legalcase->year = date("Y");
        $legalcase->save();

        LegalCaseLawyer::where('legal_case_id', $id)->delete();
        if ($request->lawyer_id) {
            foreach ($request->lawyer_id as $lawyer_id) {
                $legal_case_lawyer = new LegalCaseLawyer();
                $legal_case_lawyer->lawyer_id = $lawyer_id;
                $legal_case_lawyer->legal_case_id = $legalcase->id;
                $legal_case_lawyer->save();
            }
        }

        if ($request->external_advocate_ids) {
            foreach ($request->external_advocate_ids as $advocate_id) {
                $legal_case_advocate = new LegalCaseLawyer();
                $legal_case_advocate->lawyer_id = $advocate_id;
                $legal_case_advocate->legal_case_id = $legalcase->id;
                $legal_case_advocate->save();
            }
        }


        ContingentLiability::where('legal_case_id', $id)->delete();
        if ($request->contigent_liability) {
            $contigentliability = new ContingentLiability();
            $contigentliability->legal_case_id = $legalcase->id;
            $contigentliability->amount = $request->contigent_liability;
            $contigentliability->description = "Contigent liability for case $legalcase->title";
            $contigentliability->save();
        }

        CaseSLA::where('legal_case_id', $id)->delete();
        if ($request->sla_agreed_amount) {
            $item = new CaseSLA();
            $item->legal_case_id = $legalcase->id;
            $item->amount = $request->sla_agreed_amount;
            $item->paid_amount = $request->sla_paid_amount;
            $item->balance = $request->sla_balance;
            $item->description = "SLA for case $legalcase->title";
            $item->save();
        }


        LegalCaseInterimFeeNote::where('legal_case_id', $id)->delete();
        if ($request->interim_fee_note_amount) {
            $item = new LegalCaseInterimFeeNote();
            $item->legal_case_id = $legalcase->id;
            $item->amount = $request->interim_fee_note_amount;
            $item->is_paid = $request->interim_fee_note_is_paid;
            $item->paid_amount = $request->interim_fee_note_amount_paid;
            $item->balance = $request->interim_fee_note_balance;
            $item->save();
        }

        LegalCaseFinalFeeNote::where('legal_case_id', $id)->delete();
        if ($request->final_fee_note_amount) {
            $item = new LegalCaseFinalFeeNote();
            $item->legal_case_id = $legalcase->id;
            $item->amount = $request->final_fee_note_amount;
            $item->is_paid = $request->final_fee_note_is_paid;
            $item->paid_amount = $request->final_fee_note_amount_paid;
            $item->balance = $request->final_fee_note_balance;
            $item->save();
        }
        LegalCaseProcurementAuthorityDocuments::where("legal_case_id", $legalcase->id)->delete();
        if ($request->procurement_attachments) {
            $attachment = new LegalCaseProcurementAuthorityDocuments();
            $attachment->legal_case_id = $legalcase->id;
            $attachment->user_id = $request->user()->id;
            $attachment->files_meta = $request->procurement_attachments;
            $attachment->identifier = "procurement_attachments";
            $attachment->save();
        }


        LegalCaseJudgement::where("legal_case_id", $legalcase->id)->delete();
        if ($request->judgement_attachments) {
            $attachment = new LegalCaseJudgement();
            $attachment->legal_case_id = $legalcase->id;
            $attachment->user_id = $request->user()->id;
            $attachment->files_meta = $request->judgement_attachments;
            $attachment->identifier = "judgement_attachments";
            $attachment->save();
        }

        LegalCaseDGApproval::where("legal_case_id", $legalcase->id)->delete();
        if ($request->dg_approval_attachments) {
            $attachment = new LegalCaseDGApproval();
            $attachment->legal_case_id = $legalcase->id;
            $attachment->user_id = $request->user()->id;
            $attachment->files_meta = $request->dg_approval_attachments;
            $attachment->identifier = "dg_approval_attachments";
            $attachment->save();
        }

        CaseSLA::where('legal_case_id', $id)->delete();
        if ($request->attachments) {
            $attachment = new CaseAttachment();
            $attachment->legal_case_id = $legalcase->id;
            $attachment->user_id = $request->user()->id;
            $attachment->files_meta = $request->attachments;
            $attachment->identifier = "attachments";
            $attachment->save();
        }

        $item = LegalCase::withTrashed()->with('filed_by', 'lawyers','contingent_liability', 'case_type', 'nature_of_claim', "case_stage","interim_fee_note","final_fee_note","judgement_attachments","dg_approval_attachments")->where("id", $legalcase->id)->first();


        return redirect()->back()->with([
            'status' => 200,
            'message' => 'Case filed successfully',
            "item" => $item
        ]);
    }

    public function updatecasestages(Request $request)
    {
        $legal_case_id = $request->legal_case_id;
        $stage_id = $request->case_stage_id;

        try {
            $legalcase = LegalCase::find($legal_case_id);
            $legalcase->case_stage_id = $stage_id;
            $legalcase->save();

            $casestageinformation = new CaseStageInformation();
            $casestageinformation->legal_case_id = $legal_case_id;
            $casestageinformation->case_stage_id = $stage_id;
            $casestageinformation->approval_status = $request->approval_status;
            $casestageinformation->save();

            // $stage_name = CaseStage::where('id', $stage_id)->first()->name;

            // return response()->json(['status' => 'success', 'message' => "Legal Case moved to $stage_name stage"], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function updatecasestagestatus(Request $request)
    {
        $legal_case_id = $request->legal_case_id;
        $stage_id = $request->case_stage_id;
        $approval_status = $request->approval_status;
        $comments = $request->comments;

        try {
            $casestageinformation = CaseStageInformation::where('legal_case_id', $legal_case_id)->where('case_stage_id', $stage_id)->first();
            $casestageinformation->approval_status = $approval_status;
            $casestageinformation->comments = $comments;
            $casestageinformation->save();
            return response()->json(['status' => 'success', 'message' => "Legal Case Stage Updated Successfully"], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }


    public function case_activites($id)
    {
        $legal_case_activities = LegalCaseActivities::with("participants", "activity")->where("legal_case_id", $id)->get();
        return $legal_case_activities;
    }

    public function create_case_activity(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'legal_case_id' => 'required',
            'case_activity_id' => 'required',
            'status' => 'required|in:1,2,3',
            'date' => 'required|date',
            'title' => 'sometimes|string|min:2|unique:legal_case_activities,title'
        ]);


        if ($validator->fails()) {
            return response()->json([
                'error' => true,
                'message' => $validator->errors()->first(),
                'payload' => $validator->errors()->toArray()
            ], 422);
        }

        $legal_case_activity = new LegalCaseActivities();
        $legal_case_activity->title = $request->title;
        $legal_case_activity->legal_case_id = $request->legal_case_id;
        $legal_case_activity->case_activity_id = $request->case_activity_id;
        $legal_case_activity->description = $request->description;
        $legal_case_activity->date = $request->date;
        $legal_case_activity->status = $request->status;
        $legal_case_activity->created_by = Auth::user()->id;

        $legal_case_activity->attachments = $request->attachments;



        $legal_case_activity->save();

        if ($request->participants) {
            foreach ($request->participants as $lawyer_id) {
                $participant = new LegalCaseActivityParticipants();
                $participant->lawyer_id = $lawyer_id;
                $participant->legal_case_activity_id = $legal_case_activity->id;
                $participant->save();
            }
        }


        $item = LegalCaseActivities::with("participants", "activity")->where("id", $legal_case_activity->id)->first();

        return response()->json([
            'status' => 200,
            'message' => 'Activity created successfully',
            'item' => $item,
        ], 200);
    }

    public function delete_case_activity($id)
    {
        $item = LegalCaseActivities::find($id);
        if (!$item) {
            return response()->json([
                'error' => true,
                'message' => "Legal case activity not found",
            ], 422);
        }

        $item->delete();
        return response()->json([
            'status' => 200,
            'message' => 'Activity deleted successfully',
        ], 200);
    }


    public function case_overview(Request $request, $id)
    {
        $item = LegalCase::withTrashed()->with('contingent_liability', 'case_type', 'nature_of_claim', 'sla', 'individual_parties', 'firm_parties', 'attachments', 'lawyers', "case_stage")->where('id', $id)->first();
        if ($item) {
            foreach ($item->individual_parties as $key => $value) {
                $party = IndividualParty::find($value->individual_party_id);
                $value->party = $party;

                $party_type = PartyType::find($value->party_type_id);
                $value->party_type = $party_type;
            }
            foreach ($item->firm_parties as $key => $value) {
                $party = FirmParty::find($value->firm_party_id);
                $value->party = $party;

                $party_type = PartyType::find($value->party_type_id);
                $value->party_type = $party_type;
            }

            //tasks
            $tasks = Task::where("legal_case_id", $id)->where("status", "!=", 3)->get();
            $item->tasks = $tasks;
            //

            //activities
            $activities = LegalCaseActivities::with("participants", "activity")->where("legal_case_id", $id)->get();
            $item->activities = $activities;
            //

            //notes --------
            $legal_case_notes = LegalCaseNotes::where("legal_case_id", $id)->get();
            $item->notes = $legal_case_notes;
            //

            $case_attachments = CaseAttachment::where("legal_case_id", $id)->pluck("files_meta");
            $activity_attachments = LegalCaseActivities::where("legal_case_id", $id)->select("id", "title", "attachments")->get();
            $activity_notes = LegalCaseNotes::where("legal_case_id", $id)->select("id", "title", "attachments")->get();
            $task_attachments = Task::where("legal_case_id", $id)->whereHas("attachments")->with("attachments")->select("tasks.id", "tasks.title")->get();

            $item->file_attachments = [
                "case" => $case_attachments,
                "tasks" => $task_attachments,
                "activity" => $activity_attachments,
                "notes" => $activity_notes,
            ];
        }
        return $item;
    }



    public function update_case_activity(Request $request, $id)
    {

        $validator = Validator::make($request->all(), [
            'legal_case_id' => 'required',
            'case_activity_id' => 'required',
            'status' => 'required|in:1,2,3',
            'date' => 'required|date',
            'title' => 'sometimes|string|min:2|unique:legal_case_activities,title,' . $id
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => true,
                'message' => $validator->errors()->first(),
                'payload' => $validator->errors()->toArray()
            ], 422);
        }


        $item = LegalCaseActivities::find($id);
        if (!$item) {
            return response()->json([
                'error' => true,
                'message' => "Legal case activity not found",
            ], 422);
        }

        $item->title = $request->title;
        $item->legal_case_id = $request->legal_case_id;
        $item->case_activity_id = $request->case_activity_id;
        $item->description = $request->description;
        $item->date = $request->date;
        $item->status = $request->status;
        $item->attachments = $request->attachments;
        $item->save();

        LegalCaseActivityParticipants::where('legal_case_activity_id', $id)->delete();
        if ($request->participants) {
            foreach ($request->participants as $lawyer_id) {
                $participant = new LegalCaseActivityParticipants();
                $participant->lawyer_id = $lawyer_id;
                $participant->legal_case_activity_id = $id;
                $participant->save();
            }
        }


        $item = LegalCaseActivities::with("participants", "activity")->where("id", $id)->first();

        return response()->json([
            'status' => 200,
            'message' => 'Activity update successfully',
            'item' => $item,
        ], 200);
    }



    public function case_attachments($id)
    {
        $attachments = CaseAttachment::where("legal_case_id", $id)->pluck("files_meta");
        if (count($attachments) > 0) {
            return $attachments[0];
        }
        return $attachments;
    }

    public function delete_case_attachment(Request $request, $legal_case_id, $attachment_id)
    {
        $caseAttachment  = CaseAttachment::where("legal_case_id", $legal_case_id)->first();
        if (!$caseAttachment) {
            return response()->json([
                'error' => true,
                'message' => "Attachment not found",
            ], 422);
        }
        $attachmentsArray = CaseAttachment::where("legal_case_id", $legal_case_id)->pluck("files_meta")[0];
        $attachment_id = (string) $attachment_id;
        $updatedAttachments = array_filter($attachmentsArray, function ($attachment) use ($attachment_id) {
            return bccomp((string) $attachment['id'], $attachment_id, 10) !== 0;
        });

        $updatedAttachments = array_values($updatedAttachments);
        $caseAttachment->files_meta = $updatedAttachments;
        $caseAttachment->save();

        return response()->json([
            'error' => false,
            'attachments' => $this->get_case_attachments_helper($legal_case_id),

        ], 200);
    }

    public function get_case_attachments_helper($legal_case_id)
    {
        $attachmentsArray = CaseAttachment::where("legal_case_id", $legal_case_id)->pluck("files_meta");
        if (count($attachmentsArray) == 0) {
            return [];
        }
        $attachmentsArray =  $attachmentsArray[0];
        foreach ($attachmentsArray as $key => $value) {
            if (isset($value['type']) && $value['type'] != -1) {
                $type = $value['type'];
                if ($type != -1) {
                    $docType = DocumentTypes::find($type);
                    if ($docType) {
                        $attachmentsArray[$key]['temp_type'] = $docType->name;
                    }
                }
            }
        }
        $updatedAttachments = array_values($attachmentsArray);
        return  $updatedAttachments;
    }

    public function edit_case_attachments(Request $request, $id)
    {
        $attachment = $request->attachment;
        $attachments  = CaseAttachment::where("legal_case_id", $id)->first();
        if (!$attachments) {
            return response()->json([
                'error' => true,
                'message' => "Attachment not found",

            ], 422);
        }
        $attachmentsArray = CaseAttachment::where("legal_case_id", $id)->pluck("files_meta")[0];

        $attachment_id = (string) $attachment["id"];

        foreach ($attachmentsArray as $key => $value) {
            if (isset($value['id'])) {
                if (bccomp((string) $value['id'], $attachment_id, 10) == 0) {
                    $attachmentsArray[$key] = $attachment;
                }
            }
        }

        $updatedAttachments = array_values($attachmentsArray);
        $attachments->files_meta = $updatedAttachments;
        $attachments->save();

        return response()->json([
            'status' => 200,
            'attachments' => $this->get_case_attachments_helper($id),
        ], 200);
    }
    public function store_case_attachments(Request $request, $id)
    {
        $attachments  = CaseAttachment::where("legal_case_id", $id)->first();
        if (!$attachments) {
            $attachments = new CaseAttachment();
            $attachments->legal_case_id = $id;
            $attachments->user_id = Auth::user()->id;
        }
        $attachments->files_meta = $request->attachments;
        $attachments->save();



        $attachmentsArray = CaseAttachment::where("legal_case_id", $id)->pluck("files_meta")[0];

        foreach ($attachmentsArray as $key => $value) {
            if (!isset($value['date_uploaded'])) {
                $attachmentsArray[$key]['date_uploaded'] = Carbon::now()->toDateTimeString();
            }
        }

        $updatedAttachments = array_values($attachmentsArray);
        $attachments->files_meta = $updatedAttachments;
        $attachments->save();


        return response()->json([
            'status' => 200,
            'attachments' => $this->get_case_attachments_helper($id),
        ], 200);
    }


    public function case_notes($id)
    {
        $legal_case_notes = LegalCaseNotes::where("legal_case_id", $id)->get();
        return $legal_case_notes;
    }

    public function create_case_note(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'legal_case_id' => 'required',
            'date' => 'required|date',
            'note' => 'required',
            'title' => 'required|string|min:2|unique:legal_case_notes,title'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => true,
                'message' => $validator->errors()->first(),
                'payload' => $validator->errors()->toArray()
            ], 422);
        }

        $legal_case_note = new LegalCaseNotes();
        $legal_case_note->title = $request->title;
        $legal_case_note->legal_case_id = $request->legal_case_id;
        $legal_case_note->date = $request->date;
        $legal_case_note->note = $request->note;
        $legal_case_note->created_by = Auth::user()->id;
        $legal_case_note->attachments = $request->attachments;
        $legal_case_note->save();



        $item = LegalCaseNotes::where("id", $legal_case_note->id)->first();

        return response()->json([
            'status' => 200,
            'message' => 'Note created successfully',
            'item' => $item,
        ], 200);
    }

    public function delete_case_note($id)
    {
        $item = LegalCaseNotes::find($id);
        if (!$item) {
            return response()->json([
                'error' => true,
                'message' => "Legal case note not found",
            ], 422);
        }

        $item->delete();
        return response()->json([
            'status' => 200,
            'message' => 'Note deleted successfully',
        ], 200);
    }
    public function update_case_note(Request $request, $id)
    {

        $validator = Validator::make($request->all(), [
            'legal_case_id' => 'required',
            'note' => 'required',
            'date' => 'required|date',
            'title' => 'required|string|min:2|unique:legal_case_notes,title,' . $id
        ]);


        if ($validator->fails()) {
            return response()->json([
                'error' => true,
                'message' => $validator->errors()->first(),
                'payload' => $validator->errors()->toArray()
            ], 422);
        }


        $item = LegalCaseNotes::find($id);
        if (!$item) {
            return response()->json([
                'error' => true,
                'message' => "Legal case note not found",
            ], 422);
        }

        $item->title = $request->title;
        $item->note = $request->note;
        $item->legal_case_id = $request->legal_case_id;
        $item->attachments = $request->attachments;
        $item->date = $request->date;
        $item->save();



        $item = LegalCaseNotes::where("id", $id)->first();

        return response()->json([
            'status' => 200,
            'message' => 'Note updated successfully',
            'item' => $item,
        ], 200);
    }
}
