<?php

namespace Database\Seeders;

use App\Models\PromoCode;
use App\Models\Tour;
use Illuminate\Database\Seeder;

class PromoCodeSeeder extends Seeder
{
    public function run(): void
    {
        $promoCodes = [
            [
                'code' => 'WELCOME10',
                'description' => 'নতুন গ্রাহকদের জন্য ১০% ছাড়',
                'discount_type' => 'percentage',
                'discount_value' => 10,
                'max_discount' => 2000,
                'usage_limit' => 100,
                'valid_from' => now()->subMonth(),
                'valid_until' => now()->addMonths(6),
                'is_active' => true,
                'tours' => [],
            ],
            [
                'code' => 'EID2026',
                'description' => 'ঈদ উপলক্ষে ১৫% ছাড় (সব ট্যুর)',
                'discount_type' => 'percentage',
                'discount_value' => 15,
                'max_discount' => 5000,
                'usage_limit' => 200,
                'valid_from' => now()->subDays(10),
                'valid_until' => now()->addMonth(),
                'is_active' => true,
                'tours' => [],
            ],
            [
                'code' => 'FLAT500',
                'description' => 'নির্দিষ্ট ৫০০ টাকা ছাড়',
                'discount_type' => 'fixed',
                'discount_value' => 500,
                'max_discount' => null,
                'usage_limit' => null,
                'valid_from' => null,
                'valid_until' => null,
                'is_active' => true,
                'tours' => [],
            ],
            [
                'code' => 'HILLS500',
                'description' => 'পাহাড়ি ট্যুরে ৫০০ টাকা ছাড়',
                'discount_type' => 'fixed',
                'discount_value' => 500,
                'max_discount' => null,
                'usage_limit' => 50,
                'valid_from' => null,
                'valid_until' => now()->addMonths(3),
                'is_active' => true,
                'tours' => ['bandarban-hills-tour', 'sajek-valley-adventure'],
            ],
            [
                'code' => 'SEA20',
                'description' => 'সৈক্ত ভ্রমণে ২০% ছাড়',
                'discount_type' => 'percentage',
                'discount_value' => 20,
                'max_discount' => 3000,
                'usage_limit' => 80,
                'valid_from' => now()->subDays(5),
                'valid_until' => now()->addMonths(2),
                'is_active' => true,
                'tours' => ['coxs-bazar-sea-tour', 'coxs-bazar-family-tour', 'sundarban-forest-tour'],
            ],
            [
                'code' => 'FLASH50',
                'description' => 'সীমিত সময়ের ফ্ল্যাশ অফার (মেয়াদ শেষ)',
                'discount_type' => 'percentage',
                'discount_value' => 50,
                'max_discount' => null,
                'usage_limit' => 25,
                'valid_from' => now()->subMonths(3),
                'valid_until' => now()->subMonth(),
                'is_active' => true,
                'tours' => [],
            ],
            [
                'code' => 'INTL3000',
                'description' => 'আন্তর্জাতিক ট্যুরে ৩০০০ টাকা ছাড়',
                'discount_type' => 'fixed',
                'discount_value' => 3000,
                'max_discount' => null,
                'usage_limit' => 30,
                'valid_from' => now(),
                'valid_until' => now()->addMonths(4),
                'is_active' => true,
                'tours' => ['malaysia-tour', 'thailand-gateway-tour'],
            ],
            [
                'code' => 'INACTIVE20',
                'description' => 'নিষ্ক্রিয় কোডের উদাহরণ',
                'discount_type' => 'percentage',
                'discount_value' => 20,
                'max_discount' => null,
                'usage_limit' => null,
                'valid_from' => null,
                'valid_until' => null,
                'is_active' => false,
                'tours' => [],
            ],
        ];

        foreach ($promoCodes as $data) {
            $slugs = $data['tours'];
            unset($data['tours']);

            $promoCode = PromoCode::create($data);

            if ($slugs !== []) {
                $tourIds = Tour::whereIn('slug', $slugs)->pluck('id');
                $promoCode->tours()->sync($tourIds);
            }
        }
    }
}
