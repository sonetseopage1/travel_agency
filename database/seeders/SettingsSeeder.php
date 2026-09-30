<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        foreach (Setting::DEFAULTS as $key => $value) {
            if ($value === null) {
                continue;
            }

            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        Setting::flush();
    }
}
