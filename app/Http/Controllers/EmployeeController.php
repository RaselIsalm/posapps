<?php

namespace App\Http\Controllers;

use App\DataTables\EmployeeDataTable;
use App\Helpers\ImageHelper;
use App\Models\Account;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

class EmployeeController extends Controller
{
    public function index(EmployeeDataTable $dataTable)
    {
        return $dataTable->render('backend.employee.index');
    }

    public function create()
    {
        $departments = Department::where('status', 1)->get();
        $branches = Branch::orderBy('name')->get();
        $roles = $this->assignableRoles();
        $accounts = Account::active()->orderBy('name')->get();
        $branchManagers = User::orderBy('name')->get();

        return view('backend.employee.ajax.create', compact(
            'departments',
            'branches',
            'roles',
            'accounts',
            'branchManagers'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:employees,email|unique:users,email',
            'phone' => 'required|string|max:20',
            'salary' => 'required|numeric',
            'department_id' => 'required|integer|exists:departments,id',
            'branch_id' => 'required|integer|exists:branches,id',
            'role' => 'required|string|exists:roles,name',
            'password' => 'required|string|min:6|confirmed',
            'joining_date' => 'required|date',
            'max_commission' => 'nullable|numeric',
            'sale_target' => 'nullable|numeric',
            'leaving_date' => 'nullable|date',
            'image' => 'nullable|file|max:5120',
        ]);

        if ($error = $this->guardRoleAssignment($request->role)) {
            return response()->json(['success' => false, 'message' => $error], 403);
        }

        $employee = null;
        $photoName = $request->hasFile('image') ? $this->storeEmployeePhoto($request->file('image')) : null;

        try {
        DB::transaction(function () use ($request, &$employee, $photoName) {
            $isActive = $request->has('status') ? 1 : 0;

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'username' => $this->uniqueUsernameFromEmail($request->email),
                'password' => $request->password,
                'is_active' => $isActive,
            ]);

            $role = Role::findByName($request->role, 'web');
            $user->syncRoles([$role]);
            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

            $employee = Employee::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'image' => $photoName,
                'salary' => $request->salary,
                'max_commission' => $request->max_commission ?? 0,
                'sale_target' => $request->sale_target ?? 0,
                'joining_date' => $request->joining_date,
                'leaving_date' => $request->leaving_date,
                'department_id' => $request->department_id,
                'branch_id' => $request->branch_id,
                'user_id' => $user->id,
                'created_by' => Auth::id(),
                'status' => $isActive,
            ]);
        });
        } catch (\Throwable $e) {
            if ($photoName) {
                ImageHelper::deleteImage($photoName, 'employees');
            }
            throw $e;
        }

        return response()->json([
            'success' => true,
            'message' => 'Employee created successfully.',
            'employee' => [
                'id' => $employee->id,
                'name' => $employee->name,
                'branch_id' => $employee->branch_id,
                'status' => (bool) $employee->status,
            ],
        ]);
    }

    public function edit($id)
    {
        $employee = Employee::with(['user.roles'])->findOrFail($id);
        $departments = Department::where('status', 1)->get();
        $branches = Branch::orderBy('name')->get();
        $roles = $this->assignableRoles();
        $accounts = Account::active()->orderBy('name')->get();
        $branchManagers = User::orderBy('name')->get();

        return view('backend.employee.ajax.edit', compact(
            'employee',
            'departments',
            'branches',
            'roles',
            'accounts',
            'branchManagers'
        ));
    }

    public function update(Request $request, $id)
    {
        $employee = Employee::with('user')->findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:employees,email,'.$id,
            'phone' => 'required|string|max:20',
            'salary' => 'required|numeric',
            'department_id' => 'required|integer|exists:departments,id',
            'branch_id' => 'required|integer|exists:branches,id',
            'role' => 'required|string|exists:roles,name',
            'password' => 'nullable|string|min:6|confirmed',
            'joining_date' => 'required|date',
            'max_commission' => 'nullable|numeric',
            'sale_target' => 'nullable|numeric',
            'leaving_date' => 'nullable|date',
            'image' => 'nullable|file|max:5120',
            'remove_image' => 'nullable|boolean',
        ]);

        if ($error = $this->guardRoleAssignment($request->role)) {
            return response()->json(['success' => false, 'message' => $error], 403);
        }

        if ($employee->user && $employee->user->hasRole('Super-Admin') && ! Auth::user()->hasRole('Super-Admin')) {
            return response()->json(['success' => false, 'message' => 'You do not have permission to update this employee.'], 403);
        }

        if (! $employee->user_id && ! $request->filled('password')) {
            return response()->json([
                'success' => false,
                'message' => 'Password is required to enable login for this employee.',
            ], 422);
        }

        $userEmailRule = 'required|email|unique:users,email';
        if ($employee->user_id) {
            $userEmailRule .= ','.$employee->user_id;
        }
        $request->validate([
            'email' => $userEmailRule,
        ]);

        $newPhoto = $request->hasFile('image') ? $this->storeEmployeePhoto($request->file('image')) : null;
        $oldPhoto = $employee->image;
        $removePhoto = $request->boolean('remove_image') && ! $newPhoto;

        try {
        DB::transaction(function () use ($request, $employee, $newPhoto, $removePhoto) {
            $isActive = $request->has('status') ? 1 : 0;
            $user = $employee->user;

            if (! $user) {
                $user = User::create([
                    'name' => $request->name,
                    'email' => $request->email,
                    'phone' => $request->phone,
                    'username' => $this->uniqueUsernameFromEmail($request->email),
                    'password' => $request->password,
                    'is_active' => $isActive,
                ]);
            } else {
                $user->name = $request->name;
                $user->email = $request->email;
                $user->phone = $request->phone;
                $user->is_active = $isActive;
                if ($request->filled('password')) {
                    $user->password = $request->password;
                }
                $user->save();
            }

            $role = Role::findByName($request->role, 'web');
            $user->syncRoles([$role]);
            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

            $employeeData = [
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'salary' => $request->salary,
                'max_commission' => $request->max_commission ?? 0,
                'sale_target' => $request->sale_target ?? 0,
                'joining_date' => $request->joining_date,
                'leaving_date' => $request->leaving_date,
                'department_id' => $request->department_id,
                'branch_id' => $request->branch_id,
                'user_id' => $user->id,
                'updated_by' => Auth::id(),
                'status' => $isActive,
            ];
            if ($newPhoto) {
                $employeeData['image'] = $newPhoto;
            } elseif ($removePhoto) {
                $employeeData['image'] = null;
            }
            $employee->update($employeeData);
        });
        } catch (\Throwable $e) {
            if ($newPhoto) {
                ImageHelper::deleteImage($newPhoto, 'employees');
            }
            throw $e;
        }

        if ($newPhoto && $oldPhoto && $oldPhoto !== $newPhoto) {
            ImageHelper::deleteImage($oldPhoto, 'employees');
        } elseif ($removePhoto && $oldPhoto) {
            ImageHelper::deleteImage($oldPhoto, 'employees');
        }

        return response()->json(['success' => true, 'message' => 'Employee updated successfully.']);
    }

    public function destroy($id)
    {
        $employee = Employee::findOrFail($id);

        if ($employee->user_id && (int) $employee->user_id === (int) Auth::id()) {
            return back()->with('error', 'You cannot remove your own account.');
        }

        if ($employee->sales()->exists()) {
            return back()->with('error', 'Warning: This employee cannot be deleted because they have associated sales records!');
        }

        DB::transaction(function () use ($employee) {
            $user = $employee->user;
            ImageHelper::deleteImage($employee->image, 'employees');
            $employee->delete();

            if (
                $user
                && ! $user->hasRole('Super-Admin')
                && $user->id !== Auth::id()
                && ! Employee::where('user_id', $user->id)->exists()
            ) {
                $user->delete();
            }
        });

        return back()->with('success', 'Employee deleted successfully.');
    }

    protected function storeEmployeePhoto(UploadedFile $file): string
    {
        $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: '');
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'avif', 'bmp', 'heic', 'heif'];
        $mime = strtolower((string) $file->getMimeType());
        $octetOk = in_array($extension, ['heic', 'heif', 'avif'], true);
        $mimeOk = $mime === '' || str_starts_with($mime, 'image/') || ($mime === 'application/octet-stream' && $octetOk);

        if (! in_array($extension, $allowed, true) || ! $mimeOk) {
            throw ValidationException::withMessages([
                'image' => __('Please choose a JPG, PNG, WEBP, GIF, AVIF, BMP or HEIC image up to 5 MB.'),
            ]);
        }

        return ImageHelper::uploadImage(Str::uuid()->toString().'.'.$extension, $file, 'employees');
    }

    protected function assignableRoles()
    {
        $query = Role::query()->orderBy('name');

        if (! Auth::user()?->hasRole('Super-Admin')) {
            $query->where('name', '!=', 'Super-Admin');
        }

        return $query->get();
    }

    protected function guardRoleAssignment(string $roleName): ?string
    {
        if ($roleName === 'Super-Admin' && ! Auth::user()?->hasRole('Super-Admin')) {
            return 'You do not have permission to assign the Super-Admin role.';
        }

        return null;
    }

    protected function uniqueUsernameFromEmail(string $email): string
    {
        $baseUsername = explode('@', $email)[0];
        $username = $baseUsername;
        $counter = 1;

        while (User::where('username', $username)->exists()) {
            $username = $baseUsername.$counter;
            $counter++;
        }

        return $username;
    }
}
