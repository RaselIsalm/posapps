<?php

namespace App\Http\Controllers;

use App\Models\SmsSetting;
use App\Models\SmsSettingParam;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsSettingController extends Controller
{
    public function index()
    {
        $settings = SmsSetting::with('params')->latest()->paginate(10);

        return view('backend.sms.index', compact('settings'));
    }

    public function create()
    {
        return view('backend.sms.ajax.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'provider_name' => 'required|string|max:255',
            'api_url' => 'required|url|max:500',
            'balance_api_url' => 'nullable|url|max:500',
            'params' => 'nullable|array',
            'params.*.key' => 'required|string|max:100',
            'params.*.value' => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            if ($request->has('is_active')) {
                SmsSetting::where('is_active', 1)->update(['is_active' => 0]);
            }

            $setting = SmsSetting::create([
                'provider_name' => $request->provider_name,
                'api_url' => $request->api_url,
                'balance_api_url' => $request->balance_api_url,
                'is_active' => $request->has('is_active') ? 1 : 0,
            ]);

            if ($request->has('params')) {
                foreach ($request->params as $param) {
                    if (! empty($param['key'])) {
                        SmsSettingParam::create([
                            'sms_setting_id' => $setting->id,
                            'param_key' => $param['key'],
                            'param_value' => $param['value'] ?? '',
                        ]);
                    }
                }
            }

            DB::commit();

            return redirect()->route('sms_settings.index')
                ->with('success', 'SMS Gateway added successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withInput()
                ->with('error', 'Something went wrong: '.$e->getMessage());
        }
    }

    public function edit($id)
    {
        $setting = SmsSetting::with('params')->findOrFail($id);

        return view('backend.sms.ajax.edit', compact('setting'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'provider_name' => 'required|string|max:255',
            'api_url' => 'required|url|max:500',
            'balance_api_url' => 'nullable|url|max:500',
            'params' => 'nullable|array',
            'params.*.key' => 'required|string|max:100',
            'params.*.value' => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            $setting = SmsSetting::findOrFail($id);
            $isActive = $request->has('is_active') ? 1 : 0;

            if ($isActive) {
                SmsSetting::where('id', '!=', $id)->update(['is_active' => 0]);
            }

            $setting->update([
                'provider_name' => $request->provider_name,
                'api_url' => $request->api_url,
                'balance_api_url' => $request->balance_api_url,
                'is_active' => $isActive,
            ]);

            $setting->params()->delete();

            if ($request->has('params')) {
                foreach ($request->params as $param) {
                    if (! empty($param['key'])) {
                        SmsSettingParam::create([
                            'sms_setting_id' => $setting->id,
                            'param_key' => $param['key'],
                            'param_value' => $param['value'] ?? '',
                        ]);
                    }
                }
            }

            DB::commit();

            return redirect()->route('sms_settings.index')
                ->with('success', 'SMS Gateway updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withInput()
                ->with('error', 'Something went wrong: '.$e->getMessage());
        }
    }

    public function destroy($id)
    {
        $setting = SmsSetting::findOrFail($id);

        if ($setting->is_active) {
            return redirect()->back()
                ->with('error', 'Cannot delete the active gateway. Please activate another gateway first.');
        }

        $setting->params()->delete();
        $setting->delete();

        return redirect()->back()->with('success', 'SMS Gateway deleted successfully.');
    }

    public function testSms(Request $request, $id)
    {
        $request->validate(['test_phone' => 'required|string|max:20']);

        $setting = SmsSetting::with('params')->findOrFail($id);
        $params = $setting->params_array;

        try {
            $queryParams = array_merge($params, [
                'number' => $request->test_phone,
                'message' => 'Test SMS from OMS. Your gateway is working correctly!',
            ]);

            $response = Http::timeout(10)->when(app()->environment('local'), fn ($http) => $http->withoutVerifying())->get($setting->api_url, $queryParams);

            return response()->json([
                'success' => $response->successful(),
                'message' => $response->successful()
                    ? 'Test SMS sent successfully!'
                    : 'Gateway error: '.$response->body(),
                'response' => $response->body(),
            ]);
        } catch (\Exception $e) {
            Log::error('SMS Test Failed: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Connection failed. Please check your Gateway URL, API Key, and internet connection.',
            ]);
        }
    }

    public function checkBalance($id)
    {
        $setting = SmsSetting::with('params')->findOrFail($id);

        if (! $setting->balance_api_url) {
            return response()->json([
                'success' => false,
                'message' => 'No balance API URL configured.',
            ]);
        }

        try {
            $params = $setting->params_array;
            $response = Http::timeout(10)->when(app()->environment('local'), fn ($http) => $http->withoutVerifying())->get($setting->balance_api_url, $params);

            return response()->json([
                'success' => true,
                'balance' => $response->body(),
            ]);
        } catch (\Exception $e) {
            \Log::error('SMS Balance Check Failed: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to connect to the SMS Gateway API. Please check your URL and internet connection.',
            ]);
        }
    }
}
