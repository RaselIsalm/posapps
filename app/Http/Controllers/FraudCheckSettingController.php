<?php

namespace App\Http\Controllers;

use App\Models\FraudCheckSetting;
use Illuminate\Http\Request;

class FraudCheckSettingController extends Controller
{
    public function index()
    {
        $setting = FraudCheckSetting::first();

        return view('backend.fraud_check.setting', compact('setting'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'api_key' => 'nullable|string|max:255',
            'api_url' => 'nullable|url|max:255',
        ]);

        $setting = FraudCheckSetting::first();

        if ($setting) {
            $setting->update([
                'api_key' => $request->api_key,
                'api_url' => $request->api_url,
                'is_active' => $request->has('is_active'),
            ]);
        } else {
            FraudCheckSetting::create([
                'api_key' => $request->api_key,
                'api_url' => $request->api_url,
                'is_active' => $request->has('is_active'),
            ]);
        }

        return redirect()->back()->with('success', 'Fraud Check API settings updated successfully.');
    }
}
