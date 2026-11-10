<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DemoInstanceSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            GeneralSettingSeeder::class,
            FraudCheckSettingSeeder::class,
            DemoGuestSeeder::class,
            DemoSeeder::class,
        ]);
    }
}
