<?php

namespace App\Services;

use App\Models\SmsLog;
use App\Models\SmsSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    public static function sendMessage(string $phone, string $message): bool
    {
        $phone = trim($phone);
        if ($phone === '') {
            return false;
        }

        try {
            $smsSetting = SmsSetting::where('is_active', 1)->first();

            if ($smsSetting && ! empty($smsSetting->api_url) && ! empty($smsSetting->api_key)) {
                $response = Http::when(app()->environment('local'), fn ($http) => $http->withoutVerifying())->get($smsSetting->api_url, [
                    'api_key' => $smsSetting->api_key,
                    'senderid' => $smsSetting->sender_id,
                    'number' => $phone,
                    'message' => $message,
                ]);

                $bodyLower = strtolower($response->body());

                if ($response->successful() && ! str_contains($bodyLower, 'error') && ! str_contains($bodyLower, 'fail') && ! str_contains($bodyLower, 'invalid')) {
                    SmsLog::create([
                        'phone_number' => $phone,
                        'message' => $message,
                        'status' => 'success',
                        'response' => 'Real API Success: '.$response->body(),
                    ]);

                    return true;
                }

                SmsLog::create([
                    'phone_number' => $phone,
                    'message' => $message,
                    'status' => 'failed',
                    'response' => 'Real API Error: '.$response->body(),
                ]);

                return false;
            }

            SmsLog::create([
                'phone_number' => $phone,
                'message' => $message,
                'status' => 'success',
                'response' => 'Mock sms sent (Testing Mode)',
            ]);

            return true;
        } catch (\Exception $e) {
            SmsLog::create([
                'phone_number' => $phone,
                'message' => $message,
                'status' => 'failed',
                'response' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
