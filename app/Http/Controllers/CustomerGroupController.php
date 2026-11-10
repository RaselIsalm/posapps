<?php

namespace App\Http\Controllers;

use App\DataTables\CustomerGroupDataTable;
use App\Models\CustomerGroup;
use Illuminate\Http\Request;

class CustomerGroupController extends Controller
{
    public function index(CustomerGroupDataTable $dataTable)
    {
        return $dataTable->render('backend.customer_group.index');
    }

    public function create()
    {
        if (request()->ajax()) {
            return view('backend.customer_group.ajax.create');
        } else {
            abort(404);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:customer_groups,name',
            'prefix_code' => 'required|unique:customer_groups,prefix_code',
        ]);

        $status = $request->status == 'on' ? 1 : 0;

        $customer_group = new CustomerGroup;
        $customer_group->name = $request->name;
        $customer_group->prefix_code = $request->prefix_code;
        $customer_group->description = $request->description;
        $customer_group->discount = $request->discount;
        $customer_group->type = $request->type;
        $customer_group->status = $status;
        $customer_group->save();

        activity_log('added customer group', 'group name: '.$customer_group->name);

        if ($request->ajax() || $request->wantsJson() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Customer Group Added Successfully',
                'customer_group' => [
                    'id' => $customer_group->id,
                    'name' => $customer_group->name,
                    'status' => (bool) $customer_group->status,
                ],
            ]);
        }

        return redirect()->route('customer-groups.index')->with('success', 'Customer Group Added Successfully');
    }

    public function edit($id)
    {
        if (request()->ajax()) {
            $customer_group = CustomerGroup::find($id);

            return view('backend.customer_group.ajax.edit', compact('customer_group'));
        } else {
            abort(404);
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|unique:customer_groups,name,'.$id,
            'prefix_code' => 'required|unique:customer_groups,prefix_code,'.$id,
        ]);

        $status = $request->status == 'on' ? 1 : 0;

        $customer_group = CustomerGroup::find($id);
        $customer_group->name = $request->name;
        $customer_group->prefix_code = $request->prefix_code;
        $customer_group->description = $request->description;
        $customer_group->discount = $request->discount;
        $customer_group->type = $request->type;
        $customer_group->status = $status;
        $customer_group->save();

        activity_log('updated customer group', 'group name: '.$customer_group->name);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Customer Group Updated Successfully'
            ]);
        }

        return redirect()->route('customer-groups.index')->with('success', 'Customer Group Updated Successfully');
    }

    public function destroy($id)
    {
        $customer_group = CustomerGroup::find($id);
        $groupName = $customer_group->name;
        $customer_group->delete();

        activity_log('deleted customer group', 'group name: '.$groupName);

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Customer Group Deleted Successfully'
            ]);
        }

        return redirect()->route('customer-groups.index')->with('success', 'Customer Group Deleted Successfully');
    }
}
