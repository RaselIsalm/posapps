<?php

namespace App\Http\Controllers;

use App\Helpers\ImageHelper;
use App\Services\ProfileOtpService;
use App\Services\AccountDeletionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function __construct(
        protected ProfileOtpService $otpService,
        protected AccountDeletionService $accountDeletionService,
    ) {}

    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $request->merge([
            'phone' => $request->filled('phone') ? trim((string) $request->input('phone')) : null,
        ]);

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:20', Rule::unique('users', 'phone')->ignore($user->id)],
            'designation' => ['nullable', 'string', 'max:120'],
        ];

        if ($request->hasFile('avatar') && $request->file('avatar')->getError() === UPLOAD_ERR_OK) {
            $rules['avatar'] = ['file', 'mimes:jpg,jpeg,png,webp', 'max:2048'];
        }

        $validated = $request->validate($rules);

        if ($request->input('remove_avatar') === '1' && ! $request->hasFile('avatar')) {
            ImageHelper::deleteImage($user->avatar, 'users');
            $user->avatar = null;
        } else {
            $this->storeAvatarIfPresent($request, $user);
        }

        $user->name = $validated['name'];
        $user->designation = $validated['designation'] ?? null;
        $user->phone = $validated['phone'] ?? null;
        $user->save();

        $newEmail = strtolower(trim((string) $validated['email']));
        $currentEmail = strtolower(trim((string) $user->email));

        if ($newEmail !== $currentEmail) {
            $result = $this->otpService->sendEmail(
                $user,
                ProfileOtpService::PURPOSE_EMAIL,
                ['email' => $validated['email']],
                $validated['email']
            );

            return Redirect::route('profile.edit')
                ->with('status', 'otp-sent')
                ->with('otp_purpose', ProfileOtpService::PURPOSE_EMAIL)
                ->with('otp_message', $result['message'].' Your email will change only after you enter the correct code.');
        }

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    public function requestPasswordOtp(Request $request): RedirectResponse
    {
        $validated = $request->validateWithBag('updatePassword', [
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $result = $this->otpService->sendEmail(
            $request->user(),
            ProfileOtpService::PURPOSE_PASSWORD,
            ['password' => $validated['password']],
            $request->user()->email
        );

        return Redirect::route('profile.edit')
            ->with('status', 'otp-sent')
            ->with('otp_purpose', ProfileOtpService::PURPOSE_PASSWORD)
            ->with('otp_message', $result['message'].' Your password will change only after you enter the correct code.');
    }

    public function verifyOtp(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'otp' => ['required', 'digits:6'],
            'purpose' => ['required', Rule::in(ProfileOtpService::PURPOSES)],
        ]);

        $user = $request->user();
        $payload = $this->otpService->verify($user, $validated['purpose'], $validated['otp']);

        if ($validated['purpose'] === ProfileOtpService::PURPOSE_PASSWORD) {
            $password = $payload['password'] ?? null;
            if (! is_string($password) || $password === '') {
                throw ValidationException::withMessages([
                    'otp' => 'Invalid verification. Password was not changed.',
                ]);
            }

            $user->password = $password;
            $user->save();

            return Redirect::route('profile.edit')->with('status', 'password-updated');
        }

        if ($validated['purpose'] === ProfileOtpService::PURPOSE_EMAIL) {
            $email = trim((string) ($payload['email'] ?? ''));
            if ($email === '') {
                throw ValidationException::withMessages([
                    'otp' => 'Invalid verification. Email was not changed.',
                ]);
            }

            $user->email = $email;
            $user->email_verified_at = now();
            $user->save();

            return Redirect::route('profile.edit')->with('status', 'profile-updated');
        }

        throw ValidationException::withMessages([
            'otp' => 'Invalid verification type. Nothing was changed.',
        ]);
    }

    public function resendOtp(Request $request): RedirectResponse
    {
        $purpose = $request->validate([
            'purpose' => ['required', Rule::in(ProfileOtpService::PURPOSES)],
        ])['purpose'];

        $result = $this->otpService->resend($request->user(), $purpose);

        return Redirect::route('profile.edit')
            ->with('status', 'otp-sent')
            ->with('otp_purpose', $purpose)
            ->with('otp_message', $result['message']);
    }

    public function destroy(Request $request): RedirectResponse
    {
        if ($request->user()->hasRole('Super-Admin')) {
            abort(403, 'Super Admin account cannot be deleted.');
        }

        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        $this->accountDeletionService->requestFromAuthenticatedUser($user);

        return Redirect::route('profile.edit')->with('status', 'delete-requested');
    }

    protected function storeAvatarIfPresent(Request $request, $user): void
    {
        if (! $request->hasFile('avatar')) {
            return;
        }

        $file = $request->file('avatar');
        if ($file->getError() !== UPLOAD_ERR_OK) {
            return;
        }

        $ext = strtolower($file->getClientOriginalExtension() ?: 'jpg');
        if (! in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)) {
            $ext = 'jpg';
        }

        $name = 'user_'.$user->id.'_'.Str::random(16).'.'.$ext;
        $source = $file->getPathname();
        $contents = ($source !== '' && is_readable($source)) ? file_get_contents($source) : false;
        if ($contents === false || $contents === '') {
            return;
        }

        ImageHelper::deleteImage($user->avatar, 'users');
        \Illuminate\Support\Facades\Storage::disk('public')->makeDirectory('users');
        \Illuminate\Support\Facades\Storage::disk('public')->put('users/'.$name, $contents);
        $user->avatar = $name;
    }
}
