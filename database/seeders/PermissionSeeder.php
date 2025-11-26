<?php

namespace Database\Seeders;

use App\Models\Permission;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            ["module" => "Reports", "actions" => ["view-analytics-reports", "view-contingency-fee-report", "view-case-reports", "view-cases-by-lawyer"]],

            ["module" => "Roles", "actions" => ["create-role", "view-role", "delete-role", "restore-role", "edit-role"]],
            ["module" => "User", "actions" => ["create-user", "view-user", "delete-user", "deactivate-user", "restore-user", "edit-user"]],
            ["module" => "Permissions", "actions" => ["assign-permissions-to-role"]],

            ["module" => "Tasks", "actions" => ["view-task", "update-task", "create-task", "add-task", "delete-task", "deactivate-task", "restore-task"]],
            ["module" => "External counsel", "actions" => ["view-external-counsel", "update-external-counsel", "create-external-counsel", "delete-external-counsel", "deactivate-external-counsel", "restore-external-counsel"]],
            ["module" => "Internal counsel", "actions" => ["view-internal-counsel", "update-internal-counsel", "create-internal-counsel", "delete-internal-counsel", "deactivate-internal-counsel", "restore-internal-counsel"]],

            ["module" => "Calendar", "actions" => ["view-master-calender", "view-my-calender"]],
            ["module" => "Case", "actions" => ["add-case", "view-case", "edit-case", "delete-case", "restore-case", "deactivate-case", "add-case-note", "edit-case-note", "view-case-note", "delete-case-note"]],

            ["module" => "Knowledge base", "actions" => ["view-document-templates", "add-document-templates", "delete-document-templates", "deactivate-document-templates", "restore-document-templates", "edit-document-templates"]],

            ["module" => "Files", "actions" => ["view-files"]],


            ["module" => "Settings", "sub_module" => "Roles and permissions", "actions" => [
                "view-roles",
                "add-roles",
                "edit-roes",
                "delete-roles",
                "assign-permissions",
            ]],
            ["module" => "Settings", "sub_module" => "Case types", "actions" => [
                "view-case-types",
                "add-case-types",
                "edit-case-types",
                "delete-case-types",
                "deactivate-case-types",
                "restore-case-types",
            ]],
            ["module" => "Settings", "sub_module" => "Case activities", "actions" => [
                "view-case-activities",
                "add-case-activities",
                "edit-case-activities",
                "delete-case-activities",
                "deactivate-case-activities",
                "restore-case-activities",
            ]],
            ["module" => "Settings", "sub_module" => "Nature of claim", "actions" => [
                "view-nature-of-claim",
                "add-nature-of-claim",
                "edit-nature-of-claim",
                "delete-nature-of-claim",
                "deactivate-nature-of-claim",
                "restore-nature-of-claim",
            ]],
            ["module" => "Settings", "sub_module" => "Case stage", "actions" => [

                "view-case-stages",
                "add-case-stages",
                "edit-case-stages",
                "delete-case-stages",
                "deactivate-case-stages",
                "restore-case-stages",
            ]],
            ["module" => "Settings", "sub_module" => "Case party types", "actions" => [
                "view-party-types",
                "add-party-types",
                "edit-party-types",
                "delete-party-types",
                "deactivate-party-types",
                "restore-party-types",
            ]],
            ["module" => "Settings", "sub_module" => "Event category", "actions" => [
                "view-event-category",
                "add-event-category",
                "edit-event-category",
                "delete-event-category",
                "deactivate-event-category",
                "restore-event-category",
            ]],
            ["module" => "Settings", "sub_module" => "Expense types", "actions" => [


                "view-expense-types",
                "add-expense-types",
                "edit-expense-types",
                "delete-expense-types",
                "deactivate-expense-types",
                "restore-expense-types",
            ]],
            ["module" => "Settings", "sub_module" => "Document types", "actions" => [

                "view-document-types",
                "add-document-types",
                "edit-document-types",
                "delete-document-types",
                "deactivate-document-types",
                "restore-document-types",
            ]],
            ["module" => "Security", "actions" => ["change_password"]],
        ];

        //delete any modules that are deleted
        $guard_names = [];

        foreach ($permissions as $value) {
            $module = $value["module"];
            $actions = $value["actions"];
            $sub_module = $value["sub_module"] ?? "";

            foreach ($actions as $action) {
                if (DB::table("permissions")->where("module", $module)->where("name", $action)->count() == 0) {
                    DB::table('permissions')->insert([
                        "module" => $module,
                        "sub_module" => $sub_module,
                        "name" => $action,
                        "created_at" => Carbon::now(),
                        "updated_at" => Carbon::now(),
                        "guard_name" => "web" . $module . $action
                    ]);
                } else {
                    $permission = Permission::query()->where("guard_name", "web" . $module . $action)->first();
                    if ($permission) {
                        $permission->sub_module = $sub_module;
                    }
                    $permission->update();
                }
                array_push($guard_names, "web" . $module . $action);
            }
        }

        DB::table('permissions')->whereNotIn('guard_name', $guard_names)->delete();

        //set admin role to have all permissions
        //first delete the previous permissions
        DB::table("role_permissions")->where("role_id", 1)->delete();
        //set the new permissions

        $permissions = DB::table("permissions")->get();
        foreach ($permissions as $value) {
            DB::table("role_permissions")->insert([
                "permission_id" => $value->id,
                "role_id" => 1
            ]);
        }
    }
}
