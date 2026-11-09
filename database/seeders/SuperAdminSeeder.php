<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'superadmin@gmail.com'],
            [
                'name' => 'Super Admin',
                'username' => 'superadmin',
                'email_verified_at' => now(),
                'password' => bcrypt('12345678'),
                'is_active' => true,
                'remember_token' => Str::random(10),
            ]
        );

        if (! $user->is_active) {
            $user->update(['is_active' => true]);
        }

        if (! $user->hasRole('Super-Admin')) {
            $user->assignRole('Super-Admin');
        }
    }
}
