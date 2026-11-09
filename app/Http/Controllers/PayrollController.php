<?php

namespace App\Http\Controllers;

use App\DataTables\PayrollDataTable;
use App\Models\Account;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeAttendance;
use App\Models\Payroll;
use App\Models\Transaction;
use App\Models\User;
use App\Services\TransactionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class PayrollController extends Controller
{
    public function index(PayrollDataTable $dataTable)
    {
        return $dataTable->render('backend.payroll.index');
    }

    public function create()
    {
        if (request()->ajax()) {
            $accounts = Account::where('is_active', 1)->get();
            $employees = Employee::where('status', 1)->get();
            $branches = Branch::where('status', 1)->orderBy('name')->get();
            $departments = Department::where('status', 1)->orderBy('name')->get();
            $employeeRoles = Role::query()
                ->orderBy('name')
                ->when(! Auth::user()?->hasRole('Super-Admin'), function ($query) {
                    $query->where('name', '!=', 'Super-Admin');
                })
                ->get();
            $branchManagers = User::orderBy('name')->get();

            return view('backend.payroll.ajax.create', compact(
                'employees',
                'accounts',
                'branches',
                'departments',
                'employeeRoles',
                'branchManagers'
            ));
        }

        return abort(404);
    }

    public function getEmployeeData(Request $request)
    {
        $employee_id = $request->employee_id;
        $month = $request->month;
        $year = $request->year;

        if (! $employee_id || ! $month || ! $year) {
            return response()->json(['success' => false, 'message' => 'Missing parameters.']);
        }

        $employee = Employee::find($employee_id);
        if (! $employee) {
            return response()->json(['success' => false, 'message' => 'Employee not found.']);
        }

        $baseSalary = $employee->salary ?? 0;

        // Month name to number
        $monthNum = date('m', strtotime("$month 1 $year"));
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $monthNum, $year);

        // Count present days from attendance
        $presentDays = EmployeeAttendance::where('employee_id', $employee_id)
            ->whereMonth('attendance_date', $monthNum)
            ->whereYear('attendance_date', $year)
            ->where('status', 1)
            ->count();

        // Count absent days from attendance
        $explicitAbsents = EmployeeAttendance::where('employee_id', $employee_id)
            ->whereMonth('attendance_date', $monthNum)
            ->whereYear('attendance_date', $year)
            ->where('status', 0)
            ->count();

        // No auto deduction as per user request
        $totalDeduction = 0;
        $netSalary = $baseSalary;

        return response()->json([
            'success' => true,
            'basic_salary' => round($baseSalary, 2),
            'total_deduction' => 0,
            'net_salary' => round($netSalary, 2),
            'present_days' => $presentDays,
            'absent_days' => $explicitAbsents,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'salary_month' => 'required',
            'salary_year' => 'required',
            'basic_salary' => 'required|numeric|min:0',
            'total_allowance' => 'nullable|numeric|min:0',
            'total_deduction' => 'nullable|numeric|min:0',
            'payment_method' => 'required',
            'status' => 'required',
            'payment_date' => 'nullable|date',
        ]);

        $exists = Payroll::where('employee_id', $request->employee_id)
            ->where('salary_month', $request->salary_month)
            ->where('salary_year', $request->salary_year)
            ->exists();

        if ($exists) {
            return redirect()->back()->with(['error' => 'Payroll for this employee already exists for this month!']);
        }

        try {
            DB::beginTransaction();

            $basic = $request->basic_salary;
            $allowance = $request->total_allowance ?? 0;
            $deduction = $request->total_deduction ?? 0;
            $net_salary = ($basic + $allowance) - $deduction;

            if ($request->status === 'paid') {
                $account = Account::find($request->payment_method);
                if ($account && $account->current_balance < $net_salary) {
                    throw new \Exception(
                        'Insufficient Balance in"'.$account->name.'" ! Available Balance is: '.number_format($account->current_balance, 2).', But Required is: '.number_format($net_salary, 2)
                    );
                }
            }

            $payroll = Payroll::create([
                'employee_id' => $request->employee_id,
                'salary_month' => $request->salary_month,
                'salary_year' => $request->salary_year,
                'basic_salary' => $basic,
                'total_allowance' => $allowance,
                'total_deduction' => $deduction,
                'net_salary' => $net_salary,
                'payment_method' => $request->payment_method,
                'transaction_id' => $request->transaction_id,
                'payment_date' => $request->payment_date,
                'status' => $request->status,
                'note' => $request->note,
            ]);

            if ($request->status === 'paid') {
                $empName = Employee::find($request->employee_id)->name ?? 'Employee';
                TransactionService::createTransaction(
                    $request->payment_method,
                    'debit',
                    $net_salary,
                    'Salary Paid to '.$empName.' ('.$request->salary_month.' '.$request->salary_year.')',
                    $request->payment_date ?? date('Y-m-d'),
                    $payroll
                );
            }

            DB::commit();

            return redirect()->route('payrolls.index')->with('success', 'Payroll created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->with('error', 'Failed: '.$e->getMessage());
        }
    }

    public function edit($id)
    {
        if (request()->ajax()) {
            $accounts = Account::where('is_active', 1)->get();
            $payroll = Payroll::findOrFail($id);
            $employees = Employee::where('status', 1)->get();
            $branches = Branch::where('status', 1)->orderBy('name')->get();
            $departments = Department::where('status', 1)->orderBy('name')->get();
            $employeeRoles = Role::query()
                ->orderBy('name')
                ->when(! Auth::user()?->hasRole('Super-Admin'), function ($query) {
                    $query->where('name', '!=', 'Super-Admin');
                })
                ->get();
            $branchManagers = User::orderBy('name')->get();

            if (! is_numeric($payroll->payment_method)) {
                $matchedAccount = $accounts->where('type', $payroll->payment_method)->first();
                if ($matchedAccount) {
                    $payroll->payment_method = $matchedAccount->id;
                }
            }

            return view('backend.payroll.ajax.edit', compact(
                'payroll',
                'employees',
                'accounts',
                'branches',
                'departments',
                'employeeRoles',
                'branchManagers'
            ));
        }

        return abort(404);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'salary_month' => 'required',
            'salary_year' => 'required',
            'basic_salary' => 'required|numeric|min:0',
            'total_allowance' => 'nullable|numeric|min:0',
            'total_deduction' => 'nullable|numeric|min:0',
            'payment_method' => 'required',
            'status' => 'required',
            'payment_date' => 'nullable|date',
        ]);

        $exists = Payroll::where('employee_id', $request->employee_id)
            ->where('salary_month', $request->salary_month)
            ->where('salary_year', $request->salary_year)
            ->where('id', '!=', $id)
            ->exists();

        if ($exists) {
            return redirect()->back()->with(['error' => 'Duplicate payroll entry detected!']);
        }

        try {
            DB::beginTransaction();
            $payroll = Payroll::findOrFail($id);

            $basic = $request->basic_salary;
            $allowance = $request->total_allowance ?? 0;
            $deduction = $request->total_deduction ?? 0;
            $net_salary = ($basic + $allowance) - $deduction;

            $oldTransaction = Transaction::where('reference_type', get_class($payroll))
                ->where('reference_id', $payroll->id)
                ->first();

            $oldAmount = 0;
            $oldAccountId = null;
            if ($oldTransaction) {
                $oldAmount = $oldTransaction->amount;
                $oldAccountId = $oldTransaction->account_id;
            }

            if ($request->status === 'paid') {
                $account = Account::find($request->payment_method);
                if ($account) {
                    $availableBalance = $account->current_balance;
                    if ($oldAccountId == $request->payment_method) {
                        $availableBalance += $oldAmount;
                    }
                    if ($availableBalance < $net_salary) {
                        throw new \Exception('Insufficient balance in '.$account->name.'! Available balance is: '.$availableBalance);
                    }
                }
            }

            if ($oldTransaction) {
                $oldAccount = Account::find($oldTransaction->account_id);
                if ($oldAccount) {
                    $oldAccount->increment('current_balance', $oldTransaction->amount);
                }
                $oldTransaction->delete();
            }

            $payroll->update([
                'employee_id' => $request->employee_id,
                'salary_month' => $request->salary_month,
                'salary_year' => $request->salary_year,
                'basic_salary' => $basic,
                'total_allowance' => $allowance,
                'total_deduction' => $deduction,
                'net_salary' => $net_salary,
                'payment_method' => $request->payment_method,
                'transaction_id' => $request->transaction_id,
                'payment_date' => $request->payment_date,
                'status' => $request->status,
                'note' => $request->note,
            ]);

            if ($request->status === 'paid') {
                $empName = Employee::find($request->employee_id)->name ?? 'Employee';
                TransactionService::createTransaction(
                    $request->payment_method,
                    'debit',
                    $net_salary,
                    'Salary Paid to '.$empName.' ('.$request->salary_month.' '.$request->salary_year.')',
                    $request->payment_date ?? date('Y-m-d'),
                    $payroll
                );
            }

            DB::commit();

            return redirect()->route('payrolls.index')->with('success', 'Payroll updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->with('error', 'Error: '.$e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $payroll = Payroll::findOrFail($id);

            $transaction = Transaction::where('reference_type', get_class($payroll))
                ->where('reference_id', $payroll->id)
                ->first();

            if ($transaction) {
                $account = Account::find($transaction->account_id);
                if ($account) {
                    $account->increment('current_balance', $transaction->amount);
                }
                $transaction->delete();
            }

            $payroll->delete();

            DB::commit();

            return redirect()->route('payrolls.index')->with('success', 'Payroll deleted and account balance restored successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->with('error', 'Something went wrong: '.$e->getMessage());
        }
    }
}
