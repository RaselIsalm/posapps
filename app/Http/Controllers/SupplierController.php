<?php

namespace App\Http\Controllers;

use App\DataTables\SuppliersDataTable;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SupplierController extends Controller
{
    public function index(SuppliersDataTable $dataTable)
    {
        return $dataTable->render('backend.supplier.index');
    }

    public function create()
    {
        return view('backend.supplier.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|unique:suppliers,phone',
            'email' => 'nullable|email|max:255',
            'previous_due' => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $previousDue = $request->previous_due ?? 0;

            $supplier = new Supplier;
            $supplier->name = $request->name;
            $supplier->email = $request->email;
            $supplier->phone = $request->phone;
            $supplier->company_name = $request->company_name;
            $supplier->address = $request->address;
            $supplier->note = $request->note;
            $supplier->previous_due = $previousDue;
            $supplier->balance = $previousDue;
            $supplier->is_active = $request->has('is_active');
            $supplier->created_by = Auth::id();
            $supplier->save();

            DB::commit();

            activity_log('added supplier', 'supplier name: '.$supplier->name);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Supplier Created Successfully',
                    'supplier' => [
                        'id' => $supplier->id,
                        'name' => $supplier->name,
                        'is_active' => (bool) $supplier->is_active,
                    ],
                ]);
            }

            return redirect()->route('suppliers.index')->with('success', 'Supplier Created Successfully');
        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed: '.$e->getMessage(),
                ], 500);
            }

            return redirect()->back()->with('error', 'Failed: '.$e->getMessage());
        }
    }

    public function edit($id)
    {
        $supplier = Supplier::findOrFail($id);

        return view('backend.supplier.edit', compact('supplier'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|unique:suppliers,phone,'.$id,
            'email' => 'nullable|email|max:255',
            'previous_due' => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $supplier = Supplier::findOrFail($id);

            $newPreviousDue = $request->previous_due ?? 0;
            $difference = $newPreviousDue - $supplier->previous_due;
            $newBalance = $supplier->balance + $difference;

            if ($newBalance < 0) {
                throw new \Exception('Cannot update previous due! It will cause the total supplier balance to become negative.');
            }

            $supplier->name = $request->name;
            $supplier->email = $request->email;
            $supplier->phone = $request->phone;
            $supplier->company_name = $request->company_name;
            $supplier->address = $request->address;
            $supplier->note = $request->note;
            $supplier->previous_due = $newPreviousDue;
            $supplier->balance = $newBalance;
            $supplier->is_active = $request->has('is_active');
            $supplier->save();

            DB::commit();

            activity_log('updated supplier', 'supplier name: '.$supplier->name);

            return redirect()->route('suppliers.index')->with('success', 'Supplier Updated Successfully');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $supplier = Supplier::findOrFail($id);

        if ($supplier->purchases()->exists()) {
            if (request()->ajax()) {
                return response()->json(['success' => false, 'message' => 'Warning: This supplier cannot be deleted because they have associated purchase records'], 400);
            }
            return back()->with('error', 'Warning: This supplier cannot be deleted because they have associated purchase records');
        }

        if ($supplier->purchaseReturns()->exists()) {
            if (request()->ajax()) {
                return response()->json(['success' => false, 'message' => 'Warning: This supplier cannot be deleted because they have associated purchase return records'], 400);
            }
            return back()->with('error', 'Warning: This supplier cannot be deleted because they have associated purchase return records');
        }

        $supplierName = $supplier->name;
        $supplier->delete();

        activity_log('deleted supplier', 'supplier name: '.$supplierName);

        if (request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'Supplier Deleted Successfully']);
        }

        return redirect()->route('suppliers.index')->with('success', 'Supplier Deleted Successfully');
    }
}
