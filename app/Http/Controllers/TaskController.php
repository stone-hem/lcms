<?php

namespace App\Http\Controllers;

use App\Helpers\QueryHelper;
use App\Models\Task;
use App\Models\Lawyer\Lawyer;
use App\Models\LegalCase\LegalCase;
use App\Models\TaskAssignees;
use App\Models\TaskAttachments;
use App\Models\User;
use Error;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use League\CommonMark\Node\Query;
use PhpParser\Node\Stmt\Foreach_;

class TaskController extends Controller
{

    public function get_kanban_view(Request $request)
    {
        $user_id = $request->user()->id;
        $task_ids = Task::where("user_id", $user_id)->pluck("id")->toArray();
        $task_assignees_id = TaskAssignees::where("user_id", $user_id)
            ->pluck("user_id")
            ->toArray();

        foreach ($task_assignees_id as $id) {
            array_push($task_ids, $id);
        }

        $status = $this->status();
        $content = [];
        foreach ($status as $value) {
            if ($value['value'] == 100) continue;
            $items = Task::with("assignees", "attachments", "legal_case")
                ->whereIn("id", $task_ids)
                ->whereIn("status", [$value['value']])->get();

            $value['tasks'] = $items;
            $value['id'] = $value['value'];
            array_push($content, $value);
        }
        
        return Inertia::render('tasks/Kanban', [
            "content" => $content
        ]);
    }

    // app/Http/Controllers/TaskController.php

    public function index(Request $request)
    {
        $user = $request->user();
        $perPage = $request->ipp ?? 15;
        $search = $request->s ?? '';
        $status = $request->status ?? 'all'; // 'all', 'open', 'in_progress', 'completed', 'favourite'
        $caseId = $request->csid ?? null;

        // Get tasks where user is creator OR assignee
        $taskIds = Task::where('user_id', $user->id)
            ->pluck('id')
            ->merge(
                TaskAssignees::where('user_id', $user->id)->pluck('task_id')
            )
            ->unique();

        $query = Task::with(['assignees:user_id,first_name,last_name', 'attachments', 'legal_case:id,case_number,title'])
            ->whereIn('id', $taskIds);

        // Status filter
        if ($status === 'open') $query->where('status', 0);
        if ($status === 'in_progress') $query->where('status', 1);
        if ($status === 'completed') $query->where('status', 2);
        if ($status === 'favourite') $query->whereJsonContains('faved_by', $user->id);
        if ($caseId && $caseId != -1) $query->where('legal_case_id', $caseId);

        // Search
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }

        $total = $query->count();
        $tasks = $query->latest()->paginate($perPage);

