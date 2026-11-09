<?php

namespace App\Http\Controllers;

use App\DataTables\UserDataTable; // Import DataTable
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(UserDataTable $dataTable)
    {
        return $dataTable->render('backend.user.index');
    }

    public function create()
    {
        $roles = Role::query()
            ->orderBy('name')
            ->when(! auth()->user()?->hasRole('Super-Admin'), function ($query) {
                $query->where('name', '!=', 'Super-Admin');
            })
            ->get();

        return view('backend.user.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'phone' => 'nullable|string|max:30',
            'password' => 'required|confirmed|min:6',
            'role' => 'required|string|exists:roles,name',
        ]);

        // Prevent a non-Super-Admin from assigning the Super-Admin role
        if (! auth()->user()->hasRole('Super-Admin') && $request->role === 'Super-Admin') {
            if ($request->ajax() || $request->wantsJson() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to assign the Super-Admin role.',
                ], 403);
            }

            return redirect()->route('users.index')->with('error', 'You do not have permission to assign the Super-Admin role.');
        }

        $baseUsername = explode('@', $request->email)[0];
        $username = $baseUsername;
        $counter = 1;
        while (User::where('username', $username)->exists()) {
            $username = $baseUsername.$counter;
            $counter++;
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'username' => $username,
            'phone' => $request->phone,
            'password' => bcrypt($request->password),
            'is_active' => 1,
        ]);
        $user->assignRole($request->role);

        if ($request->ajax() || $request->wantsJson() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'User created successfully',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'is_active' => (bool) $user->is_active,
                ],
            ]);
        }

        return redirect()->route('users.index')->with('success', 'User created successfully');
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);

        // Prevent non-Super-Admin from editing a Super-Admin
        if ($user->hasRole('Super-Admin') && ! auth()->user()->hasRole('Super-Admin')) {
            return redirect()->route('users.index')->with('error', 'You do not have permission to edit a Super-Admin.');
        }

        $roles = Role::all();

        return view('backend.user.edit', compact('user', 'roles'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email,'.$id,
            'role' => 'required',
        ]);

        $user = User::findOrFail($id);

        // Prevent non-Super-Admin from updating a Super-Admin
        if ($user->hasRole('Super-Admin') && ! auth()->user()->hasRole('Super-Admin')) {
            return redirect()->route('users.index')->with('error', 'You do not have permission to update a Super-Admin.');
        }

        // Prevent a non-Super-Admin from assigning the Super-Admin role
        if (! auth()->user()->hasRole('Super-Admin') && in_array('Super-Admin', (array) $request->role)) {
            return redirect()->route('users.index')->with('error', 'You do not have permission to assign the Super-Admin role.');
        }

        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->save();

        $user->syncRoles($request->role);

        return redirect()->route('users.index')->with('success', 'User updated successfully');
    }

    public function removalPreview($id)
    {
        $user = User::findOrFail($id);
        if ($message = $this->guardUserRemoval($user)) {
            return response()->json(['error' => $message], 403);
        }

        if ($user->hasOperationalHistory()) {
            return response()->json([
                'mode' => 'deactivate',
                'title' => 'Deactivate user?',
                'message' => 'This user has sales or other records, so the account will be deactivated instead of deleted. They will not be able to log in. Past bills will still show their name. You can activate the account again later.',
                'confirm' => 'Deactivate',
            ]);
        }

        return response()->json([
            'mode' => 'delete',
            'title' => 'Delete user?',
            'message' => 'This user has no sales or other records. The account will be permanently deleted. This cannot be undone.',
            'confirm' => 'Delete',
        ]);
    }

    public function activate($id)
    {
        $user = User::findOrFail($id);
        if ($message = $this->guardUserRemoval($user)) {
            return response()->json(['error' => $message], 403);
        }

        $user->activateAccount();

        return response()->json(['success' => true, 'message' => 'User activated. They can log in again.']);
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        if ($message = $this->guardUserRemoval($user)) {
            return response()->json(['error' => $message], 403);
        }

        if ($user->hasOperationalHistory()) {
            $user->deactivateAccount();

            return response()->json([
                'success' => true,
                'message' => 'User deactivated. Past records were kept, and they can no longer log in.',
            ]);
        }

        try {
            DB::transaction(function () use ($user) {
                $user->syncRoles([]);
                $user->delete();
            });
        } catch (QueryException $e) {
            $user->deactivateAccount();

            return response()->json([
                'success' => true,
                'message' => 'User deactivated. Past records were kept, and they can no longer log in.',
            ]);
        }

        return response()->json(['success' => true, 'message' => 'User deleted successfully']);
    }

    private function guardUserRemoval(User $user): ?string
    {
        if ($user->hasRole('Super-Admin')) {
            return 'Cannot delete Super Admin user.';
        }

        if ($user->id === auth()->id()) {
            return 'You cannot delete your own account.';
        }

        return null;
    }
}
