<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = [
            [
                'name' => '1 Month Offer',
                'amount' => 21,
                'reg_amount' => 1499,
                'duration_months' => 1,
                'description' => 'Special offer for first-time users',
                'is_first_time_only' => true,
            ],
            [
                'name' => 'Installation + 1 Year',
                'amount' => 9999,
                'reg_amount' => 14999,
                'duration_months' => 12,
                'description' => 'Installation + 1 Year (First Time)',
                'is_first_time_only' => true,
            ],
            [
                'name' => 'Yearly Plan',
                'amount' => 5999,
                'reg_amount' => 9999,
                'duration_months' => 12,
                'description' => 'Standard yearly subscription',
                'is_first_time_only' => false,
            ],
            [
                'name' => '6 Month Plan',
                'amount' => 3899,
                'reg_amount' => 4599,
                'duration_months' => 6,
                'description' => 'Standard 6-month subscription',
                'is_first_time_only' => false,
            ],
            [
                'name' => 'Monthly Plan',
                'amount' => 799,
                'reg_amount' => 1499,
                'duration_months' => 1,
                'description' => 'Standard monthly subscription',
                'is_first_time_only' => false,
            ],
        ];

        foreach ($plans as $plan) {
            \App\Models\Plan::updateOrCreate(['name' => $plan['name']], $plan);
        }
    }
}
