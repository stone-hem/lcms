<?php

namespace App\Http\Controllers;

use App\Models\CalenderEvents;
use App\Models\EventCategories;
use App\Models\EventParticipants;
use App\Models\ExternalFirm;
use App\Models\LegalCase\LegalCase;
use App\Models\LegalCase\LegalCaseLawyer;
use App\Models\LegalCaseActivities;
use App\Models\LegalCaseActivityParticipants;
use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\TaskAssignees;
use Carbon\Carbon;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use stdClass;


class CalenderController extends Controller
{


    public function presets(Request $request)
    {
        $lawyers = User::where('role_id', 2)->orWhere("is_external_counsel", 1)->get();
        $external_advocates = ExternalFirm::all();
        $legal_cases = LegalCase::select("id", "title", "case_number", "court_name", "case_stage_id");

        if ($request->lawyer_id && $request->lawyer_id != -1) {
            $lawyer_cases_ids = LegalCaseLawyer::where('lawyer_id', $request->lawyer_id)->pluck('legal_case_id')->toArray();
            $filed_by_cases_ids = LegalCase::where('user_id', $request->lawyer_id)->pluck('id')->toArray();
            $ids = array_merge($lawyer_cases_ids, $filed_by_cases_ids);
            $legal_cases = $legal_cases->whereIn("id", $ids);
        }

        $legal_cases = $legal_cases->get();

        $base_file_path = asset('storage') . '/uploads/temp/';
        $event_categories = EventCategories::all();

        return [
            "lawyers" => $lawyers,
            "external_advocates" => $external_advocates,
            "event_categories" => $event_categories,
            "base_file_path" => $base_file_path,
            "legal_cases" => $legal_cases
        ];
    }

