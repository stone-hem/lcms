<?php

namespace App\Http\Controllers;

use App\Helpers\QueryHelper;
use App\Models\Notifications;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class NotificationController extends Controller
{


    public function mark_as_read(Request $request)
    {
        $notification_ids = $request->notification_ids;
        Notifications::whereIn("id", $notification_ids)->update(["read_at" => Carbon::now()]);
        return response(
            [
                "error" => false,
                "message" => "Success"
            ],
            200
        );
    }

    public function get_unread_notification_count(Request $request)
    {
        $auth = Auth::user();
        return Notifications::where("user_id", $auth->id)->where("read_at", null)->count();
    }
    public function my_notifications(Request $request)
    {
        $auth = Auth::user();
        $items_per_page = $request->ipp ?? 10; //default items per page is 10
        $search_query = $request->s ?? "";
        $page = $request->p ?? 0;
        $sort_by = $request->sort_by ?? "created_at";
        $order_by = $request->order_by ?? "asc";
        $start_date = $request->sd ?? "";
        $end_date = $request->ed ?? "";


        $items = Notifications::with("event", "task")->where("user_id", $auth->id);

        if ($search_query) {
            $message_where_array = QueryHelper::get_where_clause_with_match_mode(
                "contains",
                $search_query,
                "notifications.message"
            );

            $type_where_array = QueryHelper::get_where_clause_with_match_mode(
                "contains",
                $search_query,
                "notifications.type"
            );
            $items = $items->whereNested(function ($query) use (
                $type_where_array,
                $message_where_array,
            ) {
                $query
                    ->orWhere([$type_where_array])
                    ->orWhere([$message_where_array]);
            });
        }
        $item_count = $items->count();
        $items = $items->skip($page)->take($items_per_page);
        if ($sort_by) {
            $items = $items->orderBy($sort_by, $order_by);
        }

        $items = $items->get();

        foreach ($items as $item) {
            if ($item->event) {
                foreach ($item->event->participants as $participant) {
                    if ($participant->lawyer_id) {
                        $lawyer = User::where("id", $participant->lawyer_id)->selectRaw("id,calling_code,phone,email,CONCAT(first_name, ' ', IFNULL(middle_name, ''), ' ', last_name) AS gen_name")->first();
                        $participant->lawyer = $lawyer;
                    }
                }
            }
        }

        $base_file_path = asset('storage') . '/uploads/temp/';

        return [
            "total_count" => $item_count,
            "base_file_path" => $base_file_path,
            "items" => $items,
            // "status_" => $status,
        ];
    }
}
