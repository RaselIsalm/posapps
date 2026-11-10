<?php

namespace App\Http\Controllers;

use App\DataTables\UnitDataTable; // Import DataTable
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UnitController extends Controller
{
    public function index(UnitDataTable $dataTable)
    {
        return $dataTable->render('backend.unit.index');
    }

    public function create()
    {
        if (request()->ajax()) {
            $baseUnits = Unit::active()->get();

            return view('backend.unit.ajax.create', compact('baseUnits'));
        }

        return abort(404);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:units,name',
            'short_code' => 'required',
            'operator' => 'nullable|in:*,/',
            'conversion_rate' => 'required|numeric'
        ]);

        $isActive = $request->has('is_active') ? 1 : 0;

        $unit = new Unit;
        $unit->name = $request->name;
        $unit->short_code = $request->short_code;
        $unit->base_unit_id = $request->base_unit ?: null;
        $unit->operator = $request->operator ?: '*';
        $unit->conversion_rate = $request->conversion_rate ?: 1;
        $unit->created_by = Auth::user()->id;
        $unit->is_active = $isActive;
        $unit->save();

        return response()->json([
            'success' => true,
            'message' => 'Unit Created Successfully',
            'unit' => [
                'id' => $unit->id,
                'name' => $unit->name,
            ],
        ]);
    }

    public function edit($id)
    {
        if (request()->ajax()) {
            $unit = Unit::findOrFail($id);
            $baseUnits = Unit::active()->where('id', '!=', $id)->get();

            return view('backend.unit.ajax.edit', compact('unit', 'baseUnits'));
        }

        return abort(404);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|unique:units,name,'.$id,
            'short_code' => 'required',
            'operator' => 'nullable|in:*,/',
            'conversion_rate' => 'required|numeric'
        ]);

        $isActive = $request->has('is_active') ? 1 : 0;

        $unit = Unit::findOrFail($id);
        $unit->name = $request->name;
        $unit->short_code = $request->short_code;
        $unit->base_unit_id = $request->base_unit ?: null;
        $unit->operator = $request->operator ?: '*';
        $unit->conversion_rate = $request->conversion_rate ?: 1;
        $unit->updated_by = Auth::user()->id;
        $unit->is_active = $isActive;
        $unit->save();

        return response()->json(['success' => true, 'message' => 'Unit Updated Successfully']);
    }

    public function destroy($id)
    {
        $unit = Unit::findOrFail($id);
        $unit->delete();

        return response()->json(['success' => true, 'message' => 'Unit deleted successfully']);
    }

    // Helper method if needed
    public function getChildUnits($id)
    {
        $units = Unit::where('base_unit_id', $id)->get();

        return response()->json($units);
    }
}
