<?php

namespace App\Http\Controllers;

use App\Models\CaseStage;
use App\Models\LegalCase\LegalCase;
use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\TaskAssignees;
use Carbon\Carbon;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use stdClass;

class ProfileController extends Controller
{

    public function profile(Request $request)
    {
       // $user = $request->user();
        $user = User::with("role")->find($request->user()->id);
        $case_stages = CaseStage::withTrashed()->select("id", "name")->get();
        foreach ($case_stages as $value) {
            $count = LegalCase::where('lawyer_id', $user->id)->where("case_stage_id", $value->id)->count();
            $value->count = $count;
        }
        return [
            "case_stages" => $case_stages,
            "user" => $user
        ];
    }
    public function get_contingency_report(Request $request)
    {
        $lawyer_id = $request->lawyer_id;
        $start_date = $request->start_date;
        $end_date  = $request->end_date;
        $all_time = $request->all_time;

        if ($all_time) {
            $start_date = '1970-01-01';
            $end_date = '2090-12-12';
        }

        //return $start_date.' '.$end_date;
        $lawyer_query_1 = $lawyer_id ? " and lc.lawyer_id='$lawyer_id'" : "";
        $lawyer_query_2 = $lawyer_id ? " and legal_cases.lawyer_id='$lawyer_id'" : "";

        $query = "SELECT 
        lc.id,
        lc.serial_number,
        lc.title,
        lc.case_number,
        lc.is_internal,
        lc.date_received,
        lc.date_of_filing,
        lc.completion_date,
        lc.court_name,
        lc.year,
        lc.description,
        lc.case_type_id,
        lc.case_stage_id,
        lc.nature_of_claim_id,
        lc.lawyer_id, 
        SUM(cl.amount) AS total_contingent_liability,
        u.first_name as lawyer_first_name,
        u.middle_name as lawyer_middle_name,
        u.last_name as last_middle_name,
        u.calling_code as lawyer_calling_code,
        u.phone as lawyer_phone,
        u.email as lawyer_email,
        noc.claim AS nature_of_claim,
        cs.name AS case_stage,
        ct.name AS case_type
        
        FROM 
        legal_cases lc
        JOIN contingent_liabilities cl ON lc.id = cl.legal_case_id
        JOIN users u ON lc.lawyer_id = u.id
        LEFT JOIN  nature_of_claims noc ON lc.nature_of_claim_id = noc.id
        LEFT JOIN case_stages cs ON lc.case_stage_id = cs.id
        LEFT JOIN case_types ct ON lc.case_type_id = ct.id
        WHERE lc.date_of_filing BETWEEN '$start_date' AND '$end_date' $lawyer_query_1
        GROUP BY lc.id,lc.serial_number,
        lc.title,lc.case_number,
        lc.is_internal,lc.date_received,
        lc.date_of_filing,lc.completion_date, 
        lc.court_name,lc.year,lc.description,lc.case_type_id,lc.case_stage_id,lc.nature_of_claim_id,lc.lawyer_id,
        u.id,u.first_name,u.middle_name,u.last_name,u.calling_code,u.phone,u.email, noc.id,noc.claim, cs.id,cs.name, ct.id,ct.name";

        $fee_query = "select SUM(amount) AS total_contingent_liability 
        FROM contingent_liabilities 
        JOIN legal_cases on legal_cases.id=contingent_liabilities.legal_case_id 
        WHERE legal_cases.date_of_filing BETWEEN '$start_date' AND '$end_date' $lawyer_query_2";

        $legal_cases =  DB::select($query);
        $fee_query = DB::selectOne($fee_query);

        return [
            "legal_cases" => $legal_cases,
            "contigent_fee" => $fee_query
        ];
    }


    public function general_report(Request $request)
    {
        $user_id = $request->user()->id;

        $all_time = $request->all_time;
        $start_date = $request->start_date;
        $end_date  = $request->end_date;
        $all_time = $request->all_time;

        if ($all_time) {
            $start_date = '1970-01-01';
            $end_date = '2090-12-12';
        }

        $internal_cases_count = LegalCase::where('lawyer_id', $user_id)->where('is_internal', 1)
            ->whereBetween('created_at', [
                Carbon::parse($start_date)->toDateString(),
                Carbon::parse($end_date)->toDateString()
            ])->count();
        $external_cases_count = LegalCase::where('lawyer_id', $user_id)->where('is_internal', 0)
            ->whereBetween('created_at', [
                Carbon::parse($start_date)->toDateString(),
                Carbon::parse($end_date)->toDateString()
            ])->count();

        $task_ids = Task::where("user_id", $user_id)->pluck("id")->toArray();
        $task_assignees_id = TaskAssignees::where("user_id", $user_id)->pluck("user_id")->toArray();

        foreach ($task_assignees_id as $id) {
            array_push($task_ids, $id);
        }

        $items = Task::with("assignees", "attachments", "legal_case")
            ->whereIn("id", $task_ids)->limit(10)->orderBy('created_at', 'desc')->get();

        $case_stages = CaseStage::withTrashed()->select("id", "name")->get();
        foreach ($case_stages as $value) {
            $count = LegalCase::where('lawyer_id', $user_id)->whereBetween('created_at', [
                Carbon::parse($start_date)->toDateString(),
                Carbon::parse($end_date)->toDateString()
            ])->where("case_stage_id", $value->id)->count();
            $value->count = $count;
        }

        $cases_by_nature_of_claim = DB::select("
        SELECT  noc.claim AS nature_of_claim, 
         COUNT(lc.id) AS case_count FROM 
         legal_cases lc
         LEFT JOIN 
         nature_of_claims noc ON lc.nature_of_claim_id = noc.id
        WHERE lc.date_of_filing BETWEEN '$start_date' AND '$end_date'
         GROUP BY 
         noc.claim;
        ");

        return [
            "internal_cases_count" => $internal_cases_count,
            "external_cases_count" => $external_cases_count,
            "cases_by_status" => $case_stages,
            "cases_by_nature_of_claim" => $cases_by_nature_of_claim,
            "tasks" => $items,
        ];
    }

    public function presets(Request $request)
    {
        return response()->json(
            [
                "error" => false,
                "presets" => [],
            ],
            200
        );
    }
}
