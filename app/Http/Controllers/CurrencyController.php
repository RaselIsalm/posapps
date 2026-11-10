<?php

namespace App\Http\Controllers;

use App\DataTables\CurrencyDataTable; // Import DataTable
use App\Models\Currency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CurrencyController extends Controller
{
    public function index(CurrencyDataTable $dataTable)
    {
        return $dataTable->render('backend.currency.index');
    }

    public function create()
    {
        if (request()->ajax()) {
            return view('backend.currency.ajax.create');
        } else {
            abort(404);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:currencies,name',
            'code' => 'required|unique:currencies,code',
            'symbol' => 'required',
            'exchange_rate' => 'required|numeric',
        ]);

        $isActive = $request->has('is_active') ? 1 : 0;
        $isDefault = $request->has('is_default') ? 1 : 0;

        if ($isDefault) {
            Currency::query()->update(['is_default' => 0]);
        }

        $currency = Currency::create([
            'name' => $request->name,
            'code' => $request->code,
            'symbol' => $request->symbol,
            'exchange_rate' => $request->exchange_rate,
            'created_by' => Auth::user()->id,
            'is_active' => $isActive,
            'is_default' => $isDefault,
            'position' => $request->position,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Currency created successfully',
            'currency' => [
                'id' => $currency->id,
                'name' => $currency->name,
                'code' => $currency->code,
                'symbol' => $currency->symbol,
                'exchange_rate' => (float) $currency->exchange_rate,
                'is_active' => (bool) $currency->is_active,
                'is_default' => (bool) $currency->is_default,
                'position' => $currency->position,
            ],
        ]);
    }

    public function edit($id)
    {
        if (request()->ajax()) {
            $currency = Currency::findOrFail($id);

            return view('backend.currency.ajax.edit', compact('currency'));
        } else {
            abort(404);
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|unique:currencies,name,'.$id,
            'code' => 'required|unique:currencies,code,'.$id,
            'symbol' => 'required',
            'exchange_rate' => 'required|numeric',
        ]);

        $isActive = $request->has('is_active') ? 1 : 0;
        $isDefault = $request->has('is_default') ? 1 : 0;

        if ($isDefault) {
            Currency::where('id', '!=', $id)->update(['is_default' => 0]);
        }

        Currency::findOrFail($id)->update([
            'name' => $request->name,
            'code' => $request->code,
            'symbol' => $request->symbol,
            'exchange_rate' => $request->exchange_rate,
            'updated_by' => Auth::user()->id,
            'is_active' => $isActive,
            'is_default' => $isDefault,
            'position' => $request->position,
        ]);

        return response()->json(['success' => true, 'message' => 'Currency updated successfully']);
    }

    public function destroy($id)
    {
        Currency::findOrFail($id)->delete();

        return response()->json(['success' => true, 'message' => 'Currency deleted successfully']);
    }
}
