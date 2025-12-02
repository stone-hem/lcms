<?php

namespace App\Http\Controllers;

use App\Helpers\Helper;
use App\Helpers\QueryHelper;
use App\Models\LegalCase\CaseType;
use App\Models\LegalCase\NatureOfClaim;
use App\Models\Party\Party;
use App\Models\Party\PartyType;
use App\Models\CaseActivity;
use App\Models\DocumentTypes;
use App\Models\CaseStage;
use App\Models\EventCategories;
use App\Models\ExpenseTypes;
use App\Models\ExternalFirm;
use App\Models\SiteSetting;
use App\Models\SMSProvider;
use App\Models\SMTPConfigTypes;
use App\Models\SMTPSettings;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;


class SettingsController extends Controller
{
    public function smtp_settings_prefetch()
    {
        return [
            "smtp_settings" => SMTPSettings::with("type")->get(),
            "config_types" => SMTPConfigTypes::where("is_active", 1)->get(),
        ];
    }

    public function smtp_settings()
    {
        return SMTPSettings::first();
    }

    public function sms_providers()
    {
        return SMSProvider::all();
    }

    public function getIdentifierTitleForProvider($provider)
    {
        if ($provider == "Twilio") {
            return "Account SID";
        }
        if ($provider == "Sematime") {
            return "User ID";
        }
        if ($provider == 'Africa\'s Talking') {
            return "Username";
        }
        return "";
    }

    public function getAuthorizationForProvider($provider)
    {
        if ($provider == "Twilio") {
            return "Auth token";
        }
        if ($provider == "Sematime") {
            return "Api key";
        }
        if ($provider == 'Africa\'s Talking') {
            return "Api key";
        }
        return "";
    }

    public function update_sms_providers(Request $request)
    {
        $this->validate($request, [
            "providers" => "required|array",
        ]);
        $active_count = 0;

        $active_sms_provider = null;
        foreach ($request->providers as $provider) {
            if ($provider["active"]) {
                $active_sms_provider = $provider;
                $active_count++;
            }
        }
        if ($active_count == 0) {
            return response()->json(
                [
                    "error" => true,
                    "message" => "You must set atleast one active SMS provider",
                ],
                422
            );
        }
        if ($active_count > 1) {
            return response()->json(
                [
                    "error" => true,
                    "message" => "You can only set one active SMS provider",
                ],
                422
            );
        }

        $identifier_title = $this->getIdentifierTitleForProvider(
            $active_sms_provider["provider"]
        );
        $auth = $this->getAuthorizationForProvider(
            $active_sms_provider["provider"]
        );

        if (!$active_sms_provider["identifier"]) {
            return response()->json(
                [
                    "error" => true,
                    "message" =>
                    "You must set " .
                        $identifier_title .
                        " for provider " .
                        $active_sms_provider["provider"],
                ],
                422
            );
        }
        if (!$active_sms_provider["auth_token"]) {
            return response()->json(
                [
                    "error" => true,
                    "message" =>
                    "You must set " .
                        $auth .
                        " for provider " .
                        $active_sms_provider["provider"],
                ],
                422
            );
        }

        foreach ($request->providers as $provider) {
            $sms_provider = SMSProvider::find($provider["id"]);
            $sms_provider->identifier = $provider["identifier"]
                ? $provider["identifier"]
                : "";
            $sms_provider->auth_token = $provider["auth_token"]
                ? $provider["auth_token"]
                : "";
            $sms_provider->active = $provider["active"];
            $sms_provider->save();
        }
        return response()->json([
            "error" => false,
            "message" => "SMS providers updated successfully ",
        ]);
    }

    public function update_site_settings(Request $request)
    {
        $item = SiteSetting::first();
        if (!$item) {
            $item = new SiteSetting();
            //return response()->json(["error" => true, "message" => "Site settings not found"], 404);
        }
        $file_path = $request->file_path;
        $site_logo_path = $request->site_logo_path;

        $item->name = $request->business_name;
        $item->tagline = $request->business_tagline;
        $item->app_store_link = $request->app_store_link;
        $item->google_play_store_link = $request->google_play_store_link;

        if ($request->app_store_logo_path) {
            $item->app_store_logo_path = $request->app_store_logo_path;
        }
        if ($request->google_play_store_logo_path) {
            $item->google_play_store_logo_path =
                $request->google_play_store_logo_path;
        }
        $item->site_logo_path = $site_logo_path;

        $item->save();

        return response()->json([
            "error" => false,
            "message" => "Site settings updated",
        ]);
    }

