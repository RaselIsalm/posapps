<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoGuestSeeder extends Seeder
{
    public function run(): void
    {
        $email = (string) config('onepos.demo.email');
        $user = User::withoutEvents(fn () => User::query()->firstOrCreate(
            ['email' => $email],
            [
                'name' => 'ONEPOS Demo Guest',
                'username' => 'onepos-demo-guest',
                'password' => Hash::make(Str::random(96)),
                'email_verified_at' => now(),
                'is_active' => true,
            ],
        ));

        $user->forceFill(['is_active' => true])->saveQuietly();
        $user->syncRoles(['Demo-Guest']);
    }
}
