<?php

namespace App\Http\Controllers;

use App\DataTables\CouponDataTable;
use App\Models\Coupon;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    public function index(CouponDataTable $dataTable)
    {
        return $dataTable->render('backend.coupons.index');
    }

    public function create()
    {
        return view('backend.coupons.ajax.create')->render();
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|unique:coupons,code',
            'type' => 'required|in:fixed,percent',
            'amount' => 'required|numeric|min:0',
            'min_purchase' => 'nullable|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'status' => 'required|boolean',
        ]);

        Coupon::create($request->all());

        return redirect()->route('coupons.index')->with('success', 'Coupon Created Successfully');
    }

    public function edit($id)
    {
        $coupon = Coupon::findOrFail($id);

        return view('backend.coupons.ajax.edit', compact('coupon'))->render();
    }

    public function update(Request $request, $id)
    {
        $coupon = Coupon::findOrFail($id);

        $request->validate([
            'code' => 'required|unique:coupons,code,'.$coupon->id,
            'type' => 'required|in:fixed,percent',
            'amount' => 'required|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $data = $request->all();
        $data['status'] = $request->has('status') ? 1 : 0;

        $coupon->update($data);

        return redirect()->route('coupons.index')->with('success', 'Coupon Updated Successfully');
    }

    public function show($id)
    {
        $coupon = Coupon::findOrFail($id);

        return view('backend.coupons.ajax.view', compact('coupon'))->render();
    }

    public function destroy($id)
    {
        try {
            $coupon = Coupon::findOrFail($id);
            $coupon->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Coupon Deleted Successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete coupon: ' . $e->getMessage()
            ], 500);
        }
    }
}
