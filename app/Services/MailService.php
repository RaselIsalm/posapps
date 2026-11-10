<?php

namespace App\Services;

use App\Mail\TestMail;
use App\Models\MailSetting;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class MailService
{
    /**
     * Configure Laravel's mailer at runtime using the saved mail settings.
     * Returns true if configured successfully, false if no active setting found.
     */
    public static function configureMailer(): bool
    {
        $setting = MailSetting::getActiveSetting();

        if (! $setting) {
            return false;
        }

        // Override Laravel's mail config at runtime
        config($setting->buildMailerConfig());

        // Purge the cached SMTP mailer instance so Laravel rebuilds it with new config
        Mail::purge('smtp');

        return true;
    }

    /**
     * Send a mailable using the configured mail settings.
     * Automatically configures the mailer before sending.
     *
     * @param  string  $to  Recipient email address
     * @param  Mailable  $mailable  The mailable instance
     *
     * @throws \Exception
     */
    public static function send(string $to, $mailable): bool
    {
        if (! static::configureMailer()) {
            throw new \Exception('Mail is not configured. Please set up mail settings first.');
        }

        try {
            Mail::to($to)->send($mailable);

            return true;
        } catch (\Exception $e) {
            Log::error('MailService::send failed', [
                'to' => $to,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Test the mail configuration by sending a test email.
     *
     * @param  string  $to  Test recipient email
     * @return array ['success' => bool, 'message' => string]
     */
    public static function sendTestMail(string $to): array
    {
        try {
            static::send($to, new TestMail);

            return [
                'success' => true,
                'message' => 'Test email sent successfully! Please check '.$to.'.',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to send email: '.$e->getMessage(),
            ];
        }
    }
}
