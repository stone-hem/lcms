<?php

namespace App\Http\Controllers\UserMgt;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Lawyer\Lawyer;
use App\Models\Lawyer\LawyerType;


use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Helpers\LogActivity;
use App\Jobs\SendEmailNotification;
use App\Jobs\SendSmsNotification;
use App\Models\Role;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class UserController extends Controller
{

    public function auth_user(Request $request)
    {
        $user =  $request->user();
        $user = User::select('id', 'name', 'email', 'phone', 'email_verified_at', 'lat', 'lng', 'type')->where('id', $user->id)->first();
        return $user;
    }

    public function update_user_details(Request $request)
    {
        $user =  $request->user();
        $first_name = $request->first_name;
        $last_name = $request->last_name;
        $phone = $request->phone;
    }

    public function activate($id)
    {
        $user = User::withTrashed()->find($id);
        if ($user) {
            $user->restore();
        }
        return response()->json(["error" => false, "message" => "User activated successfully"]);
    }


    public function deactivate($id)
    {
        $user = User::withTrashed()->find($id);
        if ($user) {
            $user->delete();
        }
        return response()->json(["error" => false, "message" => "User deleted successfully"]);
    }


    public function index(Request $request)
    {
        // Determine if we're showing internal counsel only or all internal users
        $internalCounselOnly = $request->boolean('ic', false);
    
        $query = User::withTrashed()
            ->with('role')
            ->where('is_external_counsel', 0);
    
        // Filter: Internal Counsel only (role_id = 2) OR all internal users (roles 1,2,3,4)
        if ($internalCounselOnly) {
            $query->where('role_id', 2);
        } else {
            $query->whereIn('role_id', [1, 2, 3, 4]); // Super Admin, Internal Counsel, etc.
        }
    
        // Global search
        if ($search = $request->input('search')) {
                $query->where(function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                      ->orWhere('middle_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%")
                      ->orWhere('calling_code', 'like', "%{$search}%")
                      ->orWhereHas('role', function ($roleQuery) use ($search) {
                          $roleQuery->where('name', 'like', "%{$search}%");
                      });
                });
            }
    
            // Additional column filters (optional, if you use DataTable-like filtering)
            if ($request->filled('role')) {
                $query->whereHas('role', fn($q) => $q->where('name', 'like', "%{$request->role}%"));
            }
    
            if ($request->filled('status')) {
                $query->where(
                    $request->status
                );
            }
    
            // Sorting
            $sortBy = $request->input('sort_by', 'created_at');
            $sortDirection = $request->input('sort_desc', true) ? 'desc' : 'asc';
    
            // Allowed sort columns to prevent injection
            $allowedSorts = ['id', 'first_name', 'email', 'phone', 'created_at', 'updated_at', 'deleted_at'];
            if (!in_array($sortBy, $allowedSorts)) {
                $sortBy = 'created_at';
            }
    
            $users = $query->orderBy($sortBy, $sortDirection)
                           ->paginate(25)
                           ->withQueryString(); // Keeps search, filters, ic, sort in URL
    
            $totalCount = User::where('is_external_counsel', 0)
                ->when($internalCounselOnly, fn($q) => $q->where('role_id', 2))
                ->when(!$internalCounselOnly, fn($q) => $q->whereIn('role_id', [1,2,3,4]))
                ->count();
    
            return Inertia::render('users/Index', [
                'users' => $users,
                'total_count' => $totalCount,
                'filters' => [
                    'search' => $request->search ?? '',
                    'ic' => $internalCounselOnly,
                    'role' => $request->role ?? '',
                    'status' => $request->status ?? '',
                    'sort_by' => $sortBy,
                    'sort_desc' => $request->boolean('sort_desc', true),
                ],
                'presets' => [
                    'roles' => Role::select('id', 'name')->orderBy('name')->get(),
                ],
            ]);
    }
    
    public function internal_counsel(Request $request)
    {
        // Determine if we're showing internal counsel only or all internal users
        $internalCounselOnly = $request->boolean('ic', false);
    
        $query = User::withTrashed()
            ->with('role')
            ->where('is_external_counsel', 0);
    
        // Filter: Internal Counsel only (role_id = 2) OR all internal users (roles 1,2,3,4)
        if ($internalCounselOnly) {
            $query->where('role_id', 2);
        } else {
            $query->whereIn('role_id', [1, 2, 3, 4]); // Super Admin, Internal Counsel, etc.
        }
    
        // Global search
        if ($search = $request->input('search')) {
                $query->where(function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                      ->orWhere('middle_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%")
                      ->orWhere('calling_code', 'like', "%{$search}%")
                      ->orWhereHas('role', function ($roleQuery) use ($search) {
                          $roleQuery->where('name', 'like', "%{$search}%");
                      });
                });
            }
    
            // Additional column filters (optional, if you use DataTable-like filtering)
            if ($request->filled('role')) {
                $query->whereHas('role', fn($q) => $q->where('name', 'like', "%{$request->role}%"));
            }
    
            if ($request->filled('status')) {
                $query->where(
                    $request->status
                );
            }
    
            // Sorting
            $sortBy = $request->input('sort_by', 'created_at');
            $sortDirection = $request->input('sort_desc', true) ? 'desc' : 'asc';
    
            // Allowed sort columns to prevent injection
            $allowedSorts = ['id', 'first_name', 'email', 'phone', 'created_at', 'updated_at', 'deleted_at'];
            if (!in_array($sortBy, $allowedSorts)) {
                $sortBy = 'created_at';
            }
    
            $users = $query->orderBy($sortBy, $sortDirection)
                           ->paginate(25)
                           ->withQueryString(); // Keeps search, filters, ic, sort in URL
    
            $totalCount = User::where('is_external_counsel', 0)
                ->when($internalCounselOnly, fn($q) => $q->where('role_id', 2))
                ->when(!$internalCounselOnly, fn($q) => $q->whereIn('role_id', [1,2,3,4]))
                ->count();
    
            return Inertia::render('users/InternalCounselIndex', [
                'users' => $users,
                'total_count' => $totalCount,
                'filters' => [
                    'search' => $request->search ?? '',
                    'ic' => $internalCounselOnly,
                    'role' => $request->role ?? '',
                    'status' => $request->status ?? '',
                    'sort_by' => $sortBy,
                    'sort_desc' => $request->boolean('sort_desc', true),
                ],
                'presets' => [
                    'roles' => Role::select('id', 'name')->orderBy('name')->get(),
                ],
            ]);
    }

    public function user_prefetch_values(Request $request)
    {
        $response = [];
        $response['countries'] = Helper::countries();
        $response['roles'] = Role::all();
        $response['lawyer_types'] = LawyerType::all();

        return $response;
    }

    public function user(Request $request, $id)
    {
        $user = User::withTrashed()->where("id", $id)->with('role')->first();
        return $user;
    }


    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "email" => "required|email|unique:users,email|unique:lawyers,email|regex:/(.+)@(.+)\.(.+)/i",
            "first_name" => "required|min:2",
            "calling_code" => "required",
            "phone" => "required",
            "lawyer_type_id" => "exists:lawyer_types,id",
            "role_id" => "required",
        ]);

        if ($request->enable_login) {
            if (strlen($request->password) < 6) {
                return response(
                    [
                        "error" => true,
                        "message" => "Password length must be equal to 6",
                    ],
                    422
                );
            }
        }
        if ($validator->fails()) {
            return response(
                [
                    "error" => true,
                    "message" => $validator->errors()->toArray(),
                    "recommendation" =>
                    "Fill all required fields correctly while observing rules",
                    "payload" => $validator->errors()->toArray(),
                ],
                422
            );
        }

        $user_phone_duplicate_count = User::where("calling_code", $request->calling_code)->where("phone", $request->phone)->count();

        if ($user_phone_duplicate_count > 0) {
            return response(
                [
                    "error" => true,
                    "message" => "User with phone number already exists",
                ],
                422
            );
        }


        $user = new User();
        $user->role_id = $request->input("role_id");
        $user->first_name = $request->input("first_name");
        $user->name = $request->input("first_name");
        $user->middle_name = $request->input("middle_name");
        $user->last_name = $request->input("last_name");
        $user->location = $request->input("location");
        $user->date_of_birth = $request->input("date_of_birth");
        $user->phone = $request->input("phone");

        if ($request->enable_login) {
            $user->password = Hash::make($request->password);
        } else {
            $user->password = Hash::make("#$03AF5YTXC"); //default password, can be cchanged later

        }
        $user->email = $request->input("email");
        $user->can_login = $request->enable_login;
        $user->calling_code = $request->calling_code;

        if ($request->active == 0) {
            $user->deleted_at = Carbon::now();
        }
        $user->is_active = true;

        $user->save();



        $phones = $request->input("phone");

        if (strpos($phones, "0") === 0) {
            $phones = $request->calling_code . substr($phones, 1);
        } else {
            $phones = $request->calling_code . '' . $request->phone;
        }

        return redirect()->back()->with([
            'success' => 'User created successfully',
        ]);

    }





    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            "email" => "required|email|unique:users,email,$id|unique:lawyers,email,$id|regex:/(.+)@(.+)\.(.+)/i",
            "first_name" => "required|min:2",
            "calling_code" => "required",
            "phone" => "required",
            "lawyer_type_id" => "exists:lawyer_types,id",
            "role_id" => "required",
        ]);

        if ($request->change_password) {
            if (strlen($request->password) < 6) {
                return response(
                    [
                        "error" => true,
                        "message" => "Password length must be equal to 6",
                    ],
                    422
                );
            }
        }
        if ($validator->fails()) {
            return response(
                [
                    "error" => true,
                    "message" => $validator->errors()->toArray(),
                    "recommendation" =>
                    "Fill all required fields correctly while observing rules",
                    "payload" => $validator->errors()->toArray(),
                ],
                422
            );
        }

        $user_phone_duplicate_count = User::where("calling_code", $request->calling_code)->where("phone", $request->phone)->where('id', '!=', $id)->count();

        if ($user_phone_duplicate_count > 0) {
            return response(
                [
                    "error" => true,
                    "message" => "User with phone number already exists",
                ],
                422
            );
        }


        $user = User::find($id);
        if (!$user) {
            return response(
                [
                    "error" => true,
                    "message" => "User not found",
                ],
                422
            );
        }
        $user->role_id = $request->input("role_id");
        $user->first_name = $request->input("first_name");
        $user->middle_name = $request->input("middle_name");
        $user->last_name = $request->input("last_name");
        $user->location = $request->input("location");
        $user->date_of_birth = $request->input("date_of_birth");
        $user->phone = $request->input("phone");

        if ($request->change_password) {
            $user->password = Hash::make($request->password);
        }

        $user->email = $request->input("email");
        $user->can_login = $request->enable_login;
        $user->calling_code = $request->calling_code;

        if ($request->active == 0) {
            $user->deleted_at = Carbon::now();
        }
        $user->is_active = true;

        $user->save();


        return redirect()->back()->with([
            'success' => 'User Saved successfully',
        ]);
    }



    public function destroy($id): Response
    {
        try {
            $user = User::find($id);
            if (!$user) {
                return response(
                    ["error" => true, "message" => "User not found"],
                    404
                );
            }

            DB::transaction(function () use ($user) {
                if ($user->role_id == 2) {
                    $lawyer = Lawyer::where("user_id", $user->id)->first();
                    if ($lawyer) {
                        if ($lawyer->photo_url) {
                            Storage::delete($lawyer->photo_url);
                        }
                        $lawyer->delete();
                    }
                }

                $user->delete();
                LogActivity::addToLog(
                    "Deleted User " . $user->first_name . "-" . $user->email
                );
            });

            return response(
                [
                    "status" => 200,
                    "message" => "User deleted successfully",
                ],
                200
            );
        } catch (\Exception $e) {
            return response(
                [
                    "error" => true,
                    "message" => "An error occurred during deletion",
                    "details" => $e->getMessage(),
                ],
                500
            );
        }
    }
}
