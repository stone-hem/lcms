<?php

namespace App\Http\Controllers;

use App\Models\ExternalFirm;
use App\Models\LegalCase\CaseAttachment;
use App\Models\LegalCase\LegalCase;
use App\Models\LegalCase\LegalCaseLawyer;
use App\Models\LegalCaseActivities;
use App\Models\LegalCaseNotes;
use App\Models\Task;
use App\Models\User;

use Illuminate\Http\Request;
use Inertia\Inertia;

class FileController extends Controller
{
    public function view_file(Request $request, $file_name) {}


    public function presets(Request $request)
    {
        $lawyers = User::where('role_id', 2)->get();
        $external_advocates = ExternalFirm::all();
        $base_file_path = asset('storage') . '/uploads/temp/';

        return [
            "base_file_path" => $base_file_path,
            "lawyers" => $lawyers,
            "external_advocates" => $external_advocates,
        ];
    }

    public function files(Request $request)
    {
        $legal_cases = LegalCase::select("id", "case_number", "title", "year", "notes", "activities");

        $user = $request->user();

        if($user->role_id != 1) {
            $lawyer_cases_ids = LegalCaseLawyer::where('lawyer_id', $user->id)->pluck('legal_case_id')->toArray();
            $filed_by_cases_ids = LegalCase::where('user_id', $user->id)->pluck('id')->toArray();
            $ids = array_merge($lawyer_cases_ids, $filed_by_cases_ids);
            $legal_cases->whereIn('id', $ids);
        }

        $legal_cases = $legal_cases->get();

        foreach ($legal_cases as $legal_case) {
            $case_attachments = CaseAttachment::where("legal_case_id", $legal_case->id)->pluck("files_meta");
            $activity_attachments = LegalCaseActivities::where("legal_case_id", $legal_case->id)->select("id", "title", "attachments")->get();
            $activity_notes = LegalCaseNotes::where("legal_case_id", $legal_case->id)->select("id", "title", "attachments")->get();
            $task_attachments = Task::where("legal_case_id", $legal_case->id)->whereHas("attachments")->with("attachments")->select("tasks.id", "tasks.title")->get();
            $legal_case->file_attachments = [
                "case" => $case_attachments,
                "tasks" => $task_attachments,
                "activity" => $activity_attachments,
                "notes" => $activity_notes,
            ];
        }

        return Inertia::render('files/Index', [
        "legal_cases" => $legal_cases,
       ]
        );
    }
}
