<?php

namespace Database\Seeders;

use App\Models\FraudCheckSetting;
use Illuminate\Database\Seeder;

class FraudCheckSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        FraudCheckSetting::updateOrCreate(
            ['id' => 1],
            [
                'api_key' => 'ahMEtynQ5YpvZOInEMONcxC9UIT3qYx0XIy1YjTHAznLWuXeu5TGhFZyKFMN',
                'api_url' => 'https://api.bdcourier.com/courier-check',
            ]
        );
    }
}
