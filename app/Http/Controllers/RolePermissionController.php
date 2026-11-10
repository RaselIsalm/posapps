<?php

namespace App\Http\Controllers;

use App\DataTables\RolePermissionDataTable;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionController extends Controller
{
    public function index(RolePermissionDataTable $dataTable)
    {
        return $dataTable->render('backend.role_permission.index');
    }

    public function create()
    {
        if (request()->ajax()) {
            return view('backend.role_permission.ajax.create');
        }

        return abort(404);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:roles|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        if ($request->name === 'Super-Admin' && ! auth()->user()?->hasRole('Super-Admin')) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to create the Super-Admin role.',
            ], 403);
        }

        $role = Role::create([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        activity('security')
            ->causedBy(auth()->user())
            ->event('role_created')
            ->withProperties([
                'role_name' => $role->name,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ])
            ->log('A new role has been created');

        return response()->json([
            'success' => true,
            'message' => 'Role created successfully',
            'role' => [
                'id' => $role->id,
                'name' => $role->name,
            ],
        ]);
    }

    public function edit($id)
    {
        if (request()->ajax()) {
            $role = Role::findOrFail($id);

            return view('backend.role_permission.ajax.edit', compact('role'));
        }

        return abort(404);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|unique:roles,name,'.$id.'|max:255',
        ]);

        $role = Role::findOrFail($id);

        if ($role->name === 'Super-Admin' || $role->name === 'Admin') {
            return response()->json(['error' => 'You cannot update system roles.'], 403);
        }

        $role->update([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        activity('security')
            ->causedBy(auth()->user())
            ->event('role_updated')
            ->withProperties([
                'role_name' => $role->name,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ])
            ->log('Role details have been updated');

        return response()->json(['success' => true, 'message' => 'Role updated successfully']);
    }

    public function destroy($id)
    {
        $role = Role::findOrFail($id);

        if ($role->name === 'Super-Admin' || $role->name === 'Admin') {
            return response()->json(['error' => 'You cannot delete system roles.'], 403);
        }

        $roleName = $role->name;

        $role->delete();

        activity('security')
            ->causedBy(auth()->user())
            ->event('role_deleted')
            ->withProperties([
                'role_name' => $roleName,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ])
            ->log('A user role has been deleted');

        return response()->json(['success' => true, 'message' => 'Role deleted successfully']);
    }

    public function managePermissions($id)
    {
        $permissions = Permission::all();
        $role = Role::findOrFail($id);
        $rolePermissions = $role->permissions->pluck('name')->toArray();

        return view('backend.role_permission.manage_permissions', compact('id', 'permissions', 'rolePermissions', 'role'));
    }

    public function updatePermissions(Request $request, $id)
    {
        $role = Role::findOrFail($id);
        $permissions = $this->expandViewPermissions($request->permissions ?? []);
        $role->syncPermissions($permissions);

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        activity('security')
            ->causedBy(auth()->user())
            ->event('permissions_updated')
            ->withProperties([
                'role_name' => $role->name,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ])
            ->log('Role permissions have been updated');

        return redirect()->route('roles.index')->with('success', 'Permissions updated successfully');
    }

    /**
     * If create/update/delete is granted, also include matching view_* so menus/routes work.
     */
    protected function expandViewPermissions(array $permissions): array
    {
        $expanded = array_values(array_unique(array_filter($permissions)));

        foreach ($expanded as $perm) {
            if (! preg_match('/^(create|update|delete)_(.+)$/', $perm, $matches)) {
                continue;
            }

            $viewName = 'view_'.$matches[2];
            if (
                ! in_array($viewName, $expanded, true)
                && Permission::where('name', $viewName)->where('guard_name', 'web')->exists()
            ) {
                $expanded[] = $viewName;
            }
        }

        return $expanded;
    }
}
