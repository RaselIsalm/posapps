<?php

namespace App\Services;

use App\Helpers\ImageHelper;
use App\Models\AccountDeletionRequest;
use App\Models\User;
use App\Notifications\AccountDeletionCompleted;
use App\Notifications\AccountDeletionRequested;
use App\Notifications\AccountDeletionVerification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class AccountDeletionService
{
    public function requestFromEmail(string $email): void
    {
        $user = User::query()->whereRaw('LOWER(email) = ?', [$email])->first();

        if (! $user || $user->hasRole('Super-Admin')) {
            return;
        }

        if (AccountDeletionRequest::query()
            ->where('user_id', $user->id)
            ->where('status', AccountDeletionRequest::STATUS_PENDING)
            ->exists()) {
            return;
        }

        $token = Str::random(64);
        $request = DB::transaction(function () use ($user, $token) {
            AccountDeletionRequest::query()
                ->where('user_id', $user->id)
                ->where('status', AccountDeletionRequest::STATUS_VERIFICATION_PENDING)
                ->update([
                    'status' => 'expired',
                    'token_hash' => null,
                    'expires_at' => now(),
                ]);

            return AccountDeletionRequest::create([
                'user_id' => $user->id,
                'email' => $user->email,
                'token_hash' => hash('sha256', $token),
                'status' => AccountDeletionRequest::STATUS_VERIFICATION_PENDING,
                'expires_at' => now()->addMinutes(60),
            ]);
        });

        $url = route('account-deletion.verify.form', [
            'deletionRequest' => $request->id,
            'token' => $token,
        ]);

        try {
            Notification::route('mail', $user->email)
                ->notify(new AccountDeletionVerification($url));
        } catch (Throwable $exception) {
            $request->delete();
            Log::warning('Could not send ONEPOS account deletion verification email.', [
                'exception' => $exception::class,
            ]);

            throw $exception;
        }
    }

    public function requestFromAuthenticatedUser(User $user): AccountDeletionRequest
    {
        if ($user->hasRole('Super-Admin')) {
            throw ValidationException::withMessages([
                'password' => 'A Super Admin account cannot be deleted through this request flow.',
            ]);
        }

        return DB::transaction(function () use ($user) {
            $pendingRequest = AccountDeletionRequest::query()
                ->where('user_id', $user->id)
                ->where('status', AccountDeletionRequest::STATUS_PENDING)
                ->whereNotNull('verified_at')
                ->first();
            if ($pendingRequest) {
                return $pendingRequest;
            }

            AccountDeletionRequest::query()
                ->where('user_id', $user->id)
                ->where('status', AccountDeletionRequest::STATUS_VERIFICATION_PENDING)
                ->update([
                    'status' => 'expired',
                    'token_hash' => null,
                    'expires_at' => now(),
                ]);

            $request = AccountDeletionRequest::create([
                'user_id' => $user->id,
                'email' => $user->email,
                'status' => AccountDeletionRequest::STATUS_PENDING,
                'verified_at' => now(),
            ]);

            $this->notifyAdministrators($request);

            return $request;
        });
    }

    public function verificationIsValid(AccountDeletionRequest $request, string $token): bool
    {
        return $request->status === AccountDeletionRequest::STATUS_VERIFICATION_PENDING
            && $request->token_hash !== null
            && $request->expires_at?->isFuture() === true
            && hash_equals($request->token_hash, hash('sha256', $token));
    }

    public function verify(AccountDeletionRequest $request, string $token): bool
    {
        return DB::transaction(function () use ($request, $token): bool {
            $locked = AccountDeletionRequest::query()->lockForUpdate()->find($request->id);
            if (! $locked || ! $this->verificationIsValid($locked, $token)) {
                return false;
            }

            $locked->forceFill([
                'status' => AccountDeletionRequest::STATUS_PENDING,
                'token_hash' => null,
                'expires_at' => null,
                'verified_at' => now(),
            ])->save();

            $this->notifyAdministrators($locked);

            return true;
        });
    }

    public function approve(AccountDeletionRequest $request, User $reviewer): void
    {
        $completionData = DB::transaction(function () use ($request, $reviewer): array {
            $locked = AccountDeletionRequest::query()->lockForUpdate()->findOrFail($request->id);
            if ($locked->status !== AccountDeletionRequest::STATUS_PENDING || ! $locked->verified_at) {
                throw ValidationException::withMessages([
                    'request' => 'Only verified, pending requests can be approved.',
                ]);
            }

            $user = $locked->user;
            if (! $user && $locked->email) {
                $user = User::query()
                    ->whereRaw('LOWER(email) = ?', [mb_strtolower($locked->email)])
                    ->lockForUpdate()
                    ->first();
            }

            if ($user?->hasRole('Super-Admin')) {
                throw ValidationException::withMessages([
                    'request' => 'A Super Admin account cannot be removed by this workflow.',
                ]);
            }

            $email = $locked->email;
            $filesToDelete = [];
            if ($user) {
                $filesToDelete = $this->erasePersonalAccountData($user);
                $locked->user_id = $user->id;
            }

            if ($locked->user_id !== null) {
                AccountDeletionRequest::query()
                    ->where('user_id', $locked->user_id)
                    ->where('id', '!=', $locked->id)
                    ->update([
                        'email' => null,
                        'token_hash' => null,
                        'expires_at' => null,
                        'status' => 'expired',
                    ]);
            }

            $locked->forceFill([
                'email' => null,
                'token_hash' => null,
                'expires_at' => null,
                'status' => AccountDeletionRequest::STATUS_COMPLETED,
                'reviewed_by' => $reviewer->id,
                'reviewed_at' => now(),
                'completed_at' => now(),
            ])->save();

            return ['email' => $email, 'files' => $filesToDelete];
        });

        foreach ($completionData['files'] as $file) {
            try {
                ImageHelper::deleteImage($file['name'], $file['directory']);
            } catch (Throwable $exception) {
                Log::warning('Could not remove a file during ONEPOS account deletion.', [
                    'exception' => $exception::class,
                ]);
            }
        }

        $emailToNotify = $completionData['email'];
        if ($emailToNotify) {
            try {
                Notification::route('mail', $emailToNotify)->notify(new AccountDeletionCompleted);
            } catch (Throwable $exception) {
                Log::warning('Could not send ONEPOS account deletion completion email.', [
                    'exception' => $exception::class,
                ]);
            }
        }
    }

    public function reject(AccountDeletionRequest $request, User $reviewer): void
    {
        DB::transaction(function () use ($request, $reviewer) {
            $locked = AccountDeletionRequest::query()->lockForUpdate()->findOrFail($request->id);
            if ($locked->status !== AccountDeletionRequest::STATUS_PENDING || ! $locked->verified_at) {
                throw ValidationException::withMessages([
                    'request' => 'Only verified, pending requests can be rejected.',
                ]);
            }

            $locked->forceFill([
                'email' => null,
                'token_hash' => null,
                'expires_at' => null,
                'status' => AccountDeletionRequest::STATUS_REJECTED,
                'reviewed_by' => $reviewer->id,
                'reviewed_at' => now(),
            ])->save();
        });
    }

    private function notifyAdministrators(AccountDeletionRequest $request): void
    {
        $administrators = User::query()
            ->whereHas('roles', fn ($query) => $query
                ->where('name', 'Super-Admin')
                ->where('guard_name', 'web'))
            ->get();
        if ($administrators->isNotEmpty()) {
            Notification::send($administrators, new AccountDeletionRequested($request->id));
        }
    }

    private function erasePersonalAccountData(User $user): array
    {
        $userId = $user->id;
        $originalEmail = $user->email;
        $avatar = $user->avatar;
        $filesToDelete = [];

        if ($avatar) {
            $filesToDelete[] = ['name' => $avatar, 'directory' => 'users'];
        }

        $employees = DB::table('employees')->where('user_id', $userId)->get(['id', 'image']);
        foreach ($employees as $employee) {
            if ($employee->image) {
                $filesToDelete[] = ['name' => $employee->image, 'directory' => 'employees'];
            }

            DB::table('employees')->where('id', $employee->id)->update([
                'name' => 'Former staff member',
                'email' => 'former-staff-'.$employee->id.'@deleted.invalid',
                'phone' => null,
                'salary' => null,
                'max_commission' => null,
                'sale_target' => null,
                'joining_date' => null,
                'leaving_date' => now()->toDateString(),
                'image' => null,
                'status' => false,
                'updated_at' => now(),
            ]);
        }

        $user->syncRoles([]);
        $user->tokens()->delete();
        $user->forceFill([
            'name' => 'Former user',
            'email' => 'deleted-user-'.$userId.'@deleted.invalid',
            'phone' => null,
            'email_verified_at' => null,
            'username' => 'deleted_user_'.$userId,
            'password' => Str::random(80),
            'remember_token' => null,
            'is_active' => false,
            'designation' => null,
            'avatar' => null,
        ])->saveQuietly();

        if (Schema::hasTable('password_reset_tokens')) {
            DB::table('password_reset_tokens')->where('email', $originalEmail)->delete();
        }
        if (Schema::hasTable('profile_otps')) {
            DB::table('profile_otps')->where('user_id', $userId)->delete();
        }
        if (Schema::hasTable('sessions')) {
            DB::table('sessions')->where('user_id', $userId)->delete();
        }
        if (Schema::hasTable('notifications')) {
            DB::table('notifications')
                ->where('notifiable_type', User::class)
                ->where('notifiable_id', $userId)
                ->delete();
        }
        if (Schema::hasTable('user_activities')) {
            DB::table('user_activities')->where('user_id', $userId)->delete();
        }
        if (Schema::hasTable('activity_log')) {
            $hasCauser = Schema::hasColumn('activity_log', 'causer_id')
                && Schema::hasColumn('activity_log', 'causer_type');
            $hasSubject = Schema::hasColumn('activity_log', 'subject_id')
                && Schema::hasColumn('activity_log', 'subject_type');

            if ($hasCauser || $hasSubject) {
                DB::table('activity_log')->where(function ($query) use ($userId, $hasCauser, $hasSubject) {
                    if ($hasCauser) {
                    $query->where(function ($subQuery) use ($userId) {
                        $subQuery->where('causer_type', User::class)->where('causer_id', $userId);
                    });
                    }
                    if ($hasSubject) {
                        $method = $hasCauser ? 'orWhere' : 'where';
                        $query->{$method}(function ($subQuery) use ($userId) {
                            $subQuery->where('subject_type', User::class)->where('subject_id', $userId);
                        });
                    }
                })->delete();
            }
        }
        if (Schema::hasTable('model_has_permissions')) {
            DB::table('model_has_permissions')
                ->where('model_type', User::class)
                ->where('model_id', $userId)
                ->delete();
        }

        return $filesToDelete;
    }
}