    public function update_smtp_settings(Request $request)
    {
        $this->validate($request, [
            "smtp_configs" => "required|array",
            "smtp_configs.*.id" => "required",
            "smtp_configs.*.smtp_host" => "required",
            "smtp_configs.*.smtp_port" => "required|numeric",
            "smtp_configs.*.smtp_encryption" => "required",
            "smtp_configs.*.smtp_username" => "required",
            "smtp_configs.*.smtp_password" => "required",
            "smtp_configs.*.mail_from" => "required|email",
            "smtp_configs.*.reply_to" => "required|email",
        ]);

        $ids = [];
        foreach ($request->smtp_configs as $config) {
            array_push($ids, $config["id"]);
        }

        SMTPSettings::whereNotIn("id", $ids)->delete();

        foreach ($request->smtp_configs as $config) {
            $id = $config["id"];
            $smtp_config = SMTPSettings::find($id);
            if (!$smtp_config) {
                $smtp_config = new SMTPSettings();
            }
            $smtp_config->smtp_host = $config["smtp_host"];
            $smtp_config->smtp_port = $config["smtp_port"];
            $smtp_config->type_id = $config["type"]["id"];
            $smtp_config->smtp_encryption = $config["smtp_encryption"];
            $smtp_config->smtp_username = $config["smtp_username"];
            $smtp_config->smtp_password = $config["smtp_password"];
            $smtp_config->mail_from = $config["mail_from"];
            $smtp_config->reply_to = $config["reply_to"];
            $smtp_config->save();
        }
        return response()->json([
            "error" => false,
            "message" => "SMTP settings  updated",
        ]);
    }

