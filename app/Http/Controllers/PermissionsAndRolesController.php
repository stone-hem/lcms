<?php
namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Permission;
use App\Models\RoleHasPermissions;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PermissionsAndRolesController extends Controller
{
    public function index(Request $request)
    {
        $roles = Role::withTrashed()
            ->with('permissions')
            ->get();

        $allPermissions = Permission::all(['id', 'name', 'description']);

        // Add `is_attached` boolean to each permission for each role
        $roles = $roles->map(function ($role) use ($allPermissions) {
            $role->permissions_list = $allPermissions->map(function ($perm) use ($role) {
                $perm->is_attached = $role->permissions->contains('id', $perm->id);
                return $perm;
            });
            return $role;
        });

        return Inertia::render('settings/Roles', [
            'roles' => $roles,
            'all_permissions' => $allPermissions,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'description' => 'nullable|string',
            'active' => 'required|boolean',
        ]);

        Role::create([
            'name' => $request->name,
            'guard_name' => $request->name,
            'description' => $request->description ?? '',
            'deleted_at' => $request->active ? null : now(),
        ]);

        return back()->with('success', 'Role created successfully');
    }

    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
            'description' => 'nullable|string',
            'active' => 'required|boolean',
        ]);

        $role->update([
            'name' => $request->name,
            'description' => $request->description ?? '',
            'deleted_at' => $request->active ? null : now(),
        ]);

        return back()->with('success', 'Role updated successfully');
    }

    public function destroy(Role $role)
    {
        $hasUsers = User::where('role_id', $role->id)->exists();

        if ($hasUsers) {
            $role->delete(); // soft delete
        } else {
            $role->forceDelete(); // permanent
        }

        return back()->with('success', 'Role deleted successfully');
    }

    public function restore($id)
    {
        $role = Role::withTrashed()->findOrFail($id);
        $role->restore();

        return back()->with('success', 'Role restored successfully');
    }

    public function assignPermissions(Request $request, Role $role)
    {
        $request->validate([
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        // Clear existing
        $role->permissions()->detach();

        // Assign new
        if (!empty($request->permissions)) {
            $role->permissions()->attach($request->permissions);
        }

        return back()->with('success', 'Permissions assigned successfully');
    }
}