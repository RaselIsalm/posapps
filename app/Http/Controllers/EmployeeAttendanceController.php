<?php

namespace App\Http\Controllers;

use App\DataTables\EmployeeAttendanceDataTable;
use App\Models\Account;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeAttendance;
use App\Models\Payroll;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;

class EmployeeAttendanceController extends Controller
{
    public function index(EmployeeAttendanceDataTable $dataTable)
    {
        $branches = Branch::where('status', 1)->orderBy('name')->get();
        $departments = Department::where('status', 1)->orderBy('name')->get();
        $employeeRoles = Role::query()
            ->orderBy('name')
            ->when(! Auth::user()?->hasRole('Super-Admin'), function ($query) {
                $query->where('name', '!=', 'Super-Admin');
            })
            ->get();
        $branchManagers = User::orderBy('name')->get(['id', 'name']);
        $accounts = Account::where('is_active', 1)->orderBy('name')->get(['id', 'name']);

        return $dataTable->render('backend.employee_attendance.index', compact(
            'branches',
            'departments',
            'employeeRoles',
            'branchManagers',
            'accounts'
        ));
    }

    public function create()
    {
        $employees = Employee::where('status', 1)->get();

        return view('backend.employee_attendance.ajax.create', compact('employees'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'attendance_date' => 'required|date',
            'check_in_time' => 'nullable',
            'check_out_time' => 'nullable',
            'note' => 'nullable|string',
        ]);

        $exists = EmployeeAttendance::where('employee_id', $request->employee_id)
            ->where('attendance_date', $request->attendance_date)
            ->exists();

        if ($exists) {
            return response()->json(['success' => false, 'message' => 'Attendance for this date already exists!'], 400);
        }

        $status = $request->has('status') ? 1 : 0;

        $totalHours = null;
        if ($request->check_in_time && $request->check_out_time) {
            $in = Carbon::parse($request->check_in_time);
            $out = Carbon::parse($request->check_out_time);
            
            if ($out->lessThan($in)) {
                $out->addDay();
            }
            
            $totalHours = $in->diffInMinutes($out) / 60;
        }

        EmployeeAttendance::create([
            'employee_id' => $request->employee_id,
            'attendance_date' => $request->attendance_date,
            'check_in_time' => $request->check_in_time,
            'check_out_time' => $request->check_out_time,
            'total_hours' => $totalHours,
            'note' => $request->note,
            'status' => $status,
            'created_by' => Auth::id(),
        ]);

        return response()->json(['success' => true, 'message' => 'Attendance recorded successfully.']);
    }

    public function edit($id)
    {
        $attendance = EmployeeAttendance::findOrFail($id);
        $employees = Employee::where('status', 1)->get();

        return view('backend.employee_attendance.ajax.edit', compact('attendance', 'employees'));
    }

    public function update(Request $request, $id)
    {
        $attendance = EmployeeAttendance::findOrFail($id);

        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'attendance_date' => 'required|date',
            'check_in_time' => 'nullable',
            'check_out_time' => 'nullable',
            'note' => 'nullable|string',
        ]);

        $exists = EmployeeAttendance::where('employee_id', $request->employee_id)
            ->where('attendance_date', $request->attendance_date)
            ->where('id', '!=', $id)
            ->exists();

        if ($exists) {
            return response()->json(['success' => false, 'message' => 'Attendance for this date already exists!'], 400);
        }

        $status = $request->has('status') ? 1 : 0;

        $totalHours = null;
        if ($request->check_in_time && $request->check_out_time) {
            $in = Carbon::parse($request->check_in_time);
            $out = Carbon::parse($request->check_out_time);
            
            if ($out->lessThan($in)) {
                $out->addDay();
            }
            
            $totalHours = $in->diffInMinutes($out) / 60;
        }

        $attendance->update([
            'employee_id' => $request->employee_id,
            'attendance_date' => $request->attendance_date,
            'check_in_time' => $request->check_in_time,
            'check_out_time' => $request->check_out_time,
            'total_hours' => $totalHours,
            'note' => $request->note,
            'status' => $status,
            'updated_by' => Auth::id(),
        ]);

        return response()->json(['success' => true, 'message' => 'Attendance updated successfully.']);
    }

    public function destroy($id)
    {
        $attendance = EmployeeAttendance::findOrFail($id);

        $attendanceMonth = Carbon::parse($attendance->attendance_date)->format('F');
        $attendanceYear = Carbon::parse($attendance->attendance_date)->format('Y');

        $payrollExists = Payroll::where('employee_id', $attendance->employee_id)
            ->where('salary_month', $attendanceMonth)
            ->where('salary_year', $attendanceYear)
            ->exists();

        if ($payrollExists) {
            $monthLabel = Carbon::parse($attendance->attendance_date)->locale(app()->getLocale())->translatedFormat('F');

            return redirect()->back()
                ->with('error', __('Warning: Cannot delete this attendance because the payroll for :month :year has already been generated!', [
                    'month' => $monthLabel,
                    'year' => $attendanceYear,
                ]));
        }

        $attendance->delete();

        return redirect()->route('attendance.index')->with('success', 'Attendance record deleted successfully.');
    }
}
