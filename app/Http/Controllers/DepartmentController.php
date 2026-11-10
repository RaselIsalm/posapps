<?php

namespace App\Http\Controllers;

use App\DataTables\DepartmentDataTable; // Import DataTable
use App\Models\Department;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DepartmentController extends Controller
{
    public function index(DepartmentDataTable $dataTable)
    {
        return $dataTable->render('backend.department.index');
    }

    public function create()
    {
        return redirect()->route('departments.index');
    }

    public function edit($id)
    {
        return redirect()->route('departments.index');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:departments,name',
            'code' => 'required|string|max:50|unique:departments,code',
            'description' => 'nullable|string',
        ]);

        $department = new Department;
        $department->name = $request->name;
        $department->code = $request->code;
        $department->description = $request->description;
        $department->status = $request->has('is_active') ? 1 : 0;
        $department->created_by = Auth::id();
        $department->save();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Department created successfully.',
                'department' => [
                    'id' => $department->id,
                    'name' => $department->name,
                ],
            ]);
        }

        return redirect()->route('departments.index')->with('success', 'Department Created Successfully');
    }


    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:departments,name,'.$id,
            'code' => 'required|string|max:50|unique:departments,code,'.$id,
            'description' => 'nullable|string',
        ]);

        $department = Department::findOrFail($id);
        $department->name = $request->name;
        $department->code = $request->code;
        $department->description = $request->description;
        $department->status = $request->has('is_active') ? 1 : 0;
        $department->updated_by = Auth::id();
        $department->save();

        return redirect()->route('departments.index')->with('success', 'Department Updated Successfully');
    }

    public function destroy($id)
    {
        $department = Department::findOrFail($id);

        if (Employee::where('department_id', $id)->exists()) {
            return redirect()->route('departments.index')->with('error', 'Cannot delete this department because it has employees assigned to it!');
        }

        $department->delete();

        return redirect()->route('departments.index')->with('success', 'Department Deleted Successfully');
    }
}