    public function case_types(Request $request)
    {
        $items_per_page = $request->ipp ?? 10; //default items per page is 10
        $search_query = $request->s ?? "";
        $page = $request->p ?? 0;
        $sort_by = $request->sort_by ?? "created_at";
        $order_by = $request->order_by ?? "asc";
        $start_date = $request->sd ?? "";
        $end_date = $request->ed ?? "";

        $items = CaseType::withTrashed();
        if ($search_query) {
            $title_where_array = QueryHelper::get_where_clause_with_match_mode(
                "contains",
                $search_query,
                "case_types.name"
            );
            $description_where_array = QueryHelper::get_where_clause_with_match_mode(
                "contains",
                $search_query,
                "case_types.description"
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

    public function store_case_type(Request $request)
    {

        $this->validate($request, [
            "name" => "required|min:2|unique:case_types,name"
        ]);

        $caseType = new CaseType();
        $caseType->name = $request->input("name");
        $caseType->description = $request->input("description");
        $caseType->save();

        return response()->json(
            [
                "error" => false,
                "message" => "Case type added successfully",
                "item" => $caseType,
            ],
            200
        );
    }

    public function update_case_type(Request $request, $id)
    {

        $this->validate($request, [
            "name" => "required|min:2|unique:case_types,name," . $id,
            "id" => "required",
        ]);



        $caseType = CaseType::find($request->id);
        $caseType->name = $request->input("name");
        $caseType->description = $request->input("description");
        $caseType->save();

        return response()->json(
            [
                "error" => false,
                "message" => "Case type updated successfully",
                "item" => $caseType,
            ],
            200
        );
    }

    public function activate_casetype($id)
    {
        $item = CaseType::withTrashed()->find($id);
        if ($item) {
            $item->restore();
        }
        return response()->json([
            "error" => false,
            "message" => "Case type activated successfully",
        ]);
    }
    public function deactivate_casetype($id)
    {
        $item = CaseType::withTrashed()->find($id);
        if ($item) {
            $item->delete();
        }
        return response()->json([
            "error" => false,
            "message" => "Case type deactivated successfully",
        ]);
    }
    public function delete_casetype($id)
    {
        $item = CaseType::withTrashed()->find($id);
        if ($item) {
            $item->forceDelete();
        }
        return response()->json([
            "error" => false,
            "message" => "Case type deleted successfully",
        ]);
    }

    public function nature_of_claims(Request $request)
    {
        $items_per_page = $request->ipp ?? 10; //default items per page is 10
        $search_query = $request->s ?? "";
        $page = $request->p ?? 0;
        $sort_by = $request->sort_by ?? "created_at";
        $order_by = $request->order_by ?? "asc";
        $start_date = $request->sd ?? "";
        $end_date = $request->ed ?? "";

        $items = NatureOfClaim::withTrashed();
        if ($search_query) {
            $title_where_array = QueryHelper::get_where_clause_with_match_mode(
                "contains",
                $search_query,
                "nature_of_claims.claim"
            );
            $description_where_array = QueryHelper::get_where_clause_with_match_mode(
                "contains",
                $search_query,
                "nature_of_claims.description"
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

    public function store_nature_of_claim(Request $request)
    {
        $this->validate($request, [
            "claim" => "required|min:2|unique:nature_of_claims"
        ]);

        $item = new NatureOfClaim();
        $item->claim = $request->input("claim");
        $item->description = $request->input("description");
        $item->save();

        return response()->json(
            [
                "error" => false,
                "message" => "Nature of claim  added successfully",
                "item" => $item,
            ],
            200
        );
    }

    public function update_nature_of_claim(Request $request, $id)
    {
        $this->validate($request, [
            "claim" => "required|min:2|unique:nature_of_claims,claim," . $id,
            "id" => "required",
        ]);

        $item = NatureOfClaim::find($id);
        $item->claim = $request->input("claim");
        $item->description = $request->input("description");
        $item->save();

        return response()->json(
            [
                "error" => false,
                "message" => "Nature of claim updated successfully",
                "item" => $item,
            ],
            200
        );
    }

    public function activate_nature_of_claim($id)
    {
        $item = NatureOfClaim::withTrashed()->find($id);
        if ($item) {
            $item->restore();
        }
        return response()->json([
            "error" => false,
            "message" => "Nature of claim activated successfully",
        ]);
    }
    public function deactivate_nature_of_claim($id)
    {
        $item = NatureOfClaim::withTrashed()->find($id);
        if ($item) {
            $item->delete();
        }
        return response()->json([
            "error" => false,
            "message" => "Nature of claim deactivated successfully",
        ]);
    }
    public function delete_nature_of_claim($id)
    {
        $item = NatureOfClaim::withTrashed()->find($id);
        if ($item) {
            $item->forceDelete();
        }
        return response()->json([
            "error" => false,
            "message" => "Nature off claim deleted successfully",
        ]);
    }

    public function party_types(Request $request)
    {
        $items_per_page = $request->ipp ?? 10; //default items per page is 10
        $search_query = $request->s ?? "";
        $page = $request->p ?? 0;
        $sort_by = $request->sort_by ?? "created_at";
        $order_by = $request->order_by ?? "asc";
        $start_date = $request->sd ?? "";
        $end_date = $request->ed ?? "";

        $items = PartyType::withTrashed();
        if ($search_query) {
            $title_where_array = QueryHelper::get_where_clause_with_match_mode(
                "contains",
                $search_query,
                "party_types.name"
            );
            $description_where_array = QueryHelper::get_where_clause_with_match_mode(
                "contains",
                $search_query,
                "party_types.description"
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

    public function store_party_types(Request $request)
    {
        $this->validate($request, [
            "name" => "required|min:2|unique:party_types,name"
        ]);

        $item = new PartyType();
        $item->name = $request->input("name");
        $item->description = $request->input("description");
        $item->save();

        return response()->json(
            [
                "error" => false,
                "message" => "Party type added successfully",
                "item" => $item,
            ],
            200
        );
    }

    public function update_party_type(Request $request, $id)
    {
        $this->validate($request, [
            "name" => "required|min:2|unique:party_types,name," . $id,
            "id" => "required",
        ]);

        $item = PartyType::find($id);
        $item->name = $request->input("name");
        $item->description = $request->input("description");
        $item->save();

        return response()->json(
            [
                "error" => false,
                "message" => "Party type updated successfully",
                "item" => $item,
            ],
            200
        );
    }

    public function activate_party_type($id)
    {
        $item = PartyType::withTrashed()->find($id);
        if ($item) {
            $item->restore();
        }
        return response()->json([
            "error" => false,
            "message" => "Party type activated successfully",
        ]);
    }
    public function deactivate_party_type($id)
    {
        $item = PartyType::withTrashed()->find($id);
        if ($item) {
            $item->delete();
        }
        return response()->json([
            "error" => false,
            "message" => "Party type deactivated successfully",
        ]);
    }
    public function delete_party_type($id)
    {
        $item = PartyType::withTrashed()->find($id);
        if ($item) {
            $item->forceDelete();
        }
        return response()->json([
            "error" => false,
            "message" => "Party type deleted successfully",
        ]);
    }
    public function case_activities(Request $request)
    {
        $items_per_page = $request->ipp ?? 10; //default items per page is 10
        $search_query = $request->s ?? "";
        $page = $request->p ?? 0;
        $sort_by = $request->sort_by ?? "created_at";
        $order_by = $request->order_by ?? "asc";
        $start_date = $request->sd ?? "";
        $end_date = $request->ed ?? "";

        $items = CaseActivity::with('after')->withTrashed();
        if ($search_query) {
            $title_where_array = QueryHelper::get_where_clause_with_match_mode(
                "contains",
                $search_query,
                "case_activities.name"
            );
            $description_where_array = QueryHelper::get_where_clause_with_match_mode(
                "contains",
                $search_query,
                "case_activities.description"
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

    public function store_case_activity(Request $request)
    {
        $this->validate($request, [
            "name" => "required|min:2|unique:case_activities,name"
        ]);

        $item = new CaseActivity();
        $item->name = $request->name;
        $item->description = $request->description;
        $item->after = $request->after;
        $item->fields = $request->fields;
        $item->save();


        return response()->json(
            [
                "error" => false,
                "message" => "Case activity added successfully",
                "item" => CaseActivity::with('after')->find($item->id),
            ],
            200
        );
    }

    public function update_case_activity(Request $request, $id)
    {
        $this->validate($request, [
            "name" => "required|min:2|unique:case_activities,name," . $id,
            "id" => "required",
        ]);

        $item = CaseActivity::find($id);
        $item->name = $request->name;
        $item->fields = $request->fields;
        $item->after = $request->after;
        $item->description = $request->description;
        $item->save();

        return response()->json(
            [
                "error" => false,
                "message" => "Case activity updated successfully",
                "item" => CaseActivity::with('after')->find($item->id),
            ],
            200
        );
    }

    public function activate_case_activity($id)
    {
        $item = CaseActivity::withTrashed()->find($id);
        if ($item) {
            $item->restore();
        }
        return response()->json([
            "error" => false,
            "message" => "Nature of claim activated successfully",
        ]);
    }
    public function deactivate_case_activity($id)
    {
        $item = CaseActivity::withTrashed()->find($id);
        if ($item) {
            $item->delete();
        }
        return response()->json([
            "error" => false,
            "message" => "Case activity deactivated successfully",
        ]);
    }
    public function delete_case_activity($id)
    {
        $item = CaseActivity::withTrashed()->find($id);
        if ($item) {
            $item->forceDelete();
        }
        return response()->json([
            "error" => false,
            "message" => "Case activity deleted successfully",
        ]);
    }




    public function document_types(Request $request)
    {
        $items_per_page = $request->ipp ?? 10; //default items per page is 10
        $search_query = $request->s ?? "";
        $page = $request->p ?? 0;
        $sort_by = $request->sort_by ?? "created_at";
        $order_by = $request->order_by ?? "asc";
        $start_date = $request->sd ?? "";
        $end_date = $request->ed ?? "";

        $items = DocumentTypes::withTrashed();
        if ($search_query) {
            $title_where_array = QueryHelper::get_where_clause_with_match_mode(
                "contains",
                $search_query,
                "document_types.name"
            );
            $abbrev_where_array = QueryHelper::get_where_clause_with_match_mode(
                "contains",
                $search_query,
                "document_types.abbreviation"
            );
            $description_where_array = QueryHelper::get_where_clause_with_match_mode(
                "contains",
                $search_query,
                "document_types.description"
            );
            $items = $items->whereNested(function ($query) use (
                $description_where_array,
                $title_where_array,
                $abbrev_where_array
            ) {
                $query
                    ->orWhere([$description_where_array])
                    ->orWhere([$abbrev_where_array])
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

    public function store_document_type(Request $request)
    {
        $this->validate($request, [
            "name" => "required|min:2|unique:document_types",
            "abbreviation" => "unique:document_types",
        ]);

        $item = new DocumentTypes();
        $item->name = $request->name;
        $item->abbreviation = $request->abbreviation;
        $item->description = $request->description;
        $item->save();


        return response()->json(
            [
                "error" => false,
                "message" => "Document type added successfully",
                "item" => $item,
            ],
            200
        );
    }

    public function update_document_type(Request $request, $id)
    {
        $this->validate($request, [
            "name" => "required|min:2|unique:document_types,name," . $id,
            "abbreviation" => "unique:document_types,abbreviation," . $id,
            "id" => "required",
        ]);

        $item = DocumentTypes::find($id);
        $item->name = $request->name;
        $item->description = $request->description;
        $item->abbreviation = $request->abbreviation;
        $item->save();

        return response()->json(
            [
                "error" => false,
                "message" => "Document type updated successfully",
                "item" => $item,
            ],
            200
        );
    }

    public function activate_document_type($id)
    {
        $item = DocumentTypes::withTrashed()->find($id);
        if ($item) {
            $item->restore();
        }
        return response()->json([
            "error" => false,
            "message" => "Document type activated successfully",
        ]);
    }
    public function deactivate_document_type($id)
    {
        $item = DocumentTypes::withTrashed()->find($id);
        if ($item) {
            $item->delete();
        }
        return response()->json([
            "error" => false,
            "message" => "Document type deactivated successfully",
        ]);
    }
    public function delete_document_type($id)
    {
        $item = DocumentTypes::withTrashed()->find($id);
        if ($item) {
            $item->forceDelete();
        }
        return response()->json([
            "error" => false,
            "message" => "Document type deleted successfully",
        ]);
    }





    public function case_stages(Request $request)
    {
        $items_per_page = $request->ipp ?? 10; //default items per page is 10
        $search_query = $request->s ?? "";
        $page = $request->p ?? 0;
        $sort_by = $request->sort_by ?? "created_at";
        $order_by = $request->order_by ?? "asc";
        $start_date = $request->sd ?? "";
        $end_date = $request->ed ?? "";

        $items = CaseStage::with('after')->withTrashed();
        if ($search_query) {
            $title_where_array = QueryHelper::get_where_clause_with_match_mode(
                "contains",
                $search_query,
                "case_stages.name"
            );
            $description_where_array = QueryHelper::get_where_clause_with_match_mode(
                "contains",
                $search_query,
                "case_stages.description"
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

    public function store_case_stage(Request $request)
    {
        $this->validate($request, [
            "name" => "required|min:2|unique:case_stages",
        ]);

        $item = new CaseStage();
        $item->name = $request->name;
        $item->description = $request->description;
        $item->order_after = $request->order_after;
        $item->save();


        return response()->json(
            [
                "error" => false,
                "message" => "Case stage added successfully",
                "item" => CaseStage::with('after')->find($item->id),
            ],
            200
        );
    }
    public function update_case_stage(Request $request, $id)
    {
        $this->validate($request, [
            "name" => "required|min:2|unique:case_stages,name," . $id,
            "id" => "required",
        ]);
        $item = CaseStage::find($id);
        $item->name = $request->name;
        $item->order_after = $request->order_after;
        $item->description = $request->description;
        $item->save();

        return response()->json(
            [
                "error" => false,
                "message" => "Case stage updated successfully",
                "item" => CaseStage::with('after')->find($item->id),
            ],
            200
        );
    }
    public function activate_case_stage($id)
    {
        $item = CaseStage::withTrashed()->find($id);
        if ($item) {
            $item->restore();
        }
        return response()->json([
            "error" => false,
            "message" => "Case stage activated successfully",
        ]);
    }
    public function deactivate_case_stage($id)
    {
        $item = CaseStage::withTrashed()->find($id);
        if ($item) {
            $item->delete();
        }
        return response()->json([
            "error" => false,
            "message" => "Case stage deactivated successfully",
        ]);
    }
    public function delete_case_stage($id)
    {
        $item = CaseStage::withTrashed()->find($id);
        if ($item) {
            $item->forceDelete();
        }
        return response()->json([
            "error" => false,
            "message" => "Case stage deleted successfully",
        ]);
    }




  




    public function event_categories(Request $request)
    {
        $items_per_page = $request->ipp ?? 10; //default items per page is 10
        $search_query = $request->s ?? "";
        $page = $request->p ?? 0;
        $sort_by = $request->sort_by ?? "created_at";
        $order_by = $request->order_by ?? "asc";
        $start_date = $request->sd ?? "";
        $end_date = $request->ed ?? "";

        $items = EventCategories::withTrashed();
        if ($search_query) {
            $title_where_array = QueryHelper::get_where_clause_with_match_mode(
                "contains",
                $search_query,
                "event_categories.category"
            );
            $description_where_array = QueryHelper::get_where_clause_with_match_mode(
                "contains",
                $search_query,
                "event_categories.description"
            );
            $items = $items->whereNested(function ($query) use (
                $description_where_array,
                $title_where_array,
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

    public function store_event_category(Request $request)
    {
        $this->validate($request, [
            "category" => "required|min:2|unique:event_categories",
        ]);

        $item = new EventCategories();
        $item->category = $request->category;
        $item->description = $request->description;
        $item->save();


        return response()->json(
            [
                "error" => false,
                "message" => "Event category added successfully",
                "item" => $item,
            ],
            200
        );
    }

    public function update_event_category(Request $request, $id)
    {
        $this->validate($request, [
            "category" => "required|min:2|unique:event_categories,category," . $id,
            "id" => "required",
        ]);


        $item = EventCategories::find($id);
        $item->category = $request->category;
        $item->description = $request->description;
        $item->save();

        return response()->json(
            [
                "error" => false,
                "message" => "Event category updated successfully",
                "item" => $item,
            ],
            200
        );
    }

    public function activate_event_category($id)
    {
        $item = EventCategories::withTrashed()->find($id);
        if ($item) {
            $item->restore();
        }
        return response()->json([
            "error" => false,
            "message" => "Event category activated successfully",
        ]);
    }
    public function deactivate_event_category($id)
    {
        $item = EventCategories::withTrashed()->find($id);
        if ($item) {
            $item->delete();
        }
        return response()->json([
            "error" => false,
            "message" => "Event category deactivated successfully",
        ]);
    }
    public function delete_event_category($id)
    {
        $item = EventCategories::withTrashed()->find($id);
        if ($item) {
            $item->forceDelete();
        }
        return response()->json([
            "error" => false,
            "message" => "Event category deleted successfully",
        ]);
    }








    public function expense_types(Request $request)
    {
        $items_per_page = $request->ipp ?? 10; //default items per page is 10
        $search_query = $request->s ?? "";
        $page = $request->p ?? 0;
        $sort_by = $request->sort_by ?? "created_at";
        $order_by = $request->order_by ?? "asc";
        $start_date = $request->sd ?? "";
        $end_date = $request->ed ?? "";

        $items = ExpenseTypes::withTrashed();
        if ($search_query) {
            $title_where_array = QueryHelper::get_where_clause_with_match_mode(
                "contains",
                $search_query,
                "expense_types.type"
            );
            $description_where_array = QueryHelper::get_where_clause_with_match_mode(
                "contains",
                $search_query,
                "expense_types.description"
            );
            $items = $items->whereNested(function ($query) use (
                $description_where_array,
                $title_where_array,
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

    public function store_expense_type(Request $request)
    {

        $this->validate($request, [
            "type" => "required|min:2|unique:expense_types"
        ]);

        $item = new ExpenseTypes();
        $item->type = $request->type;
        $item->description = $request->description;
        $item->save();


        return response()->json(
            [
                "error" => false,
                "message" => "Expense type added successfully",
                "item" => $item,
            ],
            200
        );
    }

    public function update_expense_type(Request $request, $id)
    {

        $this->validate($request, [
            "type" => "required|min:2|unique:expense_types,type," . $id,
            "id" => "required",
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

        $item = ExpenseTypes::find($id);
        $item->type = $request->type;
        $item->description = $request->description;
        $item->save();

        return response()->json(
            [
                "error" => false,
                "message" => "Expense type updated successfully",
                "item" => $item,
            ],
            200
        );
    }

    public function activate_expense_type($id)
    {
        $item = ExpenseTypes::withTrashed()->find($id);
        if ($item) {
            $item->restore();
        }
        return response()->json([
            "error" => false,
            "message" => "Expense type activated successfully",
        ]);
    }
    public function deactivate_expense_type($id)
    {
        $item = ExpenseTypes::withTrashed()->find($id);
        if ($item) {
            $item->delete();
        }
        return response()->json([
            "error" => false,
            "message" => "Expense type deactivated successfully",
        ]);
    }
    public function delete_expense_type($id)
    {
        $item = ExpenseTypes::withTrashed()->find($id);
        if ($item) {
            $item->forceDelete();
        }
        return response()->json([
            "error" => false,
            "message" => "Expense type deleted successfully",
        ]);
    }
}
