<?php

namespace App\Http\Controllers;

use App\Models\Courier;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CourierSettingController extends Controller
{
    public function index()
    {
        $couriers = Courier::latest()->paginate(10);

        return view('backend.couriers.index', compact('couriers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:50',
            'address' => 'required|string',
            'base_url' => 'nullable|url|max:500',
            'params' => 'nullable|array',
            'default_charge' => 'nullable|numeric|min:0',
        ]);

        try {
            $apiConfig = [
                'base_url' => $request->base_url,
                'params' => [],
            ];

            if ($request->has('params')) {
                foreach ($request->params as $param) {
                    if (! empty($param['key'])) {
                        $apiConfig['params'][] = [
                            'key' => $param['key'],
                            'value' => $param['value'] ?? '',
                        ];
                    }
                }
            }

            $courier = Courier::create([
                'name' => $request->name,
                'code' => Str::slug($request->name),
                'phone' => $request->phone,
                'address' => $request->address,
                'has_api' => 1,
                'api_config' => $apiConfig,
                'default_charge' => $request->input('default_charge', 0) ?? 0,
                'is_active' => $request->has('is_active') ? 1 : 0,
            ]);

            if ($request->ajax() || $request->wantsJson() || $request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Courier added successfully.',
                    'courier' => [
                        'id' => $courier->id,
                        'name' => $courier->name,
                        'default_charge' => (float) $courier->default_charge,
                        'is_active' => (bool) $courier->is_active,
                    ],
                ]);
            }

            return redirect()->route('courier_settings.index')
                ->with('success', 'Courier added successfully.');
        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Something went wrong: '.$e->getMessage(),
                ], 500);
            }

            return back()->withInput()
                ->with('error', 'Something went wrong: '.$e->getMessage());
        }
    }

    public function create()
    {
        return view('backend.couriers.ajax.create');
    }

    public function edit($id)
    {
        $courier = Courier::findOrFail($id);

        return view('backend.couriers.ajax.edit', compact('courier'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:50',
            'address' => 'required|string',
            'base_url' => 'nullable|url|max:500',
            'params' => 'nullable|array',
        ]);

        try {
            $courier = Courier::findOrFail($id);

            $apiConfig = [
                'base_url' => $request->base_url,
                'params' => [],
            ];

            if ($request->has('params')) {
                foreach ($request->params as $param) {
                    if (! empty($param['key'])) {
                        $apiConfig['params'][] = [
                            'key' => $param['key'],
                            'value' => $param['value'] ?? '',
                        ];
                    }
                }
            }

            $courier->update([
                'name' => $request->name,
                'code' => Str::slug($request->name),
                'phone' => $request->phone,
                'address' => $request->address,
                'api_config' => $apiConfig,
                'is_active' => $request->has('is_active') ? 1 : 0,
            ]);

            return redirect()->route('courier_settings.index')
                ->with('success', 'Courier updated successfully.');
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Something went wrong: '.$e->getMessage());
        }
    }

    public function destroy($id)
    {
        $courier = Courier::findOrFail($id);

        if ($courier->deliveries()->exists() ?? false) {
            return redirect()->back()
                ->with('error', 'Cannot delete courier as it is linked to existing deliveries.');
        }

        $courier->delete();

        return redirect()->back()->with('success', 'Courier deleted successfully.');
    }
}
