<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        User::create([
            'name' => 'Admin',
            'email' => 'admin@banglatraveller.com',
            'password' => Hash::make('password123'),
            'is_admin' => true,
            'phone' => '01700000000',
        ]);

        $this->call([
            SettingsSeeder::class,
            DestinationSeeder::class,
            TourSeeder::class,
            ReviewSeeder::class,
            BookingSeeder::class,
            PromoCodeSeeder::class,
        ]);
    }
}
