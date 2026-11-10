<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ImageHelper;
use App\Models\Account;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeAttendance;
use App\Models\Payroll;
use App\Models\Transaction;
use App\Models\User;
use App\Services\TransactionService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class HrmController extends ApiController
{
    private const PERMISSIONS = [
        'employees' => ['view_employees', 'create_employees', 'update_employees', 'delete_employees'],
        'attendance' => ['view_employees_attendances', 'create_employees_attendances', 'update_employees_attendances', 'delete_employees_attendances'],
        'departments' => ['view_departments', 'create_departments', 'update_departments', 'delete_departments'],
        'payrolls' => ['view_payrolls', 'create_payrolls', 'update_payrolls', 'delete_payrolls'],
    ];

    public function index(Request $request, string $resource): JsonResponse
    {
        $this->authorizeResource($resource, 0);
        $query = match ($resource) {
            'employees' => $this->applyBranchScope(Employee::query()->with(['department', 'branch', 'user.roles']), $request),
            'attendance' => EmployeeAttendance::query()->with('employee')->whereHas('employee', fn ($q) => $this->applyBranchScope($q, $request)),
            'departments' => Department::query()->withCount('employees'),
            'payrolls' => Payroll::query()->with('employee')->whereHas('employee', fn ($q) => $this->applyBranchScope($q, $request)),
        };
        $term = trim((string) $request->query('search', ''));
        if ($term !== '') {
            $like = '%'.$term.'%';
            $query->where(function ($q) use ($resource, $like) {
                match ($resource) {
                    'employees' => $q->where('name', 'like', $like)->orWhere('email', 'like', $like)->orWhere('phone', 'like', $like),
                    'attendance' => $q->whereHas('employee', fn ($e) => $e->where('name', 'like', $like)),
                    'departments' => $q->where('name', 'like', $like)->orWhere('code', 'like', $like),
                    'payrolls' => $q->whereHas('employee', fn ($e) => $e->where('name', 'like', $like)),
                };
            });
        }
        if ($resource === 'attendance') {
            $query->when($request->filled('employee_id'), fn ($q) => $q->where('employee_id', $request->integer('employee_id')))
                ->when($request->filled('from_date'), fn ($q) => $q->whereDate('attendance_date', '>=', $request->query('from_date')))
                ->when($request->filled('to_date'), fn ($q) => $q->whereDate('attendance_date', '<=', $request->query('to_date')))
                ->when($request->filled('status'), fn ($q) => $q->where('status', $request->boolean('status')));
        }
        if ($resource === 'employees') $query->when($request->filled('department_id'), fn ($q) => $q->where('department_id', $request->integer('department_id')))->when($request->filled('status'), fn ($q) => $q->where('status', $request->boolean('status')));
        if ($resource === 'departments') $query->when($request->filled('status'), fn ($q) => $q->where('status', $request->boolean('status')));
        if ($resource === 'payrolls') $query->when($request->filled('employee_id'), fn ($q) => $q->where('employee_id', $request->integer('employee_id')))->when($request->filled('month'), fn ($q) => $q->where('salary_month', $request->query('month')))->when($request->filled('year'), fn ($q) => $q->where('salary_year', $request->integer('year')))->when($request->filled('status'), fn ($q) => $q->where('status', $request->query('status')));

        $data = $this->page($request, $query->latest('id'), fn ($row) => $this->serialize($resource, $row), $resource);
        $data['options'] = $this->optionsData($request);
        return $this->success($data);
    }

    public function options(Request $request, string $resource): JsonResponse
    {
        $this->authorizeResource($resource, 0);
        return $this->success($this->optionsData($request));
    }

    public function show(Request $request, string $resource, int $id): JsonResponse
    {
        $this->authorizeResource($resource, 0);
        $model = $this->findScoped($request, $resource, $id);
        return $this->success(['item' => $this->serialize($resource, $model)]);
    }

    public function store(Request $request, string $resource): JsonResponse
    {
        $this->authorizeResource($resource, 1);
        $item = match ($resource) {
            'employees' => $this->createEmployee($request),
            'attendance' => $this->createAttendance($request),
            'departments' => $this->createDepartment($request),
            'payrolls' => $this->createPayroll($request),
        };
        return $this->success(['item' => $this->serialize($resource, $item)], 'Created successfully.', 201);
    }

    public function update(Request $request, string $resource, int $id): JsonResponse
    {
        $this->authorizeResource($resource, 2);
        $model = $this->findScoped($request, $resource, $id);
        $item = match ($resource) {
            'employees' => $this->updateEmployee($request, $model),
            'attendance' => $this->updateAttendance($request, $model),
            'departments' => $this->updateDepartment($request, $model),
            'payrolls' => $this->updatePayroll($request, $model),
        };
        return $this->success(['item' => $this->serialize($resource, $item)], 'Updated successfully.');
    }

    public function destroy(Request $request, string $resource, int $id): JsonResponse
    {
        $this->authorizeResource($resource, 3);
        $model = $this->findScoped($request, $resource, $id);
        if ($resource === 'departments' && Employee::where('department_id', $id)->exists()) abort(409, 'This department still has employees.');
        if ($resource === 'attendance') {
            $date = Carbon::parse($model->attendance_date);
            if (Payroll::where('employee_id', $model->employee_id)->where('salary_month', $date->format('F'))->where('salary_year', $date->year)->exists()) abort(409, 'Attendance cannot be deleted after payroll has been generated for that month.');
        }
        if ($resource === 'employees') {
            abort_if($model->user_id === $request->user()->id, 409, 'You cannot remove your own employee account.');
            abort_if($model->sales()->exists(), 409, 'This employee has sales history and cannot be deleted.');
        }
        DB::transaction(function () use ($resource, $model) {
            if ($resource === 'employees') {
                $user = $model->user;
                ImageHelper::deleteImage($model->image, 'employees');
                $model->delete();
                if ($user && ! $user->hasRole('Super-Admin') && ! Employee::where('user_id', $user->id)->exists()) {
                    $user->syncRoles([]);
                    $user->delete();
                }
            } elseif ($resource === 'payrolls') {
                $this->reversePayrollTransaction($model);
                $model->delete();
            } else {
                $model->delete();
            }
        });
        return $this->success([], 'Deleted successfully.');
    }

    public function payrollEmployeeData(Request $request): JsonResponse
    {
        $this->requirePermission('create_payrolls', 'update_payrolls');
        $data = $request->validate(['employee_id' => ['required', 'integer', 'exists:employees,id'], 'month' => ['required', 'string'], 'year' => ['required', 'integer', 'between:2000,2200']]);
        $employee = Employee::findOrFail($data['employee_id']);
        $this->assertBranchAccess($request, (int) $employee->branch_id);
        $month = Carbon::parse('1 '.$data['month'].' '.$data['year']);
        $attendance = EmployeeAttendance::where('employee_id', $employee->id)->whereMonth('attendance_date', $month->month)->whereYear('attendance_date', $month->year);
        return $this->success(['basic_salary' => (float) $employee->salary, 'total_deduction' => 0, 'net_salary' => (float) $employee->salary, 'present_days' => (clone $attendance)->where('status', 1)->count(), 'absent_days' => (clone $attendance)->where('status', 0)->count()]);
    }

    private function createDepartment(Request $request): Department
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:100', 'unique:departments,name'], 'code' => ['required', 'string', 'max:50', 'unique:departments,code'], 'description' => ['nullable', 'string'], 'status' => ['sometimes', 'boolean']]);
        return Department::create($data + ['status' => $request->boolean('status', true), 'created_by' => $request->user()->id]);
    }

    private function updateDepartment(Request $request, Department $department): Department
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:100', Rule::unique('departments', 'name')->ignore($department->id)], 'code' => ['required', 'string', 'max:50', Rule::unique('departments', 'code')->ignore($department->id)], 'description' => ['nullable', 'string'], 'status' => ['sometimes', 'boolean']]);
        $department->update($data + ['status' => $request->boolean('status', true), 'updated_by' => $request->user()->id]);
        return $department->fresh()->loadCount('employees');
    }

    private function createEmployee(Request $request): Employee
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:255'], 'email' => ['required', 'email', 'unique:employees,email', 'unique:users,email'], 'phone' => ['required', 'string', 'max:20'], 'salary' => ['required', 'numeric', 'min:0'], 'department_id' => ['required', 'integer', 'exists:departments,id'], 'branch_id' => ['required', 'integer', 'exists:branches,id'], 'role' => ['required', 'string', 'exists:roles,name'], 'password' => ['required', 'string', 'min:6', 'confirmed'], 'joining_date' => ['required', 'date'], 'max_commission' => ['nullable', 'numeric', 'min:0'], 'sale_target' => ['nullable', 'numeric', 'min:0'], 'leaving_date' => ['nullable', 'date'], 'status' => ['sometimes', 'boolean'], 'image' => ['nullable', 'image', 'max:5120']]);
        $this->assertBranchAccess($request, (int) $data['branch_id']);
        $this->guardRole($request, $data['role']);
        $image = $request->file('image') ? ImageHelper::uploadImage(\Illuminate\Support\Str::uuid().'.'.$request->file('image')->extension(), $request->file('image'), 'employees') : null;
        try {
            return DB::transaction(function () use ($request, $data, $image) {
                $active = $request->boolean('status', true);
                $user = User::create(['name' => $data['name'], 'email' => $data['email'], 'phone' => $data['phone'], 'username' => $this->uniqueUsername($data['email']), 'password' => $data['password'], 'is_active' => $active]);
                $user->syncRoles([Role::findByName($data['role'], 'web')]);
                app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
                return Employee::create(['name' => $data['name'], 'email' => $data['email'], 'phone' => $data['phone'], 'salary' => $data['salary'], 'department_id' => $data['department_id'], 'branch_id' => $data['branch_id'], 'joining_date' => $data['joining_date'], 'leaving_date' => $data['leaving_date'] ?? null, 'max_commission' => $data['max_commission'] ?? 0, 'sale_target' => $data['sale_target'] ?? 0, 'status' => $active, 'image' => $image, 'user_id' => $user->id, 'created_by' => $request->user()->id])->load(['department', 'branch', 'user.roles']);
            });
        } catch (\Throwable $e) { if ($image) ImageHelper::deleteImage($image, 'employees'); throw $e; }
    }

    private function updateEmployee(Request $request, Employee $employee): Employee
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:255'], 'email' => ['required', 'email', Rule::unique('employees', 'email')->ignore($employee->id), Rule::unique('users', 'email')->ignore($employee->user_id)], 'phone' => ['required', 'string', 'max:20'], 'salary' => ['required', 'numeric', 'min:0'], 'department_id' => ['required', 'integer', 'exists:departments,id'], 'branch_id' => ['required', 'integer', 'exists:branches,id'], 'role' => ['required', 'string', 'exists:roles,name'], 'password' => ['nullable', 'string', 'min:6', 'confirmed'], 'joining_date' => ['required', 'date'], 'max_commission' => ['nullable', 'numeric', 'min:0'], 'sale_target' => ['nullable', 'numeric', 'min:0'], 'leaving_date' => ['nullable', 'date'], 'status' => ['sometimes', 'boolean'], 'image' => ['nullable', 'image', 'max:5120'], 'remove_image' => ['sometimes', 'boolean']]);
        $this->assertBranchAccess($request, (int) $data['branch_id']);
        $this->guardRole($request, $data['role']);
        abort_if($employee->user?->hasRole('Super-Admin') && ! $request->user()->hasRole('Super-Admin'), 403, 'You cannot edit a Super Admin employee.');
        abort_if(! $employee->user && empty($data['password']), 422, 'Password is required to create an employee login.');
        $newImage = $request->file('image') ? ImageHelper::uploadImage(\Illuminate\Support\Str::uuid().'.'.$request->file('image')->extension(), $request->file('image'), 'employees') : null;
        $oldImage = $employee->image;
        try {
            $updated = DB::transaction(function () use ($request, $employee, $data, $newImage) {
                $user = $employee->user;
                if (! $user) $user = new User(['username' => $this->uniqueUsername($data['email'])]);
                $user->fill(['name' => $data['name'], 'email' => $data['email'], 'phone' => $data['phone'], 'is_active' => $request->boolean('status', true)]);
                if (! empty($data['password'])) $user->password = $data['password'];
                $user->save();
                $user->syncRoles([Role::findByName($data['role'], 'web')]);
                app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
                $employee->update(['name' => $data['name'], 'email' => $data['email'], 'phone' => $data['phone'], 'salary' => $data['salary'], 'department_id' => $data['department_id'], 'branch_id' => $data['branch_id'], 'joining_date' => $data['joining_date'], 'leaving_date' => $data['leaving_date'] ?? null, 'max_commission' => $data['max_commission'] ?? 0, 'sale_target' => $data['sale_target'] ?? 0, 'status' => $request->boolean('status', true), 'user_id' => $user->id, 'image' => $newImage ?: ($request->boolean('remove_image') ? null : $employee->image), 'updated_by' => $request->user()->id]);
                return $employee->fresh(['department', 'branch', 'user.roles']);
            });
        } catch (\Throwable $e) { if ($newImage) ImageHelper::deleteImage($newImage, 'employees'); throw $e; }
        if ($newImage || $request->boolean('remove_image')) ImageHelper::deleteImage($oldImage, 'employees');
        return $updated;
    }

    private function createAttendance(Request $request): EmployeeAttendance
    {
        $data = $request->validate(['employee_id' => ['required', 'integer', 'exists:employees,id'], 'attendance_date' => ['required', 'date'], 'check_in_time' => ['nullable', 'date_format:H:i'], 'check_out_time' => ['nullable', 'date_format:H:i'], 'note' => ['nullable', 'string', 'max:1000'], 'status' => ['sometimes', 'boolean']]);
        abort_if(EmployeeAttendance::where('employee_id', $data['employee_id'])->whereDate('attendance_date', $data['attendance_date'])->exists(), 409, 'Attendance for this employee and date already exists.');
        $employee = Employee::findOrFail($data['employee_id']); $this->assertBranchAccess($request, (int) $employee->branch_id);
        $data['status'] = $request->boolean('status', true); $data['total_hours'] = $this->hours($data['check_in_time'] ?? null, $data['check_out_time'] ?? null); $data['created_by'] = $request->user()->id;
        return EmployeeAttendance::create($data)->load('employee');
    }

    private function updateAttendance(Request $request, EmployeeAttendance $attendance): EmployeeAttendance
    {
        $data = $request->validate(['employee_id' => ['required', 'integer', 'exists:employees,id'], 'attendance_date' => ['required', 'date'], 'check_in_time' => ['nullable', 'date_format:H:i'], 'check_out_time' => ['nullable', 'date_format:H:i'], 'note' => ['nullable', 'string', 'max:1000'], 'status' => ['sometimes', 'boolean']]);
        abort_if(EmployeeAttendance::where('employee_id', $data['employee_id'])->whereDate('attendance_date', $data['attendance_date'])->whereKeyNot($attendance->id)->exists(), 409, 'Attendance for this employee and date already exists.');
        $employee = Employee::findOrFail($data['employee_id']); $this->assertBranchAccess($request, (int) $employee->branch_id);
        $data['status'] = $request->boolean('status', true); $data['total_hours'] = $this->hours($data['check_in_time'] ?? null, $data['check_out_time'] ?? null); $data['updated_by'] = $request->user()->id;
        $attendance->update($data); return $attendance->fresh('employee');
    }

    private function createPayroll(Request $request): Payroll
    {
        $data = $this->payrollInput($request);
        abort_if(Payroll::where('employee_id', $data['employee_id'])->where('salary_month', $data['salary_month'])->where('salary_year', $data['salary_year'])->exists(), 409, 'Payroll already exists for this employee and period.');
        return DB::transaction(function () use ($request, $data) {
            $payroll = Payroll::create($data);
            if ($data['status'] === 'paid') $this->postPayrollTransaction($payroll, $data['payment_method'], $data['net_salary'], $data['payment_date'] ?: now()->toDateString());
            return $payroll->load('employee');
        });
    }

    private function updatePayroll(Request $request, Payroll $payroll): Payroll
    {
        $data = $this->payrollInput($request, $payroll);
        abort_if(Payroll::where('employee_id', $data['employee_id'])->where('salary_month', $data['salary_month'])->where('salary_year', $data['salary_year'])->where('id', '!=', $payroll->id)->exists(), 409, 'Payroll already exists for this employee and period.');
        return DB::transaction(function () use ($payroll, $data) {
            $this->reversePayrollTransaction($payroll);
            $payroll->update($data);
            if ($data['status'] === 'paid') $this->postPayrollTransaction($payroll, $data['payment_method'], $data['net_salary'], $data['payment_date'] ?: now()->toDateString());
            return $payroll->fresh('employee');
        });
    }

    private function payrollInput(Request $request, ?Payroll $current = null): array
    {
        $data = $request->validate(['employee_id' => ['required', 'integer', 'exists:employees,id'], 'salary_month' => ['required', 'string', 'max:20'], 'salary_year' => ['required', 'integer', 'between:2000,2200'], 'basic_salary' => ['required', 'numeric', 'min:0'], 'total_allowance' => ['nullable', 'numeric', 'min:0'], 'total_deduction' => ['nullable', 'numeric', 'min:0'], 'payment_method' => ['required', 'string', 'max:50'], 'transaction_id' => ['nullable', 'string', 'max:255'], 'payment_date' => ['nullable', 'date'], 'status' => ['required', Rule::in(['pending', 'paid', 'cancelled'])], 'note' => ['nullable', 'string', 'max:1000']]);
        $employee = Employee::findOrFail($data['employee_id']);
        $this->assertBranchAccess($request, (int) $employee->branch_id);
        $data['total_allowance'] = $data['total_allowance'] ?? 0; $data['total_deduction'] = $data['total_deduction'] ?? 0;
        $data['net_salary'] = (float) $data['basic_salary'] + (float) $data['total_allowance'] - (float) $data['total_deduction'];
        if ($data['status'] === 'paid') {
            $accountId = (int) $data['payment_method'];
            $account = Account::active()->findOrFail($accountId);
            $previous = $current ? Transaction::where('reference_type', Payroll::class)->where('reference_id', $current->id)->first() : null;
            $available = (float) $account->current_balance;
            if ($previous && (int) $previous->account_id === $accountId) $available += (float) $previous->amount;
            abort_if($available < $data['net_salary'], 422, 'Insufficient account balance for payroll payment.');
            $data['payment_method'] = (string) $accountId;
        }
        return $data;
    }

    private function postPayrollTransaction(Payroll $payroll, string $accountId, float $amount, string $date): void
    {
        $account = Account::active()->findOrFail((int) $accountId);
        abort_if((float) $account->current_balance < $amount, 422, 'Insufficient account balance for payroll payment.');
        TransactionService::createTransaction((int) $accountId, 'debit', $amount, 'Salary Paid to '.$payroll->employee?->name.' ('.$payroll->salary_month.' '.$payroll->salary_year.')', $date, $payroll);
    }

    private function reversePayrollTransaction(Payroll $payroll): void
    {
        $transaction = Transaction::where('reference_type', Payroll::class)->where('reference_id', $payroll->id)->first();
        if ($transaction) { Account::whereKey($transaction->account_id)->increment('current_balance', $transaction->amount); $transaction->delete(); }
    }

    private function findScoped(Request $request, string $resource, int $id): mixed
    {
        if ($resource === 'employees') return $this->applyBranchScope(Employee::query()->with(['department', 'branch', 'user.roles']), $request)->findOrFail($id);
        if ($resource === 'attendance') return EmployeeAttendance::whereHas('employee', fn ($q) => $this->applyBranchScope($q, $request))->with('employee')->findOrFail($id);
        if ($resource === 'payrolls') return Payroll::whereHas('employee', fn ($q) => $this->applyBranchScope($q, $request))->with('employee')->findOrFail($id);
        return Department::withCount('employees')->findOrFail($id);
    }

    private function authorizeResource(string $resource, int $action): void
    {
        abort_unless(isset(self::PERMISSIONS[$resource]), 404);
        $this->requirePermission(self::PERMISSIONS[$resource][$action]);
    }

    private function assertBranchAccess(Request $request, int $branchId): void
    {
        $user = $request->user(); $employee = Employee::where('user_id', $user->id)->where('status', 1)->first();
        if ($employee) abort_unless((int) $employee->branch_id === $branchId, 403, 'You cannot access this branch.');
        else abort_unless(app(CatalogController::class)->accessibleBranchesForUser($user)->whereKey($branchId)->exists(), 403, 'You cannot access this branch.');
    }

    private function guardRole(Request $request, string $role): void
    {
        abort_if($role === 'Super-Admin' && ! $request->user()->hasRole('Super-Admin'), 403, 'You cannot assign the Super-Admin role.');
    }

    private function uniqueUsername(string $email): string
    {
        $base = explode('@', $email)[0]; $username = $base; $index = 1;
        while (User::where('username', $username)->exists()) $username = $base.$index++;
        return $username;
    }

    private function hours(?string $in, ?string $out): ?float
    {
        if (! $in || ! $out) return null;
        $start = Carbon::parse($in); $end = Carbon::parse($out); if ($end->lessThan($start)) $end->addDay();
        return round($start->diffInMinutes($end) / 60, 2);
    }

    private function optionsData(Request $request): array
    {
        return [
            'employees' => $this->applyBranchScope(Employee::query(), $request)->where('status', 1)->orderBy('name')->get(['id', 'name', 'salary', 'branch_id', 'department_id']),
            'departments' => Department::where('status', 1)->orderBy('name')->get(['id', 'name']),
            'branches' => app(CatalogController::class)->accessibleBranchesForUser($request->user())->orderBy('name')->get(['id', 'name']),
            'accounts' => Account::active()->orderBy('name')->get(['id', 'name', 'current_balance']),
            'roles' => Role::query()->when(! $request->user()->hasRole('Super-Admin'), fn ($q) => $q->where('name', '!=', 'Super-Admin'))->orderBy('name')->get(['id', 'name']),
        ];
    }

    private function serialize(string $resource, mixed $model): array
    {
        return match ($resource) {
            'employees' => ['id' => $model->id, 'name' => $model->name, 'email' => $model->email, 'phone' => $model->phone, 'image' => $model->image ? ImageHelper::publicUrl($model->image, 'employees') : null, 'salary' => (float) $model->salary, 'joining_date' => $model->joining_date?->toDateString(), 'leaving_date' => $model->leaving_date?->toDateString(), 'max_commission' => (float) $model->max_commission, 'sale_target' => (float) $model->sale_target, 'department_id' => $model->department_id, 'department' => $model->department?->name, 'branch_id' => $model->branch_id, 'branch' => $model->branch?->name, 'user_id' => $model->user_id, 'role' => $model->user?->roles->first()?->name, 'status' => (bool) $model->status],
            'attendance' => ['id' => $model->id, 'employee_id' => $model->employee_id, 'employee' => $model->employee?->name, 'attendance_date' => $model->attendance_date?->toDateString(), 'check_in_time' => $model->check_in_time?->format('H:i'), 'check_out_time' => $model->check_out_time?->format('H:i'), 'total_hours' => $model->total_hours === null ? null : (float) $model->total_hours, 'note' => $model->note, 'status' => (bool) $model->status],
            'departments' => ['id' => $model->id, 'name' => $model->name, 'code' => $model->code, 'description' => $model->description, 'status' => (bool) $model->status, 'employees_count' => (int) ($model->employees_count ?? 0)],
            'payrolls' => ['id' => $model->id, 'employee_id' => $model->employee_id, 'employee' => $model->employee?->name, 'salary_month' => $model->salary_month, 'salary_year' => (int) $model->salary_year, 'basic_salary' => (float) $model->basic_salary, 'total_allowance' => (float) $model->total_allowance, 'total_deduction' => (float) $model->total_deduction, 'net_salary' => (float) $model->net_salary, 'payment_method' => $model->payment_method, 'transaction_id' => $model->transaction_id, 'payment_date' => $model->payment_date, 'status' => $model->status, 'note' => $model->note],
        };
    }
}
