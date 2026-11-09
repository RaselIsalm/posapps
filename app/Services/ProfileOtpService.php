<?php

namespace App\Services;

use App\Mail\ProfileOtpMail;
use App\Models\ProfileOtp;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ProfileOtpService
{
    public const PURPOSE_PASSWORD = 'password';

    public const PURPOSE_EMAIL = 'email';

    public const PURPOSES = [
        self::PURPOSE_PASSWORD,
        self::PURPOSE_EMAIL,
    ];

    public function sendEmail(User $user, string $purpose, array $payload, string $toEmail): array
    {
        $this->assertNotThrottled($user, $purpose);
        $code = $this->store($user, $purpose, $payload);

        try {
            MailService::send($toEmail, new ProfileOtpMail($user, $code, $purpose));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Profile OTP email failed', [
                'user_id' => $user->id,
                'purpose' => $purpose,
                'to' => $toEmail,
                'error' => $e->getMessage(),
            ]);

            throw ValidationException::withMessages([
                'mail' => 'Could not send verification email. Please check your SMTP/mail settings and try again.',
            ]);
        }

        return [
            'message' => 'A verification code was sent to '.$toEmail.'.',
        ];
    }

    public function resend(User $user, string $purpose): array
    {
        $otp = ProfileOtp::query()
            ->where('user_id', $user->id)
            ->where('purpose', $purpose)
            ->latest('id')
            ->first();

        if (! $otp || empty($otp->payload)) {
            throw ValidationException::withMessages([
                'otp' => 'Nothing to resend. Start the change again.',
            ]);
        }

        $payload = $otp->payload;

        return match ($purpose) {
            self::PURPOSE_PASSWORD => $this->sendEmail($user, $purpose, $payload, $user->email),
            self::PURPOSE_EMAIL => $this->sendEmail($user, $purpose, $payload, (string) ($payload['email'] ?? '')),
            default => throw ValidationException::withMessages(['otp' => 'Invalid verification type.']),
        };
    }

    public function verify(User $user, string $purpose, string $code): array
    {
        $code = preg_replace('/\D+/', '', (string) $code);

        if (strlen($code) !== 6) {
            throw ValidationException::withMessages([
                'otp' => 'Enter the 6-digit code from your email.',
            ]);
        }

        return DB::transaction(function () use ($user, $purpose, $code) {
            $otp = ProfileOtp::query()
                ->where('user_id', $user->id)
                ->where('purpose', $purpose)
                ->latest('id')
                ->lockForUpdate()
                ->first();

            if (! $otp) {
                throw ValidationException::withMessages([
                    'otp' => 'No verification code found. Please request a new one.',
                ]);
            }

            if ($otp->expires_at->isPast()) {
                $otp->delete();
                throw ValidationException::withMessages([
                    'otp' => 'This code has expired. Please request a new one.',
                ]);
            }

            if ($otp->attempts >= 5) {
                $otp->delete();
                throw ValidationException::withMessages([
                    'otp' => 'Too many attempts. Please request a new code.',
                ]);
            }

            if (! Hash::check($code, (string) $otp->code)) {
                $otp->increment('attempts');
                throw ValidationException::withMessages([
                    'otp' => 'Invalid verification code. Email or password was not changed.',
                ]);
            }

            $payload = $otp->payload ?? [];
            if ($purpose === self::PURPOSE_EMAIL && empty($payload['email'])) {
                $otp->delete();
                throw ValidationException::withMessages([
                    'otp' => 'This code is no longer valid. Start the email change again.',
                ]);
            }
            if ($purpose === self::PURPOSE_PASSWORD && empty($payload['password'])) {
                $otp->delete();
                throw ValidationException::withMessages([
                    'otp' => 'This code is no longer valid. Start the password change again.',
                ]);
            }

            $otp->delete();

            return $payload;
        });
    }

    protected function assertNotThrottled(User $user, string $purpose): void
    {
        $latest = ProfileOtp::query()
            ->where('user_id', $user->id)
            ->where('purpose', $purpose)
            ->latest('id')
            ->first();

        if ($latest && $latest->created_at->gt(now()->subSeconds(60))) {
            throw ValidationException::withMessages([
                'otp' => 'Please wait 60 seconds before requesting another code.',
            ]);
        }
    }

    protected function store(User $user, string $purpose, array $payload): string
    {
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        ProfileOtp::query()->where('user_id', $user->id)->where('purpose', $purpose)->delete();

        ProfileOtp::create([
            'user_id' => $user->id,
            'purpose' => $purpose,
            'code' => Hash::make($code),
            'payload' => $payload,
            'expires_at' => now()->addMinutes(10),
        ]);

        return $code;
    }
}
