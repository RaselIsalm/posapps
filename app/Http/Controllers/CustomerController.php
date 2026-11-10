<?php

namespace App\Http\Controllers;

use App\DataTables\CustomerDataTable;
use App\Models\Customer;
use App\Models\CustomerGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerController extends Controller
{
    public function index(CustomerDataTable $dataTable)
    {
        return $dataTable->render('backend.customer.index');
    }

    public function create()
    {
        $customer_groups = CustomerGroup::where('status', 1)->get();

        return view('backend.customer.create', compact('customer_groups'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'nullable|string|max:255',
            'phone' => 'required|string|max:30|unique:customers,phone',
            'email' => 'nullable|email|max:255',
            'customer_group_id' => 'nullable|exists:customer_groups,id',
            'previous_due' => 'nullable|numeric|min:0',
        ]);

        $customer_group = CustomerGroup::find($request->customer_group_id);

        $prefix_code = 'GEN_CUST';
        if ($customer_group) {
            $prefix_code = str_replace(' ', '_', $customer_group->prefix_code);
        }

        $previousDue = $request->previous_due ?? 0;

        $customer = new Customer;
        $customer->name = $request->name ?? 'Customer - '.$request->phone;
        $customer->phone = $request->phone;
        $customer->email = $request->email;
        $customer->address = $request->address;
        $customer->note = $request->note;
        $customer->previous_due = $previousDue;
        $customer->balance = $previousDue;
        $customer->code = $prefix_code.uniqid();
        $customer->created_by = Auth::user()->id;
        $customer->customer_group_id = $request->customer_group_id;
        // Default active when status is omitted (legacy quick-adds). Respect explicit on/0/1.
        if ($request->has('status')) {
            $customer->status = in_array((string) $request->status, ['on', '1', 'true'], true) ? 1 : 0;
        } else {
            $customer->status = 1;
        }
        $customer->save();

        activity_log('added customer', 'customer name: '.$customer->name);

        if ($request->ajax() || $request->wantsJson() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Customer created successfully',
                'customer' => [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'phone' => $customer->phone,
                    'status' => (bool) $customer->status,
                    'reward_points' => $customer->reward_points ?? 0,
                    'group_discount' => $customer_group ? $customer_group->discount : 0,
                    'group_discount_type' => $customer_group ? $customer_group->type : 'percentage',
                ],
            ]);
        }

        return redirect()->route('customers.index')->with('success', 'Customer Created Successfully');
    }

    public function edit($id)
    {
        $customer = Customer::findOrFail($id);
        $customer_groups = CustomerGroup::where('status', 1)->get();

        return view('backend.customer.edit', compact('customer', 'customer_groups'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'nullable|string|max:255',
            'phone' => 'required|numeric|unique:customers,phone,'.$id,
            'customer_group_id' => 'nullable|exists:customer_groups,id',
            'previous_due' => 'nullable|numeric|min:0',
        ]);

        $customer = Customer::findOrFail($id);

        $newPreviousDue = $request->previous_due ?? 0;
        $difference = $newPreviousDue - $customer->previous_due;
        $newBalance = $customer->balance + $difference;

        if ($newBalance < 0) {
            return redirect()->back()->with('error', 'Cannot update previous due! It will cause the total customer balance to become negative.');
        }

        $customer->name = $request->name ?? 'Customer - '.$request->phone;
        $customer->phone = $request->phone;
        $customer->email = $request->email;
        $customer->address = $request->address;
        $customer->note = $request->note;
        $customer->previous_due = $newPreviousDue;
        $customer->balance = $newBalance;

        if ($request->customer_group_id != $customer->customer_group_id) {
            $customer_group = CustomerGroup::find($request->customer_group_id);
            $prefix_code = str_replace(' ', '_', $customer_group->prefix_code);
            $customer->code = $prefix_code.uniqid();
            $customer->customer_group_id = $request->customer_group_id;
        }

        $customer->status = $request->status == 'on' ? 1 : 0;
        $customer->save();

        activity_log('updated customer', 'customer name: '.$customer->name);

        return redirect()->route('customers.index')->with('success', 'Customer Updated Successfully');
    }

    public function destroy($id)
    {
        $customer = Customer::findOrFail($id);

        if ($customer->sales()->exists()) {
            if (request()->ajax()) {
                return response()->json(['success' => false, 'message' => 'Warning: This customer cannot be deleted because they have existing sales records!'], 400);
            }
            return redirect()->back()->with('error', 'Warning: This customer cannot be deleted because they have existing sales records!');
        }

        if ($customer->balance > 0) {
            if (request()->ajax()) {
                return response()->json(['success' => false, 'message' => 'Warning: Customer cannot be deleted! They have a due amount of '.$customer->balance], 400);
            }
            return redirect()->back()->with('error', 'Warning: Customer cannot be deleted! They have a due amount of '.$customer->balance);
        }

        $customerName = $customer->name;
        $customer->delete();

        activity_log('deleted customer', 'customer name: '.$customerName);

        if (request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'Customer Deleted Successfully']);
        }

        return redirect()->route('customers.index')->with('success', 'Customer Deleted Successfully');
    }
}
