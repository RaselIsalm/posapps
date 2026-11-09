<?php

namespace App\Http\Controllers;

use App\Models\RewardPointSetting;
use Illuminate\Http\Request;

class RewardPointSettingController extends Controller
{
    public function index()
    {
        $setting = RewardPointSetting::first();

        return view('backend.reward_point.index', compact('setting'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'is_active' => 'required|boolean',
            'min_purchase_amount' => 'required|numeric|min:0',
            'point_earning_rate' => 'required|numeric|min:1',
            'min_redeem_point' => 'required|integer|min:1',
            'point_redemption_rate' => 'required|numeric|min:0.01',
        ]);

        $setting = RewardPointSetting::first();

        if ($setting) {
            $setting->update($request->all());
        } else {
            RewardPointSetting::create($request->all());
        }

        return redirect()->back()->with('success', 'Reward Point Settings Updated Successfully!');
    }
}
