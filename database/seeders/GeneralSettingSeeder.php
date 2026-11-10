<?php

namespace Database\Seeders;

use App\Models\GeneralSettings;
use Illuminate\Database\Seeder;

class GeneralSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            'company_name' => 'Oms Onetech',
            'logo' => 'logo1.png',
            'favicon' => 'favicon.png',
            'currency' => 'BDT',
            'timezone' => 'Europe/Madrid',
            'date_format' => 'Y-m-d',
            'contact_email' => 'info@oms.com',
            'contact_phone' => '123456789',
            'address' => '123, Main Street, Dhaka',
            'language' => 'en',
            'pos_printer_type' => 'thermal',
            'thermal_printer_size' => '80mm',
            'thermal_logo_height' => '40',
            'default_payment_method' => '',
            'print_layout_thermal' => [],
            'print_layout_a4' => [],
            'primary_color' => '#308e87',
            'primary_hover_color' => '#256e69',
            'link_color' => '#308e87',
            'toast_success_color' => '#308e87',
            'primary_light_color' => '#eaf4f3',
        ];

        foreach ($settings as $key => $value) {
            if (! GeneralSettings::where('key_name', $key)->exists()) {
                GeneralSettings::create([
                    'key_name' => $key,
                    'value' => $value,
                    'category' => 'general',
                ]);
            }
        }
    }
}
