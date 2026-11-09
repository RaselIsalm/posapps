<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string'],
        ]);

        $user = User::query()
            ->where('email', $credentials['email'])
            ->orWhere('username', $credentials['email'])
            ->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password) || ! $user->is_active) {
            return response()->json(['message' => 'The provided credentials are invalid.'], 401);
        }

        $token = $user->createToken('one-pos-mobile', ['*'], now()->addDays(30))->plainTextToken;

        return response()->json([
            'message' => 'Signed in successfully.',
            'data' => [
                'access' => ['token' => $token],
                'user' => $this->userData($user),
            ],
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'message' => 'Current user loaded.',
            'data' => ['user' => $this->userData($request->user())],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json(['message' => 'Signed out successfully.', 'data' => []]);
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();
        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => ['sometimes', 'required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone' => ['sometimes', 'nullable', 'string', 'max:30'],
            'designation' => ['sometimes', 'nullable', 'string', 'max:120'],
            'avatar' => ['sometimes', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_avatar' => ['sometimes', 'boolean'],
        ]);

        $avatarName = null;
        if ($request->hasFile('avatar')) {
            $oldAvatar = $user->avatar;
            $avatar = $request->file('avatar')->store('users', 'public');
            $avatarName = basename($avatar);
            if ($oldAvatar) {
                Storage::disk('public')->delete('users/'.$oldAvatar);
            }
        } elseif ($request->boolean('remove_avatar') && $user->avatar) {
            Storage::disk('public')->delete('users/'.$user->avatar);
        }

        unset($validated['avatar'], $validated['remove_avatar']);
        $user->fill($validated);
        if ($request->hasFile('avatar')) {
            $user->forceFill(['avatar' => $avatarName]);
        } elseif ($request->boolean('remove_avatar')) {
            $user->forceFill(['avatar' => null]);
        }
        $user->save();

        return response()->json([
            'message' => 'Profile updated successfully.',
            'data' => ['user' => $this->userData($user->fresh())],
        ]);
    }

    public function updatePassword(Request $request): JsonResponse
    {
        $request->merge([
            'current_password' => $request->input('current_password', $request->input('old_password')),
            'password' => $request->input('password', $request->input('new_password')),
            'password_confirmation' => $request->input('password_confirmation', $request->input('confirm_password')),
        ]);

        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if (! Hash::check($validated['current_password'], $request->user()->password)) {
            return response()->json(['message' => 'The current password is incorrect.'], 422);
        }

        $request->user()->forceFill(['password' => $validated['password']])->save();

        return response()->json(['message' => 'Password updated successfully.', 'data' => []]);
    }

    private function userData(User $user): array
    {
        $lockedEmployee = Employee::query()
            ->where('user_id', $user->id)
            ->where('status', 1)
            ->first();

        if ($lockedEmployee) {
            $branches = Branch::query()->whereKey($lockedEmployee->branch_id)->where('status', 1)->get(['id', 'name']);
        } elseif ($user->hasAnyRole(['Super-Admin', 'Admin']) || $user->can('view_branches')) {
            $branches = Branch::query()->where('status', 1)->orderBy('name')->get(['id', 'name']);
        } else {
            $branches = Branch::query()->where('manager_id', $user->id)->where('status', 1)->orderBy('name')->get(['id', 'name']);
        }

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'designation' => $user->designation,
            'profile_photo' => $user->profile_photo_url ? url($user->profile_photo_url) : null,
            'roles' => $user->getRoleNames()->values(),
            'permissions' => $user->getAllPermissions()->pluck('name')->values(),
            'branch_id' => $lockedEmployee?->branch_id,
            'branches' => $branches,
        ];
    }
}