        return Inertia::render('tasks/Index', [
            'tasks' => $tasks,
            'filters' => $request->only(['s', 'status', 'csid']),
            'status_counts' => [
                'all' => Task::whereIn('id', $taskIds)->count(),
                'open' => Task::whereIn('id', $taskIds)->where('status', 0)->count(),
                'in_progress' => Task::whereIn('id', $taskIds)->where('status', 1)->count(),
                'completed' => Task::whereIn('id', $taskIds)->where('status', 2)->count(),
                'favourite' => Task::whereIn('id', $taskIds)->whereJsonContains('faved_by', $user->id)->count(),
            ]
        ]);
    }

    public function status()
    {
        return [
            [
                "value" => 100,
                "name" => "All",
                "color" => "#354863",
                "icon" => "ic:round-clear-all",
            ],
            [
                "value" => 1,
                "name" => "Pending",
                "color" => "#4669FA",
                "icon" => "heroicons:clock",
            ],
            [
                "value" => 2,
                "name" => "In progress",
                "color" => "#FA916B",
                "icon" => "heroicons:arrow-path",
            ],
            [
                "value" => 3,
                "name" => "Completed",
                "color" => "#50C793",
                "icon" => "heroicons:document-check",
            ],

            [
                "value" => 4,
                "name" => "Cancelled",
                "color" => "#ECA0A8",
                "icon" => "heroicons:trash",
            ],
        ];
    }

    //presets are anything that can be attached to a tasks
    public function presets(Request $request)
    {
        $user_id = $request->user()->id;
        $user_role = $request->user()->role_id;
        //fetch cases
        $lawyers = User::select(
            "id",
            "is_external_counsel",
            "first_name",
            "middle_name",
            "last_name",
            "email",
            "calling_code",
            "phone"
        )
            ->where("role_id", 2)->orWhere("is_external_counsel", 1)
            ->get();
        $task_ids = TaskAssignees::select("task_id")
            ->where("user_id", $user_id)
            ->pluck("task_id")
            ->toArray();

        $tags = [];
        $tags_json = Task::whereIn("id", $task_ids)
            ->orWhere("user_id", $user_id)
            ->pluck("tags")
            ->toArray();

        $tags_json = array_filter($tags_json, function ($a) {
            return $a;
        }); //return only arrays with values
        foreach ($tags_json as $tags_) {
            // return $tags_json;

            foreach ($tags_ as $tag_) {
                if (!in_array(strtolower($tag_), $tags)) {
                    array_push($tags, $tag_);
                }
            }
        }

        $cases = LegalCase::select(
            "id",
            "serial_number",
            "title",
            "court_name",
            "title",
            "year",
            "description",
            "date_received",
            "date_of_filing"
        );

        if($user_role != 1) {
            $cases = $cases->where("user_id", $user_id);
        }

        $cases = $cases->orderBy("created_at", "desc")->get();

        $base_file_path = asset("storage") . "/uploads/temp/";

        return [
            "assignees" => [
                "lawyers" => $lawyers,
            ],
            "cases" => $cases,
            "base_file_path" => $base_file_path,
            "filter_status" => $this->status(),
            "tags" => $tags, //tag suggestions, preselected from previous tasg
        ];
    }

    public function store(Request $request)
    {
        $user_id = $request->user()->id;
        $validator = Validator::make($request->all(), [
            "title" => "required|min:2",
            "due_date" => "required",
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

        $task = new Task();
        $task->title = $request->title;
        $task->description = $request->description;
        $task->priority = $request->priority ?? 1;
        $task->status = $request->status ?? 1;
        $task->start_datetime = Carbon::parse($request->start_date);
        $task->end_datetime = Carbon::parse($request->due_date);
        $task->user_id = $user_id;
        $task->legal_case_id = $request->case_id;
        $task->tags = $request->tags;
        $task->save();

        //save attachements
        foreach ($request->upload_files as $file_) {
            $attachment = new TaskAttachments();
            $attachment->task_id = $task->id;
            $attachment->user_id = $user_id;
            $attachment->file_name = $file_["name"];
            $attachment->size = $file_["size"];
            $attachment->extension = $file_["extension"];
            $attachment->type = $file_["type"];
            $attachment->save();
        }

        //save assignees for this taks
        foreach ($request->assignees as $assignee_) {
            $assignee = new TaskAssignees();
            $assignee->task_id = $task->id;
            $assignee->user_id = $assignee_;
            $assignee->save();
        }

        $task = Task::with("attachments", "assignees", "legal_case")
            ->where("id", $task->id)
            ->first();

        return response()->json(
            [
                "error" => false,
                "message" => "Task added successfully",
                "task" => $task,
                "kanban_view" => $request->is_kan_ban ? $this->get_kanban_view($request) : null,
            ],
            200
        );
    }

    public function show(Request $request) {}


    public function change_status(Request $request)
    {
        foreach ($request->items as $value) {
            $task = $value["taskID"];
            $status = $value["newStatus"];

            $task = Task::find($task);
            if ($task) {
                $task->status = $status;
                $task->save();
            }
        }

        return response()->json(
            [
                "error" => false,
                "message" => "Success",
            ],
            200
        );
    }
    public function update_task_status(Request $request, $id, $status)
    {
        $task = Task::find($id);
        if (!$task) {
            return response()->json(
                [
                    "error" => true,
                    "message" => "Task not found",
                ],
                422
            );
        }

        if (!in_array($status, [1, 2, 3, 4])) {
            return response()->json(
                [
                    "error" => true,
                    "message" => "Invalid task status",
                ],
                422
            );
        }
        $task->status = $status;
        $task->save();

        return response()->json(
            [
                "error" => false,
                "message" => "Task status successfully updated",
            ],
            200
        );
    }

    public function update(Request $request)
    {
        $user_id = $request->user()->id;

        $validator = Validator::make($request->all(), [
            "id" => "required|exists:tasks,id",
            "title" => "required|min:2",
            "due_date" => "required",
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
        $task = Task::find($request->id);
        $task->title = $request->title;
        $task->description = $request->description;
        $task->priority = $request->priority;
        $task->legal_case_id = $request->case_id;
        $task->status = $request->status;
        $task->start_datetime = Carbon::parse($request->start_date);
        $task->end_datetime = Carbon::parse($request->due_date);
        $task->tags = $request->tags;
        $task->save();

        TaskAttachments::where("task_id", $task->id)->delete();
        //save attachements
        foreach ($request->upload_files as $file_) {
            $attachment = new TaskAttachments();
            $attachment->task_id = $task->id;
            $attachment->file_name = $file_["name"];
            $attachment->user_id = $user_id;
            $attachment->size = $file_["size"];
            $attachment->extension = $file_["extension"];
            $attachment->type = $file_["type"];
            $attachment->save();
        }

        TaskAssignees::where("task_id", $request->id)->delete();
        //save assignees for this taks
        foreach ($request->assignees as $assignee_) {
            $assignee = new TaskAssignees();
            $assignee->task_id = $task->id;
            $assignee->user_id = $assignee_;
            $assignee->save();
        }

        $task = Task::with("attachments", "assignees", "legal_case")
            ->where("id", $request->id)
            ->first();

        return response()->json(
            [
                "error" => false,
                "message" => "Task added successfully",
                "task" => $task,
                "kanban_view" => $request->is_kan_ban ? $this->get_kanban_view($request) : null,
            ],
            200
        );
    }

    public function change_favourite_status(Request $request, $id)
    {
        $user_id = $request->user()->id;
        $task = Task::find($id);
        if (!$task) {
            return response()->json(
                [
                    "error" => true,
                    "message" => "Task not found",
                ],
                422
            );
        }
        $faved_by = $task->faved_by;
        if ($faved_by) {
            if (in_array($user_id, $faved_by)) {
                $array = array_values(array_diff($faved_by, [$user_id]));
                $task->faved_by = array_unique($array);
            } else {
                array_push($faved_by, $user_id);
                $task->faved_by = array_unique($faved_by);
            }
        } else {
            $task->faved_by = [$user_id];
        }
        $task->save();

        $message = in_array($user_id, $task->faved_by)
            ? "Task added to favourites"
            : "Task removed from favourites";
        return response()->json(
            [
                "error" => false,
                "message" => $message,
                "faved_by" => $task->faved_by,
            ],
            200
        );
    }

    public function destroy(Request $request, $id)
    {
        $task = Task::find($id);
        if (!$task) {
            return response()->json(
                [
                    "error" => true,
                    "message" => "Task not found",
                ],
                422
            );
        }
        $task->forceDelete();
        return response()->json(
            [
                "error" => false,
                "message" => "Task successfully deleted",
            ],
            200
        );
    }
}