    public function get_calender(Request $request, $start_date, $end_date)
    {
        $case_id = $request->case_id;
        $lawyer_id = $request->lawyer_id;
        $external_advocate_id = $request->external_advocate_id;
        $user_id = $request->user()->id;
        $display_activities = $request->activities ?? 1;
        $display_tasks = $request->tasks ?? 1;
        $display_events = $request->events ?? 1;


        $task_ids = [];
        if ($lawyer_id && $lawyer_id != -1) {
            $task_ids = Task::where("user_id", $lawyer_id)->pluck("id")->toArray();
            $task_assignees_id = TaskAssignees::where("user_id", $lawyer_id)->pluck("user_id")->toArray();

            foreach ($task_assignees_id as $id) {
                array_push($task_ids, $id);
            }
            $task_ids = array_unique($task_ids);
        }


        $tasks = Task::with("assignees", "attachments", "legal_case");
        if (count($task_ids) > 0) {
            $tasks =  $tasks->whereIn("id", $task_ids);
        }

        $tasks = $tasks->where(function ($query) use ($start_date, $end_date) {
            $query->whereBetween('start_datetime', [
                Carbon::parse($start_date)->toDateTimeString(),
                Carbon::parse($end_date)->toDateTimeString()
            ])->orWhereBetween('end_datetime', [
                Carbon::parse($start_date)->toDateTimeString(),
                Carbon::parse($end_date)->toDateTimeString()
            ]);
        });

        if ($case_id) {
            $tasks = $tasks->where("legal_case_id", $case_id);
        }

        if (count($task_ids) == 0 && $lawyer_id && $lawyer_id != -1) {
            $tasks = [];
        } else {
            $tasks = $tasks->get();
        }

        $events = [];
        if ($display_events) {
            $events = CalenderEvents::with("participants")
                ->whereBetween("start_date", [
                    Carbon::parse($start_date)->toDateString(),
                    Carbon::parse($end_date)->toDateString()
                ])->orWhereBetween('end_date', [
                    Carbon::parse($start_date)->toDateString(),
                    Carbon::parse($end_date)->toDateString()
                ]);
            if ($lawyer_id) {
                $calender_event_ids = EventParticipants::where("lawyer_id", $lawyer_id)->pluck("calender_event_id")->toArray();
                $events =  $events->whereIn("id", $calender_event_ids);
            }

            if ($case_id) {
                $events = $events->where("legal_case_id", $case_id);
            }

            $events = $events->get();

            foreach ($events as $event) {
                $event->unique_id = Str::uuid();
                foreach ($event->participants as $participant) {
                    if ($participant->lawyer_id) {
                        $lawyer = User::where("id", $participant->lawyer_id)->selectRaw("id,calling_code,phone,email,CONCAT(first_name, ' ', IFNULL(middle_name, ''), ' ', last_name) AS gen_name")->first();
                        $participant->lawyer = $lawyer;
                    }
                    if ($participant->external_advocate_id) {
                        $external_advocate = ExternalFirm::where("id", $participant->external_advocate_id)->selectRaw("id,firm_name as gen_name")->first();
                        $participant->advocate = $external_advocate;
                    }
                }
            }
        }


        $legal_cases = LegalCase::with("case_type", "nature_of_claim", "lawyers", "case_stage");
        if ($case_id) {
            $legal_cases = $legal_cases->where('id', $case_id);
        }
        if ($lawyer_id) {
            $legal_cases = $legal_cases->where('lawyer_id', $lawyer_id);
        }


        $tasks_ = [];
        $activities = [];

        if ($display_tasks) {
            foreach ($tasks as $task) {
                $item = new stdClass;
                $item->title = $task->title;

                $start_date_time_split = explode(" ", $task->start_datetime);
                $end_date_time_split = explode(" ", $task->end_datetime);

                if (count($start_date_time_split) > 0) {
                    $start_date_time_split = $start_date_time_split[0] . ' 20:00:00';
                }

                if (count($end_date_time_split) > 0) {
                    $end_date_time_split = $end_date_time_split[0] . ' 20:00:00';
                }

                $item->start = $start_date_time_split; //$task->start_datetime;
                $item->end = $end_date_time_split; // $task->end_datetime;
                $item->unique_id = Str::uuid();

                $item->meta = [
                    "status" => $task->status,
                    "id" => $task->id,
                    "tags" => $task->tags,
                    "legal_case_id" => $task->legal_case_id,
                    "priority" => $task->priority,
                    "description" => $task->description,
                    "attachments" => $task->attachments,
                    "assignees" => $task->assignees
                ];
                array_push($tasks_, $item);
            }
        }

        if ($display_activities) {

            $activities = LegalCaseActivities::with("participants")
                ->whereBetween("date", [
                    Carbon::parse($start_date)->toDateString(),
                    Carbon::parse($end_date)->toDateString()
                ]);

            if ($lawyer_id) {
                $calender_activity_ids = LegalCaseActivityParticipants::where("lawyer_id", $lawyer_id)->pluck("legal_case_activity_id")->toArray();
                if (empty($calender_activity_ids)) {
                    $calender_activity_ids = [-1321, -10000000000];
                }
                $activities = $activities->whereIn("id", $calender_activity_ids);
            }

            if ($case_id) {
                $activities = $activities->where("legal_case_id", $case_id);
            }

            $activities = $activities->get();

            foreach ($activities as $activity) {
                $activity->unique_id = Str::uuid();
                foreach ($activity->participants as $participant) {
                    if ($participant->lawyer_id) {
                        $lawyer = User::where("id", $participant->lawyer_id)->selectRaw("id,calling_code,phone,is_external_counsel email,CONCAT(first_name, ' ', IFNULL(middle_name, ''), ' ', last_name) AS gen_name")->first();
                        $participant->lawyer = $lawyer;
                    }
                    $activity->start = $activity->date;
                    $activity->end = $activity->date;

                }
            }

        }

        return [
            "lawyer_id" => $lawyer_id,
            "tasks" => $tasks_,
            "activities" => $activities,
            "events" => $events,
        ];
    }





    public function delete_event($id)
    {
        $item = CalenderEvents::find($id);
        $item->delete();
        return response()->json(
            [
                "error" => false,
                "message" => "Calender event deleted successfully",
            ],
            200
        );
    }

