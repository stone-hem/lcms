<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\LegalCase\LegalCase;
use App\Models\TaskAssignees;
use App\Models\TaskAttachments;
use App\Models\User;
use Auth;
use Illuminate\Http\Request;
use Inertia\Inertia;

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

        $query = Task::with(['assignees', 'attachments', 'legal_case:id,case_number,title'])
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
            'cases' => LegalCase::all(),
            'lawyers' => User::all(),
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

    public function store(Request $request)
        {
            $request->validate([
                'title'       => 'required|string|min:2',
                'end_datetime'    => 'required|date',
                'priority'    => 'sometimes|in:0,1,2,3',
                'description' => 'required|string',
                'status'      => 'sometimes|in:0,1,2',
                'legal_case_id' => 'nullable|exists:legal_cases,id',
                'assignee_ids'  => 'sometimes|array',
                'assignee_ids.*'=> 'exists:users,id',
                'attachments.*' => 'file|mimes:jpg,jpeg,png,pdf,doc,docx,xls,xlsx|max:10240',
            ]);

            $task = Task::create([
                'title'         => $request->title,
                'description'   => $request->description,
                'priority'      => $request->priority ?? 1,
                'status'        => $request->status ?? 0,
                'end_datetime'      => $request->end_datetime,
                'start_datetime' => $request->start_datetime ?? now(),
                'legal_case_id' => $request->legal_case_id,
                'user_id'       => Auth::id(),
            ]);
            

            // Assignees
            if ($request->filled('assignee_ids')) {
                foreach ($request->assignee_ids as $userId) {
                    TaskAssignees::create([
                        'task_id' => $task->id,
                        'user_id' => $userId,
                    ]);
                }
            }

            // Attachments
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $path = $file->store('task-attachments', 'private'); // or 'public'

                    TaskAttachments::create([
                        'task_id'   => $task->id,
                        'user_id'   => Auth::id(),
                        'file_name' => $path,
                        'size'      => $file->getSize(),
                        'type' => $file->getMimeType(),
                        'extension' => $file->getClientOriginalExtension(),
                        'description' => $request->input('description')
                    ]);
                    
                }
            }

            return redirect()->back()->with('success', 'Task created successfully.');
        }

        public function update(Request $request, Task $task)
        {
            $request->validate([
                'title' => 'required|string|min:2',
                'end_datetime' => 'required|date', // Changed from 'due_date' to match store
                'priority' => 'sometimes|in:0,1,2,3',
                'description' => 'nullable|string',
                'status' => 'sometimes|in:0,1,2',
                'legal_case_id' => 'nullable|exists:legal_cases,id',
                'assignee_ids' => 'sometimes|array',
                'assignee_ids.*' => 'exists:users,id',
                'attachments.*' => 'file|mimes:jpg,jpeg,png,pdf,doc,docx,xls,xlsx|max:10240',
            ]);
        
            $task->update([
                'title' => $request->title, // Removed strip_tags() to match store
                'description' => $request->description,
                'priority' => $request->priority ?? $task->priority,
                'status' => $request->status ?? $task->status,
                'start_datetime' => $request->start_datetime ?? now(),
                'end_datetime' => $request->end_datetime,
                'legal_case_id' => $request->legal_case_id ?? null,
            ]);
        
            // Sync assignees (delete old, insert new)
            TaskAssignees::where('task_id', $task->id)->delete();
            if ($request->filled('assignee_ids')) {
                foreach ($request->assignee_ids as $userId) {
                    TaskAssignees::create([
                        'task_id' => $task->id,
                        'user_id' => $userId,
                    ]);
                }
            }
        
            // Attachments - Add new ones only (don't delete existing)
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $path = $file->store('task-attachments', 'private'); // Match store method
                    
                    TaskAttachments::create([
                        'task_id' => $task->id,
                        'user_id' => Auth::id(),
                        'file_name' => $path, // Match store field name
                        'size' => $file->getSize(),
                        'type' => $file->getMimeType(), // Match store field name
                        'extension' => $file->getClientOriginalExtension(),
                        'description' => $request->input('description') // Match store
                    ]);
                }
            }
        
            return redirect()->back()->with('success', 'Task updated successfully.');
        }


        public function change_favourite_status(Request $request, $id)
        {
            $task = Task::findOrFail($id);
            $user_id = $request->user()->id;

            $faved_by = $task->faved_by ?? [];

            if (in_array($user_id, $faved_by)) {
                $faved_by = array_diff($faved_by, [$user_id]);
            } else {
                $faved_by[] = $user_id;
            }

            $task->faved_by = array_values($faved_by);
            $task->save();

            return back()->with('success', in_array($user_id, $faved_by)
                ? 'Task added to favorites'
                : 'Task removed from favorites'
            );
        }

        public function update_task_status(Request $request, $id, $status)
        {
            $task = Task::findOrFail($id);

            if (!in_array($status, [0, 1, 2])) {
                return back()->with('error', 'Invalid status');
            }

            $task->status = $status;
            $task->save();

            return back()->with('success', 'Task status updated');
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
        return redirect()->back()->with('success', 'Task successfully deleted');
    }
}
