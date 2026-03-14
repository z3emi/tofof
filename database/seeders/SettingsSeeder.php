<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingsSeeder extends Seeder
{
    public function run()
    {
        Setting::updateOrCreate(
            ['key' => 'maintenance_mode'],
            ['value' => 'off']
        );

        Setting::updateOrCreate(
            ['key' => 'session_lifetime'],
            ['value' => '120']
        );
    }
}
