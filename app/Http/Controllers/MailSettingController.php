<?php

namespace App\Http\Controllers;

use App\Models\MailSetting;
use App\Services\MailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MailSettingController extends Controller
{
    /**
     * Show the mail settings form.
     */
    public function index()
    {
        $mailSetting = MailSetting::first();

        return view('backend.settings.mail_settings', compact('mailSetting'));
    }

    /**
     * Store or update the mail settings.
     */
    public function store(Request $request)
    {
        $request->validate([
            'mail_host' => 'required|string|max:255',
            'mail_port' => 'required|integer|min:1|max:65535',
            'mail_username' => 'required|email|max:255',
            'mail_password' => 'required|string|max:255',
            'mail_encryption' => 'required|in:tls,ssl,null',
            'mail_from_address' => 'required|email|max:255',
            'mail_from_name' => 'required|string|max:255',
        ]);

        try {
            // If activating, ensure only one is active
            $isActive = $request->has('is_active') ? true : false;

            $mailSetting = MailSetting::first();

            $data = [
                'mail_mailer' => 'smtp',
                'mail_host' => $request->mail_host,
                'mail_port' => $request->mail_port,
                'mail_username' => $request->mail_username,
                'mail_encryption' => $request->mail_encryption,
                'mail_from_address' => $request->mail_from_address,
                'mail_from_name' => $request->mail_from_name,
                'is_active' => $isActive,
            ];

            // Only update password if a new one is provided (not the masked placeholder)
            if ($request->mail_password !== '••••••••••••••••') {
                $data['mail_password'] = $request->mail_password;
            }

            if ($mailSetting) {
                $mailSetting->update($data);
            } else {
                // For new entry, password is always required
                $data['mail_password'] = $request->mail_password;
                $mailSetting = MailSetting::create($data);
            }

            return back()->with('success', 'Mail settings saved successfully!');
        } catch (\Exception $e) {
            Log::error('MailSettingController::store failed', ['error' => $e->getMessage()]);

            return back()->withInput()->with('error', 'Failed to save: '.$e->getMessage());
        }
    }

    /**
     * Send a test email using the saved configuration.
     */
    public function testMail(Request $request)
    {
        $request->validate([
            'test_email' => 'required|email|max:255',
        ]);

        $result = MailService::sendTestMail($request->test_email);

        return response()->json($result);
    }
}
