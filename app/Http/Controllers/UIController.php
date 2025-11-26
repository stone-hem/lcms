<?php

namespace App\Http\Controllers;

use App\Helpers\Helper;
use Illuminate\Http\Request;

class UIController extends Controller
{


    function filterMenuByPermissions($menuItems, $permissions)
    {
        foreach ($menuItems as $key => $item) {
            // If the item has children, recursively filter them
            if (isset($item['child'])) {
                $menuItems[$key]['child'] = $this->filterMenuByPermissions($item['child'], $permissions);
                // Remove the menu item if no children remain after filtering
                if (empty($menuItems[$key]['child'])) {
                    unset($menuItems[$key]);
                }
            }
            // Check permissions for items that require it
            elseif (isset($item['permissions'])) {
                if (!array_intersect($item['permissions'], $permissions)) {
                    unset($menuItems[$key]); // Remove the item if no permissions match
                }
            }
        }

        // After filtering children, remove any headers without corresponding children
        $menuItems = array_values($menuItems); // Re-index array
        $menuItems = array_filter($menuItems, function ($item) {
            return !(isset($item['isHeadr']) && empty($item['child']));
        });

        return array_values($menuItems);
    }

    public function user_menu(Request $request)
    {
        $menuItems = [
            [
                "isHeadr" => true,
                "title" => "Home",
            ],

            [
                "title" => "Dashboard",
                "icon" => "heroicons-outline:home",
                "isOpen" => true,
                "child" => [
                    [
                        "childtitle" => "Overiew",
                        "permissions" => ["view-analytics-reports"],
                        "childlink" => "/app/home",
                    ],
                ],

            ],


            [
                "title" => "Reports",
                "icon" => "iconoir:reports",
                "isOpen" => true,
                "child" => [
                    [
                        "childtitle" => "Cases reports",
                        "permissions" => ["view-case-reports"],
                        "childlink" => "/app/cases_report",
                    ],

                    [
                        "childtitle" => "Cases by counsel",
                        "permissions" => ["view-cases-by-lawyer"],
                        "childlink" => "/app/cases_by_lawyer_report",
                    ],
                ],

            ],

            [
                "isHeadr" => true,
                "title" => "case",
            ],

            [
                "title" => "Kanban",
                "icon" => "teenyicons:kanban-solid",
                "permissions" => ["view-task"],
                "link" => "/app/task_kanban",
            ],
            [
                "title" => "Master Calender",
                "icon" => "heroicons-outline:calendar",
                "permissions" => ["view-master-calender"],
                "link" => "/app/calender",
            ],

            [
                "title" => "My Calender",
                "icon" => "heroicons-outline:calendar",
                "permissions" => ["view-my-calender"],
                "link" => "/app/my_calender",
            ],

            [
                "title" => "Todo",
                "permissions" => ["view-task"],
                "icon" => "heroicons-outline:clipboard-check",
                "link" => "/app/todo",
            ],

            [
                "title" => "Files",
                // "permissions" => ["view-files"],
                "icon" => "simple-icons:files",
                "link" => "/app/files",
            ],

            [
                "title" => "Case",
                "icon" => "heroicons-outline:archive-box",
                "link" => "#",
                "child" => [
                    [
                        "childtitle" => "Cases",
                        "permissions" => ["view-case"],
                        "childlink" => "/app/cases",
                    ],
                    // [
                    //     "childtitle" => "Add case",
                    //     "permissions" => ["add-case"],
                    //     "childlink" => "/app/case-add",
                    // ],
                ],
            ],

            [
                "title" => "External counsel",
                "icon" => "map:lawyer",
                "permissions" => ["view-external-counsel"],
                "link" => "/app/external_counsel",
            ],

            [
                "title" => "Internal counsel",
                "icon" => "map:lawyer",
                "permissions" => ["view-internal-counsel"],
                "link" => "/app/internal_counsel",
            ],

            [
                "isHeadr" => true,
                "permissions" => [
                    "view-file-templates",
                ],
                "title" => "knowledge base",
            ],
            [
                "title" => "Document templates",
                "icon" => "basil:document-outline",
                "permissions" => ["view-document-templates"],
                "link" => "/app/document_templates",
            ],



            [
                "isHeadr" => true,
                "title" => "Users",
            ],

            [
                "title" => "users",
                "icon" => "mdi:users",
                "child" => [
                    [
                        "childtitle" => "Users",
                        "permissions" => ["view-user"],
                        "childlink" => "/app/users",
                    ],
                    [
                        "permissions" => ["create-user"],
                        "childtitle" => "Add user",
                        "childlink" => "/app/user-add",
                    ],
                ],

            ],


            [
                "isHeadr" => true,
                "title" => "settings",
            ],

            [
                "title" => "Settings",
                "icon" => "material-symbols:settings",
                "link" => "#",
                "child" => [
                    [
                        "childtitle" => "Roles",
                        "permissions" => [
                            "view-roles",
                        ],
                        "childlink" => "/app/roles",
                    ],
                    [
                        "childtitle" => "Permissions",
                        "permissions" => ["assign-permissions-to-role"],
                        "childlink" => "/app/assign-permissions",
                    ],

                    [
                        "childtitle" => "Manage",
                        "permissions" => ["assign-permissions-to-role"],
                        "childlink" => "/app/settings-manage",
                    ],
                ],
            ],
        ];


        $menu = [];
        $user = $request->user();
        $permissions = Helper::get_role_permissions($user->role_id);

        $permissions_flattened = [];
        foreach ($permissions as $value) {
            $permission = $value->name;
            array_push($permissions_flattened, $permission);
        }

        $filteredMenu = $this->filterMenuByPermissions($menuItems, $permissions_flattened);


        $response = [];
        $response["menu"] = $filteredMenu;
        $response["base_file_path"] = asset('storage') . '/uploads/temp/';
        $response["permissions"] = $permissions_flattened;

        return $response;
    }
}
