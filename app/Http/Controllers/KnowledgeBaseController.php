<?php

namespace App\Http\Controllers;

use App\Helpers\QueryHelper;
use App\Models\DocumentTemplate;
use App\Models\DocumentTypes;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class KnowledgeBaseController extends Controller
{


    public function document_templates(Request $request)
    {
        $items_per_page = $request->ipp ?? 10; //default items per page is 10
        $search_query = $request->s ?? "";
        $page = $request->p ?? 0;
        $sort_by = $request->sort_by ?? "created_at";
        $order_by = $request->order_by ?? "asc";
        $start_date = $request->sd ?? "";
        $end_date = $request->ed ?? "";

        $items = DocumentTemplate::with("document_type")->withTrashed();
        if ($search_query) {
            $title_where_array = QueryHelper::get_where_clause_with_match_mode(
                "contains",
                $search_query,
                "document_templates.title"
            );
            $description_where_array = QueryHelper::get_where_clause_with_match_mode(
                "contains",
                $search_query,
                "document_templates.description"
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
        if ($sort_by) {
            $items = $items->orderBy($sort_by, $order_by);
        }

        $items = $items->get();

        return [
            "total_count" => $item_count,
            "items" => $items,
            // "status_" => $status,
        ];
    }

    public function presets(Request $request)
    {
        $document_types = DocumentTypes::all();
        $base_file_path = asset('storage') . '/uploads/temp/';
        return [
            "base_file_path" => $base_file_path,
            "document_types" => $document_types,
        ];
    }



    public function store_document_template(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "title" => "required|min:2|unique:document_templates",
            "type" => "required",
            "upload_files" => "required"
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

        $item = new DocumentTemplate();
        $item->title = $request->input("title");
        $item->description = $request->input("description");
        $item->document_type_id = $request->type;
        $item->templates = $request->upload_files;
        $item->save();

        return response()->json(
            [
                "error" => false,
                "message" => "Document template added successfully",
                "item" => DocumentTemplate::with("document_type")->find($item->id),
            ],
            200
        );
    }

    public function update_document_template(Request $request, $id)
    {

        $this->validate($request, [
            "title" => "required|min:2|unique:document_templates,title," . $id,
            "upload_files" => "required",
            "type" => "required",

            "id" => "required",
        ]);



        $item = DocumentTemplate::withTrashed()->find($request->id);
        $item->title = $request->input("title");
        $item->description = $request->input("description");
        $item->document_type_id = $request->type;
        $item->templates = $request->upload_files;
        $item->save();

        return response()->json(
            [
                "error" => false,
                "message" => "Document template updated successfully",
                "item" =>  DocumentTemplate::with("document_type")->find($item->id),
            ],
            200
        );
    }

    public function activate_document_template($id)
    {
        $item = DocumentTemplate::withTrashed()->find($id);
        if ($item) {
            $item->restore();
        }
        return response()->json([
            "error" => false,
            "message" => "Document template activated successfully",
        ]);
    }
    public function deactivate_document_template($id)
    {
        $item = DocumentTemplate::withTrashed()->find($id);
        if ($item) {
            $item->delete();
        }
        return response()->json([
            "error" => false,
            "message" => "Document template deactivated successfully",
        ]);
    }
    public function delete_document_template($id)
    {
        $item = DocumentTemplate::withTrashed()->find($id);
        if ($item) {
            $item->forceDelete();
        }
        return response()->json([
            "error" => false,
            "message" => "Document template deleted successfully",
        ]);
    }
}