    public function edit_event(Request $request, $id)
    {
        $current_user_id = $request->user()->id;

        $validator = Validator::make($request->all(), [
            "name" => "required|min:2",
            "start_date" => "required|min:2",
            "end_date" => "required|min:2",
        ]);
        if ($validator->fails()) {
            return response()->json(
                [
                    "error" => true,
                    "message" => $validator->errors()->toArray(),
                ],
                422
            );
        }

        $duplicateEvent = CalenderEvents::where(DB::raw('lower(title)'), '=', strtolower($request->name))
            ->where('added_by', $current_user_id)
            ->where('start_date', Carbon::parse($request->start_date))
            ->where('end_date', Carbon::parse($request->end_date))
            ->where('id', '!=', $id)
            ->count();
        if ($duplicateEvent > 0) {
            return response()->json(['error' => true, 'message' => "You have another event with the same start date and end date"], 422);
        }

        $item = CalenderEvents::find($id);
        $item->title = $request->name;
        $item->description = $request->description;
        $item->start_date = Carbon::parse($request->start_date);
        $item->end_date = Carbon::parse($request->end_date);
        $item->legal_case_id = $request->case_id;
        $item->category_id = $request->category;
        $item->priority = $request->priority;

        //$item->user_id = $request->user_id;
        //$item->external_advocate_id = $request->external_advocate_id;
        $item->added_by = $current_user_id;
        $item->attachments = $request->upload_files;
        $item->alert = true; // $request->alert;
        $item->is_public = true; // $request->is_public;
        $item->save();


        EventParticipants::where('calender_event_id', $id)->delete();
        foreach ($request->lawyers as $lawyer_id) {
            $participant = new EventParticipants();
            $participant->calender_event_id = $item->id;
            $participant->lawyer_id = $lawyer_id;
            $participant->save();
        }

        $event_ = CalenderEvents::with("participants")->find($item->id);
        $event_->unique_id = Str::uuid();
        foreach ($event_->participants as $participant) {
            if ($participant->lawyer_id) {
                $lawyer = User::where("id", $participant->lawyer_id)->selectRaw("id,calling_code,phone,email,CONCAT(first_name, ' ', IFNULL(middle_name, ''), ' ', last_name) AS gen_name")->first();
                $participant->lawyer = $lawyer;
            }
            if ($participant->external_advocate_id) {
                $external_advocate = ExternalFirm::where("id", $participant->external_advocate_id)->selectRaw("id,firm_name as gen_name")->first();
                $participant->advocate = $external_advocate;
            }
        }

        return response()->json(
            [
                "error" => false,
                "message" => "Calender event added successfully",
                "event" => $event_,
            ],
            200
        );
    }
    public function store_event(Request $request)
    {
        $current_user_id = $request->user()->id;

        $validator = Validator::make($request->all(), [
            "name" => "required|min:2",
            "start_date" => "required|min:2",
            "end_date" => "required|min:2",
        ]);
        if ($validator->fails()) {
            return response()->json(
                [
                    "error" => true,
                    "message" => $validator->errors()->toArray(),
                ],
                422
            );
        }

        $duplicateEvent = CalenderEvents::where(DB::raw('lower(title)'), '=', strtolower($request->name))
            ->where('added_by', $current_user_id)
            ->where('start_date', Carbon::parse($request->start_date))
            ->where('end_date', Carbon::parse($request->end_date))
            ->count();
        if ($duplicateEvent > 0) {
            return response()->json(['error' => true, 'message' => "You have another event with the same start date and end date"], 422);
        }

        $item = new CalenderEvents();
        $item->title = $request->name;
        $item->description = $request->description;
        $item->start_date = Carbon::parse($request->start_date);
        $item->end_date = Carbon::parse($request->end_date);
        $item->legal_case_id = $request->case_id;
        $item->category_id = $request->category;
        $item->priority = $request->priority;
        //$item->user_id = $request->user_id;
        //$item->external_advocate_id = $request->external_advocate_id;
        $item->added_by = $current_user_id;
        $item->attachments = $request->upload_files;
        $item->alert = true; // $request->alert;
        $item->is_public = true; // $request->is_public;

        $item->save();


        foreach ($request->lawyers as $lawyer_id) {
            $participant = new EventParticipants();
            $participant->calender_event_id = $item->id;
            $participant->lawyer_id = $lawyer_id;
            $participant->save();
        }


        $event_ = CalenderEvents::with("participants")->find($item->id);
        $event_->unique_id = Str::uuid();

        foreach ($event_->participants as $participant) {
            if ($participant->lawyer_id) {
                $lawyer = User::where("id", $participant->lawyer_id)->selectRaw("id,calling_code,phone,email,CONCAT(first_name, ' ', IFNULL(middle_name, ''), ' ', last_name) AS gen_name")->first();
                $participant->lawyer = $lawyer;
            }
            if ($participant->external_advocate_id) {
                $external_advocate = ExternalFirm::where("id", $participant->external_advocate_id)->selectRaw("id,firm_name as gen_name")->first();
                $participant->advocate = $external_advocate;
            }
        }






        return response()->json(
            [
                "error" => false,
                "message" => "Calender event added successfully",
                "event" => $event_,
            ],
            200
        );
    }
}
